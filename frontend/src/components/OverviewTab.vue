<template>
  <div class="space-y-6">
    <!-- Fleet Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
      <div
          v-for="stat in fleetStats"
          :key="stat.label"
          class="bg-white dark:bg-slate-800 rounded-xl shadow-sm hover:shadow-md transition-all p-6 border border-slate-200 dark:border-slate-700"
      >
        <div class="flex items-start justify-between mb-3">
          <div :class="['p-3 rounded-lg', stat.bgColor]">
            <component :is="stat.icon" class="w-6 h-6 text-white" />
          </div>
          <span :class="['text-xs font-semibold px-2.5 py-1 rounded-full', stat.badgeColor]">
            {{ stat.badge }}
          </span>
        </div>
        <div>
          <p class="text-sm text-slate-600 dark:text-slate-400 mb-1">{{ stat.label }}</p>
          <p class="text-3xl font-bold text-slate-900 dark:text-white">{{ stat.value }}</p>
        </div>
      </div>
    </div>

    <!-- Turbine Fleet Grid -->
    <section class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6 border border-slate-200 dark:border-slate-700">
      <div class="flex items-center justify-between mb-6">
        <div>
          <h2 class="text-xl font-bold text-slate-900 dark:text-white">Turbine Fleet</h2>
          <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
            {{ filteredTurbines.length }} turbines • {{ runningCount }} running
          </p>
        </div>

        <!-- View Toggle -->
        <div class="flex bg-slate-100 dark:bg-slate-700 rounded-lg p-1">
          <button
              v-for="view in ['grid', 'list']"
              :key="view"
              @click="viewMode = view"
              :class="[
              'px-3 py-1.5 rounded-md text-sm font-medium transition-all',
              viewMode === view
                ? 'bg-white dark:bg-slate-600 text-slate-900 dark:text-white shadow-sm'
                : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'
            ]"
          >
            {{ view === 'grid' ? 'Grid' : 'List' }}
          </button>
        </div>
      </div>

      <!-- Grid View -->
      <div
          v-if="viewMode === 'grid'"
          class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4"
      >
        <div
            v-for="turbine in filteredTurbines"
            :key="turbine.id"
            @click="selectTurbine(turbine)"
            :class="[
            'group relative bg-slate-50 dark:bg-slate-900 rounded-xl p-4 text-center border-2 transition-all cursor-pointer',
            selectedTurbine?.id === turbine.id
              ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20 shadow-lg'
              : 'border-transparent hover:border-slate-300 dark:hover:border-slate-600 hover:shadow-md'
          ]"
        >
          <!-- Turbine Icon -->
          <div class="relative mx-auto mb-3 w-16 h-16">
            <svg viewBox="0 0 100 100" :class="['w-full h-full transition-colors', getTurbineIconColor(turbine.status)]">
              <!-- Tower -->
              <rect x="47" y="45" width="6" height="45" fill="currentColor" opacity="0.3"/>

              <!-- Nacelle (house) -->
              <ellipse cx="50" cy="42" rx="8" ry="6" fill="currentColor" opacity="0.4"/>

              <!-- Hub center -->
              <circle cx="50" cy="42" r="3.5" fill="currentColor"/>

              <!-- Blades group with rotation -->
              <g :class="{ 'animate-spin-slow': turbine.status === 'running' }" style="transform-origin: 50px 42px">
                <!-- Blade 1 -->
                <ellipse cx="50" cy="20" rx="3" ry="18" fill="currentColor" opacity="0.9"/>
                <!-- Blade 2 -->
                <ellipse cx="50" cy="20" rx="3" ry="18" fill="currentColor" opacity="0.9" transform="rotate(120 50 42)"/>
                <!-- Blade 3 -->
                <ellipse cx="50" cy="20" rx="3" ry="18" fill="currentColor" opacity="0.9" transform="rotate(240 50 42)"/>
              </g>

              <!-- Base -->
              <rect x="44" y="88" width="12" height="3" fill="currentColor" opacity="0.3" rx="1"/>
            </svg>

            <!-- Status Indicator -->
            <div :class="['absolute -top-1 -right-1 w-5 h-5 rounded-full border-2 border-white dark:border-slate-900', getStatusBadgeColor(turbine.status)]">
              <div v-if="turbine.status === 'running'" class="absolute inset-0 rounded-full bg-green-500 animate-ping opacity-75"></div>
            </div>
          </div>

          <!-- Turbine Info -->
          <div class="font-bold text-slate-900 dark:text-white">{{ turbine.id }}</div>
          <div class="text-xs text-slate-500 dark:text-slate-400 mb-2">{{ turbine.location }}</div>
          <div :class="['text-xs font-semibold px-2 py-1 rounded-full inline-block', getStatusClass(turbine.status)]">
            {{ getStatusLabel(turbine.status) }}
          </div>

          <!-- Quick Stats (on hover) -->
          <div class="absolute inset-0 bg-slate-900/95 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center p-4 text-white">
            <p class="text-xs mb-2">Power Output</p>
            <p class="text-xl font-bold">{{ turbine.metrics?.power || '-' }}</p>
            <p class="text-xs text-slate-300 mt-2">Click for details</p>
          </div>
        </div>
      </div>

      <!-- List View -->
      <div v-else class="space-y-2">
        <div
            v-for="turbine in filteredTurbines"
            :key="turbine.id"
            @click="selectTurbine(turbine)"
            :class="[
            'flex items-center gap-4 p-4 rounded-lg border transition-all cursor-pointer',
            selectedTurbine?.id === turbine.id
              ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20'
              : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-900'
          ]"
        >
          <!-- Icon -->
          <div class="relative w-12 h-12">
            <svg viewBox="0 0 100 100" :class="['w-full h-full', getTurbineIconColor(turbine.status)]">
              <!-- Tower -->
              <rect x="47" y="45" width="6" height="40" fill="currentColor" opacity="0.3"/>

              <!-- Nacelle -->
              <ellipse cx="50" cy="42" rx="7" ry="5" fill="currentColor" opacity="0.4"/>

              <!-- Hub -->
              <circle cx="50" cy="42" r="3" fill="currentColor"/>

              <!-- Blades -->
              <g :class="{ 'animate-spin-slow': turbine.status === 'running' }" style="transform-origin: 50px 42px">
                <ellipse cx="50" cy="22" rx="2.5" ry="16" fill="currentColor" opacity="0.9"/>
                <ellipse cx="50" cy="22" rx="2.5" ry="16" fill="currentColor" opacity="0.9" transform="rotate(120 50 42)"/>
                <ellipse cx="50" cy="22" rx="2.5" ry="16" fill="currentColor" opacity="0.9" transform="rotate(240 50 42)"/>
              </g>

              <!-- Base -->
              <rect x="45" y="84" width="10" height="2" fill="currentColor" opacity="0.3" rx="1"/>
            </svg>
            <div :class="['absolute -top-1 -right-1 w-4 h-4 rounded-full border-2 border-white', getStatusBadgeColor(turbine.status)]"></div>
          </div>

          <!-- Info -->
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 mb-1">
              <h3 class="font-semibold text-slate-900 dark:text-white">{{ turbine.id }}</h3>
              <span :class="['text-xs font-semibold px-2 py-0.5 rounded-full', getStatusClass(turbine.status)]">
                {{ getStatusLabel(turbine.status) }}
              </span>
            </div>
            <p class="text-sm text-slate-600 dark:text-slate-400">{{ turbine.location }}</p>
          </div>

          <!-- Metrics -->
          <div class="hidden md:flex items-center gap-6 text-sm">
            <div>
              <p class="text-slate-500 dark:text-slate-400 text-xs">Power</p>
              <p class="font-semibold text-slate-900 dark:text-white">{{ turbine.metrics?.power || '-' }}</p>
            </div>
            <div>
              <p class="text-slate-500 dark:text-slate-400 text-xs">Wind</p>
              <p class="font-semibold text-slate-900 dark:text-white">{{ turbine.metrics?.wind || '-' }}</p>
            </div>
            <div>
              <p class="text-slate-500 dark:text-slate-400 text-xs">Availability</p>
              <p class="font-semibold text-slate-900 dark:text-white">{{ turbine.metrics?.availability || '-' }}</p>
            </div>
          </div>

          <!-- Arrow -->
          <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </div>
      </div>
    </section>

    <!-- Selected Turbine Details -->
    <section v-if="selectedTurbine?.id" class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6 border border-slate-200 dark:border-slate-700">
      <div class="flex items-center justify-between mb-6">
        <div>
          <h3 class="text-xl font-bold text-slate-900 dark:text-white">{{ selectedTurbine.id }} Performance</h3>
          <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">Real-time operational metrics</p>
        </div>
        <button class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition-colors">
          View Details
        </button>
      </div>

      <!-- Metrics Grid -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div
            v-for="metric in selectedMetrics"
            :key="metric.label"
            class="bg-slate-50 dark:bg-slate-900 rounded-lg p-4 hover:shadow-md transition-shadow"
        >
          <div class="flex items-center gap-2 mb-2">
            <component :is="metric.icon" class="w-4 h-4 text-slate-500 dark:text-slate-400" />
            <p class="text-sm text-slate-600 dark:text-slate-400">{{ metric.label }}</p>
          </div>
          <p class="text-2xl font-bold text-slate-900 dark:text-white mb-1">{{ metric.value }}</p>
          <p :class="['text-xs font-medium', metric.trendUp ? 'text-green-600 dark:text-green-400' : 'text-slate-500']">
            {{ metric.trend }}
          </p>
        </div>
      </div>

      <!-- Component Health -->
      <div>
        <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">Component Health</h4>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
          <div
              v-for="component in componentHealth"
              :key="component.name"
              class="flex items-center justify-between bg-slate-50 dark:bg-slate-900 p-3 rounded-lg"
          >
            <span class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ component.name }}</span>
            <div class="flex items-center gap-2">
              <div class="w-24 h-2 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                <div
                    :class="['h-full transition-all', getHealthColor(component.health)]"
                    :style="{ width: component.health + '%' }"
                ></div>
              </div>
              <span class="text-sm font-semibold text-slate-900 dark:text-white min-w-[3ch]">{{ component.health }}%</span>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  turbines: { type: Array, default: () => [] },
  selectedTurbine: { type: Object, default: null },
  componentHealth: { type: Array, default: () => [] },
  searchQuery: { type: String, default: '' }
})

