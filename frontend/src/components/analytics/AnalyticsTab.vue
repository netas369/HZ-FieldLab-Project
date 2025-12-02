<template>
  <div class="space-y-6">
    <!-- Loading State -->
    <div v-if="loading" class="flex items-center justify-center py-20">
      <div class="text-center">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-indigo-500 border-t-transparent mb-4"></div>
        <p class="text-slate-600 dark:text-slate-400">Loading analytics data...</p>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-6">
      <div class="flex items-center gap-3">
        <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div>
          <h3 class="font-semibold text-red-900 dark:text-red-300">Failed to load analytics</h3>
          <p class="text-sm text-red-700 dark:text-red-400 mt-1">{{ error }}</p>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <template v-else>
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
        <!-- Fleet Status Overview -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6 border border-slate-200 dark:border-slate-700">
          <div class="mb-4">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Fleet Status Overview</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Current turbine status distribution</p>
          </div>
          <div class="flex items-center justify-center h-64">
            <svg viewBox="0 0 200 200" class="w-48 h-48">
              <circle
                  v-for="(segment, index) in fleetStatusSegments"
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
                {{ turbines.length }}
              </text>
              <text x="100" y="110" text-anchor="middle" class="text-xs fill-slate-600 dark:fill-slate-400">
                turbines
              </text>
            </svg>
          </div>
          <div class="space-y-2 mt-4">
            <div
                v-for="item in fleetStatusData"
                :key="item.status"
                class="flex items-center justify-between p-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors"
            >
              <div class="flex items-center gap-2">
                <div :class="['w-3 h-3 rounded-full', getStatusColorClass(item.status)]"></div>
                <span class="text-sm text-slate-700 dark:text-slate-300 capitalize">{{ item.status }}</span>
              </div>
              <div class="text-right">
                <span class="text-sm font-semibold text-slate-900 dark:text-white">{{ item.count }}</span>
                <span class="text-xs text-slate-500 dark:text-slate-400 ml-1">({{ item.percent }}%)</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Turbine Performance Comparison -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6 border border-slate-200 dark:border-slate-700">
          <div class="mb-4">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Power Output Comparison</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Current power generation per turbine</p>
          </div>
          <div class="h-64 flex items-end justify-around gap-2 px-4">
            <div
                v-for="turbine in sortedTurbinesByPower"
                :key="turbine.id"
                class="flex-1 flex flex-col items-center group"
            >
              <div class="w-full relative">
                <div
                    :style="{ height: `${getPowerHeight(turbine.metrics?.power_mw)}px` }"
                    :class="[
                  'w-full rounded-t-lg transition-all duration-300 group-hover:opacity-80',
                  getPowerColor(turbine.metrics?.power_mw)
                ]"
                >
                  <div class="absolute -top-8 left-1/2 -translate-x-1/2 opacity-0 group-hover:opacity-100 transition-opacity">
                    <div class="bg-slate-900 dark:bg-slate-700 text-white text-xs px-2 py-1 rounded whitespace-nowrap">
                      {{ (turbine.metrics?.power_mw || 0).toFixed(2) }} MW
                    </div>
                  </div>
                </div>
              </div>
              <span class="text-xs text-slate-600 dark:text-slate-400 mt-2 font-medium">{{ turbine.id }}</span>
            </div>
          </div>
        </div>

        <!-- Alarms by Priority -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6 border border-slate-200 dark:border-slate-700">
          <div class="mb-4">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Alarms by Priority</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Active alarm distribution</p>
          </div>
          <div class="h-64 flex items-end justify-around gap-4 px-8">
            <div
                v-for="priority in alarmPriorityData"
                :key="priority.level"
                class="flex-1 flex flex-col items-center group"
            >
              <div class="w-full relative">
                <div
                    :style="{ height: `${getAlarmHeight(priority.count)}px` }"
                    :class="[
                  'w-full rounded-t-lg transition-all duration-300 group-hover:scale-105',
                  getPriorityBarColor(priority.level)
                ]"
                >
                  <div class="absolute -top-6 left-1/2 -translate-x-1/2 text-sm font-bold text-slate-900 dark:text-white">
                    {{ priority.count }}
                  </div>
                </div>
              </div>
              <span class="text-xs text-slate-600 dark:text-slate-400 mt-3 font-medium">{{ priority.level }}</span>
            </div>
          </div>
        </div>

        <!-- Average Wind Speed -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6 border border-slate-200 dark:border-slate-700">
          <div class="mb-4">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Wind Speed Distribution</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Current wind speeds across fleet</p>
          </div>
          <div class="space-y-3 mt-6">
            <div v-for="turbine in turbines" :key="turbine.id" class="space-y-1.5">
              <div class="flex items-center justify-between text-sm">
                <span class="font-medium text-slate-700 dark:text-slate-300">{{ turbine.id }}</span>
                <div class="flex items-center gap-2">
                  <span class="font-bold text-slate-900 dark:text-white">
                    {{ (turbine.metrics?.wind_speed_ms || 0).toFixed(1) }} m/s
                  </span>
                  <span
                      :class="[
                    'w-2 h-2 rounded-full',
                    getWindSpeedDotColor(turbine.metrics?.wind_speed_ms)
                  ]"
                  ></span>
                </div>
              </div>

              <div class="relative h-2 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                <div
                    :class="['h-full rounded-full transition-all duration-500', getWindSpeedBarColor(turbine.metrics?.wind_speed_ms)]"
                    :style="{ width: `${getWindSpeedPercentage(turbine.metrics?.wind_speed_ms)}%` }"
                ></div>
              </div>
            </div>
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
            <h4 class="font-semibold text-slate-900 dark:text-white">Peak Power Output</h4>
          </div>
          <p class="text-2xl font-bold text-blue-600 dark:text-blue-400 mb-1">{{ maxPowerOutput.toFixed(2) }} MW</p>
          <p class="text-sm text-slate-600 dark:text-slate-400">From turbine {{ maxPowerTurbine }}</p>
        </div>

        <div class="bg-gradient-to-br from-emerald-50 to-emerald-100 dark:from-emerald-900/20 dark:to-emerald-800/20 rounded-xl p-6 border border-emerald-200 dark:border-emerald-800">
          <div class="flex items-center gap-3 mb-3">
            <div class="p-2 bg-emerald-500 rounded-lg">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
              </svg>
            </div>
            <h4 class="font-semibold text-slate-900 dark:text-white">Average Wind Speed</h4>
          </div>
          <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mb-1">{{ averageWindSpeed.toFixed(1) }} m/s</p>
          <p class="text-sm text-slate-600 dark:text-slate-400">Across {{ turbines.length }} turbines</p>
        </div>

        <div class="bg-gradient-to-br from-violet-50 to-violet-100 dark:from-violet-900/20 dark:to-violet-800/20 rounded-xl p-6 border border-violet-200 dark:border-violet-800">
          <div class="flex items-center gap-3 mb-3">
            <div class="p-2 bg-violet-500 rounded-lg">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <h4 class="font-semibold text-slate-900 dark:text-white">Fleet Availability</h4>
          </div>
          <p class="text-2xl font-bold text-violet-600 dark:text-violet-400 mb-1">{{ fleetAvailability.toFixed(1) }}%</p>
          <p class="text-sm text-slate-600 dark:text-slate-400">{{ runningTurbines }} of {{ turbines.length }} running</p>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  turbines: {
    type: Array,
    default: () => []
  },
  alarms: {
    type: Array,
    default: () => []
  },
  loading: {
    type: Boolean,
    default: false
  },
  error: {
    type: String,
    default: null
  }
})

