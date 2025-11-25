<template>
  <div class="flex flex-col h-full">
    <!-- Header Section -->
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 mb-6">
      <div class="flex items-center justify-between mb-6">
        <div>
          <h2 class="text-2xl font-bold text-slate-900 dark:text-white">
            Active Alarms
          </h2>
          <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
            {{ stats.total }} total •
            <span :class="stats.critical > 0 ? 'text-red-600 dark:text-red-400 font-semibold' : ''">
              {{ stats.critical }} critical
            </span> •
            {{ stats.unacknowledged }} unacknowledged
          </p>
        </div>

        <div class="flex items-center gap-3">
          <!-- View Toggle -->
          <div class="flex bg-slate-100 dark:bg-slate-700 rounded-lg p-1">
            <button
                v-for="view in ['list', 'grid']"
                :key="view"
                @click="currentView = view"
                :class="[
                'px-3 py-1.5 rounded-md text-sm font-medium transition-all',
                currentView === view
                  ? 'bg-white dark:bg-slate-600 text-slate-900 dark:text-white shadow-sm'
                  : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'
              ]"
                :title="`${view} view`"
            >
              <svg v-if="view === 'list'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
              </svg>
              <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
              </svg>
            </button>
          </div>

          <!-- Sort Dropdown -->
          <select
              v-model="sortBy"
              class="px-3 py-2 bg-slate-100 dark:bg-slate-700 border-0 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-300 focus:ring-2 focus:ring-indigo-500 cursor-pointer"
          >
            <option value="time">Most Recent</option>
            <option value="priority">Priority</option>
            <option value="turbine">Turbine</option>
          </select>

          <!-- Refresh Button -->
          <button
              @click="$emit('refresh')"
              class="p-2 rounded-lg bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 transition-colors"
              title="Refresh alarms"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Priority Filter Chips -->
      <div class="flex flex-wrap gap-2">
        <button
            v-for="filter in filterOptions"
            :key="filter.value"
            @click="toggleFilter(filter.value)"
            :class="[
            'px-4 py-2 rounded-lg text-sm font-medium transition-all transform active:scale-95',
            isFilterActive(filter.value)
              ? filter.activeClass
              : 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600'
          ]"
        >
          <span class="flex items-center gap-2">
            {{ filter.label }}
            <span
                v-if="filter.value !== 'all'"
                :class="[
                'px-2 py-0.5 rounded-full text-xs font-bold',
                isFilterActive(filter.value) ? 'bg-white/20' : 'bg-slate-200 dark:bg-slate-600'
              ]"
            >
              {{ getCountForPriority(filter.value) }}
            </span>
          </span>
        </button>

        <!-- Acknowledged Filter Toggle -->
        <button
            @click="showAcknowledged = !showAcknowledged"
            :class="[
            'px-4 py-2 rounded-lg text-sm font-medium transition-all',
            showAcknowledged
              ? 'bg-green-500 text-white'
              : 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600'
          ]"
        >
          <span class="flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ showAcknowledged ? 'Hide' : 'Show' }} Acknowledged
          </span>
        </button>
      </div>
    </div>

    <!-- Alarms Content -->
    <div class="flex-1 overflow-hidden">
      <transition name="fade" mode="out-in">
        <!-- Empty State -->
        <div
            v-if="filteredAndSortedAlarms.length === 0"
            class="flex flex-col items-center justify-center h-full bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-12"
        >
          <div class="w-24 h-24 bg-green-100 dark:bg-green-900/20 rounded-full flex items-center justify-center mb-4">
            <svg class="w-12 h-12 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <h3 class="text-xl font-semibold text-slate-900 dark:text-white mb-2">
            All Clear!
          </h3>
          <p class="text-slate-600 dark:text-slate-400 text-center max-w-md">
            {{ activeFilters.length > 1 || !showAcknowledged ? 'No alarms match your current filters' : 'No active alarms at the moment' }}
          </p>
          <button
              v-if="activeFilters.length > 1 || !showAcknowledged"
              @click="resetFilters"
              class="mt-4 px-4 py-2 bg-indigo-500 text-white rounded-lg hover:bg-indigo-600 transition-colors"
          >
            Clear Filters
          </button>
        </div>

        <!-- List View -->
        <div
            v-else-if="currentView === 'list'"
            class="space-y-3 overflow-y-auto h-full pr-2 scrollbar-thin"
        >
          <transition-group name="list" tag="div">
            <AlarmCard
                v-for="alarm in filteredAndSortedAlarms"
                :key="alarm.id"
                :alarm="alarm"
                view-mode="list"
                @click="handleAlarmClick(alarm)"
                @acknowledge="handleAcknowledge(alarm)"
                @toggle-details="toggleAlarmDetails(alarm.id)"
                :show-details="expandedAlarms.has(alarm.id)"
            />
          </transition-group>
        </div>

        <!-- Grid View -->
        <div
            v-else
            class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 overflow-y-auto h-full pr-2 scrollbar-thin"
        >
          <transition-group name="grid" tag="div" class="contents">
            <AlarmCard
                v-for="alarm in filteredAndSortedAlarms"
                :key="alarm.id"
                :alarm="alarm"
                view-mode="grid"
                @click="handleAlarmClick(alarm)"
                @acknowledge="handleAcknowledge(alarm)"
            />
          </transition-group>
        </div>
      </transition>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import AlarmCard from './AlarmCard.vue'

