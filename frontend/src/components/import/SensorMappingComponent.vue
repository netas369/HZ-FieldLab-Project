<template>
  <div class="transition-colors duration-200">
    <h2 class="text-xl font-bold mb-4 text-gray-900 dark:text-white">Step 3: Map Sensor Columns</h2>

    <div class="space-y-6">
      <div
        class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4"
      >
        <div class="flex items-start">
          <svg
            class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5 mr-3 flex-shrink-0"
            fill="currentColor"
            viewBox="0 0 20 20"
          >
            <path
              fill-rule="evenodd"
              d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
              clip-rule="evenodd"
            />
          </svg>
          <div>
            <h4 class="font-semibold text-blue-900 dark:text-blue-100">Map Your Sensor Columns</h4>
            <p class="text-sm text-blue-800 dark:text-blue-200 mt-2">
              Map your CSV columns to sensor readings. The system auto-detected
              {{ totalDetected }} columns. All categories are optional - map only what you have.
              Missing data will be stored as NULL.
            </p>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div
          v-for="category in categories"
          :key="category.id"
          class="p-4 border-2 rounded-lg transition-colors"
          :class="
            getMappedCount(category.id) > 0
              ? 'border-green-300 bg-green-50 dark:bg-green-900/20 dark:border-green-800'
              : 'border-gray-300 bg-gray-50 dark:bg-slate-800 dark:border-slate-700'
          "
        >
          <div class="flex items-center justify-between">
            <span class="text-2xl">{{ category.icon }}</span>
            <span
              class="text-xs font-semibold px-2 py-1 rounded transition-colors"
              :class="
                getMappedCount(category.id) > 0
                  ? 'bg-green-200 text-green-800 dark:bg-green-900/40 dark:text-green-300'
                  : 'bg-gray-200 text-gray-600 dark:bg-slate-700 dark:text-slate-400'
              "
            >
              {{ getMappedCount(category.id) }}/{{ category.fields.length }}
            </span>
          </div>
          <h4
            class="font-semibold mt-2 text-sm transition-colors"
            :class="
              getMappedCount(category.id) > 0
                ? 'text-green-900 dark:text-green-100'
                : 'text-gray-900 dark:text-slate-300'
            "
          >
            {{ category.name }}
          </h4>
          <p
            class="text-xs mt-1 transition-colors"
            :class="
              getMappedCount(category.id) > 0
                ? 'text-green-800 dark:text-green-300'
                : 'text-gray-600 dark:text-slate-500'
            "
          >
            {{ category.fields.length }} fields
          </p>
        </div>
      </div>

      <div
        v-for="category in categories"
        :key="category.id"
        class="border border-gray-300 dark:border-slate-700 rounded-lg overflow-hidden transition-colors"
      >
        <div
          class="px-6 py-4 border-b border-gray-300 dark:border-slate-700 cursor-pointer transition-colors"
          :class="
            expandedCategories[category.id]
              ? 'bg-gray-50 dark:bg-slate-800'
              : 'bg-white dark:bg-slate-900 hover:bg-gray-50 dark:hover:bg-slate-800'
          "
          @click="toggleCategory(category.id)"
        >
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
              <span class="text-2xl">{{ category.icon }}</span>
              <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                  {{ category.name }}
                </h3>
                <p class="text-sm text-gray-600 dark:text-slate-400">
                  {{ category.description }}
                </p>
              </div>
            </div>
            <div class="flex items-center space-x-4">
              <span
                class="text-sm font-medium px-3 py-1 rounded transition-colors"
                :class="
                  getMappedCount(category.id) > 0
                    ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300'
                    : 'bg-gray-200 text-gray-600 dark:bg-slate-700 dark:text-slate-400'
                "
              >
                {{ getMappedCount(category.id) }} mapped
              </span>
              <svg
                class="w-5 h-5 text-gray-400 dark:text-slate-500 transition-transform"
                :class="expandedCategories[category.id] ? 'transform rotate-180' : ''"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M19 9l-7 7-7-7"
                />
              </svg>
            </div>
          </div>
        </div>

        <div v-show="expandedCategories[category.id]" class="p-6 bg-white dark:bg-slate-900">
          <div class="space-y-4">
            <div
              v-for="field in category.fields"
              :key="field.name"
              class="grid grid-cols-1 md:grid-cols-3 gap-4 items-start border-b border-gray-100 dark:border-slate-800 pb-4 last:border-0 last:pb-0"
            >
              <div>
                <h4 class="font-medium text-gray-900 dark:text-slate-200">
                  {{ field.label }}
                </h4>
                <p class="text-xs text-gray-500 dark:text-slate-500 mt-1">
                  {{ field.description }}
                </p>
                <span class="text-xs text-gray-400 dark:text-slate-600">{{ field.unit }}</span>
              </div>

              <div>
                <select
                  v-model="sensorMapping[field.name]"
                  class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 text-sm transition-colors border-gray-300 bg-white text-gray-900 dark:border-slate-600 dark:bg-slate-800 dark:text-white dark:placeholder-slate-400"
                >
                  <option value="">-- Skip (NULL) --</option>
                  <option v-for="header in availableColumns" :key="header" :value="header">
                    {{ header }}
                  </option>
                </select>
              </div>

              <div>
                <div v-if="sensorMapping[field.name]" class="text-sm">
                  <p class="text-xs text-gray-500 dark:text-slate-500 mb-1">Sample values:</p>
                  <div class="space-y-1">
                    <p
                      v-for="(sample, idx) in getSampleData(sensorMapping[field.name])"
                      :key="idx"
                      class="font-mono text-xs text-gray-700 dark:text-slate-300 truncate"
                    >
                      {{ sample }}
                    </p>
                  </div>
                </div>
                <p v-else class="text-xs text-gray-400 dark:text-slate-600 italic">No mapping</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div
        v-if="unmappedColumns.length > 0"
        class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4"
      >
        <div class="flex items-start">
          <svg
            class="w-5 h-5 text-yellow-600 dark:text-yellow-500 mt-0.5 mr-3 flex-shrink-0"
            fill="currentColor"
            viewBox="0 0 20 20"
          >
            <path
              fill-rule="evenodd"
              d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
              clip-rule="evenodd"
            />
          </svg>
          <div class="flex-1">
            <h4 class="font-semibold text-yellow-900 dark:text-yellow-100">
              Unmapped Columns ({{ unmappedColumns.length }})
            </h4>
            <p class="text-sm text-yellow-800 dark:text-yellow-200 mt-1">
              These CSV columns were not mapped and will be ignored:
            </p>
            <div class="flex flex-wrap gap-2 mt-2">
              <span
                v-for="col in unmappedColumns"
                :key="col"
                class="px-2 py-1 bg-yellow-100 dark:bg-yellow-900/40 text-yellow-800 dark:text-yellow-200 rounded text-xs font-mono border border-transparent dark:border-yellow-800"
              >
                {{ col }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <div class="flex justify-between pt-4">
        <button
          class="px-4 py-2 border border-gray-300 dark:border-slate-600 text-gray-700 dark:text-slate-300 rounded-lg hover:bg-gray-50 dark:hover:bg-slate-800 transition-colors"
          @click="$emit('back')"
        >
          Back
        </button>
        <button
          class="px-6 py-2 bg-blue-600 hover:bg-blue-700 dark:bg-blue-600 dark:hover:bg-blue-500 text-white rounded-lg transition-colors shadow-sm"
          @click="proceedToValidation"
        >
          Next: Validate & Import
        </button>
      </div>
    </div>
  </div>
</template>

<script>
// The logic remains identical to your original component
import { ref, computed, onMounted } from 'vue'

export default {
  name: 'SensorMappingComponent',
  props: {
    csvData: {
      type: Object,
      required: true,
    },
    keyColumns: {
      type: Object,
      required: true,
    },
  },
  emits: ['mapping-complete', 'back'],
  setup(props, { emit }) {
    const sensorMapping = ref({})
    const expandedCategories = ref({})

    // Same category definitions as your original code
    const categories = [
      {
        id: 'scada',
        name: 'SCADA Readings',
        icon: '📊',
        description: 'Wind speed, power output, rotor speed, pitch angle',
        fields: [
          {
            name: 'wind_speed_ms',
            label: 'Wind Speed',
            description: 'Wind speed measurement',
            unit: 'm/s',
          },
          {
            name: 'power_kw',
            label: 'Power Output',
            description: 'Active power output',
            unit: 'kW',
          },
          {
            name: 'rotor_speed_rpm',
            label: 'Rotor Speed',
            description: 'Rotor rotational speed',
            unit: 'RPM',
          },
          {
            name: 'generator_speed_rpm',
            label: 'Generator Speed',
            description: 'Generator rotational speed',
            unit: 'RPM',
          },
          {
            name: 'pitch_angle_deg',
            label: 'Pitch Angle',
            description: 'Blade pitch angle',
            unit: 'degrees',
          },
          {
            name: 'yaw_angle_deg',
            label: 'Yaw Angle',
            description: 'Nacelle yaw angle',
            unit: 'degrees',
          },
          {
            name: 'nacelle_direction_deg',
            label: 'Nacelle Direction',
            description: 'Nacelle compass direction',
            unit: 'degrees',
          },
          {
            name: 'ambient_temp_c',
            label: 'Ambient Temperature',
            description: 'Outside air temperature',
            unit: '°C',
          },
          {
            name: 'wind_direction_deg',
            label: 'Wind Direction',
            description: 'Wind compass direction',
            unit: 'degrees',
          },
          {
            name: 'status_code',
            label: 'Status Code',
            description: 'Turbine operational status code',
            unit: 'code',
          },
          {
            name: 'alarm_code',
            label: 'Alarm Code',
            description: 'Active alarm code',
            unit: 'code',
          },
        ],
      },
      {
        id: 'vibration',
        name: 'Vibration Readings',
        icon: '📳',
        description: 'Vibration sensors for bearings, gearbox, generator, tower, blades',
        fields: [
          {
            name: 'main_bearing_vibration_rms_mms',
            label: 'Main Bearing RMS',
            description: 'Main bearing vibration RMS',
            unit: 'mm/s',
          },
          {
            name: 'main_bearing_vibration_peak_mms',
            label: 'Main Bearing Peak',
            description: 'Main bearing vibration peak',
            unit: 'mm/s',
          },
          {
            name: 'gearbox_vibration_axial_mms',
            label: 'Gearbox Axial',
            description: 'Gearbox axial vibration',
            unit: 'mm/s',
          },
          {
            name: 'gearbox_vibration_radial_mms',
            label: 'Gearbox Radial',
            description: 'Gearbox radial vibration',
            unit: 'mm/s',
          },
          {
            name: 'generator_vibration_de_mms',
            label: 'Generator Drive End',
            description: 'Generator DE vibration',
            unit: 'mm/s',
          },
          {
            name: 'generator_vibration_nde_mms',
            label: 'Generator Non-Drive End',
            description: 'Generator NDE vibration',
            unit: 'mm/s',
          },
          {
            name: 'tower_vibration_fa_mms',
            label: 'Tower Fore-Aft',
            description: 'Tower fore-aft vibration',
            unit: 'mm/s',
          },
          {
            name: 'tower_vibration_ss_mms',
            label: 'Tower Side-Side',
            description: 'Tower side-side vibration',
            unit: 'mm/s',
          },
          {
            name: 'blade1_vibration_mms',
            label: 'Blade 1',
            description: 'Blade 1 vibration',
            unit: 'mm/s',
          },
          {
            name: 'blade2_vibration_mms',
            label: 'Blade 2',
            description: 'Blade 2 vibration',
            unit: 'mm/s',
          },
          {
            name: 'blade3_vibration_mms',
            label: 'Blade 3',
            description: 'Blade 3 vibration',
            unit: 'mm/s',
          },
          {
            name: 'acoustic_level_db',
            label: 'Acoustic Level',
            description: 'Noise level measurement',
            unit: 'dB',
          },
        ],
      },
      {
        id: 'temperature',
        name: 'Temperature Readings',
        icon: '🌡️',
        description: 'Temperature sensors for bearings, gearbox, generator',
        fields: [
          {
            name: 'nacelle_temp_c',
            label: 'Nacelle Temperature',
            description: 'Nacelle internal temperature',
            unit: '°C',
          },
          {
            name: 'gearbox_bearing_temp_c',
            label: 'Gearbox Bearing Temp',
            description: 'Gearbox bearing temperature',
            unit: '°C',
          },
          {
            name: 'gearbox_oil_temp_c',
            label: 'Gearbox Oil Temp',
            description: 'Gearbox oil temperature',
            unit: '°C',
          },
          {
            name: 'generator_bearing1_temp_c',
            label: 'Generator Bearing 1 Temp',
            description: 'Generator bearing 1 temperature',
            unit: '°C',
          },
          {
            name: 'generator_bearing2_temp_c',
            label: 'Generator Bearing 2 Temp',
            description: 'Generator bearing 2 temperature',
            unit: '°C',
          },
          {
            name: 'generator_stator_temp_c',
            label: 'Generator Stator Temp',
            description: 'Generator stator temperature',
            unit: '°C',
          },
          {
            name: 'main_bearing_temp_c',
            label: 'Main Bearing Temp',
            description: 'Main bearing temperature',
            unit: '°C',
          },
        ],
      },
      {
        id: 'hydraulic',
        name: 'Hydraulic Readings',
        icon: '⚙️',
        description: 'Hydraulic and oil pressure measurements',
        fields: [
          {
            name: 'gearbox_oil_pressure_bar',
            label: 'Gearbox Oil Pressure',
            description: 'Gearbox lubrication pressure',
            unit: 'bar',
          },
          {
            name: 'hydraulic_pressure_bar',
            label: 'Hydraulic Pressure',
            description: 'Hydraulic system pressure',
            unit: 'bar',
          },
        ],
      },
      {
        id: 'grid',
        name: 'Grid Electrical Readings',
        icon: '⚡',
        description: 'Grid connection electrical parameters',
        fields: [
          {
            name: 'grid_voltage_v',
            label: 'Grid Voltage',
            description: 'Grid connection voltage',
            unit: 'V',
          },
          {
            name: 'grid_current_a',
            label: 'Grid Current',
            description: 'Grid connection current',
            unit: 'A',
          },
          {
            name: 'grid_frequency_hz',
            label: 'Grid Frequency',
            description: 'Grid frequency',
            unit: 'Hz',
          },
          {
            name: 'grid_power_factor',
            label: 'Power Factor',
            description: 'Grid power factor',
            unit: 'ratio',
          },
          {
            name: 'reactive_power_kvar',
            label: 'Reactive Power',
            description: 'Reactive power',
            unit: 'kVAR',
          },
        ],
      },
    ]

    const availableColumns = computed(() => {
      return props.csvData.headers.filter(
        (h) => h !== props.keyColumns.turbineColumn && h !== props.keyColumns.timestampColumn
      )
    })

    const totalDetected = computed(() => {
      return Object.values(sensorMapping.value).filter((v) => v).length
    })

    const unmappedColumns = computed(() => {
      const mappedColumns = Object.values(sensorMapping.value).filter((v) => v)
      return availableColumns.value.filter((col) => !mappedColumns.includes(col))
    })

    const getMappedCount = (categoryId) => {
      const category = categories.find((c) => c.id === categoryId)
      if (!category) return 0
      return category.fields.filter((f) => sensorMapping.value[f.name]).length
    }

    const getSampleData = (columnName) => {
      if (!columnName || !props.csvData.data.length) return []
      return props.csvData.data
        .slice(0, 3)
        .map((row) => row[columnName])
        .filter((v) => v !== null && v !== undefined && v !== '')
    }

    const toggleCategory = (categoryId) => {
      expandedCategories.value[categoryId] = !expandedCategories.value[categoryId]
    }

    const autoDetectMappings = () => {
      categories.forEach((category) => {
        category.fields.forEach((field) => {
          const fieldNameLower = field.name.toLowerCase().replace(/_/g, ' ')
          const fieldWords = fieldNameLower.split(' ')

          const foundColumn = availableColumns.value.find((column) => {
            const columnLower = column.toLowerCase().replace(/_/g, ' ')

            // Exact match
            if (columnLower === fieldNameLower) return true

            // Check if column contains key words from field name
            const keyWords = fieldWords.filter((w) => w.length > 3) // Skip short words
            return keyWords.some((word) => columnLower.includes(word))
          })

          if (foundColumn) {
            sensorMapping.value[field.name] = foundColumn
          }
        })
      })
    }

    const proceedToValidation = () => {
      emit('mapping-complete', {
        sensorMapping: sensorMapping.value,
        mappedCategories: categories.map((c) => ({
          id: c.id,
          name: c.name,
          mappedCount: getMappedCount(c.id),
          totalFields: c.fields.length,
        })),
      })
    }

    onMounted(() => {
      // Expand all categories by default
      categories.forEach((c) => {
        expandedCategories.value[c.id] = true
      })

      autoDetectMappings()
    })

    return {
      sensorMapping,
      expandedCategories,
      categories,
      availableColumns,
      totalDetected,
      unmappedColumns,
      getMappedCount,
      getSampleData,
      toggleCategory,
      proceedToValidation,
    }
  },
}
</script>
