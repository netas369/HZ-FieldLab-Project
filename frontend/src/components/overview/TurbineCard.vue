<template>
  <div
    :class="[
      'group relative bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-900 dark:to-slate-800',
      'rounded-xl p-5 transition-all cursor-pointer overflow-hidden',
      borderClass,
      'hover:shadow-2xl hover:scale-[1.02] transform',
    ]"
    @click="$emit('select', turbine)"
  >
    <div class="absolute inset-0 opacity-5">
      <svg class="w-full h-full" viewBox="0 0 100 100">
        <pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse">
          <path d="M 10 0 L 0 0 0 10" fill="none" stroke="currentColor" stroke-width="0.5" />
        </pattern>
        <rect width="100" height="100" fill="url(#grid)" />
      </svg>
    </div>

    <div class="relative z-10">
      <div class="flex items-start justify-between mb-4">
        <div class="flex-1">
          <div class="flex items-center justify-between mr-4">
            <div>
              <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-1">
                {{ turbine.id }}
              </h3>
              <p class="text-sm text-slate-600 dark:text-slate-400 flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                  />
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                  />
                </svg>
                {{ turbine.location }}
              </p>
            </div>

            <div v-if="turbine.healthData" class="flex flex-col items-end">
              <div
                class="flex items-center gap-1 bg-white/50 dark:bg-slate-800/50 px-2 py-1 rounded-lg border border-slate-100 dark:border-slate-700 backdrop-blur-sm"
              >
                <span
                  class="text-lg font-bold"
                  :class="
                    getHealthTextColor(turbine.healthData.overall_health.overall_health_score)
                  "
                >
                  {{ turbine.healthData.overall_health.overall_health_score.toFixed(0) }}%
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
              <span class="text-[10px] font-bold uppercase tracking-wide text-slate-400 mt-1"
                >Health</span
              >
            </div>
          </div>
        </div>

        <div class="relative w-16 h-16 flex-shrink-0">
          <svg viewBox="0 0 100 100" :class="['w-full h-full', iconColor]">
            <rect x="47" y="45" width="6" height="45" fill="currentColor" opacity="0.3" />
            <ellipse cx="50" cy="42" rx="8" ry="6" fill="currentColor" opacity="0.4" />
            <circle cx="50" cy="42" r="3.5" fill="currentColor" />
            <g
              :class="{ 'animate-spin-slow': turbine.status === 'running' }"
              style="transform-origin: 50px 42px"
            >
              <ellipse cx="50" cy="20" rx="3" ry="18" fill="currentColor" opacity="0.9" />
              <ellipse
                cx="50"
                cy="20"
                rx="3"
                ry="18"
                fill="currentColor"
                opacity="0.9"
                transform="rotate(120 50 42)"
              />
              <ellipse
                cx="50"
                cy="20"
                rx="3"
                ry="18"
                fill="currentColor"
                opacity="0.9"
                transform="rotate(240 50 42)"
              />
            </g>
            <rect x="44" y="88" width="12" height="3" fill="currentColor" opacity="0.3" rx="1" />
          </svg>

          <div
            :class="[
              'absolute -top-1 -right-1 w-5 h-5 rounded-full border-2 border-white dark:border-slate-900',
              statusBadgeColor,
            ]"
          >
            <div
              v-if="turbine.status === 'running'"
              class="absolute inset-0 rounded-full bg-green-500 animate-ping opacity-75"
            />
          </div>

          <div
            v-if="turbine.alarmSummary?.total > 0"
            class="absolute -top-1 -left-1 w-6 h-6 rounded-full border-2 border-white dark:border-slate-900 bg-red-600 flex items-center justify-center shadow-lg animate-pulse"
          >
            <span class="text-[10px] font-bold text-white">{{ turbine.alarmSummary.total }}</span>
          </div>
        </div>
      </div>

      <div class="mb-4">
        <span
          :class="[
            'inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-full',
            statusClass,
          ]"
        >
          <span :class="['w-2 h-2 rounded-full', statusBadgeColor]" />
          {{ statusLabel }}
        </span>
      </div>

      <div class="grid grid-cols-2 gap-3 mb-4">
        <div class="bg-white/50 dark:bg-slate-800/50 rounded-lg p-3 backdrop-blur-sm">
          <p class="text-xs text-slate-600 dark:text-slate-400 mb-1">Power</p>
          <p class="text-lg font-bold text-slate-900 dark:text-white">
            {{ turbine.metrics?.power_mw?.toFixed(1) || '0.0' }}
            <span class="text-sm font-normal text-slate-500">MW</span>
          </p>
        </div>
        <div class="bg-white/50 dark:bg-slate-800/50 rounded-lg p-3 backdrop-blur-sm">
          <p class="text-xs text-slate-600 dark:text-slate-400 mb-1">Wind</p>
          <p class="text-lg font-bold text-slate-900 dark:text-white">
            {{ turbine.metrics?.wind_ms?.toFixed(1) || '-' }}
            <span class="text-sm font-normal text-slate-500">m/s</span>
          </p>
        </div>
      </div>

      <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
        <span class="flex items-center gap-1">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M13 10V3L4 14h7v7l9-11h-7z"
            />
          </svg>
          {{ turbine.metrics?.rotor_rpm?.toFixed(0) || '-' }} RPM
        </span>
        <span class="text-indigo-600 dark:text-indigo-400 font-medium group-hover:underline">
          View Details →
        </span>
      </div>
    </div>

    <div
      class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none"
    >
      <div :class="['absolute inset-0 rounded-xl', glowClass]" />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  turbine: { type: Object, required: true },
})

