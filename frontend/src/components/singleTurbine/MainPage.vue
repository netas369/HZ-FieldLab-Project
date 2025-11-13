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

      <div v-if="currentTab === 'temperature'">
        <h3 class="text-lg font-bold mb-2">temperature Data</h3>
        <pre class="bg-white p-2 rounded shadow-inner">{{ turbine.temperatureData }}</pre>
      </div>
        
        <div v-if="currentTab === 'alarms'">
        <h3 class="text-lg font-bold mb-2">alarms Data</h3>
        <pre class="bg-white p-2 rounded shadow-inner">{{ turbine.alarms }}</pre>
      </div>
    </div>
  </div>





        </div>
</template>

<script>
import { ref, reactive, onMounted } from 'vue'
import { useRoute} from 'vue-router'

export default {
  name: 'MainPage',

  data() {
    return {
      turbine: {},
      loading: false,
      error: null,
      refreshInterval: null,
      currentTab: 'scada',
      tabs: [
          { key: 'scada', label: 'SCADA' },
          { key: 'hydraulic', label: 'Hydraulic' },
          { key: 'vibration', label: 'Vibration' },
          { key: 'temperature', label: 'Temparature' },
          { key: 'alarms', label: 'Alarms' },

      ]
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
      }, 1200000); // Refresh every 30 seconds
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