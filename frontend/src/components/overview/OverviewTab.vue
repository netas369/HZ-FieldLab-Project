<template>
  <div class="space-y-6">
    <!-- Loading State -->
    <div v-if="loading" class="flex items-center justify-center py-20">
      <div class="text-center">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-indigo-500 border-t-transparent mb-4"></div>
        <p class="text-slate-600 dark:text-slate-400">Loading fleet data...</p>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-6">
      <div class="flex items-center gap-3">
        <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div>
          <h3 class="font-semibold text-red-900 dark:text-red-300">Failed to load data</h3>
          <p class="text-sm text-red-700 dark:text-red-400 mt-1">{{ error }}</p>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <template v-else>
      <!-- Fleet Statistics Cards -->
      <FleetStatsCards :stats="fleetStats" />

      <!-- Active Alarms Banner -->
      <div v-if="totalAlarms > 0" class="bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 rounded-lg p-4">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <svg class="w-6 h-6 text-red-600 dark:text-red-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <div>
              <h4 class="font-semibold text-red-900 dark:text-red-300">Active Alarms Detected</h4>
              <p class="text-sm text-red-700 dark:text-red-400">
                {{ turbinesWithAlarms }} turbine{{ turbinesWithAlarms > 1 ? 's' : '' }} with {{ totalAlarms }} total alarm{{ totalAlarms > 1 ? 's' : '' }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Turbine Fleet Section -->
      <section class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
        <!-- Section Header -->
        <div class="p-6 border-b border-slate-200 dark:border-slate-700">
          <div class="flex items-center justify-between mb-4">
            <div>
              <h2 class="text-xl font-bold text-slate-900 dark:text-white">Turbine Fleet</h2>
              <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
                {{ filteredAndSortedTurbines.length }} of {{ turbines.length }} turbine{{ turbines.length > 1 ? 's' : '' }}
                <span v-if="hasActiveFilters" class="text-indigo-600 dark:text-indigo-400">• Filtered</span>
              </p>
            </div>

            <!-- View Mode Toggle -->
            <div class="flex gap-2">
              <div class="flex bg-slate-100 dark:bg-slate-700 rounded-lg p-1">
                <button
                  v-for="view in viewModes"
                  :key="view.value"
                  @click="viewMode = view.value"
                  :class="[
                    'p-2 rounded-md transition-all',
                    viewMode === view.value
                      ? 'bg-white dark:bg-slate-600 text-slate-900 dark:text-white shadow-sm'
                      : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'
                  ]"
                  :title="view.label"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="view.iconPath" />
                  </svg>
                </button>
              </div>
            </div>
          </div>

          <!-- Filters Bar -->
          <FleetFiltersBar
            v-model:search-query="filters.searchQuery"
            v-model:status-filters="filters.statusFilters"
            v-model:show-only-with-alarms="filters.showOnlyWithAlarms"
            v-model:sort-by="filters.sortBy"
            :status-options="statusOptions"
            @clear-filters="clearFilters"
          />
        </div>

        <!-- Turbines Display Area -->
        <div class="p-6">
          <!-- Empty State -->
          <div v-if="filteredAndSortedTurbines.length === 0" class="text-center py-16">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-100 dark:bg-slate-800 mb-4">
              <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-2">No turbines found</h3>
            <p class="text-slate-600 dark:text-slate-400 mb-4">
              {{ hasActiveFilters ? 'Try adjusting your filters' : 'No turbines available' }}
            </p>
            <button
              v-if="hasActiveFilters"
              @click="clearFilters"
              class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition-colors"
            >
              Clear Filters
            </button>
          </div>

          <!-- Dynamic View Component -->
          <component
            v-else
            :is="currentViewComponent"
            :turbines="filteredAndSortedTurbines"
            @select="handleTurbineSelect"
          />
        </div>
      </section>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, reactive } from 'vue'
import FleetStatsCards from './FleetStatsCards.vue'
import FleetFiltersBar from './FleetFiltersBar.vue'
import TurbineGridView from './TurbineGridView.vue'
import TurbineListView from './TurbineListView.vue'
import TurbineCompactView from './TurbineCompactView.vue'

// Props
const props = defineProps({
  turbines: { type: Array, default: () => [] },
  loading: { type: Boolean, default: false },
  error: { type: String, default: null }
})

// Emits
const emit = defineEmits(['select-turbine'])

// ============================================================================
// STATE
// ============================================================================

const viewMode = ref('grid')

const filters = reactive({
  searchQuery: '',
  statusFilters: [],
  showOnlyWithAlarms: false,
  sortBy: 'id'
})

// View modes configuration (fixed - using iconPath instead of template objects)
const viewModes = [
  {
    value: 'grid',
    label: 'Grid view',
    iconPath: 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z'
  },
  {
    value: 'list',
    label: 'List view',
    iconPath: 'M4 6h16M4 12h16M4 18h16'
  },
  {
    value: 'compact',
    label: 'Compact view',
    iconPath: 'M4 8h16M4 16h16'
  }
]

