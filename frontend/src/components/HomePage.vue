<template>
  <div class="max-w-7xl mx-auto px-4 py-8">
    <h1 class="text-4xl font-bold text-gray-800 text-center mb-8">
      Wind Turbine Park
    </h1>

    <div v-if="loading" class="text-center py-20">
      <div class="text-blue-500 text-xl">Loading turbines...</div>
    </div>

    <div v-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded text-center">
      {{ error }}
    </div>

    <div v-if="!loading && !error">
      <h2 class="text-2xl font-semibold text-gray-700 mb-6">
        All Turbines ({{ turbines.length }})
      </h2>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <div
            v-for="turbine in turbines"
            :key="turbine.id"
            class="bg-white border-2 rounded-lg p-6 hover:shadow-lg transition-shadow cursor-pointer"
            :class="getTurbineBorderClass(turbine)"
            @click="selectTurbine(turbine)"
        >
          <div class="flex items-center justify-between mb-2">
            <h3 class="text-xl font-bold text-gray-800">
              {{ turbine.turbine_id }}
            </h3>
            <span
                :class="['w-3 h-3 rounded-full', getStatusDotClass(turbine.status)]"
                :title="getStatusLabel(turbine.status)"
            ></span>
          </div>

          <p class="text-gray-600 text-sm mb-1">
            ID: {{ turbine.id }}
          </p>
          <p class="text-gray-400 text-xs">
            Added: {{ formatDate(turbine.created_at) }}
          </p>

          <div v-if="turbine.dataLoadError" class="mt-2 text-xs text-red-600 bg-red-50 p-2 rounded">
            Error loading data: {{ turbine.dataLoadError }}
          </div>

          <div v-if="turbine.alarms && turbine.alarms.total_alarms > 0" class="mt-4 mb-4">
            <div class="bg-red-50 border border-red-200 rounded p-3">
              <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-semibold text-red-800">
                  🚨 {{ turbine.alarms.total_alarms }} Active Alarm{{ turbine.alarms.total_alarms > 1 ? 's' : '' }}
                </span>
              </div>
              <div class="flex gap-2 text-xs">
                <span v-if="turbine.alarms.counts_by_severity.failed > 0" class="bg-red-100 text-red-800 px-2 py-1 rounded">
                  Failed: {{ turbine.alarms.counts_by_severity.failed }}
                </span>
                <span v-if="turbine.alarms.counts_by_severity.critical > 0" class="bg-orange-100 text-orange-800 px-2 py-1 rounded">
                  Critical: {{ turbine.alarms.counts_by_severity.critical }}
                </span>
                <span v-if="turbine.alarms.counts_by_severity.warning > 0" class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded">
                  Warning: {{ turbine.alarms.counts_by_severity.warning }}
                </span>
              </div>
            </div>
          </div>

          <div v-if="turbine.scadaData" class="mt-4 pt-4 border-t border-gray-200">
            <div class="mb-3 flex items-center justify-between">
              <span class="text-xs text-gray-500">Last Update:</span>
              <span class="text-xs font-medium text-blue-600">
                {{ formatTime(turbine.scadaData.latest_reading) }}
              </span>
            </div>

            <div class="mb-3">
              <span :class="['text-xs px-2 py-1 rounded', getDataAgeClass(turbine.scadaData.latest_reading)]">
                {{ getDataAge(turbine.scadaData.latest_reading) }}
              </span>
            </div>

            <div class="mb-3">
              <div
                  :class="[
                  'text-sm font-semibold px-3 py-2 rounded flex items-center',
                  getTurbineStatusClass(turbine.status)
                ]"
              >
                <span class="mr-2">{{ getTurbineStatusIcon(turbine.status) }}</span>
                {{ getStatusLabel(turbine.status) }}
              </div>
            </div>

            <div
                v-if="turbine.scadaData.alarm_code && turbine.scadaData.alarm_code !== 0"
                :class="[
                'mb-3 border rounded p-3',
                getAlarmBoxClass(turbine.scadaData.alarm_severity)
              ]"
            >
              <div class="flex items-start">
                <span class="mr-2 text-lg">
                  {{ getAlarmEmoji(turbine.scadaData.alarm_severity) }}
                </span>
                <div class="flex-1">
                  <div :class="['text-sm font-semibold', getAlarmTextClass(turbine.scadaData.alarm_severity)]">
                    {{ turbine.scadaData.alarm_description }}
                  </div>
                  <div class="text-xs mt-1 opacity-75">
                    Code: {{ turbine.scadaData.alarm_code }}
                  </div>
                </div>
              </div>
            </div>

            <div class="mb-3">
              <h4 class="text-xs font-semibold text-gray-600 mb-2">SCADA Data</h4>
              <div class="grid grid-cols-2 gap-2 text-sm">
                <div>
                  <span class="text-gray-500">Wind:</span>
                  <span class="font-semibold ml-1">{{ formatNumber(turbine.scadaData.wind_speed_ms, 1) }} m/s</span>
                </div>
                <div>
                  <span class="text-gray-500">Power:</span>
                  <span class="font-semibold ml-1">{{ formatNumber(turbine.scadaData.power_kw, 0) }} kW</span>
                </div>
                <div>
                  <span class="text-gray-500">Rotor:</span>
                  <span class="font-semibold ml-1">{{ formatNumber(turbine.scadaData.rotor_speed_rpm, 1) }} RPM</span>
                </div>
                <div>
                  <span class="text-gray-500">Temp:</span>
                  <span class="font-semibold ml-1">{{ formatNumber(turbine.scadaData.ambient_temp_c, 1) }}°C</span>
                </div>
                <div>
                  <span class="text-gray-500">Pitch:</span>
                  <span class="font-semibold ml-1">{{ formatNumber(turbine.scadaData.pitch_angle_deg, 1) }}°</span>
                </div>
                <div>
                  <span class="text-gray-500">Yaw:</span>
                  <span class="font-semibold ml-1">{{ formatNumber(turbine.scadaData.yaw_angle_deg, 1) }}°</span>
                </div>
              </div>
            </div>

            <div v-if="turbine.temperatureData" class="mb-3 pt-3 border-t border-gray-100">
              <h4 class="text-xs font-semibold text-gray-600 mb-2">Temperature Readings</h4>
              <div class="grid grid-cols-2 gap-2 text-sm">
                <div>
                  <span class="text-gray-500">Nacelle:</span>
                  <span :class="['font-semibold ml-1', getStatusColor(turbine.temperatureData.nacelle_status)]">
                    {{ formatNumber(turbine.temperatureData.nacelle_temp, 1) }}°C
                  </span>
                </div>
                <div>
                  <span class="text-gray-500">Gearbox Oil:</span>
                  <span :class="['font-semibold ml-1', getStatusColor(turbine.temperatureData.gearbox_oil_status)]">
                    {{ formatNumber(turbine.temperatureData.gearbox_oil_temp, 1) }}°C
                  </span>
                </div>
                <div>
                  <span class="text-gray-500">Generator:</span>
                  <span :class="['font-semibold ml-1', getStatusColor(turbine.temperatureData.generator_status)]">
                    {{ formatNumber(turbine.temperatureData.generator_stator_temp, 1) }}°C
                  </span>
                </div>
                <div>
                  <span class="text-gray-500">Main Bearing:</span>
                  <span :class="['font-semibold ml-1', getStatusColor(turbine.temperatureData.main_bearing_status)]">
                    {{ formatNumber(turbine.temperatureData.main_bearing_temp, 1) }}°C
                  </span>
                </div>
              </div>
            </div>

            <div v-if="turbine.vibrationData" class="mb-3 pt-3 border-t border-gray-100">
              <h4 class="text-xs font-semibold text-gray-600 mb-2">Vibration Status</h4>
              <div class="grid grid-cols-2 gap-2 text-sm">
                <div>
                  <span class="text-gray-500">Main Bearing:</span>
                  <span :class="['font-semibold ml-1', getStatusColor(turbine.vibrationData.main_bearing_status)]">
                    {{ formatNumber(turbine.vibrationData.main_bearing_vibration_rms, 2) }} mm/s
                  </span>
                </div>
                <div>
                  <span class="text-gray-500">Gearbox:</span>
                  <span :class="['font-semibold ml-1', getStatusColor(turbine.vibrationData.gearbox_status)]">
                    {{ formatNumber(turbine.vibrationData.gearbox_vibration_axial, 2) }} mm/s
                  </span>
                </div>
                <div>
                  <span class="text-gray-500">Generator:</span>
                  <span :class="['font-semibold ml-1', getStatusColor(turbine.vibrationData.generator_status)]">
                    {{ formatNumber(turbine.vibrationData.generator_vibration_de, 2) }} mm/s
                  </span>
                </div>
                <div>
                  <span class="text-gray-500">Tower:</span>
                  <span :class="['font-semibold ml-1', getStatusColor(turbine.vibrationData.tower_status)]">
                    {{ formatNumber(turbine.vibrationData.tower_vibration_fa, 2) }} mm/s
                  </span>
                </div>
              </div>
            </div>

            <div v-if="turbine.hydraulicData" class="mb-3 pt-3 border-t border-gray-100">
              <h4 class="text-xs font-semibold text-gray-600 mb-2">Hydraulic Pressures</h4>
              <div class="grid grid-cols-2 gap-2 text-sm">
                <div>
                  <span class="text-gray-500">Gearbox Oil:</span>
                  <span :class="['font-semibold ml-1', getStatusColor(turbine.hydraulicData.gearbox_oil_pressure_status)]">
                    {{ formatNumber(turbine.hydraulicData.gearbox_oil_pressure_bar, 1) }} bar
                  </span>
                </div>
                <div>
                  <span class="text-gray-500">Hydraulic:</span>
                  <span :class="['font-semibold ml-1', getStatusColor(turbine.hydraulicData.hydraulic_pressure_status)]">
                    {{ formatNumber(turbine.hydraulicData.hydraulic_pressure_bar, 1) }} bar
                  </span>
                </div>
              </div>
            </div>
          </div>

          <div v-else-if="!turbine.dataLoadError" class="mt-4 pt-4 border-t border-gray-200 text-center text-sm text-gray-400">
            <div class="animate-pulse">Loading live data...</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import * as api from '@/services/api'; // Adjust this path if needed

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
        // 2. Use the service to fetch turbines
        const data = await api.getTurbines();
        console.log('Turbines fetched:', data);
        
        // Initialize the turbine objects
        this.turbines = data.map(turbine => ({
          ...turbine,
          scadaData: null,
          hydraulicData: null,
          vibrationData: null,
          temperatureData: null,
          alarms: null,
          dataLoadError: null
        }));

        // Fetch details and start refreshing
        await this.fetchAllTurbineData();
        this.startAutoRefresh();

      } catch (err) {
        // Errors from the service are caught here
        this.error = err.message;
        console.error('Error fetching turbines:', err);
      } finally {
        this.loading = false;
      }
    },

    async fetchAllTurbineData() {
      console.log('Fetching data for', this.turbines.length, 'turbines');

      // Create an array of promises, one for each turbine
      const promises = this.turbines.map(async (turbine) => {
        try {
          // 3. Use the service to get all details for *each* turbine
          const details = await api.getAllTurbineDetails(turbine.id);

          // 4. Update the turbine object reactively
          turbine.scadaData = details.scadaData;
          turbine.hydraulicData = details.hydraulicData;
          turbine.vibrationData = details.vibrationData;
          turbine.temperatureData = details.temperatureData;
          turbine.alarms = details.alarms;
          
          if (details.errors.length > 0) {
            turbine.dataLoadError = details.errors.join(', ');
            console.warn(`Partial data load error for turbine ${turbine.id}:`, details.errors);
          } else {
            turbine.dataLoadError = null; // Clear any previous errors
          }

        } catch (err) {
          // Catch any unexpected critical error during the fetch for this turbine
          console.error(`Failed to fetch all data for turbine ${turbine.id}:`, err);
          turbine.dataLoadError = "A critical error occurred while loading data.";
        }
      });

      // Wait for all turbine data to be fetched before finishing
      await Promise.all(promises);
      console.log('All turbine data refreshed');
    },

    async refreshLiveData() {
      console.log('Refreshing live data...');
      await this.fetchAllTurbineData();
    },

    startAutoRefresh() {
      this.refreshInterval = setInterval(() => {
        this.refreshLiveData();
      }, 30000); // Refresh every 30 seconds
    },

    selectTurbine(turbine) {
      console.log('Selected turbine:', turbine);
      // this.$router.push(`/turbine/${turbine.id}`);
    },

    //
    // --- All Formatting & Helper Methods ---
    // (These all stay exactly the same as before)
    //

    // Get status label from TurbineStatus enum value
    getStatusLabel(status) {
      const statusLabels = {
        'normal': 'Operational',
        'idle': 'Idle',
        'maintenance': 'Maintenance',
        'error': 'Error',
        'grid_fault': 'Grid Fault'
      };
      return statusLabels[status] || status;
    },

    // Border color based on turbine status and alarms
    getTurbineBorderClass(turbine) {
      // Priority 1: Check for alarms
      if (turbine.alarms && turbine.alarms.total_alarms > 0) {
        if (turbine.alarms.counts_by_severity?.failed > 0) return 'border-red-500';
        if (turbine.alarms.counts_by_severity?.critical > 0) return 'border-orange-500';
        if (turbine.alarms.counts_by_severity?.warning > 0) return 'border-yellow-400';
      }

      // Priority 2: Check turbine status
      if (turbine.status === 'error') return 'border-red-500';
      if (turbine.status === 'grid_fault') return 'border-orange-500';
      if (turbine.status === 'maintenance') return 'border-yellow-400';
      if (turbine.status === 'idle') return 'border-blue-300';
      if (turbine.status === 'normal') return 'border-green-300';

      return 'border-gray-200';
    },

    // Get color based on status object from API (for component statuses)
    getStatusColor(statusData) {
      if (!statusData) return 'text-gray-800';
      const status = statusData.status || statusData;
      if (status === 'normal' || status === 'good') return 'text-green-600';
      if (status === 'warning') return 'text-yellow-600';
      if (status === 'critical') return 'text-orange-600';
      if (status === 'failed') return 'text-red-600';
      return 'text-gray-800';
    },

    // Status dot color indicator (from turbines table)
    getStatusDotClass(status) {
      const classes = {
        'normal': 'bg-green-500',
        'idle': 'bg-blue-500',
        'maintenance': 'bg-yellow-500',
        'error': 'bg-red-500',
        'grid_fault': 'bg-orange-500'
      };
      return classes[status] || 'bg-gray-400';
    },

    // Status badge styling (from turbines table)
    getTurbineStatusClass(status) {
      const classes = {
        'normal': 'bg-green-100 text-green-800',
        'idle': 'bg-blue-100 text-blue-800',
        'maintenance': 'bg-yellow-100 text-yellow-800',
        'error': 'bg-red-100 text-red-800',
        'grid_fault': 'bg-orange-100 text-orange-800'
      };
      return classes[status] || 'bg-gray-100 text-gray-800';
    },

    // Status icon (from turbines table)
    getTurbineStatusIcon(status) {
      const icons = {
        'normal': '✓',
        'idle': '⏸',
        'maintenance': '🔧',
        'error': '✗',
        'grid_fault': '⚡'
      };
      return icons[status] || '•';
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

      if (diffSeconds < 60) return 'bg-green-100 text-green-700';       // Live
      if (diffSeconds < 300) return 'bg-blue-100 text-blue-700';      // Recent
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