<template>
  <div>
    <h2 class="text-xl font-bold mb-4">Step 4: Validate & Import Data</h2>

    <div class="space-y-6">
      <!-- Summary Before Validation -->
      <div v-if="!validationStarted" class="space-y-4">
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
          <h3 class="font-semibold text-blue-900 mb-4">Import Summary</h3>
          <div class="grid grid-cols-2 gap-6">
            <div>
              <p class="text-sm text-blue-800 mb-2"><strong>Total Rows:</strong> {{ csvData.rowCount }}</p>
              <p class="text-sm text-blue-800 mb-2"><strong>Turbines:</strong> {{ keyColumns.uniqueTurbines.length }}</p>
              <p class="text-sm text-blue-800"><strong>Timestamp Column:</strong> {{ keyColumns.timestampColumn }}</p>
            </div>
            <div>
              <p class="text-sm text-blue-800 font-semibold mb-2">Data Categories:</p>
              <ul class="space-y-1">
                <li v-for="cat in sensorMapping.mappedCategories.filter(c => c.mappedCount > 0)" :key="cat.id"
                    class="text-sm text-blue-800">
                  {{ cat.name }}: {{ cat.mappedCount }} fields
                </li>
              </ul>
            </div>
          </div>
        </div>

        <div class="flex justify-between">
          <button @click="$emit('back')"
                  class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
            Back to Mapping
          </button>
          <button @click="startValidation"
                  class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            Start Validation
          </button>
        </div>
      </div>

      <!-- Validation in Progress -->
      <div v-if="validating" class="bg-blue-50 border border-blue-200 rounded-lg p-6 text-center">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
        <h3 class="font-semibold text-blue-900 mb-2">Validating Data...</h3>
        <p class="text-sm text-blue-700">Checking {{ csvData.rowCount }} rows for errors</p>
      </div>

      <!-- Validation Complete -->
      <div v-if="validationComplete && !importing">
        <!-- Validation Summary Cards -->
        <div class="grid grid-cols-4 gap-4 mb-6">
          <div class="bg-white border border-gray-300 rounded-lg p-4">
            <div class="text-sm text-gray-600">Total Rows</div>
            <div class="text-2xl font-bold text-gray-900">{{ validationResults.totalRows }}</div>
          </div>
          <div class="bg-green-50 border border-green-300 rounded-lg p-4">
            <div class="text-sm text-green-700">Valid Rows</div>
            <div class="text-2xl font-bold text-green-700">{{ validationResults.validRows }}</div>
          </div>
          <div class="bg-yellow-50 border border-yellow-300 rounded-lg p-4">
            <div class="text-sm text-yellow-700">Warnings</div>
            <div class="text-2xl font-bold text-yellow-700">{{ validationResults.warningRows }}</div>
          </div>
          <div class="bg-red-50 border border-red-300 rounded-lg p-4">
            <div class="text-sm text-red-700">Invalid Rows</div>
            <div class="text-2xl font-bold text-red-700">{{ validationResults.invalidRows }}</div>
          </div>
        </div>

        <!-- Errors Display -->
        <div v-if="validationResults.errors.length > 0" class="mb-6">
          <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
            <div class="flex items-start justify-between">
              <div class="flex items-start">
                <svg class="w-5 h-5 text-red-600 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
                <div>
                  <h4 class="font-semibold text-red-900">Validation Errors ({{ validationResults.errors.length }})</h4>
                  <p class="text-sm text-red-800 mt-1">These rows will be skipped</p>
                </div>
              </div>
              <button @click="showErrors = !showErrors" class="text-red-600 hover:text-red-800 font-medium text-sm">
                {{ showErrors ? 'Hide' : 'Show' }} Details
              </button>
            </div>
          </div>

          <div v-if="showErrors" class="border border-red-300 rounded-lg overflow-hidden mb-4">
            <div class="overflow-x-auto max-h-96">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-red-50">
                <tr>
                  <th class="px-4 py-2 text-left text-xs font-medium text-red-900 uppercase">Row</th>
                  <th class="px-4 py-2 text-left text-xs font-medium text-red-900 uppercase">Error</th>
                </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="(error, index) in validationResults.errors.slice(0, 50)" :key="index">
                  <td class="px-4 py-2 text-sm text-gray-900">{{ error.row }}</td>
                  <td class="px-4 py-2 text-sm text-red-700">{{ error.message }}</td>
                </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Warnings Display -->
        <div v-if="validationResults.warnings.length > 0" class="mb-6">
          <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
            <div class="flex items-start justify-between">
              <div class="flex items-start">
                <svg class="w-5 h-5 text-yellow-600 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                <div>
                  <h4 class="font-semibold text-yellow-900">Warnings ({{ validationResults.warnings.length }})</h4>
                  <p class="text-sm text-yellow-800 mt-1">These rows will be imported but have potential issues</p>
                </div>
              </div>
              <button @click="showWarnings = !showWarnings" class="text-yellow-600 hover:text-yellow-800 font-medium text-sm">
                {{ showWarnings ? 'Hide' : 'Show' }} Details
              </button>
            </div>
          </div>

          <div v-if="showWarnings" class="border border-yellow-300 rounded-lg overflow-hidden mb-4">
            <div class="overflow-x-auto max-h-96">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-yellow-50">
                <tr>
                  <th class="px-4 py-2 text-left text-xs font-medium text-yellow-900 uppercase">Row</th>
                  <th class="px-4 py-2 text-left text-xs font-medium text-yellow-900 uppercase">Warning</th>
                </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="(warning, index) in validationResults.warnings.slice(0, 50)" :key="index">
                  <td class="px-4 py-2 text-sm text-gray-900">{{ warning.row }}</td>
                  <td class="px-4 py-2 text-sm text-yellow-700">{{ warning.message }}</td>
                </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Ready to Import -->
        <div v-if="validationResults.validRows > 0" class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
          <div class="flex items-start">
            <svg class="w-5 h-5 text-green-600 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <div>
              <h4 class="font-semibold text-green-900">Ready to Import</h4>
              <p class="text-sm text-green-800 mt-1">
                {{ validationResults.validRows }} valid rows will be imported into the database.
                Missing sensor values will be stored as NULL.
              </p>
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-between">
          <button @click="$emit('back')"
                  class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
            Back
          </button>
          <button v-if="validationResults.validRows > 0" @click="startImport"
                  class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
            Import {{ validationResults.validRows }} Rows
          </button>
        </div>
      </div>

      <!-- REPLACE YOUR "Import in Progress" SECTION IN ValidationImportComponent.vue TEMPLATE WITH THIS: -->

      <!-- Import in Progress -->
      <div v-if="importing" class="space-y-4">
        <!-- Regular Progress (small files) -->
        <div v-if="totalChunks === 0" class="bg-blue-50 border border-blue-200 rounded-lg p-6 text-center">
          <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
          <h3 class="font-semibold text-blue-900 mb-2">Importing Data...</h3>
          <p class="text-sm text-blue-700">Processing {{ csvData.data.length.toLocaleString() }} rows</p>
          <div class="w-full bg-gray-200 rounded-full h-2 mt-4 max-w-md mx-auto">
            <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" :style="{ width: importProgress + '%' }"></div>
          </div>
          <p class="text-xs text-blue-600 mt-2">{{ importStatusMessage }}</p>
        </div>

        <!-- Chunked Progress (large files) -->
        <div v-else class="bg-blue-50 border border-blue-200 rounded-lg p-6">
          <div class="flex items-start mb-4">
            <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-600 mr-4 flex-shrink-0"></div>
            <div class="flex-1">
              <h3 class="font-semibold text-blue-900 mb-2">
                Processing Large Import
              </h3>
              <p class="text-sm text-blue-700 mb-1">
                Chunk {{ currentChunk }} of {{ totalChunks }}
              </p>
              <p class="text-xs text-blue-600">
                Total rows: {{ csvData.data.length.toLocaleString() }} |
                Processed: {{ Math.floor((chunkedProgress / 100) * csvData.data.length).toLocaleString() }}
              </p>
            </div>
            <div class="text-right">
              <div class="text-2xl font-bold text-blue-900">
                {{ chunkedProgress.toFixed(1) }}%
              </div>
            </div>
          </div>

          <!-- Progress Bar -->
          <div class="w-full bg-blue-200 rounded-full h-3 mb-3">
            <div
                class="bg-blue-600 h-3 rounded-full transition-all duration-300 flex items-center justify-end pr-2"
                :style="{ width: chunkedProgress + '%' }"
            >
        <span v-if="chunkedProgress > 10" class="text-xs text-white font-medium">
          {{ chunkedProgress.toFixed(0) }}%
        </span>
            </div>
          </div>

          <!-- Info Message -->
          <div class="bg-blue-100 border border-blue-300 rounded-lg p-3 mt-4">
            <div class="flex items-start">
              <svg class="w-4 h-4 text-blue-600 mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
              </svg>
              <p class="text-xs text-blue-800">
                Large file detected - using chunked processing to prevent timeouts.
                This may take a few minutes. Please don't close this page.
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref } from 'vue';
import { useChunkedImport } from '@/composables/useChunkedImport'


