<template>
  <div class="space-y-3">
    <!-- Search & Filters Bar -->
    <div class="flex flex-col sm:flex-row gap-3">
      <!-- Search Input -->
      <div class="relative flex-1">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
          <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
            />
          </svg>
        </div>
        <input
          :value="searchQuery"
          type="text"
          placeholder="Search turbines by ID or location..."
          class="w-full pl-10 pr-10 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
          @input="$emit('update:searchQuery', $event.target.value)"
        />
        <button
          v-if="searchQuery"
          class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-300"
          @click="$emit('update:searchQuery', '')"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M6 18L18 6M6 6l12 12"
            />
          </svg>
        </button>
      </div>

      <!-- Status Filter Dropdown -->
      <div ref="statusDropdown" class="relative">
        <button
          :class="[
            'flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-medium transition-all border',
            statusFilters.length > 0
              ? 'bg-indigo-50 dark:bg-indigo-900/20 border-indigo-300 dark:border-indigo-700 text-indigo-700 dark:text-indigo-300'
              : 'bg-slate-50 dark:bg-slate-900 border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800',
          ]"
          @click="toggleStatusFilter"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"
            />
          </svg>
          <span>Status</span>
          <span
            v-if="statusFilters.length > 0"
            class="px-2 py-0.5 bg-indigo-600 dark:bg-indigo-500 text-white text-xs font-bold rounded-full"
          >
            {{ statusFilters.length }}
          </span>
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M19 9l-7 7-7-7"
            />
          </svg>
        </button>

        <!-- Dropdown Menu -->
        <div
          v-if="showStatusDropdown"
          class="absolute right-0 mt-2 w-56 bg-white dark:bg-slate-800 rounded-lg shadow-xl border border-slate-200 dark:border-slate-700 py-2 z-50"
        >
          <label
            v-for="option in statusOptions"
            :key="option.value"
            class="flex items-center gap-3 px-4 py-2 hover:bg-slate-50 dark:hover:bg-slate-700 cursor-pointer transition-colors"
          >
            <input
              type="checkbox"
              :value="option.value"
              :checked="statusFilters.includes(option.value)"
              class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500"
              @change="toggleStatusOption(option.value)"
            />
            <div class="flex items-center gap-2 flex-1">
              <div :class="['w-3 h-3 rounded-full', option.color]" />
              <span class="text-sm text-slate-700 dark:text-slate-300">{{ option.label }}</span>
            </div>
            <span class="text-xs text-slate-500 dark:text-slate-400">
              {{ option.count }}
            </span>
          </label>
        </div>
      </div>

      <!-- Alarm Filter Toggle -->
      <button
        :class="[
          'flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-medium transition-all border',
          showOnlyWithAlarms
            ? 'bg-red-50 dark:bg-red-900/20 border-red-300 dark:border-red-700 text-red-700 dark:text-red-300'
            : 'bg-slate-50 dark:bg-slate-900 border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800',
        ]"
        @click="$emit('update:showOnlyWithAlarms', !showOnlyWithAlarms)"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
          />
        </svg>
        <span class="hidden sm:inline">{{ showOnlyWithAlarms ? 'With Alarms' : 'All' }}</span>
      </button>

      <!-- Sort Dropdown -->
      <select
        :value="sortBy"
        class="px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all"
        @change="$emit('update:sortBy', $event.target.value)"
      >
        <option value="id">Sort by ID</option>
        <option value="power">Sort by Power</option>
        <option value="alarms">Sort by Alarms</option>
        <option value="status">Sort by Status</option>
      </select>

      <!-- Clear Filters -->
      <button
        v-if="hasActiveFilters"
        class="px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all"
        title="Clear all filters"
        @click="$emit('clear-filters')"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M6 18L18 6M6 6l12 12"
          />
        </svg>
      </button>
    </div>

    <!-- Active Filters Tags -->
    <div v-if="hasActiveFilters" class="flex flex-wrap gap-2">
      <span
        v-for="status in statusFilters"
        :key="status"
        class="inline-flex items-center gap-1.5 px-3 py-1 bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 text-sm font-medium rounded-full"
      >
        {{ statusOptions.find((s) => s.value === status)?.label }}
        <button
          class="hover:bg-indigo-200 dark:hover:bg-indigo-800 rounded-full p-0.5"
          @click="removeStatusFilter(status)"
        >
          <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M6 18L18 6M6 6l12 12"
            />
          </svg>
        </button>
      </span>
      <span
        v-if="showOnlyWithAlarms"
        class="inline-flex items-center gap-1.5 px-3 py-1 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 text-sm font-medium rounded-full"
      >
        With Alarms
        <button
          class="hover:bg-red-200 dark:hover:bg-red-800 rounded-full p-0.5"
          @click="$emit('update:showOnlyWithAlarms', false)"
        >
          <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M6 18L18 6M6 6l12 12"
            />
          </svg>
        </button>
      </span>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  searchQuery: { type: String, default: '' },
  statusFilters: { type: Array, default: () => [] },
  showOnlyWithAlarms: { type: Boolean, default: false },
  sortBy: { type: String, default: 'id' },
  statusOptions: { type: Array, required: true },
})

const emit = defineEmits([
  'update:searchQuery',
  'update:statusFilters',
  'update:showOnlyWithAlarms',
  'update:sortBy',
  'clear-filters',
])

const showStatusDropdown = ref(false)
const statusDropdown = ref(null)

const hasActiveFilters = computed(
  () => props.searchQuery || props.statusFilters.length > 0 || props.showOnlyWithAlarms
)

const toggleStatusFilter = () => {
  showStatusDropdown.value = !showStatusDropdown.value
}

const toggleStatusOption = (value) => {
  const newFilters = props.statusFilters.includes(value)
    ? props.statusFilters.filter((f) => f !== value)
    : [...props.statusFilters, value]
  emit('update:statusFilters', newFilters)
}

const removeStatusFilter = (value) => {
  emit(
    'update:statusFilters',
    props.statusFilters.filter((f) => f !== value)
  )
}

// Close dropdown when clicking outside
const handleClickOutside = (event) => {
  if (statusDropdown.value && !statusDropdown.value.contains(event.target)) {
    showStatusDropdown.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>
