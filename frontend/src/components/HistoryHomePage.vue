<template>
  <div class="max-w-7xl mx-auto px-4 py-8">
    <h1 class="text-4xl font-bold text-gray-800 text-center mb-8">
      Turbines Data History
    </h1>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-20">
      <div class="text-blue-500 text-xl">Loading turbines...</div>
    </div>

    <!-- Error State -->
    <div v-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded text-center">
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
            @click="selectTurbine(turbine)"
            class="bg-white border-2 rounded-lg p-6 cursor-pointer transition-all duration-300 hover:shadow-lg"
            :class="selectedTurbine && selectedTurbine.id === turbine.id ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-blue-300'"
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

      <!-- Selected Turbine Details Section (appears below when turbine is clicked) -->
      <div v-if="selectedTurbine" class="bg-white border-2 border-blue-500 rounded-lg p-8 mt-8">
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-2xl font-bold text-gray-800">
            Selected Turbine: {{ selectedTurbine.turbine_id }}
          </h2>
          <button
              @click="clearSelection"
              class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors"
          >
            Close
          </button>
        </div>

        <!-- Placeholder for turbine details/history -->
        <div class="text-center py-12 bg-gray-50 rounded-lg">
          <p class="text-gray-600 text-lg mb-2">
            History data for turbine <strong>{{ selectedTurbine.turbine_id }}</strong>
          </p>
          <p class="text-gray-400">
            Content coming soon...
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'HistoryPage',

  data() {
    return {
      turbines: [],
      selectedTurbine: null,
      loading: false,
      error: null
    }
  },

  created() {
    this.fetchTurbines();
  },

  methods: {
    async fetchTurbines() {
      this.loading = true;
      this.error = null;

      try {
        const apiUrl = import.meta.env.VITE_API_BASE_URL;
        const response = await fetch(`${apiUrl}/turbines/`);

        if (!response.ok) {
          throw new Error('Failed to fetch turbines');
        }

        const data = await response.json();
        this.turbines = data;
      } catch (err) {
        this.error = err.message;
        console.error('Error:', err);
      } finally {
        this.loading = false;
      }
    },

    formatDate(dateString) {
      const date = new Date(dateString);
      return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
    },

    // Select a turbine to show details
    selectTurbine(turbine) {
      this.selectedTurbine = turbine;

      this.$nextTick(() => {
        const element = document.querySelector('.border-blue-500');
        if (element) {
          element.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
      });
    },

    // Clear selection
    clearSelection() {
      this.selectedTurbine = null;
    }
  }
}
</script>