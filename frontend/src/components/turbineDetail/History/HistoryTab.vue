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
          <input type="date" v-model="form.start_date" class="input-field">
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">End Date</label>
          <input type="date" v-model="form.end_date" class="input-field">
        </div>
        <button
            @click="handleFetch"
            :disabled="historyStore.loading || !form.start_date || !form.end_date"
            class="btn-primary"
        >
          <span v-if="historyStore.loading" class="animate-spin rounded-full h-4 w-4 border-2 border-white border-t-transparent"></span>
          <span>{{ historyStore.loading ? 'Fetching...' : 'Fetch Data' }}</span>
        </button>
      </div>
    </div>

    <div v-if="historyStore.data && chartData" class="space-y-6">

      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">

        <div>
          <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Metric</label>
          <select v-model="selectedMetric" class="input-field w-64">
            <option value="performance">Performance (Power vs Wind)</option>
            <option value="temperatures">Component Temperatures</option>
            <option value="vibration_bearings">Bearing Vibrations</option>
            <option value="hydraulics">Hydraulic Pressures</option>
          </select>
        </div>

        <div class="flex items-end gap-4">
          <div>
            <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">
              Data Density
              <span class="text-xs font-normal text-slate-400">(Set to 'Full' for deep zooming)</span>
            </label>
            <div class="flex items-center gap-2 bg-slate-50 dark:bg-slate-900 rounded-lg p-1 border border-slate-200 dark:border-slate-700">
              <button @click="updateResolution(50)" :class="getResClass(50)">Low</button>
              <button @click="updateResolution(200)" :class="getResClass(200)">Med</button>
              <button @click="updateResolution(1000)" :class="getResClass(1000)">High</button>
              <button @click="updateResolution(99999)" :class="getResClass(99999)">Full</button>
            </div>
          </div>

          <button
              @click="resetZoom"
              class="h-[38px] px-3 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-300 rounded-lg text-sm font-medium transition-colors flex items-center gap-1"
              title="Reset Zoom Level"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" /></svg>
            Reset
          </button>
        </div>

      </div>

      <div class="relative h-[400px] w-full bg-slate-50 dark:bg-slate-900/50 rounded-xl p-4 border border-slate-100 dark:border-slate-700 group">
        <div class="absolute top-6 right-6 text-xs text-slate-400 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-10">
          Scroll to Zoom • Drag to Pan
        </div>

        <Line ref="chartRef" :data="chartData" :options="chartOptions" />
      </div>

      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-xs text-slate-500">
        <div>Total Points: <span class="font-bold text-slate-900 dark:text-white">{{ rawDataCount }}</span></div>
        <div>Displayed Points: <span class="font-bold text-slate-900 dark:text-white">{{ displayedDataCount }}</span></div>
      </div>
    </div>

    <div v-else-if="historyStore.error" class="error-box">
      <p>{{ historyStore.error }}</p>
      <button @click="historyStore.clear()" class="text-sm underline mt-2">Clear</button>
    </div>
    <div v-else-if="!historyStore.loading" class="empty-state">
      <svg class="w-12 h-12 text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
      <p>Select a date range to visualize trends</p>
    </div>

  </div>
</template>

<script setup>
import { reactive, onUnmounted, ref, computed } from 'vue'
import { useScadaService } from '@/composables/api.js'
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend
} from 'chart.js'
import { Line } from 'vue-chartjs'
// 1. Import the Plugin and HammerJS
import zoomPlugin from 'chartjs-plugin-zoom'
import 'hammerjs' // Required for panning

// 2. Register the Plugin
ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
    zoomPlugin
)

const props = defineProps({ turbineId: { type: String, required: true } })
const { historyStore } = useScadaService()

const form = reactive({ start_date: '', end_date: '' })
const selectedMetric = ref('performance')
const resolution = ref(200)
const chartRef = ref(null) // Reference to the chart component

const handleFetch = () => {
  historyStore.fetchHistory(props.turbineId, form.start_date, form.end_date)
}

// Reset Zoom Function
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
  return `px-3 py-1 text-xs font-medium rounded transition-colors ${
      resolution.value === val
          ? 'bg-white dark:bg-slate-700 shadow text-indigo-600'
          : 'text-slate-500 hover:text-slate-700'
  }`
}

onUnmounted(() => historyStore.clear())

// --- Data Logic (Downsampling) ---
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


