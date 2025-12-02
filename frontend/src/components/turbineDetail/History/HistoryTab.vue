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

        <span v-if="form.start_date && form.end_date" class="text-slate-500 dark:text-slate-400">

          Estimated wait: <span class="font-mono text-indigo-600 dark:text-indigo-400 font-bold">{{ estimatedDuration }}s</span>
        </span>

        <button
            @click="initiateFetch"
            :disabled="historyStore.loading || !form.start_date || !form.end_date"
            class="w-full bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-medium py-2 px-4 rounded-lg flex items-center justify-center gap-2 transition-all shadow-sm active:scale-95"
        >
          <svg v-if="historyStore.loading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <span>{{ historyStore.loading ? 'Processing...' : 'Fetch Data' }}</span>
        </button>
      </div>
    </div>

    <div v-if="historyStore.loading" class="space-y-6">
      <div class="flex items-center justify-between text-sm">
        <span class="text-slate-600 dark:text-slate-300 font-medium">
          Retrieving {{ dayCount }} days of telemetry...
        </span>
      </div>

      <div class="h-2 w-full bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
        <div
            class="h-full bg-indigo-500 transition-all ease-out duration-300 rounded-full relative overflow-hidden"
            :style="{ width: `${progressPercentage}%` }"
        >
          <div class="absolute top-0 left-0 bottom-0 right-0 bg-gradient-to-r from-transparent via-white/30 to-transparent w-full -translate-x-full animate-[shimmer_1.5s_infinite]"></div>
        </div>
      </div>

      <div class="relative h-[400px] w-full bg-slate-50 dark:bg-slate-900/50 rounded-xl border border-slate-100 dark:border-slate-700 overflow-hidden flex flex-col">
        <div class="absolute inset-0"
             style="background-image: linear-gradient(to right, rgba(148, 163, 184, 0.1) 1px, transparent 1px), linear-gradient(to bottom, rgba(148, 163, 184, 0.1) 1px, transparent 1px); background-size: 40px 40px;">
        </div>

        <div class="absolute inset-0 flex items-center justify-center">
          <svg class="w-full h-32 text-slate-200 dark:text-slate-700 opacity-50" viewBox="0 0 400 100" preserveAspectRatio="none">
            <path d="M0,50 Q20,60 40,50 T80,50 T120,50 T160,50 T200,50 T240,50 T280,50 T320,50 T360,50 T400,50"
                  fill="none" stroke="currentColor" stroke-width="4"
                  class="animate-[pulse_1.5s_ease-in-out_infinite]"/>
          </svg>
        </div>

        <div class="absolute inset-0 flex flex-col items-center justify-center z-10">
          <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm px-6 py-3 rounded-full shadow-sm border border-slate-200 dark:border-slate-700 flex items-center gap-3">
             <span class="relative flex h-3 w-3">
               <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
               <span class="relative inline-flex rounded-full h-3 w-3 bg-indigo-500"></span>
             </span>
            <span class="text-sm font-medium text-slate-700 dark:text-slate-200">Synchronizing Timestamp {{ progressPercentage.toFixed(0) }}%</span>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-4 gap-4 animate-pulse">
        <div v-for="i in 4" :key="i" class="h-16 bg-slate-100 dark:bg-slate-800 rounded-lg"></div>
      </div>
    </div>

    <div v-else-if="historyStore.data && chartData" class="space-y-6">

      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Metric</label>
          <select
              v-model="selectedMetric"
              class="w-full md:w-64 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg px-3 py-2 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all shadow-sm"
          >
            <option value="performance">Performance (Power vs Wind)</option>
            <option value="temperatures">Component Temperatures</option>
            <option value="vibration_bearings">Bearing Vibrations</option>
            <option value="hydraulics">Hydraulic Pressures</option>
          </select>
        </div>

        <div class="flex flex-col sm:flex-row items-end sm:items-center gap-4">
          <div>
            <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">
              Data Density <span class="text-xs font-normal text-slate-400 ml-1">(Scroll to zoom)</span>
            </label>
            <div class="flex items-center gap-1 bg-slate-100 dark:bg-slate-900 rounded-lg p-1 border border-slate-200 dark:border-slate-700">
              <button @click="updateResolution(50)" :class="getResClass(50)">Low</button>
              <button @click="updateResolution(200)" :class="getResClass(200)">Med</button>
              <button @click="updateResolution(1000)" :class="getResClass(1000)">High</button>
              <button @click="updateResolution(99999)" :class="getResClass(99999)">Full</button>
            </div>
          </div>

          <button
              @click="resetZoom"
              class="h-[38px] px-4 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-300 rounded-lg text-sm font-medium transition-colors flex items-center gap-2 border border-slate-200 dark:border-slate-600"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" /></svg>
            Reset Zoom
          </button>
        </div>
      </div>

      <div class="relative h-[400px] w-full bg-slate-50 dark:bg-slate-900/50 rounded-xl p-4 border border-slate-100 dark:border-slate-700 group overflow-hidden">
        <div class="absolute top-4 right-4 text-xs font-medium text-slate-400 bg-white/80 dark:bg-slate-800/80 px-2 py-1 rounded backdrop-blur-sm opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-10 border border-slate-200 dark:border-slate-600">
          Scroll to Zoom • Drag to Pan
        </div>
        <Line ref="chartRef" :data="chartData" :options="chartOptions" />
      </div>

      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-slate-50 dark:bg-slate-900 p-4 rounded-lg border border-slate-100 dark:border-slate-700 text-center">
          <p class="text-xs text-slate-500 dark:text-slate-400 mb-1 font-medium uppercase tracking-wider">Data Points</p>
          <p class="text-lg font-bold text-slate-900 dark:text-white">{{ displayedDataCount }} <span class="text-xs text-slate-400 font-normal">/ {{ rawDataCount }}</span></p>
        </div>
        <div class="bg-slate-50 dark:bg-slate-900 p-4 rounded-lg border border-slate-100 dark:border-slate-700 text-center">
          <p class="text-xs text-slate-500 dark:text-slate-400 mb-1 font-medium uppercase tracking-wider">Avg Power</p>
          <p class="text-lg font-bold text-slate-900 dark:text-white">{{ stats.avgPower }} <span class="text-xs text-slate-400 font-normal">kW</span></p>
        </div>
        <div class="bg-slate-50 dark:bg-slate-900 p-4 rounded-lg border border-slate-100 dark:border-slate-700 text-center">
          <p class="text-xs text-slate-500 dark:text-slate-400 mb-1 font-medium uppercase tracking-wider">Max Wind</p>
          <p class="text-lg font-bold text-slate-900 dark:text-white">{{ stats.maxWind }} <span class="text-xs text-slate-400 font-normal">m/s</span></p>
        </div>
        <div class="bg-slate-50 dark:bg-slate-900 p-4 rounded-lg border border-slate-100 dark:border-slate-700 text-center">
          <p class="text-xs text-slate-500 dark:text-slate-400 mb-1 font-medium uppercase tracking-wider">Avg Temp</p>
          <p class="text-lg font-bold text-slate-900 dark:text-white">{{ stats.avgTemp }} <span class="text-xs text-slate-400 font-normal">°C</span></p>
        </div>
      </div>
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
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, Filler } from 'chart.js'
import { Line } from 'vue-chartjs'
import zoomPlugin from 'chartjs-plugin-zoom'
import 'hammerjs'

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, Filler, zoomPlugin)

