<template>
  <div class="space-y-6">
    <!-- Turbine Fleet Status -->
    <section class="bg-white/80 p-6 rounded-xl shadow">
      <h2 class="text-xl font-semibold mb-4">Turbine Fleet Status</h2>
      <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        <div
            v-for="turbine in filteredTurbines"
            :key="turbine.id"
            @click="selectTurbine(turbine)"
            :class="['bg-white rounded-xl p-4 text-center border-2 transition cursor-pointer',
                   selectedTurbine.id === turbine.id ? 'border-purple-500 bg-purple-50' : 'border-transparent hover:shadow-lg hover:border-indigo-400']"
        >
          <div class="relative mx-auto mb-2 w-14 h-14">
            <svg viewBox="0 0 100 100" class="w-full h-full text-gray-800">
              <circle cx="50" cy="50" r="2" fill="currentColor"/>
              <path d="M50 20 L50 40 L40 30 M50 40 L60 30" stroke="currentColor" stroke-width="2" fill="none"/>
              <path d="M50 20 L50 40 L40 30 M50 40 L60 30" stroke="currentColor" stroke-width="2" fill="none" transform="rotate(120 50 50)"/>
              <path d="M50 20 L50 40 L40 30 M50 40 L60 30" stroke="currentColor" stroke-width="2" fill="none" transform="rotate(240 50 50)"/>
              <line x1="50" y1="50" x2="50" y2="80" stroke="currentColor" stroke-width="2"/>
            </svg>
            <div :class="['absolute top-0 right-0 w-4 h-4 rounded-full border-2 border-white animate-pulse',
                          turbine.status === 'running' ? 'bg-green-500' :
                          turbine.status === 'maintenance' ? 'bg-yellow-500' :
                          turbine.status === 'stopped' ? 'bg-red-500' :
                          turbine.status === 'warning' ? 'bg-yellow-400' : 'bg-purple-500']"></div>
          </div>
          <div class="font-bold">{{ turbine.id }}</div>
          <div class="text-xs text-gray-500">{{ turbine.location }}</div>
          <div class="text-sm font-medium" :style="{ color: getStatusColor(turbine.status) }">
            {{ turbine.statusText }}
          </div>
        </div>
      </div>
    </section>

    <!-- Selected Turbine Metrics -->
    <section v-if="selectedTurbine.id" class="space-y-4">
      <h3 class="text-lg font-semibold">Performance Metrics - {{ selectedTurbine.id }}</h3>
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div v-for="metric in selectedTurbineMetrics" :key="metric.label" class="bg-white/80 p-4 rounded-xl shadow hover:shadow-lg transition">
          <div class="text-sm text-gray-500">{{ metric.label }}</div>
          <div class="text-2xl font-bold bg-gradient-to-r from-indigo-500 to-purple-600 bg-clip-text text-transparent my-2">
            {{ metric.value }}
          </div>
          <div class="text-xs text-gray-600">{{ metric.trend }}</div>
        </div>
      </div>
    </section>

    <!-- Component Health -->
    <section class="space-y-2">
      <h3 class="text-lg font-semibold">Component Health</h3>
      <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
        <div v-for="component in componentHealth" :key="component.name" class="flex justify-between items-center bg-gray-100 p-2 rounded-lg">
          <span class="text-sm">{{ component.name }}</span>
          <div class="w-24 h-2 bg-gray-300 rounded overflow-hidden">
            <div class="h-full bg-gradient-to-r from-green-500 to-indigo-500 transition-all"
                 :style="{ width: component.health + '%' }"></div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  turbines: { type: Array, default: () => [] },
  selectedTurbine: { type: Object, default: () => ({}) },
  turbineMetrics: { type: Object, default: () => ({}) },
  componentHealth: { type: Array, default: () => [] },
  searchQuery: { type: String, default: '' }
})

const emit = defineEmits(['select-turbine'])

const selectTurbine = (turbine) => emit('select-turbine', turbine)

const filteredTurbines = computed(() => {
  if (!props.searchQuery) return props.turbines
  return props.turbines.filter(t =>
      t.id.includes(props.searchQuery) || t.location.includes(props.searchQuery)
  )
})

const selectedTurbineMetrics = computed(() => {
  const id = props.selectedTurbine.id || (props.turbines[0] && props.turbines[0].id)
  const metrics = props.turbineMetrics[id] || {}
  return [
    { label: 'Power Output', value: metrics.power || '-', trend: '↑ 5% from yesterday' },
    { label: 'Wind Speed', value: metrics.wind || '-', trend: 'Optimal range' },
    { label: 'Availability', value: metrics.availability || '-', trend: 'Above target' },
    { label: 'Rotor Speed', value: metrics.rotor || '-', trend: 'Normal operation' }
  ]
})

const getStatusColor = (status) => {
  switch (status) {
    case 'running':
      return '#10b981'
    case 'maintenance':
      return '#f59e0b'
    case 'stopped':
      return '#ef4444'
    case 'warning':
      return '#facc15'
    default:
      return '#6b7280'
  }
}
</script>

<style scoped>
/* Optional: leave animations from Tailwind */
</style>
