<template>
  <div class="space-y-6">
    <!-- Overall Status Banner -->
    <div :class="['rounded-xl p-4 border-l-4', getOverallStatusColor()]">
      <div class="flex items-center gap-3">
        <div class="flex-shrink-0">
          <svg
            v-if="isAllNormal"
            class="w-8 h-8 text-green-600 dark:text-green-400"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
            />
          </svg>
          <svg
            v-else
            class="w-8 h-8 text-orange-600 dark:text-orange-400"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
            />
          </svg>
        </div>
        <div class="flex-1">
          <h4 class="font-semibold text-slate-900 dark:text-white">
            {{ isAllNormal ? 'All Hydraulic Systems Normal' : 'Hydraulic System Alert' }}
          </h4>
          <p class="text-sm text-slate-600 dark:text-slate-400">
            Last reading: {{ formatDateTime(hydraulic.latest_reading) }}
          </p>
        </div>
      </div>
    </div>

    <!-- Hydraulic Pressure Card -->
    <div
      class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden"
    >
      <div class="p-6">
        <div class="flex items-start justify-between mb-4">
          <div class="flex items-center gap-3">
            <div
              :class="[
                'p-3 rounded-xl',
                getStatusBgColor(hydraulic.hydraulic_pressure_status?.color),
              ]"
            >
              <svg
                class="w-6 h-6"
                :class="getStatusTextColor(hydraulic.hydraulic_pressure_status?.color)"
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
            </div>
            <div>
              <h3 class="text-lg font-bold text-slate-900 dark:text-white">Hydraulic Pressure</h3>
              <p class="text-sm text-slate-600 dark:text-slate-400">Pitch Control System</p>
            </div>
          </div>

          <span
            :class="[
              'px-3 py-1.5 rounded-full text-sm font-bold',
              getStatusBadgeClass(hydraulic.hydraulic_pressure_status?.color),
            ]"
          >
            {{ hydraulic.hydraulic_pressure_status?.label || 'Unknown' }}
          </span>
        </div>

        <!-- Pressure Display -->
        <div class="mb-4">
          <div class="flex items-baseline gap-2 mb-2">
            <p class="text-4xl font-bold text-slate-900 dark:text-white">
              {{ formatNumber(hydraulic.hydraulic_pressure_bar, 1) }}
            </p>
            <span class="text-xl text-slate-500">bar</span>
          </div>

          <!-- Pressure Bar Visualization -->
          <div class="relative h-3 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
            <div
              :class="[
                'h-full rounded-full transition-all duration-500',
                getPressureBarColor(hydraulic.hydraulic_pressure_status?.color),
              ]"
              :style="{ width: `${getPressurePercentage(hydraulic.hydraulic_pressure_bar)}%` }"
            />
          </div>

          <!-- Range Labels -->
          <div class="flex justify-between text-xs text-slate-500 dark:text-slate-400 mt-1">
            <span>0 bar</span>
            <span>250 bar</span>
          </div>
        </div>

        <!-- Status Description -->
        <div
          :class="[
            'p-3 rounded-lg border',
            hydraulic.hydraulic_pressure_status?.color === 'green'
              ? 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800'
              : 'bg-orange-50 dark:bg-orange-900/20 border-orange-200 dark:border-orange-800',
          ]"
        >
          <div class="flex items-start gap-2">
            <svg
              :class="[
                'w-5 h-5 flex-shrink-0 mt-0.5',
                hydraulic.hydraulic_pressure_status?.color === 'green'
                  ? 'text-green-600 dark:text-green-400'
                  : 'text-orange-600 dark:text-orange-400',
              ]"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
              />
            </svg>
            <div class="text-sm">
              <p
                :class="[
                  'font-medium',
                  hydraulic.hydraulic_pressure_status?.color === 'green'
                    ? 'text-green-900 dark:text-green-300'
                    : 'text-orange-900 dark:text-orange-300',
                ]"
              >
                {{ hydraulic.hydraulic_pressure_status?.description || 'No description available' }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Gearbox Oil Pressure Card -->
    <div
      class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden"
    >
      <div class="p-6">
        <div class="flex items-start justify-between mb-4">
          <div class="flex items-center gap-3">
            <div
              :class="[
                'p-3 rounded-xl',
                getStatusBgColor(hydraulic.gearbox_oil_pressure_status?.color),
              ]"
            >
              <svg
                class="w-6 h-6"
                :class="getStatusTextColor(hydraulic.gearbox_oil_pressure_status?.color)"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
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
              </svg>
            </div>
            <div>
              <h3 class="text-lg font-bold text-slate-900 dark:text-white">Gearbox Oil Pressure</h3>
              <p class="text-sm text-slate-600 dark:text-slate-400">Lubrication System</p>
            </div>
          </div>

          <span
            :class="[
              'px-3 py-1.5 rounded-full text-sm font-bold',
              getStatusBadgeClass(hydraulic.gearbox_oil_pressure_status?.color),
            ]"
          >
            {{ hydraulic.gearbox_oil_pressure_status?.label || 'Unknown' }}
          </span>
        </div>

        <!-- Pressure Display -->
        <div class="mb-4">
          <div class="flex items-baseline gap-2 mb-2">
            <p class="text-4xl font-bold text-slate-900 dark:text-white">
              {{ formatNumber(hydraulic.gearbox_oil_pressure_bar, 2) }}
            </p>
            <span class="text-xl text-slate-500">bar</span>
          </div>

          <!-- Pressure Bar Visualization -->
          <div class="relative h-3 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
            <div
              :class="[
                'h-full rounded-full transition-all duration-500',
                getPressureBarColor(hydraulic.gearbox_oil_pressure_status?.color),
              ]"
              :style="{ width: `${getOilPressurePercentage(hydraulic.gearbox_oil_pressure_bar)}%` }"
            />
          </div>

          <!-- Range Labels -->
          <div class="flex justify-between text-xs text-slate-500 dark:text-slate-400 mt-1">
            <span>0 bar</span>
            <span>Normal: 2-4 bar</span>
            <span>5 bar</span>
          </div>
        </div>

        <!-- Status Description -->
        <div
          :class="[
            'p-3 rounded-lg border',
            hydraulic.gearbox_oil_pressure_status?.color === 'green'
              ? 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800'
              : 'bg-orange-50 dark:bg-orange-900/20 border-orange-200 dark:border-orange-800',
          ]"
        >
          <div class="flex items-start gap-2">
            <svg
              :class="[
                'w-5 h-5 flex-shrink-0 mt-0.5',
                hydraulic.gearbox_oil_pressure_status?.color === 'green'
                  ? 'text-green-600 dark:text-green-400'
                  : 'text-orange-600 dark:text-orange-400',
              ]"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
              />
            </svg>
            <div class="text-sm">
              <p
                :class="[
                  'font-medium',
                  hydraulic.gearbox_oil_pressure_status?.color === 'green'
                    ? 'text-green-900 dark:text-green-300'
                    : 'text-orange-900 dark:text-orange-300',
                ]"
              >
                {{
                  hydraulic.gearbox_oil_pressure_status?.description || 'No description available'
                }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- System Health Overview -->
    <div
      class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6"
    >
      <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
          />
        </svg>
        System Health Overview
      </h3>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Hydraulic Pressure Summary -->
        <div class="bg-slate-50 dark:bg-slate-900 rounded-lg p-4">
          <div class="flex items-center justify-between mb-2">
            <p class="text-sm font-medium text-slate-600 dark:text-slate-400">Hydraulic System</p>
            <span
              :class="[
                'w-3 h-3 rounded-full',
                hydraulic.hydraulic_pressure_status?.color === 'green'
                  ? 'bg-green-500'
                  : 'bg-orange-500',
              ]"
            />
          </div>
          <p class="text-2xl font-bold text-slate-900 dark:text-white mb-1">
            {{ formatNumber(hydraulic.hydraulic_pressure_bar, 1) }} bar
          </p>
          <p class="text-xs text-slate-500 dark:text-slate-400">
            {{ hydraulic.hydraulic_pressure_status?.label }}
          </p>
        </div>

        <!-- Gearbox Oil Summary -->
        <div class="bg-slate-50 dark:bg-slate-900 rounded-lg p-4">
          <div class="flex items-center justify-between mb-2">
            <p class="text-sm font-medium text-slate-600 dark:text-slate-400">Lubrication System</p>
            <span
              :class="[
                'w-3 h-3 rounded-full',
                hydraulic.gearbox_oil_pressure_status?.color === 'green'
                  ? 'bg-green-500'
                  : 'bg-orange-500',
              ]"
            />
          </div>
          <p class="text-2xl font-bold text-slate-900 dark:text-white mb-1">
            {{ formatNumber(hydraulic.gearbox_oil_pressure_bar, 2) }} bar
          </p>
          <p class="text-xs text-slate-500 dark:text-slate-400">
            {{ hydraulic.gearbox_oil_pressure_status?.label }}
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  hydraulic: {
    type: Object,
    required: true,
  },
})

