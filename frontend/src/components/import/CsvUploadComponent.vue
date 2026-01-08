<template>
  <div class="transition-colors duration-200">
    <h2 class="text-xl font-bold mb-4 text-gray-900 dark:text-white">Step 2: Upload CSV File</h2>

    <div v-if="!csvParsed" class="space-y-4">
      <div
          @dragover.prevent="dragOver = true"
          @dragleave.prevent="dragOver = false"
          @drop.prevent="handleFileDrop"
          class="border-2 border-dashed rounded-lg p-8 text-center transition-colors dark:bg-slate-800/50"
          :class="dragOver
            ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/20 dark:border-blue-500'
            : 'border-gray-300 hover:border-blue-400 dark:border-slate-600 dark:hover:border-blue-400'"
      >
        <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-slate-500" stroke="currentColor" fill="none" viewBox="0 0 48 48">
          <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <div class="mt-4">
          <label for="file-upload" class="cursor-pointer">
            <span class="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 font-medium">Upload a file</span>
            <span class="text-gray-600 dark:text-slate-400"> or drag and drop</span>
            <input id="file-upload" name="file-upload" type="file" accept=".csv" class="sr-only" @change="handleFileSelect">
          </label>
        </div>
        <p class="text-xs text-gray-500 dark:text-slate-500 mt-2">CSV files only, up to 10MB</p>
      </div>

      <div v-if="processing" class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
        <div class="flex items-center">
          <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-blue-600 dark:border-blue-400 mr-3"></div>
          <span class="text-blue-800 dark:text-blue-200">Processing CSV file...</span>
        </div>
      </div>

      <div v-if="error" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
        <div class="flex items-start">
          <svg class="w-5 h-5 text-red-600 dark:text-red-400 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
          </svg>
          <div>
            <h4 class="font-semibold text-red-900 dark:text-red-200">Error</h4>
            <p class="text-sm text-red-800 dark:text-red-300 mt-1">{{ error }}</p>
          </div>
        </div>
      </div>
    </div>

    <div v-if="csvParsed && !error" class="space-y-4">
      <div class="bg-green-50 dark:bg-emerald-900/20 border border-green-200 dark:border-emerald-800 rounded-lg p-4">
        <div class="flex items-center">
          <svg class="w-5 h-5 text-green-600 dark:text-emerald-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
          </svg>
          <div>
            <h4 class="font-semibold text-green-900 dark:text-emerald-200">CSV Parsed Successfully</h4>
            <p class="text-sm text-green-800 dark:text-emerald-300 mt-1">
              Found {{ parsedData.data.length }} rows with {{ parsedData.headers.length }} columns
            </p>
          </div>
        </div>
      </div>

      <div>
        <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Detected Columns ({{ parsedData.headers.length }})</h3>
        <div class="flex flex-wrap gap-2">
          <span v-for="(header, index) in parsedData.headers" :key="index"
                class="px-3 py-1 bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-200 rounded-full text-sm font-medium border border-transparent dark:border-blue-800">
            {{ header }}
          </span>
        </div>
      </div>

      <div>
        <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Preview (First 10 Rows)</h3>
        <div class="overflow-x-auto border border-gray-300 dark:border-slate-700 rounded-lg">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700">
            <thead class="bg-gray-50 dark:bg-slate-800">
            <tr>
              <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">#</th>
              <th v-for="header in parsedData.headers" :key="header"
                  class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase truncate max-w-xs">
                {{ header }}
              </th>
            </tr>
            </thead>
            <tbody class="bg-white dark:bg-slate-900 divide-y divide-gray-200 dark:divide-slate-800">
            <tr v-for="(row, index) in previewData" :key="index" class="hover:bg-gray-50 dark:hover:bg-slate-800/50 transition-colors">
              <td class="px-3 py-2 text-sm text-gray-500 dark:text-slate-500">{{ index + 1 }}</td>
              <td v-for="header in parsedData.headers" :key="header"
                  class="px-3 py-2 text-sm text-gray-900 dark:text-slate-300 truncate max-w-xs">
                {{ row[header] || '-' }}
              </td>
            </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="flex justify-between pt-4">
        <button @click="resetUpload"
                class="px-4 py-2 border border-gray-300 dark:border-slate-600 text-gray-700 dark:text-slate-300 rounded-lg hover:bg-gray-50 dark:hover:bg-slate-800 transition-colors">
          Upload Different File
        </button>
        <div class="space-x-3">
          <button @click="$emit('back')"
                  class="px-4 py-2 border border-gray-300 dark:border-slate-600 text-gray-700 dark:text-slate-300 rounded-lg hover:bg-gray-50 dark:hover:bg-slate-800 transition-colors">
            Back
          </button>
          <button @click="proceedToMapping"
                  class="px-6 py-2 bg-blue-600 hover:bg-blue-700 dark:bg-blue-600 dark:hover:bg-blue-500 text-white rounded-lg transition-colors shadow-sm">
            Next: Map Columns
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
// The script section remains exactly the same as your original component.
import { ref, computed } from 'vue';
import Papa from 'papaparse';

export default {
  name: 'CsvUploadComponent',
  props: {
    dataType: {
      type: String,
      required: true
    }
  },
  emits: ['csv-parsed', 'back'],
  setup(props, { emit }) {
    const dragOver = ref(false);
    const processing = ref(false);
    const error = ref('');
    const csvParsed = ref(false);
    const parsedData = ref(null);
    const fileName = ref('');

    const previewData = computed(() => {
      if (!parsedData.value) return [];
      return parsedData.value.data.slice(0, 10);
    });

    const handleFileSelect = (event) => {
      const file = event.target.files[0];
      if (file) {
        processFile(file);
      }
    };

    const handleFileDrop = (event) => {
      dragOver.value = false;
      const file = event.dataTransfer.files[0];
      if (file) {
        if (file.type !== 'text/csv' && !file.name.endsWith('.csv')) {
          error.value = 'Please upload a CSV file';
          return;
        }
        processFile(file);
      }
    };

    const processFile = (file) => {
      // Validate file size (10GB limit)
      if (file.size > 10 * 1024 * 1024 * 1024) {
        error.value = 'File size exceeds 10GB limit';
        return;
      }

      error.value = '';
      processing.value = true;
      fileName.value = file.name;

      Papa.parse(file, {
        header: true,
        skipEmptyLines: true,
        dynamicTyping: true,
        complete: (results) => {
          processing.value = false;

          if (!results.data || results.data.length === 0) {
            error.value = 'CSV file is empty or invalid';
            return;
          }

          if (!results.meta.fields || results.meta.fields.length === 0) {
            error.value = 'No column headers found in CSV';
            return;
          }

          if (results.errors && results.errors.length > 0) {
            console.warn('CSV parsing warnings:', results.errors);
          }

          parsedData.value = {
            headers: results.meta.fields,
            data: results.data,
            fileName: fileName.value,
            rowCount: results.data.length
          };

          csvParsed.value = true;
        },
        error: (error) => {
          processing.value = false;
          error.value = `Failed to parse CSV: ${error.message}`;
        }
      });
    };

    const resetUpload = () => {
      csvParsed.value = false;
      parsedData.value = null;
      error.value = '';
      fileName.value = '';
    };

    const proceedToMapping = () => {
      emit('csv-parsed', parsedData.value);
    };

    return {
      dragOver,
      processing,
      error,
      csvParsed,
      parsedData,
      previewData,
      handleFileSelect,
      handleFileDrop,
      resetUpload,
      proceedToMapping
    };
  }
};
</script>