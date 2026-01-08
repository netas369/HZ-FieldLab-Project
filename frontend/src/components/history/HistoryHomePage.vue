<template>
  <div class="max-w-7xl mx-auto px-4 py-8">
    <h1 class="text-4xl font-bold text-gray-800 text-center mb-8">
      Turbines Data History
    </h1>

    <!-- Loading State -->
    <div
      v-if="loading"
      class="text-center py-20"
    >
      <div class="text-blue-500 text-xl">
        Loading turbines...
      </div>
    </div>

    <!-- Error State -->
    <div
      v-if="error"
      class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded text-center"
    >
      {{ error }}
    </div>

    <!-- Turbines List -->
    <div v-if="!loading && !error">
      <h2 class="text-2xl font-semibold text-gray-700 mb-6">
        All Turbines ({{ turbines.length }})
      </h2>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-8">
        <div
          v-for="turbine in turbines"
          :key="turbine.id"
          class="bg-white border-2 rounded-lg p-6 cursor-pointer transition-all duration-300 hover:shadow-lg"
          :class="
            selectedTurbine && selectedTurbine.id === turbine.id
              ? 'border-blue-500 bg-blue-50'
              : 'border-gray-200 hover:border-blue-300'
          "
          @click="selectTurbine(turbine)"
        >
          <h3 class="text-xl font-bold text-gray-800 mb-2">
            {{ turbine.turbine_id }}
          </h3>
          <p class="text-gray-600 text-sm mb-1">
            ID: {{ turbine.id }}
          </p>
          <p class="text-gray-400 text-xs">
            Added: {{ formatDate(turbine.created_at) }}
          </p>
        </div>
      </div>

      <!-- Use the separate detail component -->
      <TurbineHistoryDetails
        v-if="selectedTurbine"
        :turbine="selectedTurbine"
        @close="clearSelection"
      />
    </div>
  </div>
</template>

<script>
import TurbineHistoryDetails from './TurbineHistoryDetails.vue'

export default {
  name: 'HistoryPage',

  components: {
    TurbineHistoryDetails, // Register the child component
  },

  data() {
    return {
      turbines: [],
      selectedTurbine: null,
      loading: false,
      error: null,
    }
  },

  created() {
    this.fetchTurbines()
  },

  methods: {
    async fetchTurbines() {
      this.loading = true
      this.error = null

      try {
        const apiUrl = import.meta.env.VITE_API_BASE_URL
        const response = await fetch(`${apiUrl}/turbines/`)

        if (!response.ok) {
          throw new Error('Failed to fetch turbines')
        }

        const data = await response.json()
        this.turbines = data
      } catch (err) {
        this.error = err.message
        console.error('Error:', err)
      } finally {
        this.loading = false
      }
    },

    formatDate(dateString) {
      const date = new Date(dateString)
      return date.toLocaleDateString() + ' ' + date.toLocaleTimeString()
    },

    selectTurbine(turbine) {
      this.selectedTurbine = turbine

      // Smooth scroll to details
      this.$nextTick(() => {
        const element = document.querySelector('.border-blue-500')
        if (element) {
          element.scrollIntoView({ behavior: 'smooth', block: 'nearest' })
        }
      })
    },

    clearSelection() {
      this.selectedTurbine = null
    },
  },
}
</script>
