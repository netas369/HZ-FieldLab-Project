
<template>
  <div class="space-y-6">
    <!-- Overall Status Banner -->
    <div
        :class="[
        'rounded-xl p-4 border-l-4',
        getOverallStatusColor()
      ]"
    >
      <div class="flex items-center gap-3">
        <div class="flex-shrink-0">
          <svg v-if="temperature.overall_temperature_status?.color === 'green'" class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <svg v-else class="w-8 h-8 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
        </div>
        <div class="flex-1">
          <h4 class="font-semibold text-slate-900 dark:text-white">
            {{ temperature.overall_temperature_status?.label || 'Temperature Status' }}
          </h4>
          <p class="text-sm text-slate-600 dark:text-slate-400">
            {{ temperature.overall_temperature_status?.message || 'Loading...' }}
          </p>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
            Last reading: {{ formatDateTime(temperature.latest_reading) }} • Load Factor: {{ formatNumber(temperature.load_factor * 100, 0) }}%
          </p>
        </div>
      </div>
    </div>

    <!-- Temperature Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <!-- Nacelle Temperature -->
      <TemperatureCard
          title="Nacelle"
          :temperature="temperature.nacelle_temp"
          :status="temperature.nacelle_status"
          icon="nacelle"
      />

      <!-- Main Bearing Temperature -->
      <TemperatureCard
          title="Main Bearing"
          :temperature="temperature.main_bearing_temp"
          :status="temperature.main_bearing_status"
          icon="bearing"
      />

      <!-- Gearbox Bearing Temperature -->
      <TemperatureCard
          title="Gearbox Bearing"
          :temperature="temperature.gearbox_bearing_temp"
          :status="temperature.gearbox_bearing_status"
          icon="bearing"
      />

      <!-- Gearbox Oil Temperature -->
      <TemperatureCard
          title="Gearbox Oil"
          :temperature="temperature.gearbox_oil_temp"
          :status="temperature.gearbox_oil_status"
          icon="oil"
      />

      <!-- Generator Bearing 1 -->
      <TemperatureCard
          title="Generator Bearing DE"
          :temperature="temperature.generator_bearing1_temp"
          :status="temperature.generator_bearing1_status"
          icon="bearing"
      />

      <!-- Generator Bearing 2 -->
      <TemperatureCard
          title="Generator Bearing NDE"
          :temperature="temperature.generator_bearing2_temp"
          :status="temperature.generator_bearing2_status"
          icon="bearing"
      />

      <!-- Generator Stator -->
      <TemperatureCard
          title="Generator Stator"
          :temperature="temperature.generator_stator_temp"
          :status="temperature.generator_status"
          icon="generator"
      />
    </div>

    <!-- Temperature Comparison Chart -->
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6">
      <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
        </svg>
        Temperature Overview
      </h3>

      <div class="space-y-3">
        <TemperatureBar
            label="Main Bearing"
            :value="temperature.main_bearing_temp"
            :status="temperature.main_bearing_status"
        />
        <TemperatureBar
            label="Gearbox Bearing"
            :value="temperature.gearbox_bearing_temp"
            :status="temperature.gearbox_bearing_status"
        />
        <TemperatureBar
            label="Gearbox Oil"
            :value="temperature.gearbox_oil_temp"
            :status="temperature.gearbox_oil_status"
        />
        <TemperatureBar
            label="Gen. Bearing DE"
            :value="temperature.generator_bearing1_temp"
            :status="temperature.generator_bearing1_status"
        />
        <TemperatureBar
            label="Gen. Bearing NDE"
            :value="temperature.generator_bearing2_temp"
            :status="temperature.generator_bearing2_status"
        />
        <TemperatureBar
            label="Generator Stator"
            :value="temperature.generator_stator_temp"
            :status="temperature.generator_status"
        />
        <TemperatureBar
            label="Nacelle"
            :value="temperature.nacelle_temp"
            :status="temperature.nacelle_status"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import TemperatureCard from './TemperatureCard.vue'
import TemperatureBar from './TemperatureBar.vue'

const props = defineProps({
  temperature: {
    type: Object,
    required: true
  }
})

// Methods
const formatNumber = (value, decimals = 1) => {
  if (value === null || value === undefined) return 'N/A'
  return parseFloat(value).toFixed(decimals)
}

const formatDateTime = (dateString) => {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  return date.toLocaleString()
}

const getOverallStatusColor = () => {
  const color = props.temperature.overall_temperature_status?.color
  const colors = {
    green: 'bg-green-50 dark:bg-green-900/20 border-green-500',
    yellow: 'bg-yellow-50 dark:bg-yellow-900/20 border-yellow-500',
    orange: 'bg-orange-50 dark:bg-orange-900/20 border-orange-500',
    red: 'bg-red-50 dark:bg-red-900/20 border-red-500'
  }
  return colors[color] || 'bg-slate-50 dark:bg-slate-800 border-slate-500'
}
</script>