const props = defineProps({ turbineId: { type: String, required: true } })
const { historyStore } = useScadaService()

const form = reactive({ start_date: '', end_date: '' })
const selectedMetric = ref('performance')
const resolution = ref(200)
const chartRef = ref(null)

// --- Estimation & Loading State ---
const progressPercentage = ref(0)
let progressInterval = null

// Calculation: 7 days = 3.0 seconds -> ~0.43s per day
const SECONDS_PER_DAY = 0.43

const dayCount = computed(() => {
  if (!form.start_date || !form.end_date) return 0
  const start = new Date(form.start_date)
  const end = new Date(form.end_date)
  const diffTime = Math.abs(end - start)
  return Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1 // Inclusive
})

const estimatedDuration = computed(() => {
  const duration = (dayCount.value * SECONDS_PER_DAY).toFixed(1)
  return Math.max(duration, 0.5) // Minimum 0.5s to see animation
})

const initiateFetch = async () => {
  // Reset
  progressPercentage.value = 0
  if (progressInterval) clearInterval(progressInterval)

  // Start Simulation
  const totalSteps = estimatedDuration.value * 10 // Update every 100ms
  const stepSize = 90 / totalSteps // Target 90% completion

  progressInterval = setInterval(() => {
    if (progressPercentage.value < 90) {
      progressPercentage.value += stepSize
    }
  }, 100)

  // Real API Call
  await historyStore.fetchHistory(props.turbineId, form.start_date, form.end_date)

  // Finish
  clearInterval(progressInterval)
  progressPercentage.value = 100
}