// --- Chart Config with Zoom ---
const chartOptions = computed(() => ({
  responsive: true,
  maintainAspectRatio: false,
  animation: false,
  interaction: { mode: 'index', intersect: false },
  plugins: {
    legend: { position: 'top', labels: { usePointStyle: true } },
    tooltip: {
      backgroundColor: 'rgba(15, 23, 42, 0.9)',
      padding: 12,
      cornerRadius: 8
    },
    // 3. Configure Zoom Plugin
    zoom: {
      pan: {
        enabled: true,
        mode: 'x', // Allow panning only horizontally
      },
      zoom: {
        wheel: {
          enabled: true,
        },
        pinch: {
          enabled: true
        },
        mode: 'x', // Allow zooming only horizontally (keep values fixed)
      },
      limits: {
        x: {min: 'original', max: 'original'}, // Prevent scrolling past data limits
      }
    }
  },
  scales: {
    y: { type: 'linear', display: true, position: 'left', grid: { color: 'rgba(148, 163, 184, 0.1)' } },
    y1: { type: 'linear', display: selectedMetric.value === 'performance', position: 'right', grid: { drawOnChartArea: false } },
  }
}))

// --- Chart Data (Same as before) ---
const chartData = computed(() => {
  if (!rawResponse.value) return null

  // Use simple index or timestamp labels
  const labels = scadaList.value.map(d => {
    const date = new Date(d.reading_timestamp)
    return date.toLocaleTimeString([], { month:'short', day:'numeric', hour: '2-digit', minute: '2-digit' })
  })

  // 1. Performance
  if (selectedMetric.value === 'performance') {
    return {
      labels,
      datasets: [
        { label: 'Power Output (kW)', data: scadaList.value.map(d => d.power_kw), borderColor: '#8b5cf6', backgroundColor: 'rgba(139, 92, 246, 0.1)', fill: true, yAxisID: 'y', pointRadius: 2, tension: 0.3 },
        { label: 'Wind Speed (m/s)', data: scadaList.value.map(d => d.wind_speed_ms), borderColor: '#3b82f6', borderDash: [5, 5], yAxisID: 'y1', pointRadius: 0, tension: 0.3 }
      ]
    }
  }
  // 2. Temperatures
  if (selectedMetric.value === 'temperatures') {
    return {
      labels,
      datasets: [
        { label: 'Gearbox Bearing', data: tempList.value.map(d => d.gearbox_bearing_temp_c), borderColor: '#f97316', pointRadius: 1 },
        { label: 'Generator Stator', data: tempList.value.map(d => d.generator_stator_temp_c), borderColor: '#ef4444', pointRadius: 1 },
        { label: 'Main Bearing', data: tempList.value.map(d => d.main_bearing_temp_c), borderColor: '#eab308', pointRadius: 1 }
      ]
    }
  }
  // 3. Vibration
  if (selectedMetric.value === 'vibration_bearings') {
    return {
      labels,
      datasets: [
        { label: 'Main Bearing RMS', data: vibList.value.map(d => d.main_bearing_vibration_rms_mms), borderColor: '#10b981', pointRadius: 1 },
        { label: 'Gearbox Axial', data: vibList.value.map(d => d.gearbox_vibration_axial_mms), borderColor: '#6366f1', pointRadius: 1 },
        { label: 'Generator DE', data: vibList.value.map(d => d.generator_vibration_de_mms), borderColor: '#ec4899', pointRadius: 1 }
      ]
    }
  }
  // 4. Hydraulics
  if (selectedMetric.value === 'hydraulics') {
    return {
      labels,
      datasets: [
        { label: 'Hydraulic Pressure', data: hydroList.value.map(d => d.hydraulic_pressure_bar), borderColor: '#06b6d4', pointRadius: 1 },
        { label: 'Gearbox Oil', data: hydroList.value.map(d => d.gearbox_oil_pressure_bar), borderColor: '#84cc16', yAxisID: 'y1', pointRadius: 1 }
      ]
    }
  }
  return null
})
</script>

<style scoped>
@reference "tailwindcss";

.input-field {
  @apply w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg px-3 py-2 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all;
}
.btn-primary {
  @apply w-full bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-medium py-2 px-4 rounded-lg flex items-center justify-center gap-2 transition-colors shadow-sm;
}
.error-box {
  @apply bg-red-50 dark:bg-red-900/10 border border-red-100 dark:border-red-900 rounded-xl p-8 text-center text-red-600 dark:text-red-400 font-medium;
}
.empty-state {
  @apply flex flex-col items-center justify-center py-12 text-slate-500 dark:text-slate-400 text-center;
}
</style>