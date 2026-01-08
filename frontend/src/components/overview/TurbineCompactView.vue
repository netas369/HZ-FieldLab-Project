<template>
  <div class="overflow-x-auto">
    <table class="w-full">
      <thead class="border-b border-slate-200 dark:border-slate-700">
        <tr>
          <th
            class="text-left py-3 px-4 text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider"
          >
            Turbine
          </th>
          <th
            class="text-left py-3 px-4 text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider"
          >
            Location
          </th>
          <th
            class="text-left py-3 px-4 text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider"
          >
            Status
          </th>

          <th
            class="text-center py-3 px-4 text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider"
          >
            Health
          </th>

          <th
            class="text-right py-3 px-4 text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider"
          >
            Power
          </th>
          <th
            class="text-right py-3 px-4 text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider"
          >
            Wind
          </th>
          <th
            class="text-right py-3 px-4 text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider"
          >
            Rotor
          </th>
          <th
            class="text-center py-3 px-4 text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider"
          >
            Alarms
          </th>
          <th class="w-8" />
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="turbine in turbines"
          :key="turbine.id"
          class="border-b border-slate-100 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-900/50 cursor-pointer transition-colors group"
          @click="$emit('select', turbine)"
        >
          <td class="py-3 px-4">
            <span class="font-semibold text-slate-900 dark:text-white">{{ turbine.id }}</span>
          </td>

          <td class="py-3 px-4 text-sm text-slate-600 dark:text-slate-400">
            {{ turbine.location }}
          </td>

          <td class="py-3 px-4">
            <span
              :class="[
                'inline-flex items-center gap-1 text-xs font-semibold px-2 py-1 rounded-full',
                getStatusClass(turbine.status),
              ]"
            >
              <span :class="['w-1.5 h-1.5 rounded-full', getStatusBadgeColor(turbine.status)]" />
              {{ getStatusLabel(turbine.status) }}
            </span>
          </td>

          <td class="py-3 px-4 text-center">
            <div
              v-if="turbine.healthData"
              class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full border bg-white dark:bg-slate-800"
              :class="getHealthBorderColor(turbine.healthData.overall_health.overall_health_score)"
            >
              <span
                class="font-bold text-xs font-mono"
                :class="getHealthTextColor(turbine.healthData.overall_health.overall_health_score)"
              >
                {{ turbine.healthData.overall_health.overall_health_score.toFixed(0) }}%
              </span>
              <span
                v-if="turbine.healthData.overall_health.critical_components.length > 0"
                class="flex h-2 w-2 relative"
                title="Critical Component Issue"
              >
                <span
                  class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"
                />
                <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500" />
              </span>
            </div>
            <span v-else class="text-xs text-slate-400 italic">--</span>
          </td>

          <td class="py-3 px-4 text-right font-semibold text-slate-900 dark:text-white">
            {{ turbine.metrics?.power_mw?.toFixed(1) || '0.0' }}
            <span class="text-xs font-normal text-slate-500 dark:text-slate-400">MW</span>
          </td>

          <td class="py-3 px-4 text-right text-slate-700 dark:text-slate-300">
            {{ turbine.metrics?.wind_ms?.toFixed(1) || '-' }}
            <span class="text-xs text-slate-500 dark:text-slate-400">m/s</span>
          </td>

          <td class="py-3 px-4 text-right text-slate-700 dark:text-slate-300">
            {{ turbine.metrics?.rotor_rpm?.toFixed(0) || '-' }}
            <span class="text-xs text-slate-500 dark:text-slate-400">RPM</span>
          </td>

          <td class="py-3 px-4 text-center">
            <span
              v-if="turbine.alarmSummary?.total > 0"
              class="inline-flex items-center justify-center w-6 h-6 bg-red-600 text-white text-xs font-bold rounded-full shadow-sm"
              >{{ turbine.alarmSummary.total }}</span
            >
            <span v-else class="text-slate-400">—</span>
          </td>

          <td class="py-3 px-4">
            <svg
              class="w-5 h-5 text-slate-400 group-hover:text-slate-600 dark:group-hover:text-slate-300 group-hover:translate-x-0.5 transition-all"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M9 5l7 7-7 7"
              />
            </svg>
          </td>
        </tr>
      </tbody>
    </table>

    <div v-if="turbines.length === 0" class="text-center py-12">
      <svg
        class="w-12 h-12 mx-auto text-slate-300 dark:text-slate-600 mb-3"
        fill="none"
        stroke="currentColor"
        viewBox="0 0 24 24"
      >
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"
        />
      </svg>
      <p class="text-sm text-slate-600 dark:text-slate-400">No turbines to display</p>
    </div>
  </div>
</template>

<script setup>
defineProps({
  turbines: { type: Array, required: true, default: () => [] },
})
defineEmits(['select'])

// Status Helpers (Existing)
const getStatusBadgeColor = (s) =>
  ({
    running: 'bg-green-500',
    idle: 'bg-blue-500',
    maintenance: 'bg-amber-500',
    stopped: 'bg-red-500',
    error: 'bg-red-500',
  })[s] || 'bg-slate-400'
const getStatusClass = (s) =>
  ({
    running: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
    idle: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
    maintenance: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
    stopped: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    error: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
  })[s] || 'bg-slate-100 text-slate-700'
const getStatusLabel = (s) =>
  ({
    running: 'Running',
    idle: 'Idle',
    maintenance: 'Maintenance',
    stopped: 'Stopped',
    error: 'Error',
  })[s] || 'Unknown'

// NEW: Health Helpers
const getHealthTextColor = (score) => {
  if (score >= 90) return 'text-green-600 dark:text-green-400'
  if (score >= 70) return 'text-amber-600 dark:text-amber-400'
  return 'text-red-600 dark:text-red-400'
}

const getHealthBorderColor = (score) => {
  if (score >= 90) return 'border-green-200 dark:border-green-800'
  if (score >= 70) return 'border-amber-200 dark:border-amber-800'
  return 'border-red-200 dark:border-red-800'
}
</script>

<style scoped>
tbody tr {
  transition: background-color 150ms ease-in-out;
}
</style>
