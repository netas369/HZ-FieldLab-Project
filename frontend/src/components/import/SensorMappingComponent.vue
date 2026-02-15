<template>
  <div class="transition-colors duration-200">
    <div class="flex items-center justify-between mb-6">
      <div>
        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Column Mapping</h2>
        <p class="text-sm text-gray-600 dark:text-slate-400 mt-1">Map CSV columns to database fields</p>
      </div>
      <div class="flex items-center gap-4">
        <div class="text-sm text-gray-600 dark:text-slate-400">
          <span class="font-semibold text-gray-900 dark:text-white">{{ totalMapped }}</span> of {{ totalFields }} fields mapped
        </div>
        <button @click="autoDetectMappings"
                class="px-3 py-1.5 text-sm border border-gray-300 dark:border-slate-600 text-gray-700 dark:text-slate-300 rounded-lg hover:bg-gray-50 dark:hover:bg-slate-800 transition-colors">
          Re-detect
        </button>
      </div>
    </div>

    <div class="space-y-4">
      <!-- Auto-detection summary -->
      <div v-if="autoDetectionRan" class="flex items-center gap-3 px-4 py-3 rounded-lg border transition-colors"
           :class="totalMapped > 0
             ? 'bg-emerald-50 dark:bg-emerald-900/20 border-emerald-200 dark:border-emerald-800'
             : 'bg-amber-50 dark:bg-amber-900/20 border-amber-200 dark:border-amber-800'">
        <div class="flex-shrink-0">
          <svg v-if="totalMapped > 0" class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <svg v-else class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
        </div>
        <div class="flex-1 text-sm">
          <span v-if="totalMapped > 0" class="text-emerald-800 dark:text-emerald-200">
            Auto-detected {{ totalMapped }} column mappings based on naming patterns
          </span>
          <span v-else class="text-amber-800 dark:text-amber-200">
            No automatic mappings found. Please map columns manually.
          </span>
        </div>
      </div>

      <!-- Quick stats bar -->
      <div class="grid grid-cols-5 gap-2">
        <button v-for="category in categories" :key="category.id"
                @click="scrollToCategory(category.id)"
                class="px-3 py-2 rounded-lg border text-left transition-all hover:shadow-sm"
                :class="getMappedCount(category.id) > 0
                  ? 'border-emerald-300 dark:border-emerald-700 bg-emerald-50 dark:bg-emerald-900/30'
                  : 'border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-800/50'">
          <div class="flex items-center justify-between">
            <span class="text-xs font-medium truncate"
                  :class="getMappedCount(category.id) > 0 ? 'text-emerald-700 dark:text-emerald-300' : 'text-gray-600 dark:text-slate-400'">
              {{ category.shortName }}
            </span>
            <span class="text-xs font-mono ml-1"
                  :class="getMappedCount(category.id) > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400 dark:text-slate-500'">
              {{ getMappedCount(category.id) }}/{{ category.fields.length }}
            </span>
          </div>
        </button>
      </div>

      <!-- Category sections -->
      <div v-for="category in categories" :key="category.id"
           :id="`category-${category.id}`"
           class="border border-gray-200 dark:border-slate-700 rounded-lg overflow-hidden">
        <button @click="toggleCategory(category.id)"
                class="w-full px-4 py-3 flex items-center justify-between transition-colors"
                :class="expandedCategories[category.id]
                  ? 'bg-gray-50 dark:bg-slate-800 border-b border-gray-200 dark:border-slate-700'
                  : 'bg-white dark:bg-slate-900 hover:bg-gray-50 dark:hover:bg-slate-800'">
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded flex items-center justify-center"
                 :class="getMappedCount(category.id) > 0
                   ? 'bg-emerald-100 dark:bg-emerald-900/40'
                   : 'bg-gray-100 dark:bg-slate-700'">
              <component :is="category.icon"
                         class="w-4 h-4"
                         :class="getMappedCount(category.id) > 0
                           ? 'text-emerald-600 dark:text-emerald-400'
                           : 'text-gray-500 dark:text-slate-400'" />
            </div>
            <div class="text-left">
              <h3 class="font-medium text-gray-900 dark:text-white">{{ category.name }}</h3>
              <p class="text-xs text-gray-500 dark:text-slate-500">{{ category.description }}</p>
            </div>
          </div>
          <div class="flex items-center gap-3">
            <span class="text-sm font-medium px-2.5 py-0.5 rounded"
                  :class="getMappedCount(category.id) > 0
                    ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300'
                    : 'bg-gray-100 text-gray-500 dark:bg-slate-700 dark:text-slate-400'">
              {{ getMappedCount(category.id) }} / {{ category.fields.length }}
            </span>
            <svg class="w-5 h-5 text-gray-400 dark:text-slate-500 transition-transform duration-200"
                 :class="{ 'rotate-180': expandedCategories[category.id] }"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </div>
        </button>

        <div v-show="expandedCategories[category.id]" class="bg-white dark:bg-slate-900">
          <table class="w-full">
            <thead class="bg-gray-50 dark:bg-slate-800/50">
            <tr>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider w-1/4">
                Database Field
              </th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider w-1/3">
                CSV Column
              </th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">
                Preview
              </th>
            </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-slate-800">
            <tr v-for="field in category.fields" :key="field.name"
                class="hover:bg-gray-50 dark:hover:bg-slate-800/50 transition-colors">
              <td class="px-4 py-3">
                <div>
                  <code class="text-sm font-mono text-gray-700 dark:text-slate-300">{{ field.name }}</code>
                  <p class="text-xs text-gray-400 dark:text-slate-500 mt-0.5">{{ field.unit }}</p>
                </div>
              </td>
              <td class="px-4 py-3">
                <select v-model="sensorMapping[field.name]"
                        class="w-full px-3 py-1.5 text-sm border rounded-md transition-colors
                                 focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                                 dark:focus:ring-blue-400 dark:focus:border-blue-400"
                        :class="sensorMapping[field.name]
                            ? 'border-emerald-300 bg-emerald-50 text-gray-900 dark:border-emerald-700 dark:bg-emerald-900/20 dark:text-white'
                            : 'border-gray-300 bg-white text-gray-900 dark:border-slate-600 dark:bg-slate-800 dark:text-white'">
                  <option value="">— Not mapped —</option>
                  <option v-for="header in availableColumns" :key="header" :value="header">
                    {{ header }}
                  </option>
                </select>
              </td>
              <td class="px-4 py-3">
                <div v-if="sensorMapping[field.name]" class="flex items-center gap-2">
                  <div class="flex-1 min-w-0">
                    <div class="flex gap-1.5">
                        <span v-for="(sample, idx) in getSampleData(sensorMapping[field.name])" :key="idx"
                              class="inline-block px-2 py-0.5 bg-gray-100 dark:bg-slate-700 rounded text-xs font-mono text-gray-600 dark:text-slate-300 truncate max-w-[80px]">
                          {{ formatSample(sample) }}
                        </span>
                    </div>
                  </div>
                  <button @click="sensorMapping[field.name] = ''"
                          class="flex-shrink-0 p-1 text-gray-400 hover:text-red-500 dark:text-slate-500 dark:hover:text-red-400 transition-colors"
                          title="Clear mapping">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                </div>
                <span v-else class="text-xs text-gray-400 dark:text-slate-600">—</span>
              </td>
            </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Unmapped columns notice -->
      <div v-if="unmappedColumns.length > 0"
           class="px-4 py-3 bg-gray-50 dark:bg-slate-800/50 border border-gray-200 dark:border-slate-700 rounded-lg">
        <div class="flex items-start gap-3">
          <svg class="w-5 h-5 text-gray-400 dark:text-slate-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <div class="flex-1 min-w-0">
            <p class="text-sm text-gray-600 dark:text-slate-400">
              <span class="font-medium">{{ unmappedColumns.length }} CSV columns</span> will be ignored:
            </p>
            <div class="flex flex-wrap gap-1.5 mt-2">
              <code v-for="col in unmappedColumns.slice(0, 15)" :key="col"
                    class="px-1.5 py-0.5 bg-gray-200 dark:bg-slate-700 rounded text-xs text-gray-600 dark:text-slate-400">
                {{ col }}
              </code>
              <span v-if="unmappedColumns.length > 15"
                    class="px-1.5 py-0.5 text-xs text-gray-500 dark:text-slate-500">
                +{{ unmappedColumns.length - 15 }} more
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Navigation -->
      <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-slate-700">
        <button @click="$emit('back')"
                class="px-4 py-2 text-sm font-medium border border-gray-300 dark:border-slate-600 text-gray-700 dark:text-slate-300 rounded-lg hover:bg-gray-50 dark:hover:bg-slate-800 transition-colors">
          Back
        </button>
        <div class="flex items-center gap-3">
          <span v-if="totalMapped === 0" class="text-sm text-amber-600 dark:text-amber-400">
            Map at least one column to continue
          </span>
          <button @click="proceedToValidation"
                  :disabled="totalMapped === 0"
                  class="px-5 py-2 text-sm font-medium bg-blue-600 hover:bg-blue-700 disabled:bg-gray-300 dark:disabled:bg-slate-700 text-white disabled:text-gray-500 dark:disabled:text-slate-500 rounded-lg transition-colors disabled:cursor-not-allowed">
            Continue
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted, h } from 'vue';

