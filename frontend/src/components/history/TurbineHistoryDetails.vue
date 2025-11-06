<template>
  <div class="bg-white border-2 border-blue-500 rounded-lg p-8 mt-8">
    <!-- Header with close button -->
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl font-bold text-gray-800">
        Selected Turbine: {{ turbine.turbine_id }}
      </h2>
      <button
          @click="$emit('close')"
          class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors"
      >
        Close
      </button>
    </div>

    <!-- Turbine Basic Info -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
      <div class="bg-gray-50 p-4 rounded-lg">
        <p class="text-gray-600 text-sm">Database ID</p>
        <p class="text-xl font-bold text-gray-800">{{ turbine.id }}</p>
      </div>

      <div class="bg-gray-50 p-4 rounded-lg">
        <p class="text-gray-600 text-sm">Created</p>
        <p class="text-xl font-bold text-gray-800">{{ formatDate(turbine.created_at) }}</p>
      </div>

      <div class="bg-gray-50 p-4 rounded-lg">
        <p class="text-gray-600 text-sm">Last Updated</p>
        <p class="text-xl font-bold text-gray-800">{{ formatDate(turbine.updated_at) }}</p>
      </div>
    </div>

    <!-- Placeholder sections for future features -->
    <div class="space-y-6">
      <!-- Section 1: Performance Chart -->
      <div class="bg-gray-50 p-6 rounded-lg">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">
          Performance History
        </h3>
        <div class="h-64 flex items-center justify-center text-gray-400">
Performance
        </div>
      </div>

      <!-- Section 2: Maintenance Records -->
      <div class="bg-gray-50 p-6 rounded-lg">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">
          Maintenance Records
        </h3>
        <div class="text-center py-8 text-gray-400">
        </div>
      </div>

      <!-- Section 3: Status Logs -->
      <div class="bg-gray-50 p-6 rounded-lg">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">
        Status logs
  <TurbineStatusLogs />
          
        </h3>
        <div class="text-center py-8 text-gray-400">
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import TurbineStatusLogs from './visualizations/TurbineStatusLogs.vue'

export default {
  name: 'TurbineHistoryDetail',

  components: {
  TurbineStatusLogs
  },

  // Receive turbine data from parent
  props: {
    turbine: {
      type: Object,
      required: true
    }
  },

  methods: {
    formatDate(dateString) {
      const date = new Date(dateString);
      return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
    }
  }
}
</script>