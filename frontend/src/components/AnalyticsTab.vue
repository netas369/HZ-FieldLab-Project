<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <div>
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Analytics Dashboard</h2>
        <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">Real-time performance metrics and insights</p>
      </div>

      <!-- Time Range Selector -->
      <div class="flex bg-slate-100 dark:bg-slate-800 rounded-lg p-1">
        <button
            v-for="range in timeRanges"
            :key="range"
            @click="selectedTimeRange = range"
            :class="[
            'px-4 py-2 rounded-md text-sm font-medium transition-all',
            selectedTimeRange === range
              ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-sm'
              : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'
          ]"
        >
          {{ range }}
        </button>
      </div>
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
      <div
          v-for="(kpi, index) in kpiMetrics"
          :key="index"
          class="bg-white dark:bg-slate-800 rounded-xl shadow-sm hover:shadow-md transition-all p-6 border border-slate-200 dark:border-slate-700 group"
      >
        <div class="flex items-start justify-between mb-4">
          <div :class="['p-3 rounded-lg group-hover:scale-110 transition-transform', getKpiColorClass(kpi.color)]">
            <component :is="getIcon(kpi.icon)" class="w-6 h-6" />
          </div>
          <div :class="['flex items-center gap-1 text-sm font-medium', getTrendColor(kpi.change)]">
            <component :is="getTrendIcon(kpi.change)" class="w-4 h-4" />
            <span>{{ kpi.trend }}</span>
          </div>
        </div>
        <div>
          <p class="text-sm text-slate-600 dark:text-slate-400 mb-1">{{ kpi.label }}</p>
          <p class="text-3xl font-bold text-slate-900 dark:text-white">{{ kpi.value }}</p>
        </div>
      </div>
    </div>

    <!-- Charts Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Power Generation Chart -->
      <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6 border border-slate-200 dark:border-slate-700">
        <div class="flex items-center justify-between mb-4">
          <div>
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Power Generation</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Last 24 hours</p>
          </div>
          <div class="flex items-center gap-2">
            <div class="flex items-center gap-1.5">
              <div class="w-3 h-3 rounded-full bg-blue-500"></div>
              <span class="text-xs text-slate-600 dark:text-slate-400">Power</span>
            </div>
            <div class="flex items-center gap-1.5">
              <div class="w-3 h-3 rounded-full bg-slate-300 dark:bg-slate-600"></div>
              <span class="text-xs text-slate-600 dark:text-slate-400">Capacity</span>
            </div>
          </div>
        </div>
        <div class="h-64">
          <svg viewBox="0 0 600 200" class="w-full h-full">
            <!-- Grid lines -->
            <line v-for="i in 5" :key="`h-${i}`"
                  :x1="60" :y1="i * 40"
                  :x2="580" :y2="i * 40"
                  stroke="currentColor"
                  class="text-slate-200 dark:text-slate-700"
                  stroke-width="1"
                  opacity="0.5" />

            <!-- Capacity line -->
            <polyline
                :points="capacityPoints"
                fill="none"
                stroke="currentColor"
                class="text-slate-300 dark:text-slate-600"
                stroke-width="2"
                stroke-dasharray="5,5"
            />

            <!-- Power area -->
            <path
                :d="powerAreaPath"
                fill="url(#powerGradient)"
                opacity="0.3"
            />

            <!-- Power line -->
            <polyline
                :points="powerPoints"
                fill="none"
                stroke="currentColor"
                class="text-blue-500"
                stroke-width="3"
                stroke-linecap="round"
                stroke-linejoin="round"
            />

            <!-- Data points -->
            <circle
                v-for="(point, i) in powerData"
                :key="`point-${i}`"
                :cx="60 + (i * 520 / (powerData.length - 1))"
                :cy="180 - (point.power / 3 * 160)"
                r="4"
                fill="currentColor"
                class="text-blue-500"
            />

            <!-- Y-axis labels -->
            <text x="50" y="185" text-anchor="end" class="text-xs fill-slate-600 dark:fill-slate-400">0</text>
            <text x="50" y="105" text-anchor="end" class="text-xs fill-slate-600 dark:fill-slate-400">1.5</text>
            <text x="50" y="25" text-anchor="end" class="text-xs fill-slate-600 dark:fill-slate-400">3.0</text>

            <!-- X-axis labels -->
            <text v-for="(point, i) in powerData" :key="`label-${i}`"
                  :x="60 + (i * 520 / (powerData.length - 1))"
                  y="195"
                  text-anchor="middle"
                  class="text-xs fill-slate-600 dark:fill-slate-400"
            >
              {{ point.time }}
            </text>

            <defs>
              <linearGradient id="powerGradient" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" stop-color="#3b82f6" />
                <stop offset="100%" stop-color="#3b82f6" stop-opacity="0" />
              </linearGradient>
            </defs>
          </svg>
        </div>
      </div>

      <!-- Turbine Performance Chart -->
      <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6 border border-slate-200 dark:border-slate-700">
        <div class="mb-4">
          <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Turbine Performance</h3>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Individual efficiency ratings</p>
        </div>
        <div class="h-64 flex items-end justify-around gap-2 px-4">
          <div
              v-for="turbine in turbinePerformance"
              :key="turbine.name"
              class="flex-1 flex flex-col items-center group"
          >
            <div class="w-full relative">
              <div
                  :style="{ height: `${turbine.performance * 2}px` }"
                  :class="[
                  'w-full rounded-t-lg transition-all duration-300 group-hover:opacity-80',
                  getPerformanceColor(turbine.performance)
                ]"
              >
                <div class="absolute -top-6 left-1/2 -translate-x-1/2 opacity-0 group-hover:opacity-100 transition-opacity">
                  <div class="bg-slate-900 dark:bg-slate-700 text-white text-xs px-2 py-1 rounded whitespace-nowrap">
                    {{ turbine.performance }}%
                  </div>
                </div>
              </div>
            </div>
            <span class="text-xs text-slate-600 dark:text-slate-400 mt-2 font-medium">{{ turbine.name }}</span>
          </div>
        </div>
      </div>

      <!-- Downtime Analysis -->
      <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6 border border-slate-200 dark:border-slate-700">
        <div class="mb-4">
          <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Downtime Analysis</h3>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Breakdown by category</p>
        </div>
        <div class="flex items-center justify-center h-64">
          <svg viewBox="0 0 200 200" class="w-48 h-48">
            <circle
                v-for="(segment, index) in downtimeSegments"
                :key="index"
                cx="100"
                cy="100"
                r="70"
                fill="none"
                :stroke="segment.color"
                stroke-width="40"
                :stroke-dasharray="`${segment.length} ${440 - segment.length}`"
                :stroke-dashoffset="segment.offset"
                class="transition-all duration-300 hover:stroke-width-[45] cursor-pointer"
                transform="rotate(-90 100 100)"
            />
            <text x="100" y="95" text-anchor="middle" class="text-2xl font-bold fill-slate-900 dark:fill-white">
              {{ totalDowntime }}
            </text>
            <text x="100" y="110" text-anchor="middle" class="text-xs fill-slate-600 dark:fill-slate-400">
              hours
            </text>
          </svg>
        </div>
        <div class="space-y-2 mt-4">
          <div
              v-for="item in downtimeData"
              :key="item.category"
              class="flex items-center justify-between p-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors"
          >
            <div class="flex items-center gap-2">
              <div :class="['w-3 h-3 rounded-full', getDowntimeColorClass(item.category)]"></div>
              <span class="text-sm text-slate-700 dark:text-slate-300">{{ item.category }}</span>
            </div>
            <div class="text-right">
              <span class="text-sm font-semibold text-slate-900 dark:text-white">{{ item.hours }}h</span>
              <span class="text-xs text-slate-500 dark:text-slate-400 ml-1">({{ item.percent }}%)</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Monthly Trends -->
      <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6 border border-slate-200 dark:border-slate-700">
        <div class="mb-4">
          <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Monthly Trends</h3>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Energy output over time</p>
        </div>
        <div class="h-64">
          <svg viewBox="0 0 600 200" class="w-full h-full">
            <!-- Grid -->
            <line v-for="i in 5" :key="`grid-${i}`"
                  :x1="60" :y1="i * 40"
                  :x2="580" :y2="i * 40"
                  stroke="currentColor"
                  class="text-slate-200 dark:text-slate-700"
                  stroke-width="1"
                  opacity="0.5"
            />

            <!-- Bars -->
            <rect
                v-for="(month, i) in monthlyTrends"
                :key="`bar-${i}`"
                :x="80 + (i * 80)"
                :y="180 - (month.energy / 3000 * 160)"
                width="50"
                :height="month.energy / 3000 * 160"
                :class="getMonthBarClass(month.energy)"
                rx="4"
                class="transition-all duration-300 hover:opacity-80 cursor-pointer"
            />

            <!-- Values -->
            <text
                v-for="(month, i) in monthlyTrends"
                :key="`val-${i}`"
                :x="105 + (i * 80)"
                :y="170 - (month.energy / 3000 * 160)"
                text-anchor="middle"
                class="text-xs font-semibold fill-slate-700 dark:fill-slate-300"
            >
              {{ month.energy }}
            </text>

            <!-- X-axis labels -->
            <text
                v-for="(month, i) in monthlyTrends"
                :key="`month-${i}`"
                :x="105 + (i * 80)"
                y="195"
                text-anchor="middle"
                class="text-xs fill-slate-600 dark:fill-slate-400"
            >
              {{ month.month }}
            </text>
          </svg>
        </div>
      </div>
    </div>

    <!-- Additional Insights -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 rounded-xl p-6 border border-blue-200 dark:border-blue-800">
        <div class="flex items-center gap-3 mb-3">
          <div class="p-2 bg-blue-500 rounded-lg">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
          </div>
          <h4 class="font-semibold text-slate-900 dark:text-white">Peak Performance</h4>
        </div>
        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400 mb-1">2.9 MW</p>
        <p class="text-sm text-slate-600 dark:text-slate-400">Achieved at 12:00 PM today</p>
      </div>

      <div class="bg-gradient-to-br from-emerald-50 to-emerald-100 dark:from-emerald-900/20 dark:to-emerald-800/20 rounded-xl p-6 border border-emerald-200 dark:border-emerald-800">
        <div class="flex items-center gap-3 mb-3">
          <div class="p-2 bg-emerald-500 rounded-lg">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <h4 class="font-semibold text-slate-900 dark:text-white">Optimal Conditions</h4>
        </div>
        <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mb-1">76%</p>
        <p class="text-sm text-slate-600 dark:text-slate-400">Of time in optimal wind range</p>
      </div>

      <div class="bg-gradient-to-br from-violet-50 to-violet-100 dark:from-violet-900/20 dark:to-violet-800/20 rounded-xl p-6 border border-violet-200 dark:border-violet-800">
        <div class="flex items-center gap-3 mb-3">
          <div class="p-2 bg-violet-500 rounded-lg">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <h4 class="font-semibold text-slate-900 dark:text-white">Revenue Today</h4>
        </div>
        <p class="text-2xl font-bold text-violet-600 dark:text-violet-400 mb-1">$12,847</p>
        <p class="text-sm text-slate-600 dark:text-slate-400">Based on current rates</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  kpis: { type: Array, default: () => [] },
  charts: { type: Array, default: () => [] }
})

