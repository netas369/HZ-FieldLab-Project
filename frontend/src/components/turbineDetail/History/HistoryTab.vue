<template>
  <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">

    <div class="mb-6 border-b border-slate-200 dark:border-slate-700 pb-6">
      <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
        <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
        </svg>
        Historical Analysis
      </h3>

      <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
        <div>
          <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Start Date</label>
          <input
              type="date"
              v-model="form.start_date"
              class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg px-3 py-2 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all shadow-sm"
          >
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">End Date</label>
          <input
              type="date"
              v-model="form.end_date"
              class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg px-3 py-2 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all shadow-sm"
          >
        </div>

        <button
            @click="initiateFetch"
            :disabled="historyStore.loading || !form.start_date || !form.end_date"
            class="w-full bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-medium py-2 px-4 rounded-lg flex items-center justify-center gap-2 transition-all shadow-sm active:scale-95"
        >
          <svg v-if="historyStore.loading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <span>{{ historyStore.loading ? 'Processing...' : ('Fetch Data (~' + estimatedDuration + 's)')}}</span>
        </button>
      </div>
    </div>

    <HistoryLoader
        v-if="historyStore.loading"
        :day-count="dayCount"
        :estimated-wait="estimatedDuration"
        :progress="progressPercentage"
    />

    <div v-else-if="historyStore.data && chartData" class="space-y-6">

      <HistoryChart
          :chart-data="chartData"
          :chart-options="chartOptions"
          v-model:metric="selectedMetric"
          v-model:resolution="resolution"
      />

      <HistoryStats
          v-if="selectedMetric !== 'alarms'"
          :stats="telemetryStats"
          :displayed-points="displayedDataCount"
          :total-points="rawDataCount"
      />

      <HistoryAlarms
          v-else
          :alarms="rawResponse.alarms"
      />

    </div>

    <div v-else-if="historyStore.error" class="bg-red-50 dark:bg-red-900/10 border border-red-100 dark:border-red-900 rounded-xl p-8 text-center">
      <h3 class="text-lg font-bold text-red-900 dark:text-red-300 mb-2">Failed to load history</h3>
      <p class="text-red-600 dark:text-red-400 mb-4">{{ historyStore.error }}</p>
      <button @click="historyStore.clear()" class="px-4 py-2 bg-white dark:bg-slate-800 border border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 rounded-lg text-sm font-medium hover:bg-red-50 transition-colors">Dismiss</button>
    </div>

    <div v-else class="flex flex-col items-center justify-center py-16 text-center">
      <div class="bg-slate-50 dark:bg-slate-800/50 p-6 rounded-full mb-4 border border-slate-100 dark:border-slate-700">
        <svg class="w-10 h-10 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
      </div>
      <h3 class="text-lg font-medium text-slate-900 dark:text-white mb-1">No Data Selected</h3>
      <p class="text-slate-500 dark:text-slate-400 max-w-sm">Select a start and end date above to visualize the historical performance of this turbine.</p>
    </div>

  </div>
</template>

<script setup>
import { reactive, onUnmounted, ref, computed } from 'vue'
import { useScadaService } from '@/composables/api.js'

// --- CHART.JS SETUP ---
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  Title,
  Tooltip,
  Legend,
  Filler
} from 'chart.js'
import zoomPlugin from 'chartjs-plugin-zoom'
import 'hammerjs'

// --- SUB COMPONENTS ---
import HistoryChart from './HistoryChart.vue'
import HistoryStats from './HistoryStats.vue'
import HistoryLoader from './HistoryLoader.vue'
import HistoryAlarms from './Alarms/HistoryAlarms.vue' // <--- NEW COMPONENT

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, BarElement, Title, Tooltip, Legend, Filler, zoomPlugin)

const props = defineProps({ turbineId: { type: String, required: true } })
const { historyStore, turbineStore } = useScadaService()

const form = reactive({ start_date: '', end_date: '' })
const selectedMetric = ref('performance')
const resolution = ref(200)

