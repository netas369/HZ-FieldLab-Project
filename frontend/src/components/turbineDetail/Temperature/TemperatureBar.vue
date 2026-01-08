<template>
  <div class="space-y-1.5">
    <div class="flex items-center justify-between text-sm">
      <span class="font-medium text-slate-700 dark:text-slate-300">{{ label }}</span>
      <div class="flex items-center gap-2">
        <span class="font-bold text-slate-900 dark:text-white">
          {{ formatTemperature(value) }}°C
        </span>
        <span :class="['w-2 h-2 rounded-full', getStatusDotColor()]" />
      </div>
    </div>

    <div class="relative h-2 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
      <div
        :class="['h-full rounded-full transition-all duration-500', getBarColor()]"
        :style="{ width: `${getPercentage()}%` }"
      />
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  label: {
    type: String,
    required: true,
  },
  value: {
    type: [Number, String],
    required: true,
  },
  status: {
    type: Object,
    default: () => ({}),
  },
})

const formatTemperature = (temp) => {
  const value = parseFloat(temp)
  if (isNaN(value)) return 'N/A'
  return value.toFixed(1)
}

const getPercentage = () => {
  const temp = parseFloat(props.value)
  if (isNaN(temp)) return 0

  // Scale: -20°C to 100°C
  const min = -20
  const max = 100
  const percentage = ((temp - min) / (max - min)) * 100
  return Math.max(0, Math.min(100, percentage))
}

const getBarColor = () => {
  const color = props.status?.color
  const colors = {
    green: 'bg-green-500',
    yellow: 'bg-yellow-500',
    orange: 'bg-orange-500',
    red: 'bg-red-500',
  }
  return colors[color] || 'bg-slate-500'
}

const getStatusDotColor = () => {
  const color = props.status?.color
  const colors = {
    green: 'bg-green-500',
    yellow: 'bg-yellow-500',
    orange: 'bg-orange-500',
    red: 'bg-red-500',
  }
  return colors[color] || 'bg-slate-400'
}
</script>