// State
const selectedTimeRange = ref('7d')
const timeRanges = ['24h', '7d', '30d', '90d']

// Data
const kpiMetrics = [
  {
    label: 'Total Energy Output',
    value: '2,847 MWh',
    trend: '+12.5%',
    change: 'up',
    icon: 'zap',
    color: 'emerald'
  },
  {
    label: 'Average Wind Speed',
    value: '12.4 m/s',
    trend: '+2.1%',
    change: 'up',
    icon: 'wind',
    color: 'blue'
  },
  {
    label: 'Fleet Efficiency',
    value: '94.2%',
    trend: '-0.8%',
    change: 'down',
    icon: 'activity',
    color: 'violet'
  },
  {
    label: 'Downtime Hours',
    value: '18.5 hrs',
    trend: '-15.2%',
    change: 'up',
    icon: 'alert',
    color: 'orange'
  }
]

const powerData = [
  { time: '00:00', power: 2.1, capacity: 3.0 },
  { time: '04:00', power: 2.5, capacity: 3.0 },
  { time: '08:00', power: 2.8, capacity: 3.0 },
  { time: '12:00', power: 2.9, capacity: 3.0 },
  { time: '16:00', power: 2.7, capacity: 3.0 },
  { time: '20:00', power: 2.4, capacity: 3.0 },
  { time: '24:00', power: 2.2, capacity: 3.0 }
]

