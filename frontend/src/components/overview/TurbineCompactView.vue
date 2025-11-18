<template>
  <div class="overflow-x-auto">
    <table class="w-full">
      <thead class="border-b border-slate-200 dark:border-slate-700">
        <tr>
          <th class="text-left py-3 px-4 text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">
            Turbine
          </th>
          <th class="text-left py-3 px-4 text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">
            Location
          </th>
          <th class="text-left py-3 px-4 text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">
            Status
          </th>
          <th class="text-right py-3 px-4 text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">
            Power
          </th>
          <th class="text-right py-3 px-4 text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">
            Wind
          </th>
          <th class="text-right py-3 px-4 text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">
            Rotor
          </th>
          <th class="text-center py-3 px-4 text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">
            Alarms
          </th>
          <th class="w-8"></th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="turbine in turbines"
          :key="turbine.id"
          @click="$emit('select', turbine)"
          class="border-b border-slate-100 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-900/50 cursor-pointer transition-colors group"
        >
          <!-- Turbine ID with Icon -->
          <td class="py-3 px-4">
              <span class="font-semibold text-slate-900 dark:text-white">{{ turbine.id }}</span>
          </td>

          <!-- Location -->
          <td class="py-3 px-4 text-sm text-slate-600 dark:text-slate-400">
            {{ turbine.location }}
          </td>

          <!-- Status -->
          <td class="py-3 px-4">
            <span :class="['inline-flex items-center gap-1 text-xs font-semibold px-2 py-1 rounded-full', getStatusClass(turbine.status)]">
              <span :class="['w-1.5 h-1.5 rounded-full', getStatusBadgeColor(turbine.status)]"></span>
              {{ getStatusLabel(turbine.status) }}
            </span>
          </td>

          <!-- Power -->
          <td class="py-3 px-4 text-right font-semibold text-slate-900 dark:text-white">
            {{ turbine.metrics?.power_mw?.toFixed(1) || '0.0' }} 
            <span class="text-xs font-normal text-slate-500 dark:text-slate-400">MW</span>
          </td>

          <!-- Wind -->
          <td class="py-3 px-4 text-right text-slate-700 dark:text-slate-300">
            {{ turbine.metrics?.wind_ms?.toFixed(1) || '-' }} 
            <span class="text-xs text-slate-500 dark:text-slate-400">m/s</span>
          </td>

          <!-- Rotor -->
          <td class="py-3 px-4 text-right text-slate-700 dark:text-slate-300">
            {{ turbine.metrics?.rotor_rpm?.toFixed(0) || '-' }} 
            <span class="text-xs text-slate-500 dark:text-slate-400">RPM</span>
          </td>

          <!-- Alarms -->
          <td class="py-3 px-4 text-center">
            <span
              v-if="turbine.alarmSummary?.total > 0"
              class="inline-flex items-center justify-center w-6 h-6 bg-red-600 text-white text-xs font-bold rounded-full shadow-sm"
            >
              {{ turbine.alarmSummary.total }}
            </span>
            <span v-else class="text-slate-400">—</span>
          </td>

          <!-- Arrow -->
          <td class="py-3 px-4">
            <svg class="w-5 h-5 text-slate-400 group-hover:text-slate-600 dark:group-hover:text-slate-300 group-hover:translate-x-0.5 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Empty State -->
    <div v-if="turbines.length === 0" class="text-center py-12">
      <svg class="w-12 h-12 mx-auto text-slate-300 dark:text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
      </svg>
      <p class="text-sm text-slate-600 dark:text-slate-400">No turbines to display</p>
    </div>
  </div>
</template>

<script setup>
defineProps({
  turbines: {
    type: Array,
    required: true,
    default: () => []
  }
})

defineEmits(['select'])

// Helper functions
const getTurbineIconColor = (status) => {
  const colors = {
    running: 'text-green-600 dark:text-green-500',
    idle: 'text-blue-600 dark:text-blue-500',
    maintenance: 'text-amber-600 dark:text-amber-500',
    stopped: 'text-red-600 dark:text-red-500',
    error: 'text-red-600 dark:text-red-500',
  }
  return colors[status] || 'text-slate-400'
}

const getStatusBadgeColor = (status) => {
  const colors = {
    running: 'bg-green-500',
    idle: 'bg-blue-500',
    maintenance: 'bg-amber-500',
    stopped: 'bg-red-500',
    error: 'bg-red-500',
  }
  return colors[status] || 'bg-slate-400'
}

const getStatusClass = (status) => {
  const classes = {
    running: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
    idle: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
    maintenance: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
    stopped: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    error: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
  }
  return classes[status] || 'bg-slate-100 text-slate-700'
}

const getStatusLabel = (status) => {
  const labels = {
    running: 'Running',
    idle: 'Idle',
    maintenance: 'Maintenance',
    stopped: 'Stopped',
    error: 'Error',
  }
  return labels[status] || 'Unknown'
}
</script>

<style scoped>
@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.animate-spin-slow {
  animation: spin 3s linear infinite;
}

/* Smooth hover transitions */
tbody tr {
  transition: background-color 150ms ease-in-out;
}
</style>