<template>
  <div>
    <h2 class="text-xl font-bold mb-4">Step 2: Identify Key Columns</h2>

    <div class="space-y-6">
      <!-- Instructions -->
      <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex items-start">
          <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
          </svg>
          <div>
            <h4 class="font-semibold text-blue-900">Identify Required Columns</h4>
            <p class="text-sm text-blue-800 mt-2">
              We need to identify which columns contain the Turbine ID and Timestamp.
              The system will auto-detect these, but you can adjust if needed.
            </p>
          </div>
        </div>
      </div>

      <!-- Auto-detection Results -->
      <div v-if="autoDetected" class="bg-green-50 border border-green-200 rounded-lg p-4">
        <div class="flex items-start">
          <svg class="w-5 h-5 text-green-600 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
          </svg>
          <div>
            <h4 class="font-semibold text-green-900">Auto-Detection Complete</h4>
            <p class="text-sm text-green-800 mt-1">
              {{ detectionResults }}
            </p>
          </div>
        </div>
      </div>

      <!-- Turbine ID Selection -->
      <div class="border border-gray-300 rounded-lg p-6">
        <div class="flex items-start justify-between mb-4">
          <div>
            <h3 class="text-lg font-semibold text-gray-900">Turbine ID Column</h3>
            <p class="text-sm text-gray-600 mt-1">
              Select the column that contains turbine identifiers (e.g., T001, T002, WTG-1)
            </p>
          </div>
          <span class="text-red-600 font-semibold">Required *</span>
        </div>

        <div class="space-y-4">
          <!-- Option 1: CSV has turbine column -->
          <div>
            <label class="flex items-center space-x-3 cursor-pointer">
              <input type="radio" v-model="turbineIdMode" value="column" class="w-4 h-4 text-blue-600">
              <span class="font-medium">CSV contains a turbine ID column</span>
            </label>
            <div v-if="turbineIdMode === 'column'" class="ml-7 mt-3">
              <select v-model="selectedTurbineColumn"
                      class="w-full max-w-md px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                      :class="!selectedTurbineColumn ? 'border-red-300 bg-red-50' : 'border-gray-300'">
                <option value="">-- Select Turbine Column --</option>
                <option v-for="header in csvData.headers" :key="header" :value="header">
                  {{ header }}
                </option>
              </select>
              <div v-if="selectedTurbineColumn" class="mt-3 p-3 bg-gray-50 rounded border border-gray-200">
                <p class="text-sm font-medium text-gray-700 mb-2">Detected Turbine IDs ({{ uniqueTurbines.length }}):</p>
                <div class="flex flex-wrap gap-2">
                  <span v-for="turbine in uniqueTurbines.slice(0, 20)" :key="turbine"
                        class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-sm font-mono">
                    {{ turbine }}
                  </span>
                  <span v-if="uniqueTurbines.length > 20" class="px-2 py-1 bg-gray-200 text-gray-600 rounded text-sm">
                    +{{ uniqueTurbines.length - 20 }} more
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Option 2: Single turbine -->
          <div>
            <label class="flex items-center space-x-3 cursor-pointer">
              <input type="radio" v-model="turbineIdMode" value="single" class="w-4 h-4 text-blue-600">
              <span class="font-medium">This CSV is for a single turbine</span>
            </label>
            <div v-if="turbineIdMode === 'single'" class="ml-7 mt-3">
              <select v-model="selectedSingleTurbine"
                      class="w-full max-w-md px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                      :class="!selectedSingleTurbine ? 'border-red-300 bg-red-50' : 'border-gray-300'">
                <option value="">-- Select Turbine --</option>
                <option v-for="turbine in existingTurbines" :key="turbine.id" :value="turbine.turbine_id">
                  {{ turbine.turbine_id }}
                </option>
              </select>
              <p class="text-xs text-gray-500 mt-2">All rows will be imported for this turbine</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Timestamp Selection -->
      <div class="border border-gray-300 rounded-lg p-6">
        <div class="flex items-start justify-between mb-4">
          <div>
            <h3 class="text-lg font-semibold text-gray-900">Timestamp Column</h3>
            <p class="text-sm text-gray-600 mt-1">
              Select the column that contains reading timestamps
            </p>
          </div>
          <span class="text-red-600 font-semibold">Required *</span>
        </div>

        <select v-model="selectedTimestampColumn"
                class="w-full max-w-md px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                :class="!selectedTimestampColumn ? 'border-red-300 bg-red-50' : 'border-gray-300'">
          <option value="">-- Select Timestamp Column --</option>
          <option v-for="header in csvData.headers" :key="header" :value="header">
            {{ header }}
          </option>
        </select>

        <div v-if="selectedTimestampColumn" class="mt-3 p-3 bg-gray-50 rounded border border-gray-200">
          <p class="text-sm font-medium text-gray-700 mb-2">Sample timestamps:</p>
          <div class="space-y-1">
            <p v-for="(sample, idx) in timestampSamples" :key="idx" class="text-sm font-mono text-gray-600">
              {{ sample }}
            </p>
          </div>
        </div>
      </div>

      <!-- Validation Errors -->
      <div v-if="validationErrors.length > 0" class="bg-red-50 border border-red-200 rounded-lg p-4">
        <div class="flex items-start">
          <svg class="w-5 h-5 text-red-600 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
          </svg>
          <div>
            <h4 class="font-semibold text-red-900">Validation Errors</h4>
            <ul class="text-sm text-red-800 mt-2 space-y-1 list-disc list-inside">
              <li v-for="(error, index) in validationErrors" :key="index">{{ error }}</li>
            </ul>
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex justify-between pt-4">
        <button @click="$emit('back')"
                class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
          Back to Upload
        </button>
        <button @click="proceedToMapping"
                :disabled="!isValid"
                class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed">
          Next: Map Sensor Columns
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted, watch } from 'vue';