// State
const selectedTimeRange = ref('7d')
const timeRanges = ['24h', '7d', '30d', '90d']

// Computed - Real KPIs from Data
const totalEnergyOutput = computed(() => {
  const total = props.turbines.reduce((sum, t) => sum + (t.metrics?.power_mw || 0), 0)
  return `${total.toFixed(1)} MW`
})

const averageWindSpeed = computed(() => {
  if (props.turbines.length === 0) return 0
  const total = props.turbines.reduce((sum, t) => sum + (t.metrics?.wind_speed_ms || 0), 0)
  return total / props.turbines.length
})

const fleetAvailability = computed(() => {
  if (props.turbines.length === 0) return 0
  const running = props.turbines.filter(t => t.status === 'running').length
  return (running / props.turbines.length) * 100
})

const runningTurbines = computed(() => {
  return props.turbines.filter(t => t.status === 'running').length
})

const activeAlarmsCount = computed(() => {
  return props.alarms.filter(a => !a.acknowledged).length
})

const kpiMetrics = computed(() => [
  {
    label: 'Total Power Output',
    value: totalEnergyOutput.value,
    trend: '+12.5%',
    change: 'up',
    icon: 'zap',
    color: 'emerald'
  },
  {
    label: 'Average Wind Speed',
    value: `${averageWindSpeed.value.toFixed(1)} m/s`,
    trend: '+2.1%',
    change: 'up',
    icon: 'wind',
    color: 'blue'
  },
  {
    label: 'Fleet Availability',
    value: `${fleetAvailability.value.toFixed(1)}%`,
    trend: '-0.8%',
    change: fleetAvailability.value >= 90 ? 'up' : 'down',
    icon: 'activity',
    color: 'violet'
  },
  {
    label: 'Active Alarms',
    value: activeAlarmsCount.value,
    trend: activeAlarmsCount.value === 0 ? 'All Clear' : `${activeAlarmsCount.value} active`,
    change: activeAlarmsCount.value === 0 ? 'up' : 'down',
    icon: 'alert',
    color: 'orange'
  }
])