const props = defineProps({
  alarms: {
    type: Array,
    required: true,
    default: () => []
  },
  filters: {
    type: Array,
    default: () => ['All', 'Critical', 'Major', 'Warning', 'Minor']
  },
  initialFilter: {
    type: String,
    default: 'All'
  },
  loading: {
    type: Boolean,
    default: false
  },
  error: {
    type: String,
    default: null
  }
})

const emit = defineEmits(['show-alarm', 'acknowledge-alarm', 'filter-change', 'refresh'])

// ============================================================================
// STATE
// ============================================================================

const currentView = ref('list')
const sortBy = ref('time')
const activeFilters = ref(['all'])
const expandedAlarms = ref(new Set())
const showAcknowledged = ref(false)

// Filter configuration
const filterOptions = [
  {
    value: 'all',
    label: 'All Alarms',
    activeClass: 'bg-slate-900 dark:bg-slate-600 text-white shadow-lg'
  },
  {
    value: 'Critical',
    label: 'Critical',
    activeClass: 'bg-red-600 text-white shadow-lg shadow-red-500/30'
  },
  {
    value: 'Major',
    label: 'Major',
    activeClass: 'bg-orange-500 text-white shadow-lg shadow-orange-500/30'
  },
  {
    value: 'Warning',
    label: 'Warning',
    activeClass: 'bg-amber-500 text-white shadow-lg shadow-amber-500/30'
  },
  {
    value: 'Minor',
    label: 'Minor',
    activeClass: 'bg-blue-500 text-white shadow-lg shadow-blue-500/30'
  }
]

// ============================================================================
// COMPUTED
// ============================================================================

const stats = computed(() => {
  const critical = props.alarms.filter(a => a.priority === 'Critical').length
  const unacknowledged = props.alarms.filter(a => !a.acknowledged).length

  return {
    total: props.alarms.length,
    critical,
    unacknowledged
  }
})

const filteredAndSortedAlarms = computed(() => {
  let result = [...props.alarms]

  // Filter by acknowledged status
  if (!showAcknowledged.value) {
    result = result.filter(alarm => !alarm.acknowledged)
  }

  // Apply priority filters
  if (!activeFilters.value.includes('all')) {
    result = result.filter(alarm => activeFilters.value.includes(alarm.priority))
  }

  // Apply sorting
  result.sort((a, b) => {
    switch (sortBy.value) {
      case 'time':
        return new Date(b.time) - new Date(a.time)

      case 'priority': {
        const priorityOrder = { Critical: 0, Major: 1, Warning: 2, Minor: 3 }
        return priorityOrder[a.priority] - priorityOrder[b.priority]
      }

      case 'turbine':
        return a.turbine.localeCompare(b.turbine)

      default:
        return 0
    }
  })

  return result
})

// ============================================================================
// METHODS
// ============================================================================

const toggleFilter = (filterValue) => {
  if (filterValue === 'all') {
    activeFilters.value = ['all']
  } else {
    // Remove 'all' if selecting specific filter
    activeFilters.value = activeFilters.value.filter(f => f !== 'all')

    const index = activeFilters.value.indexOf(filterValue)
    if (index > -1) {
      activeFilters.value.splice(index, 1)
      // If no filters left, select 'all'
      if (activeFilters.value.length === 0) {
        activeFilters.value = ['all']
      }
    } else {
      activeFilters.value.push(filterValue)
    }
  }

  emit('filter-change', activeFilters.value)
}

const isFilterActive = (filterValue) => {
  return activeFilters.value.includes(filterValue)
}

const resetFilters = () => {
  activeFilters.value = ['all']
  showAcknowledged.value = false
  emit('filter-change', ['all'])
}

const getCountForPriority = (priority) => {
  if (priority === 'all') return props.alarms.length
  return props.alarms.filter(a => a.priority === priority).length
}

const handleAlarmClick = (alarm) => {
  emit('show-alarm', alarm)
}

const handleAcknowledge = (alarm) => {
  emit('acknowledge-alarm', alarm)
}

const toggleAlarmDetails = (alarmId) => {
  if (expandedAlarms.value.has(alarmId)) {
    expandedAlarms.value.delete(alarmId)
  } else {
    expandedAlarms.value.add(alarmId)
  }
}

// ============================================================================
// WATCHERS
// ============================================================================

watch(() => props.initialFilter, (newFilter) => {
  if (newFilter && newFilter !== 'All') {
    activeFilters.value = [newFilter]
  }
}, { immediate: true })
</script>

<style scoped>
/* Scrollbar styling */
.scrollbar-thin::-webkit-scrollbar {
  width: 6px;
}

.scrollbar-thin::-webkit-scrollbar-track {
  @apply bg-slate-100 dark:bg-slate-700 rounded-full;
}

.scrollbar-thin::-webkit-scrollbar-thumb {
  @apply bg-slate-300 dark:bg-slate-600 rounded-full hover:bg-slate-400 dark:hover:bg-slate-500;
}

/* List transitions */
.list-enter-active,
.list-leave-active {
  transition: all 0.3s ease;
}

.list-enter-from {
  opacity: 0;
  transform: translateX(-20px);
}

.list-leave-to {
  opacity: 0;
  transform: translateX(20px);
}

.list-move {
  transition: transform 0.3s ease;
}

/* Grid transitions */
.grid-enter-active,
.grid-leave-active {
  transition: all 0.3s ease;
}

.grid-enter-from {
  opacity: 0;
  transform: scale(0.9);
}

.grid-leave-to {
  opacity: 0;
  transform: scale(0.9);
}

.grid-move {
  transition: transform 0.3s ease;
}

/* Fade transition */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>