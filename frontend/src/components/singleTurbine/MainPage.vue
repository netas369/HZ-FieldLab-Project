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



<div class="max-w-3xl mx-auto mt-6">
    <!-- Tabs -->
    <div class="flex border-b border-gray-200 mb-4">
      <button
        v-for="tab in tabs"
        :key="tab.key"
        @click="currentTab = tab.key"
        :class="[
          'py-2 px-4 -mb-px font-semibold text-gray-700 focus:outline-none',
          currentTab === tab.key
            ? 'border-b-2 border-blue-500 text-blue-500'
            : 'hover:text-blue-500'
        ]"
      >
        {{ tab.label }}
      </button>
    </div>

    <!-- Tab content -->
    <div class="bg-gray-50 p-4 rounded shadow">
      <div v-if="currentTab === 'scada'">
        <h3 class="text-lg font-bold mb-2">SCADA Data</h3>
        <pre class="bg-white p-2 rounded shadow-inner">{{ turbine.scadaData }}</pre>
      </div>

      <div v-if="currentTab === 'hydraulic'">
        <h3 class="text-lg font-bold mb-2">Hydraulic Data</h3>
        <pre class="bg-white p-2 rounded shadow-inner">{{ turbine.hydraulicData }}</pre>
      </div>

      <div v-if="currentTab === 'vibration'">
        <h3 class="text-lg font-bold mb-2">Vibration Data</h3>
        <pre class="bg-white p-2 rounded shadow-inner">{{ turbine.vibrationData }}</pre>
      </div>
    </div>
  </div>





        </div>
</template>

<script>
import { ref, reactive, onMounted } from 'vue'
import { useRoute} from 'vue-router'
const currentTab = ref('scada')

const tabs = [
  { key: 'scada', label: 'SCADA' },
  { key: 'hydraulic', label: 'Hydraulic' },
  { key: 'vibration', label: 'Vibration' }
]

// Reactive turbine data
const turbine = reactive({
  scadaData: null,
  hydraulicData: null,
  vibrationData: null
})

export default {
  name: 'MainPage',

  data() {
    return {
      turbine: {},
      loading: false,
      error: null,
      refreshInterval: null
    }
  },

  created() {
    const route = useRoute()
    this.fetchAllTurbineData(route.params.id);
  },

  beforeUnmount() {
    if (this.refreshInterval) {
      clearInterval(this.refreshInterval);
    }
  },

  methods: {
    async fetchAllTurbineData(id) {
      this.loading = true;
      const apiUrl = import.meta.env.VITE_API_BASE_URL;
      console.log('Fetching data for turbine: ', id);

        try {

          const [scadaRes, hydraulicRes, vibrationRes, temperatureRes, alarmsRes] = await Promise.all([
            fetch(`${apiUrl}/turbine/${id}/latestScadaData`).catch(err => {
              console.error(`SCADA fetch error for turbine ${id}`, err);
              return { ok: false, error: err.message };
            }),
            fetch(`${apiUrl}/turbine/${id}/latestHydraulicReadings`).catch(err => {
              console.error(`Hydraulic fetch error for turbine ${id}`, err);
              return { ok: false, error: err.message };
            }),
            fetch(`${apiUrl}/turbine/${id}/vibrations`).catch(err => {
              console.error(`Vibration fetch error for turbine ${id}`, err);
              return { ok: false, error: err.message };
            }),
            fetch(`${apiUrl}/turbine/${id}/latestTemperatures`).catch(err => {
              console.error(`Temperature fetch error for turbine ${id}`, err);
              return { ok: false, error: err.message };
            }),
            fetch(`${apiUrl}/turbine/${id}/alarms`).catch(err => {
              console.error(`Alarms fetch error for turbine ${id}`, err);
              return { ok: false, error: err.message };
            })
          ]);


            this.turbine.scadaData = scadaRes.ok ? await scadaRes.json() : null
            this.turbine.hydraulicData = hydraulicRes.ok ? await hydraulicRes.json() : null
            this.turbine.vibrationData = vibrationRes.ok ? await vibrationRes.json() : null
            this.turbine.temperatureData = temperatureRes.ok ? await temperatureRes.json() : null
            this.turbine.alarms = alarmsRes.ok ? await alarmsRes.json() : null


        this.startAutoRefresh();

        } catch (err) {
          console.error(`Failed to fetch data for turbine ${id}:`, err);
          turbine.dataLoadError = err.message;
        } finally {
        this.loading = false;
        }
        console.log(this.turbine)
      console.log('All turbine data fetched');
    },

    async refreshLiveData() {
      console.log('Refreshing live data...');
      await this.fetchAllTurbineData();
    },

    startAutoRefresh() {
      this.refreshInterval = setInterval(() => {
        this.refreshLiveData();
      }, 120000); // Refresh every 30 seconds
    },

    selectTurbine(turbine) {
      console.log('Selected turbine:', turbine);
      this.$router.push({ name: 'scadaData', params: { id: turbine.id } })
    },

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