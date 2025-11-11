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
            class="bg-white border-2 rounded-lg p-6 hover:shadow-lg transition-shadow cursor-pointer"
            :class="getTurbineBorderClass(turbine.liveData)"
            @click="selectTurbine(turbine)"
        >
          <!-- Header with Turbine Name and Status Dot -->
          <div class="flex items-center justify-between mb-2">
            <h3 class="text-xl font-bold text-gray-800">
              {{ turbine.turbine_id }}
            </h3>
            <span
                v-if="turbine.liveData"
                :class="['w-3 h-3 rounded-full', getStatusDotClass(turbine.liveData?.status_severity)]"
                :title="turbine.liveData?.status_description"
            ></span>
            <span v-else class="w-3 h-3 rounded-full bg-gray-300 animate-pulse"></span>
          </div>

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
                {{ formatTime(turbine.liveData.latest_reading) }}
              </span>
            </div>

            <!-- Data Age Indicator -->
            <div class="mb-3">
              <span :class="['text-xs px-2 py-1 rounded', getDataAgeClass(turbine.liveData.latest_reading)]">
                {{ getDataAge(turbine.liveData.latest_reading) }}
              </span>
            </div>

            <!-- Status Badge -->
            <div class="mb-3">
              <div
                  :class="[
                  'text-sm font-semibold px-3 py-2 rounded flex items-center',
                  getStatusClass(turbine.liveData.status_severity)
                ]"
              >
                <span class="mr-2">{{ getStatusIcon(turbine.liveData.status_code) }}</span>
                {{ turbine.liveData.status_description }}
              </div>
            </div>

            <!-- Alarm Warning (if exists) -->
            <div
                v-if="turbine.liveData.alarm_code && turbine.liveData.alarm_code !== 0"
                :class="[
                'mb-3 border rounded p-3',
                getAlarmBoxClass(turbine.liveData.alarm_severity)
              ]"
            >
              <div class="flex items-start">
                <span class="mr-2 text-lg">
                  {{ getAlarmEmoji(turbine.liveData.alarm_severity) }}
                </span>
                <div class="flex-1">
                  <div :class="['text-sm font-semibold', getAlarmTextClass(turbine.liveData.alarm_severity)]">
                    {{ turbine.liveData.alarm_description }}
                  </div>
                  <div class="text-xs mt-1 opacity-75">
                    Code: {{ turbine.liveData.alarm_code }}
                  </div>
                </div>
              </div>
            </div>

            <!-- Live Data Grid -->
            <div class="grid grid-cols-2 gap-2 text-sm">
              <div>
                <span class="text-gray-500">Wind:</span>
                <span class="font-semibold ml-1">{{ formatNumber(turbine.liveData.wind_speed_ms, 1) }} m/s</span>
              </div>
              <div>
                <span class="text-gray-500">Power:</span>
                <span class="font-semibold ml-1">{{ formatNumber(turbine.liveData.power_kw, 0) }} kW</span>
              </div>
              <div>
                <span class="text-gray-500">Rotor:</span>
                <span class="font-semibold ml-1">{{ formatNumber(turbine.liveData.rotor_speed_rpm, 1) }} RPM</span>
              </div>
              <div>
                <span class="text-gray-500">Temp:</span>
                <span class="font-semibold ml-1">{{ formatNumber(turbine.liveData.ambient_temp_c, 1) }}°C</span>
              </div>
              <div>
                <span class="text-gray-500">Pitch:</span>
                <span class="font-semibold ml-1">{{ formatNumber(turbine.liveData.pitch_angle_deg, 1) }}°</span>
              </div>
              <div>
                <span class="text-gray-500">Yaw:</span>
                <span class="font-semibold ml-1">{{ formatNumber(turbine.liveData.yaw_angle_deg, 1) }}°</span>
              </div>
            </div>
          </div>

          <!-- Loading live data -->
          <div v-else class="mt-4 pt-4 border-t border-gray-200 text-center text-sm text-gray-400">
            <div class="animate-pulse">Loading live data...</div>
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
          const response = await fetch(`${apiUrl}/turbine/${turbine.id}/latestScadaData`);

          if (response.ok) {
            const liveData = await response.json();

            // Vue 3: Just assign directly - reactivity works automatically
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
      // Add navigation here: this.$router.push(`/turbine/${turbine.id}`);
    },

    // Border color based on status and alarms
    getTurbineBorderClass(liveData) {
      if (!liveData) return 'border-gray-200';

      // Failed alarm = Red border
      if (liveData.alarm_severity === 'failed') return 'border-red-500';

      // Critical alarm = Orange border
      if (liveData.alarm_severity === 'critical') return 'border-orange-500';

      // Status-based borders
      if (liveData.status_severity === 'normal') return 'border-green-300';
      if (liveData.status_severity === 'critical') return 'border-red-400';
      if (liveData.status_severity === 'maintenance') return 'border-yellow-400';
      if (liveData.status_severity === 'external') return 'border-orange-400';

      return 'border-gray-200';
    },

    // Status dot color indicator
    getStatusDotClass(severity) {
      const classes = {
        'normal': 'bg-green-500',
        'idle': 'bg-blue-500',
        'maintenance': 'bg-yellow-500',
        'critical': 'bg-red-500',
        'external': 'bg-orange-500'
      };
      return classes[severity] || 'bg-gray-400';
    },

    // Status badge styling
    getStatusClass(severity) {
      const classes = {
        'normal': 'bg-green-100 text-green-800',
        'idle': 'bg-blue-100 text-blue-800',
        'maintenance': 'bg-yellow-100 text-yellow-800',
        'critical': 'bg-red-100 text-red-800',
        'external': 'bg-orange-100 text-orange-800'
      };
      return classes[severity] || 'bg-gray-100 text-gray-800';
    },

    // Status icon
    getStatusIcon(statusCode) {
      if (statusCode === 100) return '✓';
      if (statusCode === 200) return '⏸';
      if (statusCode === 300) return '🔧';
      if (statusCode === 400) return '✗';
      if (statusCode === 500) return '⚡';
      return '•';
    },

    // Alarm box styling
    getAlarmBoxClass(severity) {
      const classes = {
        'warning': 'bg-yellow-50 border-yellow-300',
        'critical': 'bg-orange-50 border-orange-300',
        'failed': 'bg-red-50 border-red-300',
        'external': 'bg-orange-50 border-orange-300'
      };
      return classes[severity] || 'bg-gray-50 border-gray-300';
    },

    // Alarm text color
    getAlarmTextClass(severity) {
      const classes = {
        'warning': 'text-yellow-800',
        'critical': 'text-orange-800',
        'failed': 'text-red-800',
        'external': 'text-orange-800'
      };
      return classes[severity] || 'text-gray-800';
    },

    // Alarm emoji
    getAlarmEmoji(severity) {
      const emojis = {
        'warning': '⚠️',
        'critical': '🔴',
        'failed': '🚨',
        'external': '⚡'
      };
      return emojis[severity] || '⚠️';
    },

    // Calculate data age
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

    // Data age badge color
    getDataAgeClass(timestamp) {
      if (!timestamp) return 'bg-gray-100 text-gray-600';

      const now = new Date();
      const readingTime = new Date(timestamp);
      const diffSeconds = Math.floor((now - readingTime) / 1000);

      if (diffSeconds < 60) return 'bg-green-100 text-green-700';      // Live
      if (diffSeconds < 300) return 'bg-blue-100 text-blue-700';       // Recent
      if (diffSeconds < 3600) return 'bg-yellow-100 text-yellow-700';  // Stale
      return 'bg-red-100 text-red-700';                                // Old
    },

    // Format numbers
    formatNumber(value, decimals) {
      if (value === null || value === undefined) return 'N/A';
      return parseFloat(value).toFixed(decimals);
    },

    // Format date and time
    formatDate(dateString) {
      if (!dateString) return 'N/A';
      const date = new Date(dateString);
      return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
    },

    // Format time only (shorter)
    formatTime(dateString) {
      if (!dateString) return 'N/A';
      const date = new Date(dateString);
      return date.toLocaleTimeString();
    }
  }
}
</script>

<style scoped>
/* Optional: Add custom styles here if needed */
</style>