export default {
  name: 'KeyColumnsComponent',
  props: {
    csvData: {
      type: Object,
      required: true
    }
  },
  emits: ['key-columns-identified', 'back'],
  setup(props, { emit }) {
    const turbineIdMode = ref('column');
    const selectedTurbineColumn = ref('');
    const selectedSingleTurbine = ref('');
    const selectedTimestampColumn = ref('');
    const existingTurbines = ref([]);
    const autoDetected = ref(false);
    const validationErrors = ref([]);

    const detectionResults = computed(() => {
      const results = [];
      if (selectedTurbineColumn.value) {
        results.push(`Turbine ID: "${selectedTurbineColumn.value}"`);
      }
      if (selectedTimestampColumn.value) {
        results.push(`Timestamp: "${selectedTimestampColumn.value}"`);
      }
      return results.length > 0 ? results.join(' | ') : 'No columns auto-detected';
    });

    const uniqueTurbines = computed(() => {
      if (!selectedTurbineColumn.value || !props.csvData.data.length) return [];

      const turbines = new Set();
      props.csvData.data.forEach(row => {
        const value = row[selectedTurbineColumn.value];
        if (value) turbines.add(String(value));
      });

      return Array.from(turbines).sort();
    });

    const timestampSamples = computed(() => {
      if (!selectedTimestampColumn.value || !props.csvData.data.length) return [];

      return props.csvData.data
          .slice(0, 5)
          .map(row => row[selectedTimestampColumn.value])
          .filter(v => v);
    });

    const isValid = computed(() => {
      return selectedTimestampColumn.value &&
          (turbineIdMode.value === 'column' ? selectedTurbineColumn.value : selectedSingleTurbine.value);
    });

    const autoDetectColumns = () => {
      // Auto-detect turbine ID column
      const turbinePatterns = ['turbine', 'turbine_id', 'turbineid', 'wtg', 'unit', 'wt', 'id'];
      const turbineColumn = props.csvData.headers.find(header => {
        const headerLower = header.toLowerCase().trim();
        return turbinePatterns.some(pattern => headerLower.includes(pattern));
      });

      if (turbineColumn) {
        selectedTurbineColumn.value = turbineColumn;
        turbineIdMode.value = 'column';
      }

      // Auto-detect timestamp column
      const timestampPatterns = ['timestamp', 'time', 'datetime', 'date', 'reading_time'];
      const timestampColumn = props.csvData.headers.find(header => {
        const headerLower = header.toLowerCase().trim();
        return timestampPatterns.some(pattern => headerLower.includes(pattern));
      });

      if (timestampColumn) {
        selectedTimestampColumn.value = timestampColumn;
      }

      autoDetected.value = !!(turbineColumn || timestampColumn);
    };

    const fetchExistingTurbines = async () => {
      try {
        const response = await fetch('http://localhost:8000/api/turbines');
        const data = await response.json();
        existingTurbines.value = data.data || data;
      } catch (error) {
        console.error('Failed to fetch turbines:', error);
      }
    };

    const validate = () => {
      validationErrors.value = [];

      if (!selectedTimestampColumn.value) {
        validationErrors.value.push('Timestamp column is required');
      }

      if (turbineIdMode.value === 'column' && !selectedTurbineColumn.value) {
        validationErrors.value.push('Turbine ID column is required when CSV contains multiple turbines');
      }

      if (turbineIdMode.value === 'single' && !selectedSingleTurbine.value) {
        validationErrors.value.push('Please select a turbine when importing for a single turbine');
      }

      if (turbineIdMode.value === 'column' && selectedTurbineColumn.value) {
        if (uniqueTurbines.value.length === 0) {
          validationErrors.value.push('No turbine IDs found in selected column');
        }
      }

      return validationErrors.value.length === 0;
    };

    const proceedToMapping = () => {
      if (!validate()) return;

      emit('key-columns-identified', {
        turbineIdMode: turbineIdMode.value,
        turbineColumn: turbineIdMode.value === 'column' ? selectedTurbineColumn.value : null,
        singleTurbineId: turbineIdMode.value === 'single' ? selectedSingleTurbine.value : null,
        timestampColumn: selectedTimestampColumn.value,
        uniqueTurbines: turbineIdMode.value === 'column' ? uniqueTurbines.value : [selectedSingleTurbine.value]
      });
    };

    watch([selectedTurbineColumn, selectedSingleTurbine, selectedTimestampColumn], () => {
      if (validationErrors.value.length > 0) {
        validate();
      }
    });

    onMounted(() => {
      autoDetectColumns();
      fetchExistingTurbines();
    });

    return {
      turbineIdMode,
      selectedTurbineColumn,
      selectedSingleTurbine,
      selectedTimestampColumn,
      existingTurbines,
      autoDetected,
      detectionResults,
      uniqueTurbines,
      timestampSamples,
      validationErrors,
      isValid,
      proceedToMapping
    };
  }
};
</script>