// Computed
const isAllNormal = computed(() => {
  return (
    props.hydraulic.hydraulic_pressure_status?.color === 'green' &&
    props.hydraulic.gearbox_oil_pressure_status?.color === 'green'
  )
})

// Methods
const formatNumber = (value, decimals = 2) => {
  if (value === null || value === undefined) return 'N/A'
  return parseFloat(value).toFixed(decimals)
}

const formatDateTime = (dateString) => {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  return date.toLocaleString()
}

const getOverallStatusColor = () => {
  if (isAllNormal.value) {
    return 'bg-green-50 dark:bg-green-900/20 border-green-500'
  }
  return 'bg-orange-50 dark:bg-orange-900/20 border-orange-500'
}

const getStatusBgColor = (color) => {
  const colors = {
    green: 'bg-green-100 dark:bg-green-900/30',
    orange: 'bg-orange-100 dark:bg-orange-900/30',
    red: 'bg-red-100 dark:bg-red-900/30',
    yellow: 'bg-yellow-100 dark:bg-yellow-900/30',
  }
  return colors[color] || 'bg-slate-100 dark:bg-slate-800'
}

const getStatusTextColor = (color) => {
  const colors = {
    green: 'text-green-600 dark:text-green-400',
    orange: 'text-orange-600 dark:text-orange-400',
    red: 'text-red-600 dark:text-red-400',
    yellow: 'text-yellow-600 dark:text-yellow-400',
  }
  return colors[color] || 'text-slate-600'
}

const getStatusBadgeClass = (color) => {
  const classes = {
    green: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
    orange: 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
    red: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    yellow: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
  }
  return classes[color] || 'bg-slate-100 text-slate-700'
}

const getPressureBarColor = (color) => {
  const colors = {
    green: 'bg-green-500',
    orange: 'bg-orange-500',
    red: 'bg-red-500',
    yellow: 'bg-yellow-500',
  }
  return colors[color] || 'bg-slate-500'
}

const getPressurePercentage = (pressure) => {
  // Normal range is 150-200 bar, max display at 250 bar
  const value = parseFloat(pressure)
  if (isNaN(value)) return 0
  return Math.min((value / 250) * 100, 100)
}

const getOilPressurePercentage = (pressure) => {
  // Normal range is 2-4 bar, max display at 5 bar
  const value = parseFloat(pressure)
  if (isNaN(value)) return 0
  return Math.min((value / 5) * 100, 100)
}
</script>
