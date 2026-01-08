<template>
  <div class="space-y-2">
    <div
      v-for="turbine in turbines"
      :key="turbine.id"
      :class="[
        'group flex items-center gap-4 p-4 rounded-xl transition-all cursor-pointer',
        getBorderClass(turbine),
        'hover:shadow-lg hover:bg-slate-50 dark:hover:bg-slate-900/50',
      ]"
      @click="$emit('select', turbine)"
    >
      <div class="relative w-14 h-14 flex-shrink-0">
        <svg viewBox="0 0 100 100" :class="['w-full h-full', getIconColor(turbine.status)]">
          <rect x="47" y="45" width="6" height="40" fill="currentColor" opacity="0.3" />
          <ellipse cx="50" cy="42" rx="7" ry="5" fill="currentColor" opacity="0.4" />
          <circle cx="50" cy="42" r="3" fill="currentColor" />
          <g
            :class="{ 'animate-spin-slow': turbine.status === 'running' }"
            style="transform-origin: 50px 42px"
          >
            <ellipse cx="50" cy="22" rx="2.5" ry="16" fill="currentColor" opacity="0.9" />
            <ellipse
              cx="50"
              cy="22"
              rx="2.5"
              ry="16"
              fill="currentColor"
              opacity="0.9"
              transform="rotate(120 50 42)"
            />
            <ellipse
              cx="50"
              cy="22"
              rx="2.5"
              ry="16"
              fill="currentColor"
              opacity="0.9"
              transform="rotate(240 50 42)"
            />
          </g>
          <rect x="45" y="84" width="10" height="2" fill="currentColor" opacity="0.3" rx="1" />
        </svg>
        <div
          :class="[
            'absolute -top-1 -right-1 w-4 h-4 rounded-full border-2 border-white',
            getStatusBadgeColor(turbine.status),
          ]"
        />
        <div
          v-if="turbine.alarmSummary?.total > 0"
          class="absolute -top-1 -left-1 w-5 h-5 rounded-full border-2 border-white bg-red-600 flex items-center justify-center"
        >
          <span class="text-[9px] font-bold text-white">{{ turbine.alarmSummary.total }}</span>
        </div>
      </div>

      <div class="flex-1 min-w-0">
        <div class="flex items-center gap-2 mb-1">
          <h3 class="font-bold text-slate-900 dark:text-white">
            {{ turbine.id }}
          </h3>
          <span
            :class="[
              'text-xs font-semibold px-2 py-0.5 rounded-full',
              getStatusClass(turbine.status),
            ]"
          >
            {{ getStatusLabel(turbine.status) }}
          </span>
        </div>
        <p class="text-sm text-slate-600 dark:text-slate-400 flex items-center gap-1">
          <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
            />
          </svg>
          {{ turbine.location }}
        </p>
      </div>

      <div
        class="hidden sm:flex flex-col items-end mr-4 min-w-[80px] border-r border-slate-200 dark:border-slate-700 pr-6"
      >
        <div v-if="turbine.healthData" class="flex flex-col items-end">
          <span
            class="text-2xl font-bold"
            :class="getHealthTextColor(turbine.healthData.overall_health.overall_health_score)"
          >
            {{ turbine.healthData.overall_health.overall_health_score.toFixed(0) }}%
          </span>
          <div class="flex items-center gap-1.5">
            <span
              class="text-[10px] font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400"
            >
              {{ turbine.healthData.overall_health.status }}
            </span>
            <span
              v-if="turbine.healthData.overall_health.critical_components.length > 0"
              class="flex h-2 w-2 relative"
              title="Critical Issue"
            >
              <span
                class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"
              />
              <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500" />
            </span>
          </div>
        </div>
        <div v-else class="text-xs text-slate-400 font-medium uppercase tracking-wide">
          No Health Data
        </div>
      </div>

      <div class="hidden lg:flex items-center gap-6">
        <div class="text-center">
          <p class="text-xs text-slate-500 dark:text-slate-400 mb-1">Power</p>
          <p class="text-lg font-bold text-slate-900 dark:text-white">
            {{ turbine.metrics?.power_mw?.toFixed(1) || '0.0' }}
          </p>
          <p class="text-xs text-slate-500">MW</p>
        </div>
        <div class="text-center">
          <p class="text-xs text-slate-500 dark:text-slate-400 mb-1">Wind</p>
          <p class="text-lg font-bold text-slate-900 dark:text-white">
            {{ turbine.metrics?.wind_ms?.toFixed(1) || '-' }}
          </p>
          <p class="text-xs text-slate-500">m/s</p>
        </div>
        <div class="text-center">
          <p class="text-xs text-slate-500 dark:text-slate-400 mb-1">Rotor</p>
          <p class="text-lg font-bold text-slate-900 dark:text-white">
            {{ turbine.metrics?.rotor_rpm?.toFixed(0) || '-' }}
          </p>
          <p class="text-xs text-slate-500">RPM</p>
        </div>
      </div>

      <svg
        class="w-6 h-6 text-slate-400 flex-shrink-0 group-hover:translate-x-1 transition-transform"
        fill="none"
        stroke="currentColor"
        viewBox="0 0 24 24"
      >
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
      </svg>
    </div>
  </div>
</template>

<script setup>
defineProps({
  turbines: { type: Array, required: true, default: () => [] },
})

defineEmits(['select'])

// New Health Helper
const getHealthTextColor = (score) => {
  if (score >= 90) return 'text-green-600 dark:text-green-400'
  if (score >= 70) return 'text-amber-600 dark:text-amber-400'
  return 'text-red-600 dark:text-red-400'
}

// Existing Helpers
const getIconColor = (status) => {
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
const getStatusLabel = (status) =>
  ({
    running: 'Running',
    idle: 'Idle',
    maintenance: 'Maintenance',
    stopped: 'Stopped',
    error: 'Error',
  })[status] || 'Unknown'
const getBorderClass = (turbine) => {
  if (turbine.alarmSummary?.total > 0) return 'border-2 border-red-500 shadow-red-500/20'
  if (turbine.status === 'error' || turbine.status === 'stopped') return 'border-2 border-red-500'
  if (turbine.status === 'maintenance') return 'border-2 border-amber-400'
  if (turbine.status === 'idle') return 'border-2 border-blue-300'
  if (turbine.status === 'running') return 'border-2 border-green-300'
  return 'border-2 border-slate-200 dark:border-slate-700'
}
</script>

<style scoped>
.animate-spin-slow {
  animation: spin 3s linear infinite;
}
</style>
