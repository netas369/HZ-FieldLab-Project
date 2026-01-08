<template>
  <div class="space-y-8 animate-fade-in">

    <!-- Overall Health + Analysis Info -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <!-- Overall Score Card -->
      <div class="md:col-span-2 bg-gradient-to-br from-white to-slate-50 dark:from-slate-800 dark:to-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-700 p-6 flex items-center justify-between shadow-sm relative overflow-hidden">
        <div class="absolute right-0 top-0 w-32 h-32 bg-current opacity-5 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2" :class="getHealthColor(overallHealth.overall_health_score)"></div>
        <div>
          <h3 class="text-lg font-semibold text-slate-700 dark:text-slate-300 mb-1">Overall Turbine Health</h3>
          <p class="text-slate-500 dark:text-slate-400 text-sm mb-4">
            Based on {{ analysisPeriod.days_analyzed || healthData.period_days }} days of sensor data
            <span v-if="analysisPeriod.confidence_level" class="ml-2 px-2 py-0.5 rounded text-xs font-medium" :class="getConfidenceBadge(analysisPeriod.confidence_level)">
              {{ analysisPeriod.confidence_level }} Confidence
            </span>
          </p>
          <div class="flex items-center gap-3 flex-wrap">
            <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide border bg-white dark:bg-slate-900" :class="getStatusBadgeColor(overallHealth.status)">
              {{ overallHealth.status }}
            </span>
            <span v-if="overallHealth.critical_components?.length > 0" class="text-xs text-red-500 font-medium flex items-center gap-1">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
              {{ overallHealth.critical_components.length }} Critical
            </span>
            <span v-if="overallHealth.soonest_critical_days" class="text-xs text-amber-600 dark:text-amber-400 font-medium flex items-center gap-1">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
              {{ overallHealth.soonest_critical_days }} days to next critical
            </span>
          </div>
          <!-- Recommendation -->
          <p v-if="overallHealth.recommendation" class="mt-3 text-xs text-slate-600 dark:text-slate-400 bg-slate-100 dark:bg-slate-900/50 px-3 py-2 rounded-lg">
            💡 {{ overallHealth.recommendation }}
          </p>
        </div>
        <!-- Circular Score -->
        <div class="relative w-24 h-24 flex items-center justify-center flex-shrink-0">
          <svg class="w-full h-full -rotate-90" viewBox="0 0 36 36">
            <path class="text-slate-200 dark:text-slate-700" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="currentColor" stroke-width="3" />
            <path :class="getHealthColor(overallHealth.overall_health_score)" :stroke-dasharray="`${overallHealth.overall_health_score || 0}, 100`" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" />
          </svg>
          <div class="absolute flex flex-col items-center">
            <span class="text-2xl font-bold text-slate-900 dark:text-white">{{ overallHealth.overall_health_score?.toFixed(0) ?? '-' }}</span>
            <span class="text-[10px] uppercase font-bold text-slate-400">Score</span>
          </div>
        </div>
      </div>

      <!-- Analysis Info Card -->
      <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6 flex flex-col justify-center">
        <div class="text-sm text-slate-500 dark:text-slate-400 mb-2 font-medium uppercase tracking-wider">Analysis Window</div>
        <div class="text-2xl font-bold text-slate-900 dark:text-white mb-1">{{ analysisPeriod.days_analyzed || healthData.period_days }} Days</div>
        <div class="text-xs text-slate-400 mb-3">
          Updated: {{ formatDate(healthData.calculation_timestamp) }}
        </div>
        <div v-if="analysisPeriod.data_availability" class="text-xs space-y-1 pt-3 border-t border-slate-100 dark:border-slate-700">
          <div class="flex justify-between">
            <span class="text-slate-500">Data from:</span>
            <span class="text-slate-700 dark:text-slate-300">{{ formatDate(analysisPeriod.data_availability.oldest_reading) }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-slate-500">Data to:</span>
            <span class="text-slate-700 dark:text-slate-300">{{ formatDate(analysisPeriod.data_availability.newest_reading) }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Component Health Cards -->
    <div>
      <h3 class="text-lg font-bold text-slate-800 dark:text-white mb-4 flex items-center gap-2">
        <svg class="w-5 h-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
        Component Health Analysis
      </h3>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div
            v-for="(comp, key) in healthData.components"
            :key="key"
            class="bg-white dark:bg-slate-800 rounded-xl border transition-all duration-200 overflow-hidden hover:shadow-md group"
            :class="[
            expandedComponent === key ? 'border-indigo-500 ring-1 ring-indigo-500/20' : 'border-slate-200 dark:border-slate-700',
            comp.status === 'NO_DATA' ? 'opacity-60' : ''
          ]"
        >
          <!-- Header -->
          <div class="p-4 cursor-pointer flex items-center justify-between" @click="toggleComponent(key)">
            <div class="flex items-center gap-3">
              <div class="relative w-10 h-10 flex items-center justify-center rounded-full bg-slate-50 dark:bg-slate-900 border" :class="getBorderColor(comp.health_score)">
                <span v-if="comp.health_score !== null" class="font-bold text-sm" :class="getTextColor(comp.health_score)">
                  {{ comp.health_score?.toFixed(0) }}
                </span>
                <span v-else class="text-slate-400 text-xs">N/A</span>
              </div>
              <div>
                <h4 class="font-semibold text-slate-900 dark:text-white capitalize text-sm">
                  {{ formatComponentName(comp.component) }}
                </h4>
                <div class="flex items-center gap-2 mt-0.5">
                  <span class="text-[10px] font-bold uppercase tracking-wide" :class="getTextColor(comp.health_score)">
                    {{ comp.status }}
                  </span>
                  <!-- Prediction Badge -->
                  <span
                      v-if="getDaysToCritical(comp)"
                      class="text-[10px] px-1.5 py-0.5 rounded font-medium"
                      :class="getDaysToCritical(comp) < 90 ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400'"
                  >
                    {{ getDaysToCritical(comp) }}d to critical
                  </span>
                </div>
              </div>
            </div>
            <button class="text-slate-400 group-hover:text-indigo-500 transition-colors">
              <svg class="w-5 h-5 transition-transform duration-200" :class="expandedComponent === key ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>
          </div>

          <!-- Expanded Content -->
          <div v-show="expandedComponent === key" class="border-t border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/30 p-4 text-sm space-y-4">

            <!-- Deterioration Analysis -->
            <div v-if="comp.deterioration_analysis?.can_analyze" class="bg-white dark:bg-slate-800 rounded-lg p-3 border border-slate-200 dark:border-slate-700">
              <div class="flex items-center justify-between mb-2">
                <h5 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Deterioration Analysis</h5>
                <span class="text-[10px] px-2 py-0.5 rounded-full font-medium" :class="getConfidenceBadge(comp.deterioration_analysis.analysis_confidence)">
                  {{ comp.deterioration_analysis.analysis_confidence }} confidence
                </span>
              </div>
              <div class="grid grid-cols-3 gap-2 text-center">
                <div class="p-2 bg-slate-50 dark:bg-slate-900/50 rounded">
                  <div class="text-[10px] text-slate-400 uppercase">To Warning</div>
                  <div class="font-mono font-bold text-sm" :class="getPredictionColor(comp.deterioration_analysis.predictions?.days_to_warning)">
                    {{ comp.deterioration_analysis.predictions?.days_to_warning ?? '—' }}
                  </div>
                </div>
                <div class="p-2 bg-slate-50 dark:bg-slate-900/50 rounded">
                  <div class="text-[10px] text-slate-400 uppercase">To Critical</div>
                  <div class="font-mono font-bold text-sm" :class="getPredictionColor(comp.deterioration_analysis.predictions?.days_to_critical)">
                    {{ comp.deterioration_analysis.predictions?.days_to_critical ?? '—' }}
                  </div>
                </div>
                <div class="p-2 bg-slate-50 dark:bg-slate-900/50 rounded">
                  <div class="text-[10px] text-slate-400 uppercase">To Failed</div>
                  <div class="font-mono font-bold text-sm" :class="getPredictionColor(comp.deterioration_analysis.predictions?.days_to_failed)">
                    {{ comp.deterioration_analysis.predictions?.days_to_failed ?? '—' }}
                  </div>
                </div>
              </div>
              <!-- Recommendation -->
              <p v-if="comp.deterioration_analysis.recommendation" class="mt-2 text-xs text-slate-600 dark:text-slate-400 italic">
                {{ comp.deterioration_analysis.recommendation }}
              </p>
            </div>

            <!-- Sensor Trends -->
            <div v-if="comp.sensor_trends && Object.keys(comp.sensor_trends).length > 0">
              <h5 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase mb-2">Sensor Trends</h5>
              <div class="space-y-2">
                <div
                    v-for="(trend, sensorKey) in comp.sensor_trends"
                    :key="sensorKey"
                    class="bg-white dark:bg-slate-800 rounded-lg p-2 border border-slate-200 dark:border-slate-700"
                >
                  <div class="flex items-center justify-between mb-1">
                    <span class="text-xs font-medium text-slate-700 dark:text-slate-300 capitalize">
                      {{ formatSensorName(sensorKey) }}
                    </span>
                    <div class="flex items-center gap-2">
                      <span v-if="trend.current_zone" class="text-[10px] px-1.5 py-0.5 rounded font-medium" :class="getZoneBadge(trend.current_zone)">
                        {{ trend.current_zone }}
                      </span>
                      <span v-if="trend.trend?.direction" class="text-[10px] flex items-center gap-0.5" :class="getTrendDirectionColor(trend.trend.direction)">
                        <svg v-if="trend.trend.direction === 'INCREASING'" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                        <svg v-else-if="trend.trend.direction === 'DECREASING'" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0v-8m0 8l-8-8-4 4-6-6" />
                        </svg>
                        <span v-else>—</span>
                        {{ trend.trend.direction }}
                      </span>
                    </div>
                  </div>
                  <div v-if="trend.has_sufficient_data" class="grid grid-cols-4 gap-1 text-[10px]">
                    <div>
                      <span class="text-slate-400">Current:</span>
                      <span class="ml-1 font-mono text-slate-700 dark:text-slate-300">{{ trend.current_value?.toFixed(2) }}</span>
                    </div>
                    <div>
                      <span class="text-slate-400">Mean:</span>
                      <span class="ml-1 font-mono text-slate-700 dark:text-slate-300">{{ trend.statistics?.mean?.toFixed(2) }}</span>
                    </div>
                    <div>
                      <span class="text-slate-400">R²:</span>
                      <span class="ml-1 font-mono" :class="trend.trend?.r_squared >= 0.7 ? 'text-green-600' : trend.trend?.r_squared >= 0.4 ? 'text-amber-600' : 'text-slate-400'">
                        {{ trend.trend?.r_squared?.toFixed(2) }}
                      </span>
                    </div>
                    <div>
                      <span class="text-slate-400">/year:</span>
                      <span class="ml-1 font-mono" :class="trend.trend?.slope_per_year > 0 ? 'text-red-500' : 'text-green-500'">
                        {{ trend.trend?.slope_per_year > 0 ? '+' : '' }}{{ trend.trend?.slope_per_year?.toFixed(2) }}
                      </span>
                    </div>
                  </div>
                  <div v-else class="text-[10px] text-slate-400 italic">
                    {{ trend.message || 'Insufficient data for trend analysis' }}
                  </div>
                </div>
              </div>
            </div>

            <!-- Legacy Trend Analysis (for backward compatibility) -->
            <div v-else-if="comp.trend_analysis" class="grid grid-cols-2 gap-4 mb-4">
              <div class="bg-white dark:bg-slate-800 p-2 rounded border border-slate-200 dark:border-slate-700">
                <div class="text-[10px] text-slate-400 uppercase">{{ healthData.period_days || 365 }} Days Ago</div>
                <div class="font-mono font-medium text-slate-700 dark:text-slate-300">
                  {{ comp.trend_analysis[`health_${healthData.period_days || 365}_days_ago`] ?? comp.trend_analysis.health_365_days_ago ?? '—' }}
                </div>
              </div>
              <div class="bg-white dark:bg-slate-800 p-2 rounded border border-slate-200 dark:border-slate-700">
                <div class="text-[10px] text-slate-400 uppercase">Trend</div>
                <div class="font-mono font-medium" :class="comp.trend_analysis.deterioration_level === 'STABLE' ? 'text-green-500' : 'text-red-500'">
                  {{ comp.trend_analysis.deterioration_level }}
                </div>
              </div>
            </div>

            <!-- Penalties -->
            <div v-if="comp.penalties && comp.penalties.total > 0">
              <h5 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase mb-2">Health Penalties</h5>
              <div class="bg-white dark:bg-slate-800 rounded-lg p-2 border border-slate-200 dark:border-slate-700">
                <ul class="space-y-1">
                  <li
                      v-for="(val, pKey) in comp.penalties"
                      :key="pKey"
                      v-show="val > 0 && pKey !== 'total'"
                      class="flex justify-between text-xs"
                  >
                    <span class="text-slate-600 dark:text-slate-300 capitalize">{{ formatSensorName(pKey) }}</span>
                    <span class="text-red-500 font-mono font-medium">-{{ typeof val === 'number' ? val.toFixed(1) : val }}</span>
                  </li>
                </ul>
                <div class="mt-2 pt-2 border-t border-slate-100 dark:border-slate-700 flex justify-between text-xs font-semibold">
                  <span class="text-slate-700 dark:text-slate-300">Total Penalty</span>
                  <span class="text-red-600 font-mono">-{{ comp.penalties.total?.toFixed(1) }}</span>
                </div>
              </div>
            </div>

            <!-- Current Readings -->
            <div v-if="comp.current_data">
              <h5 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase mb-2">Current Readings</h5>
              <div class="bg-white dark:bg-slate-800 rounded-lg p-2 border border-slate-200 dark:border-slate-700">
                <div class="grid grid-cols-2 gap-x-4 gap-y-1">
                  <div
                      v-for="(val, dKey) in comp.current_data"
                      :key="dKey"
                      v-show="dKey !== 'timestamp'"
                      class="flex justify-between text-xs py-1 border-b border-slate-100 dark:border-slate-700/50 last:border-0"
                  >
                    <span class="text-slate-600 dark:text-slate-400 capitalize truncate pr-2">{{ formatSensorName(dKey) }}</span>
                    <span class="font-mono text-slate-900 dark:text-white">
                      {{ typeof val === 'number' ? val.toFixed(3) : val }}
                    </span>
                  </div>
                </div>
                <div v-if="comp.current_data.timestamp" class="mt-2 pt-2 border-t border-slate-100 dark:border-slate-700 text-[10px] text-slate-400">
                  Last reading: {{ formatDate(comp.current_data.timestamp) }}
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  healthData: { type: Object, required: true }
})