// Fleet Status Distribution
const fleetStatusData = computed(() => {
  const statusCounts = props.turbines.reduce((acc, t) => {
    acc[t.status] = (acc[t.status] || 0) + 1
    return acc
  }, {})

  return Object.entries(statusCounts).map(([status, count]) => ({
    status,
    count,
    percent: ((count / props.turbines.length) * 100).toFixed(0)
  }))
})

const fleetStatusSegments = computed(() => {
  const circumference = 2 * Math.PI * 70
  let offset = 0

  return fleetStatusData.value.map(item => {
    const length = (item.percent / 100) * circumference
    const segment = {
      length,
      offset: -offset,
      color: getStatusColor(item.status)
    }
    offset += length
    return segment
  })
})

// Sorted Turbines by Power
const sortedTurbinesByPower = computed(() => {
  return [...props.turbines].sort((a, b) => (b.metrics?.power_mw || 0) - (a.metrics?.power_mw || 0))
})

// Max Power Stats
const maxPowerOutput = computed(() => {
  if (props.turbines.length === 0) return 0
  return Math.max(...props.turbines.map(t => t.metrics?.power_mw || 0))
})

const maxPowerTurbine = computed(() => {
  if (props.turbines.length === 0) return 'N/A'
  const maxTurbine = props.turbines.reduce((max, t) => 
    (t.metrics?.power_mw || 0) > (max.metrics?.power_mw || 0) ? t : max
  )
  return maxTurbine.id
})

// Alarms by Priority
const alarmPriorityData = computed(() => {
  const priorities = ['Critical', 'Major', 'Warning', 'Minor']
  return priorities.map(level => ({
    level,
    count: props.alarms.filter(a => a.priority === level).length
  }))
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
    down: { template: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" /></svg>' }
  }
  return icons[change] || icons.up
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

const getStatusColor = (status) => {
  const colors = {
    running: '#10b981',
    idle: '#3b82f6',
    maintenance: '#f59e0b',
    stopped: '#ef4444',
    error: '#dc2626'
  }
  return colors[status] || '#64748b'
}

const getStatusColorClass = (status) => {
  const classes = {
    running: 'bg-green-500',
    idle: 'bg-blue-500',
    maintenance: 'bg-amber-500',
    stopped: 'bg-red-500',
    error: 'bg-red-600'
  }
  return classes[status] || 'bg-slate-500'
}

const getPowerHeight = (power) => {
  const maxHeight = 200
  const maxPower = 3 // Assuming 3MW max
  return Math.min((power / maxPower) * maxHeight, maxHeight)
}

const getPowerColor = (power) => {
  if (power >= 2.5) return 'bg-emerald-500'
  if (power >= 2.0) return 'bg-blue-500'
  if (power >= 1.0) return 'bg-amber-500'
  return 'bg-slate-400'
}

const getAlarmHeight = (count) => {
  const maxHeight = 200
  const maxCount = Math.max(...alarmPriorityData.value.map(p => p.count), 1)
  return (count / maxCount) * maxHeight
}

const getPriorityBarColor = (level) => {
  const colors = {
    Critical: 'bg-red-500',
    Major: 'bg-orange-500',
    Warning: 'bg-amber-500',
    Minor: 'bg-blue-500'
  }
  return colors[level] || 'bg-slate-500'
}

const getWindSpeedPercentage = (speed) => {
  const maxSpeed = 25 // m/s
  return Math.min((speed / maxSpeed) * 100, 100)
}

const getWindSpeedBarColor = (speed) => {
  if (speed >= 15) return 'bg-emerald-500'
  if (speed >= 10) return 'bg-blue-500'
  if (speed >= 5) return 'bg-amber-500'
  return 'bg-slate-400'
}

const getWindSpeedDotColor = (speed) => {
  if (speed >= 15) return 'bg-emerald-500'
  if (speed >= 10) return 'bg-blue-500'
  if (speed >= 5) return 'bg-amber-500'
  return 'bg-slate-400'
}
</script>