const resetZoom = () => {
  if (chartRef.value && chartRef.value.chart) {
    chartRef.value.chart.resetZoom()
  }
}

const updateResolution = (newRes) => {
  resolution.value = newRes;
  resetZoom();
}

const getResClass = (val) => {
  const base = "px-3 py-1 text-xs font-medium rounded-md transition-all duration-200"
  if (resolution.value === val) {
    return `${base} bg-white dark:bg-slate-700 shadow-sm text-indigo-600 dark:text-indigo-400 ring-1 ring-slate-200 dark:ring-slate-600`
  }
  return `${base} text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 hover:bg-slate-200/50 dark:hover:bg-slate-800`
}

onUnmounted(() => {
  historyStore.clear()
  if (progressInterval) clearInterval(progressInterval)
})

// --- Data Parsing (Standard) ---
const rawResponse = computed(() => historyStore.data?.[0] || null)
const rawDataCount = computed(() => rawResponse.value?.scada?.scada_data?.length || 0)

const downsample = (dataArray) => {
  if (!dataArray || dataArray.length === 0) return []
  const totalPoints = dataArray.length
  if (totalPoints <= resolution.value) return dataArray
  const step = Math.ceil(totalPoints / resolution.value)
  return dataArray.filter((_, index) => index % step === 0)
}

const scadaList = computed(() => downsample(rawResponse.value?.scada?.scada_data))
const tempList = computed(() => downsample(rawResponse.value?.temperature?.temperature_data))
const vibList = computed(() => downsample(rawResponse.value?.vibration?.vibration_data))
const hydroList = computed(() => downsample(rawResponse.value?.hydraulic?.hydraulic_data))
const displayedDataCount = computed(() => scadaList.value.length)

const stats = computed(() => {
  const fullScada = rawResponse.value?.scada?.scada_data || []
  if (!fullScada.length) return { avgPower: 0, maxWind: 0, avgTemp: 0 }
  const avgPower = (fullScada.reduce((sum, d) => sum + parseFloat(d.power_kw), 0) / fullScada.length).toFixed(0)
  const maxWind = Math.max(...fullScada.map(d => parseFloat(d.wind_speed_ms))).toFixed(1)
  const avgTemp = (fullScada.reduce((sum, d) => sum + parseFloat(d.ambient_temp_c), 0) / fullScada.length).toFixed(1)
  return { avgPower, maxWind, avgTemp }
})

const chartOptions = computed(() => ({
  responsive: true,
  maintainAspectRatio: false,
  animation: false,
  interaction: { mode: 'index', intersect: false },
  plugins: {
    legend: { position: 'top', align: 'end', labels: { usePointStyle: true, boxWidth: 8, padding: 20, font: { size: 11 } } },
    tooltip: { backgroundColor: 'rgba(15, 23, 42, 0.9)', padding: 12, cornerRadius: 8, titleFont: { size: 13, weight: 'bold' }, displayColors: true, usePointStyle: true },
    zoom: { pan: { enabled: true, mode: 'x' }, zoom: { wheel: { enabled: true }, pinch: { enabled: true }, mode: 'x' }, limits: { x: {min: 'original', max: 'original'} } }
  },
  scales: {
    x: { grid: { display: false }, ticks: { maxRotation: 0, autoSkip: true, maxTicksLimit: 8 } },
    y: { type: 'linear', display: true, position: 'left', grid: { color: 'rgba(148, 163, 184, 0.1)' }, border: { display: false } },
    y1: { type: 'linear', display: selectedMetric.value === 'performance' || selectedMetric.value === 'hydraulics', position: 'right', grid: { drawOnChartArea: false }, border: { display: false } },
  }
}))