export default {
  name: 'ValidationImportComponent',
  props: {
    csvData: {
      type: Object,
      required: true
    },
    keyColumns: {
      type: Object,
      required: true
    },
    sensorMapping: {
      type: Object,
      required: true
    }
  },
  emits: ['import-complete', 'back'],
  setup(props, { emit }) {
    const validationStarted = ref(false);
    const validating = ref(false);
    const validationComplete = ref(false);
    const importing = ref(false);
    const importProgress = ref(0);
    const importStatusMessage = ref('');
    const showErrors = ref(false);
    const showWarnings = ref(false);

    const { progress: chunkedProgress, currentChunk, totalChunks, startImport: startChunkedImport } = useChunkedImport()

    const validationResults = ref({
      totalRows: 0,
      validRows: 0,
      invalidRows: 0,
      warningRows: 0,
      errors: [],
      warnings: []
    });

    const startValidation = () => {
      validationStarted.value = true;
      validating.value = true;

      setTimeout(() => {
        validateData();
        validating.value = false;
        validationComplete.value = true;
      }, 2000);
    };

    const validateData = () => {
      validationResults.value = {
        totalRows: props.csvData.data.length,
        validRows: 0,
        invalidRows: 0,
        warningRows: 0,
        errors: [],
        warnings: []
      };

      props.csvData.data.forEach((row, index) => {
        let rowValid = true;
        let rowWarning = false;

        // Validate timestamp
        const timestamp = row[props.keyColumns.timestampColumn];
        if (!timestamp) {
          validationResults.value.errors.push({
            row: index + 1,
            message: 'Missing timestamp'
          });
          rowValid = false;
        } else {
          const date = new Date(timestamp);
          if (isNaN(date.getTime())) {
            validationResults.value.errors.push({
              row: index + 1,
              message: `Invalid timestamp format: ${timestamp}`
            });
            rowValid = false;
          }
        }

        // Validate turbine ID
        if (props.keyColumns.turbineIdMode === 'column') {
          const turbineId = row[props.keyColumns.turbineColumn];
          if (!turbineId) {
            validationResults.value.errors.push({
              row: index + 1,
              message: 'Missing turbine ID'
            });
            rowValid = false;
          }
        }

        // Check if row has at least some sensor data
        const hasSensorData = Object.entries(props.sensorMapping.sensorMapping)
            .some(([field, column]) => column && row[column] != null && row[column] !== '');

        if (!hasSensorData) {
          validationResults.value.warnings.push({
            row: index + 1,
            message: 'No sensor data found - all values will be NULL'
          });
          rowWarning = true;
        }

        if (rowValid) {
          validationResults.value.validRows++;
          if (rowWarning) {
            validationResults.value.warningRows++;
          }
        } else {
          validationResults.value.invalidRows++;
        }
      });
    };

    const startImport = async () => {
      importing.value = true
      importProgress.value = 0
      importStatusMessage.value = 'Preparing data...'

      try {
        const requestData = {
          key_columns: {
            turbineIdMode: props.keyColumns.turbineIdMode,
            turbineColumn: props.keyColumns.turbineColumn,
            singleTurbineId: props.keyColumns.singleTurbineId,
            timestampColumn: props.keyColumns.timestampColumn,
            uniqueTurbines: props.keyColumns.uniqueTurbines
          },
          sensor_mapping: { ...props.sensorMapping.sensorMapping },
          data: props.csvData.data,
          file_name: props.csvData.fileName
        }

        console.log('Starting import:', {
          rows: requestData.data.length,
          mode: requestData.key_columns.turbineIdMode
        })

        // Estimate file size
        const estimatedSize = JSON.stringify(requestData.data).length

        // Use chunked import composable (automatically chooses chunked vs regular)
        const result = await startChunkedImport(
            requestData.key_columns,
            requestData.sensor_mapping,
            requestData.data,
            requestData.file_name,
            estimatedSize
        )

        // Update progress from chunked import
        if (totalChunks.value > 0) {
          importProgress.value = chunkedProgress.value
          importStatusMessage.value = `Processing chunk ${currentChunk.value} of ${totalChunks.value}...`
        } else {
          importProgress.value = 100
          importStatusMessage.value = 'Import complete!'
        }

        setTimeout(() => {
          importing.value = false
          emit('import-complete', result)
        }, 1000)

      } catch (error) {
        importing.value = false
        alert('Import failed: ' + error.message)
        console.error('Import error:', error)
      }
    }

    return {
      validationStarted,
      validating,
      validationComplete,
      validationResults,
      importing,
      importProgress,
      importStatusMessage,
      showErrors,
      showWarnings,
      startValidation,
      startImport,
      currentChunk,
      totalChunks,
      chunkedProgress
    };
  }
};
</script>