const turbinePerformance = [
  { name: 'WT-001', performance: 98.5 },
  { name: 'WT-002', performance: 97.2 },
  { name: 'WT-003', performance: 85.3 },
  { name: 'WT-004', performance: 99.1 },
  { name: 'WT-005', performance: 92.8 },
  { name: 'WT-006', performance: 96.4 }
]

const downtimeData = [
  { category: 'Scheduled', hours: 8, percent: 43, color: '#3b82f6' },
  { category: 'Unscheduled', hours: 6, percent: 32, color: '#f59e0b' },
  { category: 'Weather', hours: 4.5, percent: 25, color: '#ef4444' }
]

const monthlyTrends = [
  { month: 'Jan', energy: 2650 },
  { month: 'Feb', energy: 2780 },
  { month: 'Mar', energy: 2920 },
  { month: 'Apr', energy: 2850 },
  { month: 'May', energy: 2740 },
  { month: 'Jun', energy: 2680 }
]

// Computed
const totalDowntime = computed(() => {
  return downtimeData.reduce((sum, item) => sum + item.hours, 0)
})

const downtimeSegments = computed(() => {
  const circumference = 2 * Math.PI * 70
  let offset = 0

  return downtimeData.map(item => {
    const length = (item.percent / 100) * circumference
    const segment = {
      length,
      offset: -offset,
      color: item.color
    }
    offset += length
    return segment
  })
})