const chartData = computed(() => {
  if (!rawResponse.value) return null
  const labels = scadaList.value.map(d => {
    const date = new Date(d.reading_timestamp)
    return date.toLocaleTimeString([], { month:'short', day:'numeric', hour: '2-digit', minute: '2-digit' })
  })

  // 1. Performance
  if (selectedMetric.value === 'performance') {
    return {
      labels,
      datasets: [
        {
          label: 'Power Output (kW)',
          data: scadaList.value.map(d => d.power_kw),
          borderColor: '#8b5cf6',
          backgroundColor: (context) => {
            const ctx = context.chart.ctx;
            const gradient = ctx.createLinearGradient(0, 0, 0, 400);
            gradient.addColorStop(0, 'rgba(139, 92, 246, 0.5)');
            gradient.addColorStop(1, 'rgba(139, 92, 246, 0.0)');
            return gradient;
          },
          fill: true,
          yAxisID: 'y',
          pointRadius: 0,
          pointHoverRadius: 4,
          borderWidth: 2
        },
        { label: 'Wind Speed (m/s)', data: scadaList.value.map(d => d.wind_speed_ms), borderColor: '#3b82f6', borderDash: [4, 4], yAxisID: 'y1', pointRadius: 0, pointHoverRadius: 4, borderWidth: 2 }
      ]
    }
  }
  // 2. Temperatures
  if (selectedMetric.value === 'temperatures') {
    return {
      labels,
      datasets: [
        { label: 'Gearbox', data: tempList.value.map(d => d.gearbox_bearing_temp_c), borderColor: '#f97316', pointRadius: 0, pointHoverRadius: 4, borderWidth: 2 },
        { label: 'Stator', data: tempList.value.map(d => d.generator_stator_temp_c), borderColor: '#ef4444', pointRadius: 0, pointHoverRadius: 4, borderWidth: 2 },
        { label: 'Main Bearing', data: tempList.value.map(d => d.main_bearing_temp_c), borderColor: '#eab308', pointRadius: 0, pointHoverRadius: 4, borderWidth: 2 }
      ]
    }
  }
  // 3. Vibration
  if (selectedMetric.value === 'vibration_bearings') {
    return {
      labels,
      datasets: [
        { label: 'Main Bearing', data: vibList.value.map(d => d.main_bearing_vibration_rms_mms), borderColor: '#10b981', pointRadius: 0, pointHoverRadius: 4, borderWidth: 2 },
        { label: 'Gearbox', data: vibList.value.map(d => d.gearbox_vibration_axial_mms), borderColor: '#6366f1', pointRadius: 0, pointHoverRadius: 4, borderWidth: 2 },
        { label: 'Generator', data: vibList.value.map(d => d.generator_vibration_de_mms), borderColor: '#ec4899', pointRadius: 0, pointHoverRadius: 4, borderWidth: 2 }
      ]
    }
  }
  // 4. Hydraulics
  if (selectedMetric.value === 'hydraulics') {
    return {
      labels,
      datasets: [
        { label: 'Hydraulic Pressure', data: hydroList.value.map(d => d.hydraulic_pressure_bar), borderColor: '#06b6d4', pointRadius: 0, pointHoverRadius: 4, borderWidth: 2 },
        { label: 'Gearbox Oil', data: hydroList.value.map(d => d.gearbox_oil_pressure_bar), borderColor: '#84cc16', yAxisID: 'y1', pointRadius: 0, pointHoverRadius: 4, borderWidth: 2 }
      ]
    }
  }
  return null
})
</script>

<style scoped>
@keyframes shimmer {
  0% { transform: translateX(-100%); }
  100% { transform: translateX(100%); }
}
</style>