// --- ID Logic ---
const resolvedTurbineId = computed(() => {
  if (props.turbineId.startsWith('WT')) return props.turbineId
  const turbine = turbineStore.turbines.find(t => t._api_id == props.turbineId)
  return turbine?.id || props.turbineId
})

// --- Loading Simulation ---
const progressPercentage = ref(0)
let progressInterval = null
const SECONDS_PER_DAY = 0.85

const dayCount = computed(() => {
  if (!form.start_date || !form.end_date) return 0
  const start = new Date(form.start_date)
  const end = new Date(form.end_date)
  return Math.ceil(Math.abs(end - start) / (1000 * 60 * 60 * 24)) + 1
})

const estimatedDuration = computed(() => Math.max((dayCount.value * SECONDS_PER_DAY).toFixed(1), 0.5))

const initiateFetch = async () => {
  progressPercentage.value = 0
  if (progressInterval) clearInterval(progressInterval)
  const totalSteps = estimatedDuration.value * 10
  const stepSize = 90 / totalSteps
  progressInterval = setInterval(() => {
    if (progressPercentage.value < 90) progressPercentage.value += stepSize
  }, 100)

  await historyStore.fetchHistory(resolvedTurbineId.value, form.start_date, form.end_date)

  clearInterval(progressInterval)
  progressPercentage.value = 100
}

onUnmounted(() => {
  historyStore.clear()
  if (progressInterval) clearInterval(progressInterval)
})

// --- Data Parsing ---
const rawResponse = computed(() => historyStore.data?.[0] || null)
const rawDataCount = computed(() => rawResponse.value?.scada?.scada_data?.length || 0)

const downsample = (dataArray) => {
  if (!dataArray || !dataArray.length) return []
  if (dataArray.length <= resolution.value) return dataArray
  const step = Math.ceil(dataArray.length / resolution.value)
  return dataArray.filter((_, index) => index % step === 0)
}

const scadaList = computed(() => downsample(rawResponse.value?.scada?.scada_data))
const tempList = computed(() => downsample(rawResponse.value?.temperature?.temperature_data))
const vibList = computed(() => downsample(rawResponse.value?.vibration?.vibration_data))
const hydroList = computed(() => downsample(rawResponse.value?.hydraulic?.hydraulic_data))
const displayedDataCount = computed(() => scadaList.value.length)

// --- Stats Logic (Telemetry Only) ---
// Alarm stats are now handled inside HistoryAlarms.vue
const telemetryStats = computed(() => {
  const fullScada = rawResponse.value?.scada?.scada_data || []
  if (!fullScada.length) return { type: 'telemetry', avgPower: 0, maxWind: 0, avgTemp: 0 }

  const avgPower = (fullScada.reduce((sum, d) => sum + parseFloat(d.power_kw), 0) / fullScada.length).toFixed(0)
  const maxWind = Math.max(...fullScada.map(d => parseFloat(d.wind_speed_ms))).toFixed(1)
  const avgTemp = (fullScada.reduce((sum, d) => sum + parseFloat(d.ambient_temp_c), 0) / fullScada.length).toFixed(1)

  return { type: 'telemetry', avgPower, maxWind, avgTemp }
})

// --- Chart Configuration ---
const chartOptions = computed(() => ({
  responsive: true,
  maintainAspectRatio: false,
  animation: false,
  interaction: { mode: 'index', intersect: false },
  plugins: {
    legend: {
      position: 'top',
      align: 'end',
      labels: { usePointStyle: true, boxWidth: 8, padding: 20, font: { size: 11 } }
    },
    tooltip: {
      backgroundColor: 'rgba(15, 23, 42, 0.9)',
      padding: 12,
      cornerRadius: 8,
      titleFont: { size: 13, weight: 'bold' },
      displayColors: true,
      usePointStyle: true
    },
    zoom: {
      pan: { enabled: true, mode: 'x' },
      zoom: { wheel: { enabled: true }, pinch: { enabled: true }, mode: 'x' },
      limits: { x: {min: 'original', max: 'original'} }
    }
  },
  scales: {
    x: {
      grid: { display: false },
      ticks: { maxRotation: 0, autoSkip: true, maxTicksLimit: 8 }
    },
    y: {
      type: 'linear',
      display: true,
      position: 'left',
      // Clean look for Bar chart vs Line chart
      grid: { color: selectedMetric.value === 'alarms' ? 'transparent' : 'rgba(148, 163, 184, 0.1)' },
      border: { display: false }
    },
    y1: {
      type: 'linear',
      display: selectedMetric.value === 'performance' || selectedMetric.value === 'hydraulics',
      position: 'right',
      grid: { drawOnChartArea: false },
      border: { display: false }
    },
  }
}))

