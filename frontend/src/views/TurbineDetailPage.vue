<template>
  <div class="space-y-6">
    <!-- Back Button -->
    <div>
      <button
        @click="$router.push({ name: 'Overview' })"
        class="flex items-center gap-2 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        <span class="font-medium">Back to Overview</span>
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex items-center justify-center py-20">
      <div class="text-center">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-indigo-500 border-t-transparent mb-4"></div>
        <p class="text-slate-600 dark:text-slate-400">Loading turbine data...</p>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="!turbine" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-6">
      <div class="flex items-center gap-3">
        <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div>
          <h3 class="font-semibold text-red-900 dark:text-red-300">Turbine not found</h3>
          <p class="text-sm text-red-700 dark:text-red-400 mt-1">Turbine ID "{{ id }}" does not exist</p>
        </div>
      </div>
    </div>

    <!-- Turbine Details -->
    <div v-else>
      <!-- Header -->
      <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
        <div class="flex items-start justify-between">
          <div>
            <div class="flex items-center gap-3 mb-2">
              <h1 class="text-3xl font-bold text-slate-900 dark:text-white">{{ turbine.id }}</h1>
              <span :class="['px-3 py-1 rounded-full text-sm font-bold', getStatusClass(turbine.status)]">
                {{ turbine.status }}
              </span>
            </div>
            <p class="text-slate-600 dark:text-slate-400 flex items-center gap-2">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
              {{ turbine.location }}
            </p>
          </div>

          <button
            @click="$emit('add-maintenance', turbine)"
            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition-colors flex items-center gap-2"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Log Maintenance
          </button>
        </div>
      </div>

      <!-- Metrics Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
          <div class="flex items-center justify-between mb-2">
            <p class="text-sm text-slate-600 dark:text-slate-400">Power Output</p>
            <svg class="w-5 h-5 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
          </div>
          <p class="text-3xl font-bold text-slate-900 dark:text-white">
            {{ turbine.metrics?.power_mw?.toFixed(2) || '0.00' }} <span class="text-lg text-slate-500">MW</span>
          </p>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
          <div class="flex items-center justify-between mb-2">
            <p class="text-sm text-slate-600 dark:text-slate-400">Wind Speed</p>
            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
            </svg>
          </div>
          <p class="text-3xl font-bold text-slate-900 dark:text-white">
            {{ turbine.metrics?.wind_speed_ms?.toFixed(1) || '0.0' }} <span class="text-lg text-slate-500">m/s</span>
          </p>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
          <div class="flex items-center justify-between mb-2">
            <p class="text-sm text-slate-600 dark:text-slate-400">Rotor Speed</p>
            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
          </div>
          <p class="text-3xl font-bold text-slate-900 dark:text-white">
            {{ turbine.metrics?.rotor_speed_rpm?.toFixed(1) || '0.0' }} <span class="text-lg text-slate-500">RPM</span>
          </p>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
          <div class="flex items-center justify-between mb-2">
            <p class="text-sm text-slate-600 dark:text-slate-400">Active Alarms</p>
            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
          </div>
          <p class="text-3xl font-bold text-slate-900 dark:text-white">
            {{ turbine.alarmSummary?.total || 0 }}
          </p>
        </div>
      </div>

      <!-- Alarms Section -->
      <div v-if="turbineAlarms.length > 0" class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
        <div class="p-6 border-b border-slate-200 dark:border-slate-700">
          <h2 class="text-xl font-bold text-slate-900 dark:text-white">Active Alarms</h2>
        </div>
        <div class="divide-y divide-slate-200 dark:divide-slate-700">
          <div
            v-for="alarm in turbineAlarms"
            :key="alarm.id"
            class="p-6 hover:bg-slate-50 dark:hover:bg-slate-700/50 cursor-pointer transition-colors"
            @click="$emit('show-alarm', alarm)"
          >
            <div class="flex items-start justify-between">
              <div class="flex-1">
                <div class="flex items-center gap-3 mb-2">
                  <span :class="['px-2 py-1 rounded text-xs font-bold', getPriorityClass(alarm.priority)]">
                    {{ alarm.priority }}
                  </span>
                  <h3 class="font-semibold text-slate-900 dark:text-white">{{ alarm.title }}</h3>
                </div>
                <p class="text-sm text-slate-600 dark:text-slate-400 mb-2">{{ alarm.description }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-500">{{ alarm.time }}</p>
              </div>
              <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Placeholder for charts/graphs -->
      <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
        <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-4">Performance History</h2>
        <div class="h-64 flex items-center justify-center text-slate-400">
          <p>Charts coming soon...</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useScadaService } from '@/composables/api.js'

const props = defineProps({
  id: {
    type: String,
    required: true
  }
})

defineEmits(['show-alarm', 'add-maintenance'])

const { turbineStore, alarmStore } = useScadaService()

// Find the turbine by ID
const turbine = computed(() => 
  turbineStore.turbines.find(t => t.id === props.id)
)

const loading = computed(() => turbineStore.loading)

// Get alarms for this specific turbine
const turbineAlarms = computed(() => 
  alarmStore.alarms.filter(alarm => alarm.turbine === props.id)
)

// Helper functions
const getStatusClass = (status) => {
  const classes = {
    running: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
    idle: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
    maintenance: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
    stopped: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    error: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'
  }
  return classes[status] || 'bg-slate-100 text-slate-700'
}

const getPriorityClass = (priority) => {
  const classes = {
    Critical: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    Major: 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
    Warning: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
    Minor: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'
  }
  return classes[priority] || 'bg-slate-100 text-slate-700'
}
</script>