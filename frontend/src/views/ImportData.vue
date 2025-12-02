<template>
  <div class="min-h-screen bg-gray-50 p-6">
    <div class="max-w-7xl mx-auto">
      <div class="bg-white rounded-lg shadow-lg p-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Wind Turbine Data Import</h1>
        <p class="text-gray-600 mb-8">Upload and map your CSV data to the appropriate database tables</p>

        <!-- Progress Steps -->
        <div class="flex items-center justify-between mb-8">
          <div v-for="(label, idx) in steps" :key="idx" class="flex items-center">
            <div :class="[
              'flex items-center justify-center w-10 h-10 rounded-full',
              currentStep > idx + 1 ? 'bg-green-500 text-white' : 
              currentStep === idx + 1 ? 'bg-blue-500 text-white' : 
              'bg-gray-300 text-gray-600'
            ]">
              <svg v-if="currentStep > idx + 1" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              <span v-else>{{ idx + 1 }}</span>
            </div>
            <span class="ml-2 text-sm font-medium text-gray-700">{{ label }}</span>
            <div v-if="idx < 3" class="w-24 h-1 bg-gray-300 mx-4"></div>
          </div>
        </div>

        <!-- Step 1: Upload File -->
        <div v-if="currentStep === 1" class="space-y-6">
          <div class="border-2 border-dashed border-gray-300 rounded-lg p-12 text-center hover:border-blue-400 transition">
            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
            </svg>
            <label for="file-upload" class="cursor-pointer">
              <span class="text-blue-600 hover:text-blue-500 font-medium">
                Click to upload
              </span>
              <span class="text-gray-600"> or drag and drop</span>
              <input
                id="file-upload"
                type="file"
                accept=".csv"
                class="hidden"
                @change="handleFileUpload"
              />
            </label>
            <p class="text-sm text-gray-500 mt-2">CSV files only</p>
            <p v-if="file" class="text-sm text-green-600 mt-4 font-medium">✓ {{ file.name }}</p>
          </div>
          
          <button
            @click="downloadTemplate"
            class="flex items-center gap-2 text-blue-600 hover:text-blue-700 font-medium"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            Download CSV Template
          </button>
        </div>

        <!-- Step 2: Select Data Batches -->
        <div v-if="currentStep === 2" class="space-y-6">
          <div class="flex items-center gap-2 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <svg class="text-blue-600 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="text-sm text-blue-800">
              Select which data batches you want to import. Each batch represents a different database table.
            </p>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div
              v-for="(batch, key) in batchDefinitions"
              :key="key"
              @click="toggleBatch(key)"
              :class="[
                'cursor-pointer border-2 rounded-lg p-4 transition',
                selectedBatches[key]
                  ? `${getColorClasses(batch.color)} border-2`
                  : 'bg-gray-50 border-gray-300 text-gray-500'
              ]"
            >
              <div class="flex items-center justify-between mb-2">
                <h3 class="font-semibold">{{ batch.name }}</h3>
                <div :class="[
                  'w-6 h-6 rounded border-2 flex items-center justify-center',
                  selectedBatches[key] ? 'bg-white border-current' : 'border-gray-400'
                ]">
                  <svg v-if="selectedBatches[key]" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                </div>
              </div>
              <p class="text-xs opacity-75">{{ batch.fields.length }} fields</p>
            </div>
          </div>

          <div class="flex justify-between">
            <button
              @click="currentStep = 1"
              class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
            >
              Back
            </button>
            <button
              @click="currentStep = 3"
              :disabled="!hasSelectedBatches"
              class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Continue to Mapping
            </button>
          </div>
        </div>

        <!-- Step 3: Map Columns -->
        <div v-if="currentStep === 3" class="space-y-6">
          <div class="flex items-center gap-2 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <svg class="text-yellow-600 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="text-sm text-yellow-800">
              Map your CSV columns to the database fields. Auto-mapping has been applied based on column names.
            </p>
          </div>

          <div class="space-y-6">
            <div
              v-for="(batch, batchKey) in selectedBatchDefinitions"
              :key="batchKey"
              class="border rounded-lg p-6"
            >
              <h3 :class="['font-semibold text-lg mb-4 inline-block px-3 py-1 rounded', getColorClasses(batch.color)]">
                {{ batch.name }}
              </h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div v-for="field in batch.fields" :key="field.key" class="space-y-2">
                  <label class="block text-sm font-medium text-gray-700">
                    {{ field.label }}
                    <span v-if="field.required" class="text-red-500 ml-1">*</span>
                  </label>
                  <select
                    v-model="columnMappings[field.key]"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  >
                    <option value="">-- Select CSV Column --</option>
                    <option v-for="header in csvHeaders" :key="header" :value="header">
                      {{ header }}
                    </option>
                  </select>
                </div>
              </div>
            </div>
          </div>

          <div class="flex justify-between">
            <button
              @click="currentStep = 2"
              class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
            >
              Back
            </button>
            <button
              @click="handleImport"
              :disabled="importing"
              class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
            >
              {{ importing ? 'Importing...' : 'Start Import' }}
            </button>
          </div>
        </div>

        <!-- Step 4: Success -->
        <div v-if="currentStep === 4" class="text-center py-12">
          <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4">
            <svg class="text-green-600 w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
          </div>
          <h2 class="text-2xl font-bold text-gray-900 mb-2">Import Successful!</h2>
          <p class="text-gray-600 mb-8">Your turbine data has been imported successfully.</p>
          <button
            @click="resetImport"
            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
          >
            Import Another File
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'DataImportPage',
  data() {
    return {
      currentStep: 1,
      file: null,
      csvHeaders: [],
      selectedBatches: {
        scada: true,
        temperature: true,
        vibration: true,
        health: true,
        grid: true,
        hydraulic: true
      },
      columnMappings: {},
      importing: false,
      steps: ['Upload File', 'Select Data Batches', 'Map Columns', 'Import'],
      batchDefinitions: {
        scada: {
          name: 'SCADA Data',
          color: 'blue',
          fields: [
            { key: 'turbine_id', label: 'Turbine ID', required: true },
            { key: 'reading_timestamp', label: 'Timestamp', required: true },
            { key: 'wind_speed_ms', label: 'Wind Speed (m/s)' },
            { key: 'power_kw', label: 'Power (kW)' },
            { key: 'rotor_speed_rpm', label: 'Rotor Speed (RPM)' },
            { key: 'generator_speed_rpm', label: 'Generator Speed (RPM)' },
            { key: 'pitch_angle_deg', label: 'Pitch Angle (deg)' },
            { key: 'yaw_angle_deg', label: 'Yaw Angle (deg)' },
            { key: 'nacelle_direction_deg', label: 'Nacelle Direction (deg)' },
            { key: 'ambient_temp_c', label: 'Ambient Temperature (°C)' },
            { key: 'wind_direction_deg', label: 'Wind Direction (deg)' },
            { key: 'status_code', label: 'Status Code' },
            { key: 'alarm_code', label: 'Alarm Code' }
          ]
        },
        temperature: {
          name: 'Temperature Data',
          color: 'orange',
          fields: [
            { key: 'turbine_id', label: 'Turbine ID', required: true },
            { key: 'reading_timestamp', label: 'Timestamp', required: true },
            { key: 'nacelle_temp_c', label: 'Nacelle Temperature (°C)' },
            { key: 'gearbox_bearing_temp_c', label: 'Gearbox Bearing Temp (°C)' },
            { key: 'gearbox_oil_temp_c', label: 'Gearbox Oil Temp (°C)' },
            { key: 'generator_bearing1_temp_c', label: 'Generator Bearing 1 Temp (°C)' },
            { key: 'generator_bearing2_temp_c', label: 'Generator Bearing 2 Temp (°C)' },
            { key: 'generator_stator_temp_c', label: 'Generator Stator Temp (°C)' },
            { key: 'main_bearing_temp_c', label: 'Main Bearing Temp (°C)' }
          ]
        },
        vibration: {
          name: 'Vibration Data',
          color: 'purple',
          fields: [
            { key: 'turbine_id', label: 'Turbine ID', required: true },
            { key: 'reading_timestamp', label: 'Timestamp', required: true },
            { key: 'main_bearing_vibration_rms_mms', label: 'Main Bearing Vibration RMS (mm/s)' },
            { key: 'main_bearing_vibration_peak_mms', label: 'Main Bearing Vibration Peak (mm/s)' },
            { key: 'gearbox_vibration_axial_mms', label: 'Gearbox Vibration Axial (mm/s)' },
            { key: 'gearbox_vibration_radial_mms', label: 'Gearbox Vibration Radial (mm/s)' },
            { key: 'generator_vibration_de_mms', label: 'Generator Vibration DE (mm/s)' },
            { key: 'generator_vibration_nde_mms', label: 'Generator Vibration NDE (mm/s)' },
            { key: 'tower_vibration_fa_mms', label: 'Tower Vibration FA (mm/s)' },
            { key: 'tower_vibration_ss_mms', label: 'Tower Vibration SS (mm/s)' },
            { key: 'blade1_vibration_mms', label: 'Blade 1 Vibration (mm/s)' },
            { key: 'blade2_vibration_mms', label: 'Blade 2 Vibration (mm/s)' },
            { key: 'blade3_vibration_mms', label: 'Blade 3 Vibration (mm/s)' },
            { key: 'acoustic_level_db', label: 'Acoustic Level (dB)' }
          ]
        },
        health: {
          name: 'Health Metrics',
          color: 'green',
          fields: [
            { key: 'turbine_id', label: 'Turbine ID', required: true },
            { key: 'reading_timestamp', label: 'Timestamp', required: true },
            { key: 'bearing_wear_index', label: 'Bearing Wear Index' },
            { key: 'oil_quality_index', label: 'Oil Quality Index' },
            { key: 'generator_health_index', label: 'Generator Health Index' },
            { key: 'overall_health_score', label: 'Overall Health Score' }
          ]
        },
        grid: {
          name: 'Grid Electrical Data',
          color: 'yellow',
          fields: [
            { key: 'turbine_id', label: 'Turbine ID', required: true },
            { key: 'reading_timestamp', label: 'Timestamp', required: true },
            { key: 'grid_voltage_v', label: 'Grid Voltage (V)' },
            { key: 'grid_current_a', label: 'Grid Current (A)' },
            { key: 'grid_frequency_hz', label: 'Grid Frequency (Hz)' },
            { key: 'grid_power_factor', label: 'Grid Power Factor' },
            { key: 'reactive_power_kvar', label: 'Reactive Power (kVAR)' }
          ]
        },
        hydraulic: {
          name: 'Hydraulic Data',
          color: 'red',
          fields: [
            { key: 'turbine_id', label: 'Turbine ID', required: true },
            { key: 'reading_timestamp', label: 'Timestamp', required: true },
            { key: 'gearbox_oil_pressure_bar', label: 'Gearbox Oil Pressure (bar)' },
            { key: 'hydraulic_pressure_bar', label: 'Hydraulic Pressure (bar)' }
          ]
        }
      }
    };
  },
  computed: {
    hasSelectedBatches() {
      return Object.values(this.selectedBatches).some(v => v);
    },
    selectedBatchDefinitions() {
      const selected = {};
      Object.entries(this.batchDefinitions).forEach(([key, batch]) => {
        if (this.selectedBatches[key]) {
          selected[key] = batch;
        }
      });
      return selected;
    }
  },
  methods: {
    handleFileUpload(event) {
      const uploadedFile = event.target.files[0];
      console.log('Uploaded filetype:', uploadedFile.type);
      if (uploadedFile) {
        this.file = uploadedFile;
        const reader = new FileReader();
        reader.onload = (e) => {
          const text = e.target.result;
          const headers = text.split('\n')[0].split(',').map(h => h.trim());
          this.csvHeaders = headers;
          
          // Auto-map columns based on header names
          const autoMappings = {};
          
          // Create mapping from CSV headers to DB fields
          headers.forEach(header => {
            const normalizedHeader = header.toLowerCase().replace(/[^a-z0-9_]/g, '_');
            
            // Try to find exact match in all batch fields
            Object.values(this.batchDefinitions).forEach(batch => {
              batch.fields.forEach(field => {
                if (field.key === normalizedHeader || field.key === header) {
                  autoMappings[field.key] = header;
                }
              });
            });
          });
          
          this.columnMappings = autoMappings;
          this.currentStep = 2;
        };
        reader.readAsText(uploadedFile);
      }
    },
    toggleBatch(batchKey) {
      this.selectedBatches[batchKey] = !this.selectedBatches[batchKey];
    },
    getColorClasses(color) {
      const colors = {
        blue: 'bg-blue-100 text-blue-800 border-blue-300',
        orange: 'bg-orange-100 text-orange-800 border-orange-300',
        purple: 'bg-purple-100 text-purple-800 border-purple-300',
        green: 'bg-green-100 text-green-800 border-green-300',
        yellow: 'bg-yellow-100 text-yellow-800 border-yellow-300',
        red: 'bg-red-100 text-red-800 border-red-300'
      };
      return colors[color] || colors.blue;
    },
    async handleImport() {
      this.importing = true;
      
      // Here you would typically send the data to your backend
      // Example:
      // const formData = new FormData();
      // formData.append('file', this.file);
      // formData.append('batches', JSON.stringify(this.selectedBatches));
      // formData.append('mappings', JSON.stringify(this.columnMappings));
      // await axios.post('/api/import-turbine-data', formData);
      
      // Simulate import process
      setTimeout(() => {
        this.importing = false;
        this.currentStep = 4;
      }, 2000);
    },
    downloadTemplate() {
      const headers = 'timestamp,turbine_id,wind_speed_ms,power_kw,rotor_speed_rpm,generator_speed_rpm,pitch_angle_deg,yaw_angle_deg,nacelle_direction_deg,ambient_temp_c,nacelle_temp_c,gearbox_bearing_temp_c,gearbox_oil_temp_c,gearbox_oil_pressure_bar,generator_bearing1_temp_c,generator_bearing2_temp_c,generator_stator_temp_c,main_bearing_temp_c,hydraulic_pressure_bar,grid_voltage_v,grid_current_a,grid_frequency_hz,grid_power_factor,reactive_power_kvar,wind_direction_deg,status_code,alarm_code,main_bearing_vibration_rms_mms,main_bearing_vibration_peak_mms,gearbox_vibration_axial_mms,gearbox_vibration_radial_mms,generator_vibration_de_mms,generator_vibration_nde_mms,tower_vibration_fa_mms,tower_vibration_ss_mms,blade1_vibration_mms,blade2_vibration_mms,blade3_vibration_mms,acoustic_level_db,bearing_wear_index,oil_quality_index,generator_health_index,overall_health_score';
      const blob = new Blob([headers], { type: 'text/csv' });
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = 'turbine_data_template.csv';
      a.click();
      window.URL.revokeObjectURL(url);
    },
    resetImport() {
      this.currentStep = 1;
      this.file = null;
      this.csvHeaders = [];
      this.columnMappings = {};
      this.selectedBatches = {
        scada: true,
        temperature: true,
        vibration: true,
        health: true,
        grid: true,
        hydraulic: true
      };
    }
  }
};
</script>

<style scoped>
/* Add any additional custom styles here if needed */
</style>