const emit = defineEmits(['select-turbine'])

// State
const viewMode = ref('grid')

// Computed
const filteredTurbines = computed(() => {
  if (!props.searchQuery) return props.turbines
  const query = props.searchQuery.toLowerCase()
  return props.turbines.filter(t =>
      t.id.toLowerCase().includes(query) || t.location.toLowerCase().includes(query)
  )
})

const runningCount = computed(() =>
    filteredTurbines.value.filter(t => t.status === 'running').length
)

const fleetStats = computed(() => [
  {
    label: 'Total Turbines',
    value: props.turbines.length,
    badge: 'Active',
    bgColor: 'bg-blue-500',
    badgeColor: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
    icon: { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>' }
  },
  {
    label: 'Running',
    value: runningCount.value,
    badge: `${Math.round(runningCount.value / props.turbines.length * 100)}%`,
    bgColor: 'bg-green-500',
    badgeColor: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
    icon: { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>' }
  },
  {
    label: 'Total Output',
    value: calculateTotalOutput(),
    badge: '+3.2%',
    bgColor: 'bg-violet-500',
    badgeColor: 'bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-400',
    icon: { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>' }
  },
  {
    label: 'Avg Efficiency',
    value: '94.2%',
    badge: 'Good',
    bgColor: 'bg-orange-500',
    badgeColor: 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
    icon: { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>' }
  }
])

const selectedMetrics = computed(() => {
  if (!props.selectedTurbine?.metrics) return []
  const m = props.selectedTurbine.metrics
  return [
    {
      label: 'Power Output',
      value: m.power || '-',
      trend: '↑ 5% today',
      trendUp: true,
      icon: { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>' }
    },
    {
      label: 'Wind Speed',
      value: m.wind || '-',
      trend: 'Optimal',
      trendUp: false,
      icon: { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>' }
    },
    {
      label: 'Availability',
      value: m.availability || '-',
      trend: 'Above target',
      trendUp: true,
      icon: { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>' }
    },
    {
      label: 'Rotor Speed',
      value: '15.2 RPM',
      trend: 'Normal',
      trendUp: false,
      icon: { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>' }
    }
  ]
})

// Methods
const selectTurbine = (turbine) => {
  emit('select-turbine', turbine)
}

const calculateTotalOutput = () => {
  const total = props.turbines.reduce((sum, t) => {
    const power = parseFloat(t.metrics?.power) || 0
    return sum + power
  }, 0)
  return `${total.toFixed(1)} MW`
}

const getTurbineIconColor = (status) => {
  const colors = {
    running: 'text-green-600 dark:text-green-500',
    maintenance: 'text-amber-600 dark:text-amber-500',
    stopped: 'text-red-600 dark:text-red-500',
    warning: 'text-yellow-600 dark:text-yellow-500'
  }
  return colors[status] || 'text-slate-400'
}

const getStatusBadgeColor = (status) => {
  const colors = {
    running: 'bg-green-500',
    maintenance: 'bg-amber-500',
    stopped: 'bg-red-500',
    warning: 'bg-yellow-500'
  }
  return colors[status] || 'bg-slate-400'
}

const getStatusClass = (status) => {
  const classes = {
    running: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
    maintenance: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
    stopped: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    warning: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400'
  }
  return classes[status] || 'bg-slate-100 text-slate-700'
}

const getStatusLabel = (status) => {
  const labels = {
    running: 'Running',
    maintenance: 'Maintenance',
    stopped: 'Stopped',
    warning: 'Warning'
  }
  return labels[status] || 'Unknown'
}

const getHealthColor = (health) => {
  if (health >= 90) return 'bg-green-500'
  if (health >= 75) return 'bg-blue-500'
  if (health >= 60) return 'bg-amber-500'
  return 'bg-red-500'
}
</script>

<style scoped>
@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.animate-spin-slow {
  animation: spin 3s linear infinite;
}
</style>