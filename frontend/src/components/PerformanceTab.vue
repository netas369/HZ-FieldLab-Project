<template>
  <div class="space-y-6">
    <h2 class="text-xl font-semibold mb-4">Performance Metrics Dashboard</h2>

    <!-- KPI Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <div v-for="kpi in kpis" :key="kpi.label" class="bg-white/80 p-4 rounded-xl shadow hover:shadow-lg transition">
        <p class="text-sm text-gray-500">{{ kpi.label }}</p>
        <p class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-indigo-500 to-purple-600">{{ kpi.value }}</p>
        <p class="text-xs text-gray-600">{{ kpi.trend }}</p>
      </div>
    </div>

    <!-- Placeholder for Charts -->
    <div v-if="charts && charts.length" class="mt-6 space-y-4">
      <div v-for="chart in charts" :key="chart.id" class="bg-white/80 p-4 rounded-xl shadow">
        <h3 class="font-semibold mb-2">{{ chart.title }}</h3>
        <!-- Chart component could go here -->
        <div class="h-48 flex items-center justify-center text-gray-400 border border-dashed border-gray-300 rounded">
          Chart Placeholder
        </div>
      </div>
    </div>

    <div v-else class="text-gray-500">No charts available. Add chart data via props.</div>
  </div>
</template>

<script setup>
const props = defineProps({
  kpis: { type: Array, default: () => [] },       // [{ label, value, trend }]
  charts: { type: Array, default: () => [] }     // [{ id, title, data }]
})
</script>

<style scoped>
/* Optional styling for chart placeholders */
</style>