// Simple icon components
const IconChart = {
  render() {
    return h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
      h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z' })
    ])
  }
};

const IconVibration = {
  render() {
    return h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
      h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M13 10V3L4 14h7v7l9-11h-7z' })
    ])
  }
};

const IconTemperature = {
  render() {
    return h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
      h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5' })
    ])
  }
};

const IconGear = {
  render() {
    return h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
      h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z' }),
      h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M15 12a3 3 0 11-6 0 3 3 0 016 0z' })
    ])
  }
};

const IconBolt = {
  render() {
    return h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
      h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M13 10V3L4 14h7v7l9-11h-7z' })
    ])
  }
};

export default {
  name: 'SensorMappingComponent',
  props: {
    csvData: {
      type: Object,
      required: true
    },
    keyColumns: {
      type: Object,
      required: true
    }
  },
  emits: ['mapping-complete', 'back'],
  setup(props, { emit }) {
    const sensorMapping = ref({});
    const expandedCategories = ref({});
    const autoDetectionRan = ref(false);

    const categories = [
      {
        id: 'scada',
        name: 'SCADA',
        shortName: 'SCADA',
        icon: IconChart,
        description: 'Operational parameters: wind, power, rotor, pitch',
        fields: [
          { name: 'wind_speed_ms', label: 'Wind Speed', unit: 'm/s', aliases: ['wind', 'windspeed', 'ws', 'wind_speed', 'wspd'] },
          { name: 'power_kw', label: 'Power Output', unit: 'kW', aliases: ['power', 'pwr', 'active_power', 'activepower', 'p_avg', 'power_output'] },
          { name: 'rotor_speed_rpm', label: 'Rotor Speed', unit: 'RPM', aliases: ['rotor', 'rotorspeed', 'rotor_rpm', 'rtr_spd'] },
          { name: 'generator_speed_rpm', label: 'Generator Speed', unit: 'RPM', aliases: ['generator', 'genspeed', 'gen_speed', 'gen_rpm'] },
          { name: 'pitch_angle_deg', label: 'Pitch Angle', unit: '°', aliases: ['pitch', 'blade_pitch', 'pitchangle', 'pitch_avg'] },
          { name: 'yaw_angle_deg', label: 'Yaw Angle', unit: '°', aliases: ['yaw', 'yawangle', 'nacelle_yaw'] },
          { name: 'nacelle_direction_deg', label: 'Nacelle Direction', unit: '°', aliases: ['nacelle_dir', 'nacelle_direction', 'nac_dir'] },
          { name: 'ambient_temp_c', label: 'Ambient Temp', unit: '°C', aliases: ['ambient', 'amb_temp', 'outside_temp', 'ext_temp', 'ambient_temperature'] },
          { name: 'wind_direction_deg', label: 'Wind Direction', unit: '°', aliases: ['wind_dir', 'winddirection', 'wdir'] },
          { name: 'status_code', label: 'Status Code', unit: 'code', aliases: ['status', 'state', 'turbine_status', 'op_state'] },
          { name: 'alarm_code', label: 'Alarm Code', unit: 'code', aliases: ['alarm', 'fault', 'error_code', 'fault_code'] }
        ]
      },
      {
        id: 'vibration',
        name: 'Vibration',
        shortName: 'Vibration',
        icon: IconVibration,
        description: 'Vibration sensors: bearings, gearbox, generator, tower',
        fields: [
          { name: 'main_bearing_vibration_rms_mms', label: 'Main Bearing RMS', unit: 'mm/s', aliases: ['main_bearing_vib', 'mb_vib_rms', 'main_brg_vib'] },
          { name: 'main_bearing_vibration_peak_mms', label: 'Main Bearing Peak', unit: 'mm/s', aliases: ['mb_vib_peak', 'main_bearing_peak'] },
          { name: 'gearbox_vibration_axial_mms', label: 'Gearbox Axial', unit: 'mm/s', aliases: ['gb_vib_axial', 'gearbox_axial', 'gbx_vib_ax'] },
          { name: 'gearbox_vibration_radial_mms', label: 'Gearbox Radial', unit: 'mm/s', aliases: ['gb_vib_radial', 'gearbox_radial', 'gbx_vib_rad'] },
          { name: 'generator_vibration_de_mms', label: 'Generator DE', unit: 'mm/s', aliases: ['gen_vib_de', 'generator_de', 'gen_de_vib'] },
          { name: 'generator_vibration_nde_mms', label: 'Generator NDE', unit: 'mm/s', aliases: ['gen_vib_nde', 'generator_nde', 'gen_nde_vib'] },
          { name: 'tower_vibration_fa_mms', label: 'Tower Fore-Aft', unit: 'mm/s', aliases: ['tower_fa', 'twr_vib_fa', 'tower_fore_aft'] },
          { name: 'tower_vibration_ss_mms', label: 'Tower Side-Side', unit: 'mm/s', aliases: ['tower_ss', 'twr_vib_ss', 'tower_side'] },
          { name: 'blade1_vibration_mms', label: 'Blade 1', unit: 'mm/s', aliases: ['blade1_vib', 'b1_vib', 'blade_1'] },
          { name: 'blade2_vibration_mms', label: 'Blade 2', unit: 'mm/s', aliases: ['blade2_vib', 'b2_vib', 'blade_2'] },
          { name: 'blade3_vibration_mms', label: 'Blade 3', unit: 'mm/s', aliases: ['blade3_vib', 'b3_vib', 'blade_3'] },
          { name: 'acoustic_level_db', label: 'Acoustic Level', unit: 'dB', aliases: ['noise', 'acoustic', 'sound_level', 'noise_level'] }
        ]
      },
      {
        id: 'temperature',
        name: 'Temperature',
        shortName: 'Temp',
        icon: IconTemperature,
        description: 'Temperature sensors: bearings, gearbox oil, generator',
        fields: [
          { name: 'nacelle_temp_c', label: 'Nacelle Temp', unit: '°C', aliases: ['nacelle_temp', 'nac_temp', 'nacelle_temperature'] },
          { name: 'gearbox_bearing_temp_c', label: 'Gearbox Bearing', unit: '°C', aliases: ['gb_bearing_temp', 'gearbox_brg_temp', 'gbx_brg_t'] },
          { name: 'gearbox_oil_temp_c', label: 'Gearbox Oil', unit: '°C', aliases: ['gb_oil_temp', 'gearbox_oil', 'gbx_oil_t', 'gear_oil_temp'] },
          { name: 'generator_bearing1_temp_c', label: 'Gen Bearing 1', unit: '°C', aliases: ['gen_brg1_temp', 'gen_bearing1', 'gen_b1_temp'] },
          { name: 'generator_bearing2_temp_c', label: 'Gen Bearing 2', unit: '°C', aliases: ['gen_brg2_temp', 'gen_bearing2', 'gen_b2_temp'] },
          { name: 'generator_stator_temp_c', label: 'Gen Stator', unit: '°C', aliases: ['gen_stator_temp', 'stator_temp', 'gen_stator'] },
          { name: 'main_bearing_temp_c', label: 'Main Bearing', unit: '°C', aliases: ['main_brg_temp', 'mb_temp', 'main_bearing_temperature'] }
        ]
      },
      {
        id: 'hydraulic',
        name: 'Hydraulic',
        shortName: 'Hydraulic',
        icon: IconGear,
        description: 'Hydraulic system and oil pressure',
        fields: [
          { name: 'gearbox_oil_pressure_bar', label: 'Gearbox Oil Pressure', unit: 'bar', aliases: ['gb_oil_press', 'gearbox_pressure', 'gbx_oil_p'] },
          { name: 'hydraulic_pressure_bar', label: 'Hydraulic Pressure', unit: 'bar', aliases: ['hyd_pressure', 'hydraulic_press', 'hyd_press'] }
        ]
      },
      {
        id: 'grid',
        name: 'Grid / Electrical',
        shortName: 'Grid',
        icon: IconBolt,
        description: 'Grid connection and electrical parameters',
        fields: [
          { name: 'grid_voltage_v', label: 'Grid Voltage', unit: 'V', aliases: ['voltage', 'grid_v', 'line_voltage', 'u_grid'] },
          { name: 'grid_current_a', label: 'Grid Current', unit: 'A', aliases: ['current', 'grid_i', 'line_current', 'i_grid'] },
          { name: 'grid_frequency_hz', label: 'Grid Frequency', unit: 'Hz', aliases: ['frequency', 'freq', 'grid_freq', 'f_grid'] },
          { name: 'grid_power_factor', label: 'Power Factor', unit: 'ratio', aliases: ['pf', 'power_factor', 'cos_phi', 'cosphi'] },
          { name: 'reactive_power_kvar', label: 'Reactive Power', unit: 'kVAR', aliases: ['reactive', 'q_power', 'kvar', 'reactive_pwr'] }
        ]
      }
    ];

    const totalFields = computed(() => {
      return categories.reduce((sum, cat) => sum + cat.fields.length, 0);
    });

    const availableColumns = computed(() => {
      return props.csvData.headers.filter(h =>
          h !== props.keyColumns.turbineColumn &&
          h !== props.keyColumns.timestampColumn
      );
    });

    const totalMapped = computed(() => {
      return Object.values(sensorMapping.value).filter(v => v).length;
    });

    const unmappedColumns = computed(() => {
      const mappedColumns = Object.values(sensorMapping.value).filter(v => v);
      return availableColumns.value.filter(col => !mappedColumns.includes(col));
    });

    const getMappedCount = (categoryId) => {
      const category = categories.find(c => c.id === categoryId);
      if (!category) return 0;
      return category.fields.filter(f => sensorMapping.value[f.name]).length;
    };

    const getSampleData = (columnName) => {
      if (!columnName || !props.csvData.data.length) return [];
      return props.csvData.data
          .slice(0, 3)
          .map(row => row[columnName])
          .filter(v => v !== null && v !== undefined && v !== '');
    };

    const formatSample = (value) => {
      if (typeof value === 'number') {
        return value.toFixed(2);
      }
      return String(value).substring(0, 10);
    };

    const toggleCategory = (categoryId) => {
      expandedCategories.value[categoryId] = !expandedCategories.value[categoryId];
    };

    const scrollToCategory = (categoryId) => {
      expandedCategories.value[categoryId] = true;
      const element = document.getElementById(`category-${categoryId}`);
      if (element) {
        element.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    };

    // Smart auto-detection with multiple strategies
    const autoDetectMappings = () => {
      // Reset mappings
      sensorMapping.value = {};

      const normalizeString = (str) => {
        return str.toLowerCase()
            .replace(/[_\-\s]+/g, '')
            .replace(/temperature/g, 'temp')
            .replace(/vibration/g, 'vib')
            .replace(/pressure/g, 'press')
            .replace(/bearing/g, 'brg')
            .replace(/gearbox/g, 'gb')
            .replace(/generator/g, 'gen');
      };

      const calculateSimilarity = (str1, str2) => {
        const s1 = normalizeString(str1);
        const s2 = normalizeString(str2);

        // Exact match
        if (s1 === s2) return 1;

        // Contains
        if (s1.includes(s2) || s2.includes(s1)) return 0.8;

        // Word overlap
        const words1 = s1.match(/[a-z]+/g) || [];
        const words2 = s2.match(/[a-z]+/g) || [];
        const commonWords = words1.filter(w => words2.some(w2 => w2.includes(w) || w.includes(w2)));
        if (commonWords.length > 0) {
          return 0.5 + (commonWords.length / Math.max(words1.length, words2.length)) * 0.3;
        }

        return 0;
      };

      const usedColumns = new Set();

      categories.forEach(category => {
        category.fields.forEach(field => {
          if (sensorMapping.value[field.name]) return; // Already mapped

          let bestMatch = null;
          let bestScore = 0;

          availableColumns.value.forEach(column => {
            if (usedColumns.has(column)) return;

            let score = 0;

            // Strategy 1: Check aliases (highest priority)
            if (field.aliases) {
              for (const alias of field.aliases) {
                const normalizedCol = normalizeString(column);
                const normalizedAlias = normalizeString(alias);

                if (normalizedCol === normalizedAlias) {
                  score = Math.max(score, 0.95);
                } else if (normalizedCol.includes(normalizedAlias) || normalizedAlias.includes(normalizedCol)) {
                  score = Math.max(score, 0.85);
                }
              }
            }

            // Strategy 2: Direct field name comparison
            const fieldNameScore = calculateSimilarity(field.name, column);
            score = Math.max(score, fieldNameScore);

            // Strategy 3: Label comparison
            const labelScore = calculateSimilarity(field.label, column);
            score = Math.max(score, labelScore * 0.9);

            if (score > bestScore && score >= 0.5) {
              bestScore = score;
              bestMatch = column;
            }
          });

          if (bestMatch) {
            sensorMapping.value[field.name] = bestMatch;
            usedColumns.add(bestMatch);
          }
        });
      });

      autoDetectionRan.value = true;
    };

    const proceedToValidation = () => {
      emit('mapping-complete', {
        sensorMapping: sensorMapping.value,
        mappedCategories: categories.map(c => ({
          id: c.id,
          name: c.name,
          mappedCount: getMappedCount(c.id),
          totalFields: c.fields.length
        }))
      });
    };

    onMounted(() => {
      // Collapse all by default, expand first category with mappings after detection
      categories.forEach(c => {
        expandedCategories.value[c.id] = false;
      });

      autoDetectMappings();

      // Expand categories that have mappings
      categories.forEach(c => {
        if (getMappedCount(c.id) > 0) {
          expandedCategories.value[c.id] = true;
        }
      });

      // If nothing mapped, expand first category
      if (totalMapped.value === 0) {
        expandedCategories.value[categories[0].id] = true;
      }
    });

    return {
      sensorMapping,
      expandedCategories,
      categories,
      availableColumns,
      totalMapped,
      totalFields,
      unmappedColumns,
      autoDetectionRan,
      getMappedCount,
      getSampleData,
      formatSample,
      toggleCategory,
      scrollToCategory,
      autoDetectMappings,
      proceedToValidation
    };
  }
};
</script>