const powerPoints = computed(() => {
  return powerData.map((point, i) => {
    const x = 60 + (i * 520 / (powerData.length - 1))
    const y = 180 - (point.power / 3 * 160)
    return `${x},${y}`
  }).join(' ')
})

const capacityPoints = computed(() => {
  return powerData.map((point, i) => {
    const x = 60 + (i * 520 / (powerData.length - 1))
    const y = 180 - (point.capacity / 3 * 160)
    return `${x},${y}`
  }).join(' ')
})

const powerAreaPath = computed(() => {
  const points = powerData.map((point, i) => {
    const x = 60 + (i * 520 / (powerData.length - 1))
    const y = 180 - (point.power / 3 * 160)
    return `${x},${y}`
  })

  const firstX = 60
  const lastX = 60 + (520)

  return `M ${firstX},180 L ${points.join(' L ')} L ${lastX},180 Z`
})

// Methods
const getIcon = (iconName) => {
  const icons = {
    zap: { template: '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>' },
    wind: { template: '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>' },
    activity: { template: '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>' },
    alert: { template: '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>' }
  }
  return icons[iconName] || icons.zap
}

const getTrendIcon = (change) => {
  const icons = {
    up: { template: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>' },
    down: { template: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" /></svg>' },
    stable: { template: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" /></svg>' }
  }
  return icons[change] || icons.stable
}

const getKpiColorClass = (color) => {
  const classes = {
    emerald: 'bg-emerald-500 text-emerald-50',
    blue: 'bg-blue-500 text-blue-50',
    violet: 'bg-violet-500 text-violet-50',
    orange: 'bg-orange-500 text-orange-50'
  }
  return classes[color] || 'bg-slate-500 text-slate-50'
}

const getTrendColor = (change) => {
  return change === 'up' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'
}

const getPerformanceColor = (performance) => {
  if (performance >= 95) return 'bg-emerald-500'
  if (performance >= 90) return 'bg-blue-500'
  if (performance >= 85) return 'bg-amber-500'
  return 'bg-red-500'
}

const getDowntimeColorClass = (category) => {
  const classes = {
    'Scheduled': 'bg-blue-500',
    'Unscheduled': 'bg-amber-500',
    'Weather': 'bg-red-500'
  }
  return classes[category] || 'bg-slate-500'
}

const getMonthBarClass = (energy) => {
  if (energy >= 2900) return 'fill-emerald-500'
  if (energy >= 2700) return 'fill-blue-500'
  return 'fill-amber-500'
}
</script>

<style scoped>
/* Custom scrollbar */
::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}

::-webkit-scrollbar-track {
  @apply bg-slate-100 dark:bg-slate-800;
}

::-webkit-scrollbar-thumb {
  @apply bg-slate-300 dark:bg-slate-600 rounded-full;
}

::-webkit-scrollbar-thumb:hover {
  @apply bg-slate-400 dark:bg-slate-500;
}
</style>