const expandedComponent = ref(null)

// Computed properties for safe access
const overallHealth = computed(() => props.healthData?.overall_health ?? {})
const analysisPeriod = computed(() => props.healthData?.analysis_period ?? { days_analyzed: props.healthData?.period_days })

// Methods
const toggleComponent = (key) => {
  expandedComponent.value = expandedComponent.value === key ? null : key
}

const formatDate = (dateStr) => {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  })
}

const formatComponentName = (name) => name?.replace(/_/g, ' ') ?? ''
const formatSensorName = (name) => name?.replace(/_/g, ' ') ?? ''

const getDaysToCritical = (comp) => {
  return comp.deterioration_analysis?.predictions?.days_to_critical ||
      comp.trend_analysis?.days_to_critical
}

// Color helpers
const getHealthColor = (score) => {
  if (score === null || score === undefined) return 'text-slate-400'
  if (score >= 80) return 'text-green-500'
  if (score >= 70) return 'text-blue-500'
  if (score >= 50) return 'text-amber-500'
  if (score >= 30) return 'text-orange-500'
  return 'text-red-500'
}

const getTextColor = (score) => {
  if (score === null || score === undefined) return 'text-slate-400'
  if (score >= 80) return 'text-green-600 dark:text-green-400'
  if (score >= 70) return 'text-blue-600 dark:text-blue-400'
  if (score >= 50) return 'text-amber-600 dark:text-amber-400'
  if (score >= 30) return 'text-orange-600 dark:text-orange-400'
  return 'text-red-600 dark:text-red-400'
}

