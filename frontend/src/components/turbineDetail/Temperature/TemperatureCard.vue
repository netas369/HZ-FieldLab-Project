<template>
  <div
    class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden"
  >
    <div class="p-5">
      <div class="flex items-start justify-between mb-4">
        <div class="flex items-center gap-3">
          <div :class="['p-2.5 rounded-xl', getIconBgColor()]">
            <component :is="getIconComponent()" class="w-5 h-5" :class="getIconColor()" />
          </div>
          <div>
            <h4 class="font-semibold text-slate-900 dark:text-white">
              {{ title }}
            </h4>
            <p class="text-xs text-slate-500 dark:text-slate-400">Temperature</p>
          </div>
        </div>

        <span :class="['px-2.5 py-1 rounded-full text-xs font-bold', getStatusBadgeClass()]">
          {{ status?.label || 'Unknown' }}
        </span>
      </div>

      <!-- Temperature Display -->
      <div class="mb-3">
        <div class="flex items-baseline gap-2">
          <p class="text-4xl font-bold text-slate-900 dark:text-white">
            {{ formatTemperature(temperature) }}
          </p>
          <span class="text-xl text-slate-500">°C</span>
        </div>
      </div>

      <!-- Temperature Gauge -->
      <div class="relative h-2 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden mb-3">
        <div
          :class="['h-full rounded-full transition-all duration-500', getGaugeColor()]"
          :style="{ width: `${getTemperaturePercentage()}%` }"
        />
      </div>

      <!-- Status Description -->
      <div class="flex items-start gap-2 text-sm">
        <svg
          :class="['w-4 h-4 flex-shrink-0 mt-0.5', getIconColor()]"
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
        <p :class="['flex-1', getDescriptionColor()]">
          {{ status?.description || 'No description available' }}
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  title: {
    type: String,
    required: true,
  },
  temperature: {
    type: [Number, String],
    required: true,
  },
  status: {
    type: Object,
    default: () => ({}),
  },
  icon: {
    type: String,
    default: 'default',
  },
})

const formatTemperature = (temp) => {
  const value = parseFloat(temp)
  if (isNaN(value)) return 'N/A'
  return value.toFixed(1)
}

const getTemperaturePercentage = () => {
  const temp = parseFloat(props.temperature)
  if (isNaN(temp)) return 0

  // Scale: -20°C to 100°C
  const min = -20
  const max = 100
  const percentage = ((temp - min) / (max - min)) * 100
  return Math.max(0, Math.min(100, percentage))
}

const getIconComponent = () => {
  const icons = {
    nacelle: {
      template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
      </svg>`,
    },
    bearing: {
      template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
      </svg>`,
    },
    oil: {
      template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
      </svg>`,
    },
    generator: {
      template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
      </svg>`,
    },
    default: {
      template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
      </svg>`,
    },
  }
  return icons[props.icon] || icons.default
}

const getIconBgColor = () => {
  const color = props.status?.color
  const colors = {
    green: 'bg-green-100 dark:bg-green-900/30',
    yellow: 'bg-yellow-100 dark:bg-yellow-900/30',
    orange: 'bg-orange-100 dark:bg-orange-900/30',
    red: 'bg-red-100 dark:bg-red-900/30',
  }
  return colors[color] || 'bg-slate-100 dark:bg-slate-800'
}

const getIconColor = () => {
  const color = props.status?.color
  const colors = {
    green: 'text-green-600 dark:text-green-400',
    yellow: 'text-yellow-600 dark:text-yellow-400',
    orange: 'text-orange-600 dark:text-orange-400',
    red: 'text-red-600 dark:text-red-400',
  }
  return colors[color] || 'text-slate-600'
}

const getStatusBadgeClass = () => {
  const color = props.status?.color
  const classes = {
    green: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
    yellow: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
    orange: 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
    red: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
  }
  return classes[color] || 'bg-slate-100 text-slate-700'
}

const getGaugeColor = () => {
  const color = props.status?.color
  const colors = {
    green: 'bg-green-500',
    yellow: 'bg-yellow-500',
    orange: 'bg-orange-500',
    red: 'bg-red-500',
  }
  return colors[color] || 'bg-slate-500'
}

const getDescriptionColor = () => {
  const color = props.status?.color
  const colors = {
    green: 'text-green-700 dark:text-green-400',
    yellow: 'text-yellow-700 dark:text-yellow-400',
    orange: 'text-orange-700 dark:text-orange-400',
    red: 'text-red-700 dark:text-red-400',
  }
  return colors[color] || 'text-slate-600'
}
</script>
