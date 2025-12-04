<template>
  <div class="space-y-6">
    <!-- Chart Toolbar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">

      <!-- Metric Selector -->
      <div>
        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Metric</label>
        <select
            :value="metric"
            @input="$emit('update:metric', $event.target.value)"
            class="w-full md:w-64 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg px-3 py-2 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all shadow-sm"
        >
          <option value="performance">Performance (Power vs Wind)</option>
          <option value="temperatures">Component Temperatures</option>
          <option value="vibration_bearings">Bearing Vibrations</option>
          <option value="hydraulics">Hydraulic Pressures</option>
        </select>
      </div>

      <!-- Right Side: Resolution + Reset Zoom -->
      <div class="flex flex-col sm:flex-row items-end sm:items-center gap-4">

        <!-- Resolution Buttons -->
        <div>
          <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">
            Data Density <span class="text-xs font-normal text-slate-400 ml-1">(Scroll to zoom)</span>
          </label>
          <div class="flex items-center gap-1 bg-slate-100 dark:bg-slate-900 rounded-lg p-1 border border-slate-200 dark:border-slate-700">
            <button @click="$emit('update:resolution', 50)" :class="getResClass(50)">Low</button>
            <button @click="$emit('update:resolution', 200)" :class="getResClass(200)">Med</button>
            <button @click="$emit('update:resolution', 1000)" :class="getResClass(1000)">High</button>
            <button @click="$emit('update:resolution', 99999)" :class="getResClass(99999)">Full</button>
          </div>
        </div>

        <!-- Reset Zoom Button -->
        <button
            @click="handleResetZoom"
            class="h-[38px] px-4 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-300 rounded-lg text-sm font-medium transition-colors flex items-center gap-2 border border-slate-200 dark:border-slate-600"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" /></svg>
          Reset Zoom
        </button>
      </div>
    </div>

    <!-- The Chart Area -->
    <div class="relative h-[400px] w-full bg-slate-50 dark:bg-slate-900/50 rounded-xl p-4 border border-slate-100 dark:border-slate-700 group overflow-hidden">
      <!-- Overlay Hint -->
      <div class="absolute top-4 right-4 text-xs font-medium text-slate-400 bg-white/80 dark:bg-slate-800/80 px-2 py-1 rounded backdrop-blur-sm opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-10 border border-slate-200 dark:border-slate-600">
        Scroll to Zoom • Drag to Pan
      </div>

      <!-- Vue ChartJS Component -->
      <Line ref="chartRef" :data="chartData" :options="chartOptions" />
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { Line } from 'vue-chartjs'

const props = defineProps({
  chartData: Object,
  chartOptions: Object,
  metric: String,
  resolution: Number
})

const emit = defineEmits(['update:metric', 'update:resolution'])

const chartRef = ref(null)

const handleResetZoom = () => {
  if (chartRef.value && chartRef.value.chart) {
    chartRef.value.chart.resetZoom()
  }
}

const getResClass = (val) => {
  const base = "px-3 py-1 text-xs font-medium rounded-md transition-all duration-200"
  if (props.resolution === val) {
    return `${base} bg-white dark:bg-slate-700 shadow-sm text-indigo-600 dark:text-indigo-400 ring-1 ring-slate-200 dark:ring-slate-600`
  }
  return `${base} text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 hover:bg-slate-200/50 dark:hover:bg-slate-800`
}
</script>