const getBorderColor = (score) => {
  if (score === null || score === undefined) return 'border-slate-200 dark:border-slate-700'
  if (score >= 80) return 'border-green-200 dark:border-green-900'
  if (score >= 70) return 'border-blue-200 dark:border-blue-900'
  if (score >= 50) return 'border-amber-200 dark:border-amber-900'
  return 'border-red-200 dark:border-red-900'
}

const getStatusBadgeColor = (status) => {
  switch (status) {
    case 'EXCELLENT': return 'bg-green-100 text-green-700 border-green-200 dark:bg-green-900/30 dark:text-green-400 dark:border-green-900'
    case 'GOOD': return 'bg-blue-100 text-blue-700 border-blue-200 dark:bg-blue-900/30 dark:text-blue-400 dark:border-blue-900'
    case 'FAIR': return 'bg-amber-100 text-amber-700 border-amber-200 dark:bg-amber-900/30 dark:text-amber-400 dark:border-amber-900'
    case 'POOR': return 'bg-orange-100 text-orange-700 border-orange-200 dark:bg-orange-900/30 dark:text-orange-400 dark:border-orange-900'
    default: return 'bg-red-100 text-red-700 border-red-200 dark:bg-red-900/30 dark:text-red-400 dark:border-red-900'
  }
}

const getConfidenceBadge = (confidence) => {
  switch (confidence) {
    case 'HIGH': return 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
    case 'MEDIUM': return 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400'
    case 'LOW': return 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400'
    default: return 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400'
  }
}

const getZoneBadge = (zone) => {
  switch (zone) {
    case 'NORMAL': return 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
    case 'ELEVATED': return 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'
    case 'WARNING': return 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400'
    case 'CRITICAL': return 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'
    case 'FAILED': return 'bg-red-200 text-red-800 dark:bg-red-900/50 dark:text-red-300'
    case 'LOW': return 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'
    default: return 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400'
  }
}

const getTrendDirectionColor = (direction) => {
  switch (direction) {
    case 'INCREASING': return 'text-red-500'
    case 'DECREASING': return 'text-green-500'
    default: return 'text-slate-400'
  }
}

const getPredictionColor = (days) => {
  if (days === null || days === undefined) return 'text-slate-400'
  if (days <= 30) return 'text-red-600 dark:text-red-400'
  if (days <= 90) return 'text-orange-600 dark:text-orange-400'
  if (days <= 180) return 'text-amber-600 dark:text-amber-400'
  return 'text-slate-700 dark:text-slate-300'
}
</script>