// Status options for filter
const statusOptions = computed(() => {
  const counts = props.turbines.reduce((acc, t) => {
    acc[t.status] = (acc[t.status] || 0) + 1
    return acc
  }, {})

  return [
    { value: 'running', label: 'Running', color: 'bg-green-500', count: counts.running || 0 },
    { value: 'idle', label: 'Idle', color: 'bg-blue-500', count: counts.idle || 0 },
    { value: 'maintenance', label: 'Maintenance', color: 'bg-amber-500', count: counts.maintenance || 0 },
    { value: 'stopped', label: 'Stopped', color: 'bg-red-500', count: counts.stopped || 0 },
    { value: 'error', label: 'Error', color: 'bg-red-600', count: counts.error || 0 }
  ]
})

// ============================================================================
// COMPUTED PROPERTIES
// ============================================================================

const filteredTurbines = computed(() => {
  let result = [...props.turbines]
  
  // Apply search filter
  if (filters.searchQuery) {
    const query = filters.searchQuery.toLowerCase()
    result = result.filter(t =>
      t.id.toLowerCase().includes(query) || 
      t.location.toLowerCase().includes(query)
    )
  }
  
  // Apply status filter
  if (filters.statusFilters.length > 0) {
    result = result.filter(t => filters.statusFilters.includes(t.status))
  }
  
  // Apply alarms filter
  if (filters.showOnlyWithAlarms) {
    result = result.filter(t => t.alarmSummary?.total > 0)
  }
  
  return result
})

const filteredAndSortedTurbines = computed(() => {
  const turbines = [...filteredTurbines.value]
  
  switch (filters.sortBy) {
    case 'power':
      return turbines.sort((a, b) => (b.scadaData.power_mw || 0) - (a.scadaData.power_mw || 0))
    
    case 'alarms':
      return turbines.sort((a, b) => (b.alarmSummary?.total || 0) - (a.alarmSummary?.total || 0))
    
    case 'status':
      const statusOrder = { error: 0, stopped: 1, maintenance: 2, idle: 3, running: 4 }
      return turbines.sort((a, b) => (statusOrder[a.status] || 5) - (statusOrder[b.status] || 5))
    
    case 'id':
    default:
      return turbines.sort((a, b) => a.id.localeCompare(b.id))
  }
})

const hasActiveFilters = computed(() => 
  filters.searchQuery || 
  filters.statusFilters.length > 0 || 
  filters.showOnlyWithAlarms
)

const currentViewComponent = computed(() => {
  const viewMap = {
    grid: TurbineGridView,
    list: TurbineListView,
    compact: TurbineCompactView
  }
  return viewMap[viewMode.value] || TurbineGridView
})

// Stats calculations
const runningCount = computed(() =>
  props.turbines.filter(t => t.status === 'running').length
)

const totalAlarms = computed(() => 
  props.turbines.reduce((sum, t) => sum + (t.alarmSummary?.total || 0), 0)
)

const turbinesWithAlarms = computed(() =>
  props.turbines.filter(t => t.alarmSummary?.total > 0).length
)

const totalPowerOutput = computed(() => {
  const total = props.turbines.reduce((sum, t) => {
    return sum + (t.metrics?.power_mw || 0)
  }, 0)
  return `${total.toFixed(1)} MW`
})

const fleetStats = computed(() => [
  {
    label: 'Total Turbines',
    value: props.turbines.length,
    badge: 'Active',
    bgColor: 'bg-blue-500',
    badgeColor: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
    iconPath: 'M19.428 15.428a2 2 0 00-1.022-.547l-2.363-.44A2 2 0 0115 13V6a2 2 0 00-2-2h-1a2 2 0 00-2 2v7a2 2 0 01-1.022 1.781l-2.363.441a2 2 0 00-1.022.547l-3 3a2 2 0 000 2.828l.5.5a2 2 0 002.828 0l3-3a2 2 0 011.022-.547l2.363-.44A2 2 0 0012 13v-1h1v1a2 2 0 001.978 1.781l2.363.441a2 2 0 011.022.547l3 3a2 2 0 002.828 0l.5-.5a2 2 0 000-2.828l-3-3z'
  },
  {
    label: 'Running',
    value: runningCount.value,
    badge: `${props.turbines.length > 0 ? Math.round(runningCount.value / props.turbines.length * 100) : 0}%`,
    bgColor: 'bg-green-500',
    badgeColor: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
    iconPath: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'
  },
  {
    label: 'Total Output',
    value: totalPowerOutput.value,
    badge: 'Live',
    bgColor: 'bg-violet-500',
    badgeColor: 'bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-400',
    iconPath: 'M13 10V3L4 14h7v7l9-11h-7z'
  },
  {
    label: 'Active Alarms',
    value: totalAlarms.value,
    badge: 'Fleet',
    bgColor: totalAlarms.value > 0 ? 'bg-red-500' : 'bg-slate-500',
    badgeColor: totalAlarms.value > 0 
      ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' 
      : 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300',
    iconPath: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'
  }
])

// ============================================================================
// METHODS
// ============================================================================

const handleTurbineSelect = (turbine) => {
  emit('select-turbine', turbine);
}

const clearFilters = () => {
  filters.searchQuery = ''
  filters.statusFilters = []
  filters.showOnlyWithAlarms = false
  filters.sortBy = 'id'
}
</script>