defineEmits(['select'])

// --- NEW HELPER ---
const getHealthTextColor = (score) => {
  if (score >= 90) return 'text-green-600 dark:text-green-400'
  if (score >= 70) return 'text-amber-600 dark:text-amber-400'
  return 'text-red-600 dark:text-red-400'
}

// --- EXISTING HELPERS ---
const iconColor = computed(() => {
  const colors = {
    running: 'text-green-600 dark:text-green-500',
    idle: 'text-blue-600 dark:text-blue-500',
    maintenance: 'text-amber-600 dark:text-amber-500',
    stopped: 'text-red-600 dark:text-red-500',
    error: 'text-red-600 dark:text-red-500',
  }
  return colors[props.turbine.status] || 'text-slate-400'
})

const statusBadgeColor = computed(() => {
  const colors = {
    running: 'bg-green-500',
    idle: 'bg-blue-500',
    maintenance: 'bg-amber-500',
    stopped: 'bg-red-500',
    error: 'bg-red-500',
  }
  return colors[props.turbine.status] || 'bg-slate-400'
})

const statusClass = computed(() => {
  const classes = {
    running: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
    idle: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
    maintenance: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
    stopped: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    error: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
  }
  return classes[props.turbine.status] || 'bg-slate-100 text-slate-700'
})

const statusLabel = computed(
  () =>
    ({
      running: 'Running',
      idle: 'Idle',
      maintenance: 'Maintenance',
      stopped: 'Stopped',
      error: 'Error',
    })[props.turbine.status] || 'Unknown'
)

const borderClass = computed(() => {
  if (props.turbine.alarmSummary?.total > 0) return 'border-2 border-red-500 shadow-red-500/20'
  if (props.turbine.status === 'error' || props.turbine.status === 'stopped')
    return 'border-2 border-red-500'
  if (props.turbine.status === 'maintenance') return 'border-2 border-amber-400'
  if (props.turbine.status === 'idle') return 'border-2 border-blue-300'
  if (props.turbine.status === 'running') return 'border-2 border-green-300'
  return 'border-2 border-slate-200 dark:border-slate-700'
})

const glowClass = computed(() => {
  const classes = {
    running: 'bg-gradient-to-t from-green-500/20 to-transparent',
    idle: 'bg-gradient-to-t from-blue-500/20 to-transparent',
    maintenance: 'bg-gradient-to-t from-amber-500/20 to-transparent',
    stopped: 'bg-gradient-to-t from-red-500/20 to-transparent',
    error: 'bg-gradient-to-t from-red-500/20 to-transparent',
  }
  return classes[props.turbine.status] || 'bg-gradient-to-t from-slate-500/20 to-transparent'
})
</script>

<style scoped>
@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}
.animate-spin-slow {
  animation: spin 3s linear infinite;
}
</style>
