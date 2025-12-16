<template>
  <div class="min-h-screen bg-gray-50 p-6">
    <div class="max-w-7xl mx-auto">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Data Import</h1>
        <p class="text-gray-600 mt-2">Import sensor data for wind turbine monitoring - all turbines and sensor types at once</p>
      </div>

      <!-- Step Indicator -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div class="flex items-center" :class="step >= 1 ? 'text-blue-600' : 'text-gray-400'">
            <div class="w-10 h-10 rounded-full flex items-center justify-center border-2"
                 :class="step >= 1 ? 'border-blue-600 bg-blue-600 text-white' : 'border-gray-400'">
              1
            </div>
            <span class="ml-2 font-medium">Upload CSV</span>
          </div>
          <div class="flex-1 h-1 mx-4" :class="step >= 2 ? 'bg-blue-600' : 'bg-gray-300'"></div>

          <div class="flex items-center" :class="step >= 2 ? 'text-blue-600' : 'text-gray-400'">
            <div class="w-10 h-10 rounded-full flex items-center justify-center border-2"
                 :class="step >= 2 ? 'border-blue-600 bg-blue-600 text-white' : 'border-gray-400'">
              2
            </div>
            <span class="ml-2 font-medium">Identify Key Columns</span>
          </div>
          <div class="flex-1 h-1 mx-4" :class="step >= 3 ? 'bg-blue-600' : 'bg-gray-300'"></div>

          <div class="flex items-center" :class="step >= 3 ? 'text-blue-600' : 'text-gray-400'">
            <div class="w-10 h-10 rounded-full flex items-center justify-center border-2"
                 :class="step >= 3 ? 'border-blue-600 bg-blue-600 text-white' : 'border-gray-400'">
              3
            </div>
            <span class="ml-2 font-medium">Map Sensor Columns</span>
          </div>
          <div class="flex-1 h-1 mx-4" :class="step >= 4 ? 'bg-blue-600' : 'bg-gray-300'"></div>

          <div class="flex items-center" :class="step >= 4 ? 'text-blue-600' : 'text-gray-400'">
            <div class="w-10 h-10 rounded-full flex items-center justify-center border-2"
                 :class="step >= 4 ? 'border-blue-600 bg-blue-600 text-white' : 'border-gray-400'">
              4
            </div>
            <span class="ml-2 font-medium">Validate & Import</span>
          </div>
        </div>
      </div>

      <!-- Step 1: Upload CSV -->
      <div v-if="step === 1" class="bg-white rounded-lg shadow-md p-6">
        <CsvUploadComponent
            @csv-parsed="handleCsvParsed"
        />
      </div>

      <!-- Step 2: Identify Key Columns -->
      <div v-if="step === 2" class="bg-white rounded-lg shadow-md p-6">
        <KeyColumnsComponent
            :csv-data="csvData"
            @key-columns-identified="handleKeyColumnsIdentified"
            @back="step = 1"
        />
      </div>

      <!-- Step 3: Map Sensor Columns -->
      <div v-if="step === 3" class="bg-white rounded-lg shadow-md p-6">
        <SensorMappingComponent
            :csv-data="csvData"
            :key-columns="keyColumns"
            @mapping-complete="handleMappingComplete"
            @back="step = 2"
        />
      </div>

      <!-- Step 4: Validate & Import -->
      <div v-if="step === 4" class="bg-white rounded-lg shadow-md p-6">
        <ValidationImportComponent
            :csv-data="csvData"
            :key-columns="keyColumns"
            :sensor-mapping="sensorMapping"
            @import-complete="handleImportComplete"
            @back="step = 3"
        />
      </div>

      <!-- Success Message -->
      <div v-if="importSuccess" class="mt-6 bg-green-50 border border-green-200 rounded-lg p-6">
        <div class="flex items-start">
          <svg class="w-8 h-8 text-green-600 mr-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
          </svg>
          <div class="flex-1">
            <h3 class="font-bold text-green-900 text-lg">Import Successful!</h3>
            <div class="mt-3 space-y-1 text-green-800">
              <p><strong>{{ importResult.total_rows }}</strong> rows processed</p>
              <p><strong>{{ importResult.imported_rows }}</strong> rows imported successfully</p>
              <p><strong>{{ importResult.turbines_count }}</strong> turbines affected</p>
              <div v-if="importResult.table_counts" class="mt-3 text-sm">
                <p class="font-semibold">Data distribution:</p>
                <ul class="ml-4 mt-1 space-y-1">
                  <li v-if="importResult.table_counts.vibration">Vibration: {{ importResult.table_counts.vibration }} records</li>
                  <li v-if="importResult.table_counts.temperature">Temperature: {{ importResult.table_counts.temperature }} records</li>
                  <li v-if="importResult.table_counts.scada">SCADA: {{ importResult.table_counts.scada }} records</li>
                  <li v-if="importResult.table_counts.hydraulic">Hydraulic: {{ importResult.table_counts.hydraulic }} records</li>
                  <li v-if="importResult.table_counts.grid">Grid: {{ importResult.table_counts.grid }} records</li>
                </ul>
              </div>
            </div>
            <div class="mt-4 flex gap-3">
              <button @click="resetImport"
                      class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                Import Another File
              </button>
              <router-link to="/dashboard"
                           class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                View Dashboard
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref } from 'vue';
import CsvUploadComponent from '../components/import/CsvUploadComponent.vue';
import KeyColumnsComponent from '../components/import/KeyColumnsComponent.vue';
import SensorMappingComponent from '../components/import/SensorMappingComponent.vue';
import ValidationImportComponent from '../components/import/ValidationImportComponent.vue';

export default {
  name: 'DataImportPage',
  components: {
    CsvUploadComponent,
    KeyColumnsComponent,
    SensorMappingComponent,
    ValidationImportComponent
  },
  setup() {
    const step = ref(1);
    const csvData = ref(null);
    const keyColumns = ref(null);
    const sensorMapping = ref(null);
    const importSuccess = ref(false);
    const importResult = ref({});

    const handleCsvParsed = (data) => {
      csvData.value = data;
      step.value = 2;
    };

    const handleKeyColumnsIdentified = (data) => {
      keyColumns.value = data;
      step.value = 3;
    };

    const handleMappingComplete = (data) => {
      sensorMapping.value = data;
      step.value = 4;
    };

    const handleImportComplete = (result) => {
      importSuccess.value = true;
      importResult.value = result;
    };

    const resetImport = () => {
      step.value = 1;
      csvData.value = null;
      keyColumns.value = null;
      sensorMapping.value = null;
      importSuccess.value = false;
      importResult.value = {};
    };

    return {
      step,
      csvData,
      keyColumns,
      sensorMapping,
      importSuccess,
      importResult,
      handleCsvParsed,
      handleKeyColumnsIdentified,
      handleMappingComplete,
      handleImportComplete,
      resetImport
    };
  }
};
</script>
