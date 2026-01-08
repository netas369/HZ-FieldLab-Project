<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
        <svg
          class="w-5 h-5 text-indigo-500"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M13 10V3L4 14h7v7l9-11h-7z"
          />
        </svg>
        Vibration Analysis
      </h3>
      <span
        class="text-xs font-medium px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-300"
      >
        Live Data
      </span>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <MetricCard
        title="Main Bearing"
        :status-color="turbine.main_bearing_status?.color"
        :status-label="turbine.main_bearing_status?.label"
      >
        <template #icon>
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
          />
        </template>

        <div class="flex items-baseline gap-2 mb-1">
          <p class="text-3xl font-bold text-slate-900 dark:text-white">
            {{ formatNumber(turbine.main_bearing_vibration_rms, 2) }}
          </p>
          <span class="text-sm text-slate-500">RMS</span>
        </div>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">
          Peak: {{ formatNumber(turbine.main_bearing_vibration_peak, 2) }}
        </p>
      </MetricCard>

      <MetricCard
        title="Gearbox"
        :status-color="turbine.gearbox_status?.color"
        :status-label="turbine.gearbox_status?.label"
      >
        <template #icon>
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"
          />
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
          />
        </template>

        <div class="grid grid-cols-2 gap-2">
          <div>
            <p class="text-xs text-slate-500 mb-1">
              Axial
            </p>
            <p class="text-xl font-bold text-slate-900 dark:text-white">
              {{ formatNumber(turbine.gearbox_vibration_axial, 2) }}
            </p>
          </div>
          <div>
            <p class="text-xs text-slate-500 mb-1">
              Radial
            </p>
            <p class="text-xl font-bold text-slate-900 dark:text-white">
              {{ formatNumber(turbine.gearbox_vibration_radial, 2) }}
            </p>
          </div>
        </div>
      </MetricCard>

      <MetricCard
        title="Generator"
        :status-color="turbine.generator_status?.color"
        :status-label="turbine.generator_status?.label"
      >
        <template #icon>
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M13 10V3L4 14h7v7l9-11h-7z"
          />
        </template>

        <div class="space-y-2">
          <div class="flex justify-between items-center">
            <span class="text-sm text-slate-500">Drive End</span>
            <span class="font-bold text-slate-900 dark:text-white">{{
              formatNumber(turbine.generator_vibration_de, 2)
            }}</span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-sm text-slate-500">Non-Drive End</span>
            <span class="font-bold text-slate-900 dark:text-white">{{
              formatNumber(turbine.generator_vibration_nde, 2)
            }}</span>
          </div>
        </div>
      </MetricCard>

      <MetricCard
        title="Tower Sway"
        :status-color="turbine.tower_status?.color"
        :status-label="turbine.tower_status?.label || 'Stable'"
      >
        <template #icon>
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
          />
        </template>

        <div class="grid grid-cols-2 gap-4">
          <div class="bg-slate-50 dark:bg-slate-900 p-2 rounded-lg text-center">
            <p class="text-lg font-bold text-slate-900 dark:text-white">
              {{ formatNumber(turbine.tower_vibration_fa, 2) }}
            </p>
            <p class="text-xs text-slate-500">
              Fore-Aft
            </p>
          </div>
          <div class="bg-slate-50 dark:bg-slate-900 p-2 rounded-lg text-center">
            <p class="text-lg font-bold text-slate-900 dark:text-white">
              {{ formatNumber(turbine.tower_vibration_ss, 2) }}
            </p>
            <p class="text-xs text-slate-500">
              Side-Side
            </p>
          </div>
        </div>
      </MetricCard>

      <MetricCard
        title="Acoustic"
        :status-color="turbine.acoustic_status?.color"
        :status-label="turbine.acoustic_status?.label"
      >
        <template #icon>
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"
          />
        </template>

        <div class="flex items-end justify-between">
          <div>
            <p class="text-3xl font-bold text-slate-900 dark:text-white">
              {{ formatNumber(turbine.acoustic_level_db, 1) }}
            </p>
            <p class="text-xs text-slate-500 dark:text-slate-400">
              Decibels (dB)
            </p>
          </div>

          <div class="flex items-end gap-1 h-12">
            <div
              v-for="n in [6, 12, 18, 24, 30, 36]"
              :key="n"
              :style="{ height: `${n}px` }"
              :class="[
                'w-2 rounded-sm transition-all duration-300',
                turbine.acoustic_status?.color === 'green' ? 'bg-green-500' : 'bg-orange-500',
              ]"
            />
          </div>
        </div>
      </MetricCard>
    </div>

    <div
      class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6"
    >
      <div class="flex flex-col md:flex-row items-center gap-8">
        <div class="flex-1 w-full">
          <div class="flex items-center justify-between mb-6">
            <div>
              <h3 class="text-lg font-bold text-slate-900 dark:text-white">
                Blade Vibration
              </h3>
              <p class="text-sm text-slate-600 dark:text-slate-400">
                Individual blade analysis
              </p>
            </div>
            <span
              :class="[
                'px-3 py-1 rounded-full text-xs font-bold',
                getStatusBadgeClass(turbine.blade_status?.color),
              ]"
            >
              {{ turbine.blade_status?.label }}
            </span>
          </div>

          <div class="space-y-3">
            <div
              v-for="i in 3"
              :key="i"
              class="flex items-center justify-between p-3 rounded-lg bg-slate-50 dark:bg-slate-900/50"
            >
              <div class="flex items-center gap-3">
                <span
                  class="w-6 h-6 flex items-center justify-center rounded-full bg-slate-200 dark:bg-slate-700 text-xs font-bold"
                >
                  {{ i }}
                </span>
                <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Blade {{ i }}</span>
              </div>
              <div class="flex items-center gap-4">
                <span class="font-mono font-bold text-slate-900 dark:text-white">
                  {{ formatNumber(turbine[`blade${i}_vibration`], 2) }}
                </span>
                <div
                  :class="[
                    'w-2 h-2 rounded-full',
                    getBladeStatusDot(turbine[`blade${i}_vibration`]),
                  ]"
                />
              </div>
            </div>
          </div>
        </div>

        <div
          class="flex-shrink-0 p-4 bg-slate-50 dark:bg-slate-900 rounded-full border border-slate-100 dark:border-slate-800"
        >
          <svg
            class="w-48 h-48 animate-[spin_10s_linear_infinite]"
            viewBox="-50 -50 100 100"
          >
            <circle
              cx="0"
              cy="0"
              r="14"
              :fill="getLedColor(turbine.blade_status?.color)"
              class="transition-colors duration-500"
            />
            <circle
              cx="0"
              cy="0"
              r="14"
              fill="none"
              class="stroke-slate-900 dark:stroke-white"
              stroke-width="0.6"
            />

            <g
              v-for="i in 3"
              :key="i"
              :transform="`rotate(${134 * i - 2 * i})`"
            >
              <path
                d="M4 1 L45 -6 L20 5 Z"
                class="stroke-slate-600 dark:stroke-slate-400 transition-colors duration-300"
                :class="getBladeFillColor(turbine[`blade${i}_vibration`])"
              />
            </g>

            <circle
              cx="0"
              cy="0"
              r="4"
              class="fill-slate-800 dark:fill-slate-200"
            />
          </svg>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import MetricCard from './MetricCard.vue'

defineProps({
  turbine: {
    type: Object,
    required: true,
  },
})

// --- Utilities ---
const formatNumber = (value, decimals = 2) => {
  if (value === null || value === undefined) return 'N/A'
  return parseFloat(value).toFixed(decimals)
}

const getStatusBadgeClass = (color) => {
  const classes = {
    green: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
    orange: 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
    red: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
  }
  return classes[color] || 'bg-slate-100 text-slate-700'
}

// --- Blade Specific Logic ---
const getBladeFillColor = (vibration) => {
  const v = parseFloat(vibration)
  if (v >= 0.7) return 'fill-red-500 dark:fill-red-600'
  if (v >= 0.5) return 'fill-orange-400 dark:fill-orange-500'
  return 'fill-green-500 dark:fill-green-600'
}

const getBladeStatusDot = (vibration) => {
  const v = parseFloat(vibration)
  if (v >= 0.7) return 'bg-red-500'
  if (v >= 0.5) return 'bg-orange-400'
  return 'bg-green-500'
}

const getLedColor = (statusColor) => {
  if (statusColor === 'green') return '#22c55e'
  if (statusColor === 'orange') return '#f97316'
  if (statusColor === 'red') return '#ef4444'
  return '#94a3b8'
}
</script>