const chartData = computed(() => {
  if (!rawResponse.value) return null

  const timeLabels = scadaList.value.map(d => new Date(d.reading_timestamp).toLocaleTimeString([], { month:'short', day:'numeric', hour: '2-digit', minute: '2-digit' }))

  const dataset = (label, data, color, yAxis = 'y', fill = false, type = 'line') => ({
    type, label, data, borderColor: color, backgroundColor: fill ? 'rgba(139, 92, 246, 0.1)' : undefined,
    fill, yAxisID: yAxis, pointRadius: 0, pointHoverRadius: 4, borderWidth: 2
  })

  // --- 1. ALARM FREQUENCY (Bar Chart) ---
  if (selectedMetric.value === 'alarms') {
    const alarmStats = rawResponse.value?.alarms?.statistics
    if (!alarmStats) return null

    const compStats = alarmStats.most_common_components
    const compLabels = Object.keys(compStats).map(k => k.replace(/_/g, ' ').toUpperCase())
    const compData = Object.values(compStats)

    return {
      labels: compLabels,
      datasets: [{
        type: 'bar',
        label: 'Alarm Frequency',
        data: compData,
        backgroundColor: ['rgba(248, 113, 113, 0.85)', 'rgba(96, 165, 250, 0.85)', 'rgba(251, 191, 36, 0.85)', 'rgba(52, 211, 153, 0.85)', 'rgba(167, 139, 250, 0.85)'],
        borderRadius: 4,
        barPercentage: 0.6,
        categoryPercentage: 0.8
      }]
    }
  }

  // --- 2. TELEMETRY CHARTS ---
  if (selectedMetric.value === 'performance') return { labels: timeLabels, datasets: [
      { ...dataset('Power Output (kW)', scadaList.value.map(d => d.power_kw), '#8b5cf6', 'y', true) },
      { ...dataset('Wind Speed (m/s)', scadaList.value.map(d => d.wind_speed_ms), '#3b82f6', 'y1'), borderDash: [4, 4] }
    ]}

  if (selectedMetric.value === 'temperatures') return { labels: timeLabels, datasets: [
      dataset('Gearbox', tempList.value.map(d => d.gearbox_bearing_temp_c), '#f97316'),
      dataset('Stator', tempList.value.map(d => d.generator_stator_temp_c), '#ef4444'),
      dataset('Main Bearing', tempList.value.map(d => d.main_bearing_temp_c), '#eab308')
    ]}

  if (selectedMetric.value === 'vibration_bearings') return { labels: timeLabels, datasets: [
      dataset('Main Bearing', vibList.value.map(d => d.main_bearing_vibration_rms_mms), '#10b981'),
      dataset('Gearbox', vibList.value.map(d => d.gearbox_vibration_axial_mms), '#6366f1'),
      dataset('Generator', vibList.value.map(d => d.generator_vibration_de_mms), '#ec4899')
    ]}

  if (selectedMetric.value === 'hydraulics') return { labels: timeLabels, datasets: [
      dataset('Hydraulic Pressure', hydroList.value.map(d => d.hydraulic_pressure_bar), '#06b6d4'),
      dataset('Gearbox Oil', hydroList.value.map(d => d.gearbox_oil_pressure_bar), '#84cc16', 'y1')
    ]}

  return null
})
</script>