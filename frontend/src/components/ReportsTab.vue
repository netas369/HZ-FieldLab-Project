<template>
  <div class="space-y-6">
    <h2 class="text-xl font-semibold mb-4">Reports</h2>

    <!-- Report List -->
    <div v-if="reports && reports.length" class="space-y-3">
      <div v-for="report in reports" :key="report.id" class="p-4 rounded-lg bg-white/80 shadow hover:shadow-lg transition flex justify-between items-center">
        <div>
          <p class="font-semibold">{{ report.title }}</p>
          <p class="text-sm text-gray-500">{{ report.date }}</p>
        </div>
        <button
            class="px-3 py-1 bg-indigo-500 text-white rounded-lg hover:bg-indigo-600 transition"
            @click="$emit('view-report', report)"
        >
          View
        </button>
      </div>
    </div>

    <div v-else class="text-gray-500">No reports available. Add reports via props.</div>

    <!-- Optional: Generate new report -->
    <div class="mt-4">
      <button
          class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition"
          @click="$emit('generate-report')"
      >
        Generate New Report
      </button>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  reports: { type: Array, default: () => [] } // [{ id, title, date }]
})

const emit = defineEmits(['view-report', 'generate-report'])
</script>

<style scoped>
/* Optional: subtle hover effects already handled in Tailwind */
</style>
