<template>
  <div class="max-w-7xl mx-auto px-4 py-8">
    <h1 class="text-4xl font-bold text-gray-800 text-center mb-8">
      Wind Turbine Park
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

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <div
            v-for="turbine in turbines"
            :key="turbine.id"
            class="bg-white border-2 border-gray-200 rounded-lg p-6 hover:shadow-lg transition-shadow cursor-pointer"
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

          <!-- Live Data Preview -->
          <div v-if="turbine.liveData" class="mt-4 pt-4 border-t border-gray-200">
            <!-- Latest Reading Timestamp -->
            <div class="mb-3 flex items-center justify-between">
              <span class="text-xs text-gray-500">Last Update:</span>
              <span class="text-xs font-medium text-blue-600">
                {{ formatDate(turbine.liveData.latest_reading) }}
              </span>
            </div>

            <!-- Data Age Indicator -->
            <div class="mb-3">
              <span :class="['text-xs px-2 py-1 rounded', getDataAgeClass(turbine.liveData.latest_reading)]">
                {{ getDataAge(turbine.liveData.latest_reading) }}
              </span>
            </div>

            <!-- Live Data Grid -->
            <div class="grid grid-cols-2 gap-2 text-sm">
              <div>
                <span class="text-gray-500">Wind:</span>
                <span class="font-semibold ml-1">{{ turbine.liveData.wind_speed_ms }} m/s</span>
              </div>
              <div>
                <span class="text-gray-500">Power:</span>
                <span class="font-semibold ml-1">{{ turbine.liveData.power_kw }} kW</span>
              </div>
              <div>
                <span class="text-gray-500">Rotor:</span>
                <span class="font-semibold ml-1">{{ turbine.liveData.rotor_speed_rpm }} RPM</span>
              </div>
              <div>
                <span class="text-gray-500">Temp:</span>
                <span class="font-semibold ml-1">{{ turbine.liveData.ambient_temp_c }}°C</span>
              </div>
              <div>
                <span class="text-gray-500">Pitch:</span>
                <span class="font-semibold ml-1">{{ turbine.liveData.pitch_angle_deg }}°</span>
              </div>
              <div>
                <span class="text-gray-500">Yaw:</span>
                <span class="font-semibold ml-1">{{ turbine.liveData.yaw_angle_deg }}°</span>
              </div>
            </div>

            <!-- Status and Alarm -->
            <div class="mt-3 flex flex-wrap gap-2">
              <span
                  :class="[
                  'inline-block px-2 py-1 text-xs rounded',
                  getStatusClass(turbine.liveData.status_code)
                ]"
              >
                Status: {{ turbine.liveData.status_code }}
              </span>
              <span
                  v-if="turbine.liveData.alarm_code"
                  class="inline-block px-2 py-1 text-xs rounded bg-red-100 text-red-800"
              >
                Alarm: {{ turbine.liveData.alarm_code }}
              </span>
            </div>
          </div>

          <!-- Loading live data -->
          <div v-else class="mt-4 pt-4 border-t border-gray-200 text-center text-sm text-gray-400">
            Loading live data...
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'HomePage',

  data() {
    return {
      turbines: [],
      loading: false,
      error: null,
      refreshInterval: null
    }
  },

  created() {
    this.fetchTurbines();
  },

  beforeUnmount() {
    if (this.refreshInterval) {
      clearInterval(this.refreshInterval);
    }
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

        await this.fetchAllLiveData();
        this.startAutoRefresh();

      } catch (err) {
        this.error = err.message;
        console.error('Error:', err);
      } finally {
        this.loading = false;
      }
    },

    async fetchAllLiveData() {
      const apiUrl = import.meta.env.VITE_API_BASE_URL;

      const promises = this.turbines.map(async (turbine) => {
        try {
          const response = await fetch(`${apiUrl}/turbine/${turbine.id}/latest`);

          if (response.ok) {
            const liveData = await response.json();
            turbine.liveData = liveData;
          }
        } catch (err) {
          console.error(`Failed to fetch live data for turbine ${turbine.id}:`, err);
        }
      });

      await Promise.all(promises);
    },

    async refreshLiveData() {
      await this.fetchAllLiveData();
    },

    startAutoRefresh() {
      this.refreshInterval = setInterval(() => {
        this.refreshLiveData();
      }, 30000); // Refresh every 30 seconds
    },

    selectTurbine(turbine) {
      console.log('Selected turbine:', turbine);
      // Add navigation here if needed
      // this.$router.push(`/turbine/${turbine.id}`);
    },

    getStatusClass(statusCode) {
      if (statusCode === 'running' || statusCode === 1) {
        return 'bg-green-100 text-green-800';
      } else if (statusCode === 'warning' || statusCode === 2) {
        return 'bg-yellow-100 text-yellow-800';
      } else if (statusCode === 'stopped' || statusCode === 0) {
        return 'bg-gray-100 text-gray-800';
      } else {
        return 'bg-red-100 text-red-800';
      }
    },

    // Calculate how old the data is
    getDataAge(timestamp) {
      if (!timestamp) return 'No data';

      const now = new Date();
      const readingTime = new Date(timestamp);
      const diffSeconds = Math.floor((now - readingTime) / 1000);

      if (diffSeconds < 60) return `${diffSeconds}s ago`;
      if (diffSeconds < 3600) return `${Math.floor(diffSeconds / 60)}m ago`;
      if (diffSeconds < 86400) return `${Math.floor(diffSeconds / 3600)}h ago`;
      return `${Math.floor(diffSeconds / 86400)}d ago`;
    },

    // Color code based on data age
    getDataAgeClass(timestamp) {
      if (!timestamp) return 'bg-gray-100 text-gray-600';

      const now = new Date();
      const readingTime = new Date(timestamp);
      const diffSeconds = Math.floor((now - readingTime) / 1000);

      if (diffSeconds < 60) return 'bg-green-100 text-green-700'; // Live (< 1 min)
      if (diffSeconds < 300) return 'bg-blue-100 text-blue-700'; // Recent (< 5 min)
      if (diffSeconds < 3600) return 'bg-yellow-100 text-yellow-700'; // Stale (< 1 hour)
      return 'bg-red-100 text-red-700'; // Old (> 1 hour)
    },

    formatDate(dateString) {
      if (!dateString) return 'N/A';
      const date = new Date(dateString);
      return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
    }
  }
}
</script>