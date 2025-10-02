<template>
  <div class="p-6 bg-slate-100 min-h-screen">
    <div class="mb-4">
      <button
          @click="$router.push('/')"
          class="text-blue-600 hover:underline flex items-center gap-1"
      >
        <ChevronLeft :size="20" />
        Back to Dashboard
      </button>
    </div>

    <div class="bg-white rounded-lg shadow p-6 mb-6">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-2xl font-bold">Wind Turbine WT-{{ turbineId }}</h2>
        <div class="flex items-center gap-2">
          <div :class="['w-4 h-4 rounded-full', getStatusColor()]" />
          <span :class="['font-semibold', getStatusTextColor()]">{{ getStatus() }}</span>
        </div>
      </div>
      <div class="grid grid-cols-5 gap-4">
        <div class="border rounded p-3">
          <div class="text-xs text-slate-600">Status</div>
          <div class="font-semibold">{{ getOperationalStatus() }}</div>
        </div>
        <div class="border rounded p-3">
          <div class="text-xs text-slate-600">Power Output</div>
          <div class="font-semibold">{{ getPowerOutput() }} MW</div>
        </div>
        <div class="border rounded p-3">
          <div class="text-xs text-slate-600">Wind Speed</div>
          <div class="font-semibold">12.4 m/s</div>
        </div>
        <div class="border rounded p-3">
          <div class="text-xs text-slate-600">Availability</div>
          <div class="font-semibold">{{ getAvailability() }}%</div>
        </div>
        <div class="border rounded p-3">
          <div class="text-xs text-slate-600">Last Maintenance</div>
          <div class="font-semibold">12 days ago</div>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-3 gap-6">
      <div class="col-span-2 space-y-6">
        <!-- System Alarms -->
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="font-semibold mb-4">System Alarms</h3>
          <div class="space-y-3">
            <div v-for="alarm in getAlarms()" :key="alarm.id" :class="[
              'border-l-4 p-4 rounded',
              alarm.severity === 'critical' ? 'border-red-600 bg-red-50' : 'border-yellow-600 bg-yellow-50'
            ]">
              <div class="flex items-center justify-between mb-2">
                <span :class="[
                  'font-semibold',
                  alarm.severity === 'critical' ? 'text-red-600' : 'text-yellow-600'
                ]">
                  {{ alarm.severity.toUpperCase() }}: {{ alarm.name }}
                </span>
                <span class="text-sm text-slate-600">{{ alarm.time }}</span>
              </div>
              <div class="text-sm mb-2">{{ alarm.description }}</div>
              <div class="text-sm text-slate-600 mb-3">Sensor: {{ alarm.sensor }}</div>
              <div class="bg-white p-3 rounded border">
                <div class="font-semibold text-sm mb-1">Recommended Action:</div>
                <div class="text-sm">{{ alarm.action }}</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Component Status -->
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="font-semibold mb-4">Component Status</h3>
          <div class="space-y-4">
            <div v-for="comp in components" :key="comp.name" class="border rounded p-3">
              <div class="flex items-center justify-between mb-2">
                <span class="font-semibold">{{ comp.name }}</span>
                <span :class="[
                  'text-sm px-2 py-1 rounded',
                  comp.status === 'Critical' ? 'bg-red-100 text-red-700' : 
                  comp.status === 'Warning' ? 'bg-yellow-100 text-yellow-700' : 
                  'bg-green-100 text-green-700'
                ]">
                  {{ comp.status }}
                </span>
              </div>
              <div class="flex items-center gap-4 text-sm">
                <span class="text-slate-600">Temp: {{ comp.temp }}</span>
                <div class="flex-1">
                  <div class="flex items-center gap-2">
                    <div class="flex-1 bg-slate-200 rounded-full h-2">
                      <div :class="[
                        'h-2 rounded-full',
                        comp.health > 80 ? 'bg-green-500' : 
                        comp.health > 60 ? 'bg-yellow-500' : 
                        'bg-red-500'
                      ]"
                           :style="{ width: comp.health + '%' }"
                      />
                    </div>
                    <span class="text-slate-600">{{ comp.health }}%</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Sidebar -->
      <div class="space-y-6">
        <!-- Performance Metrics -->
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="font-semibold mb-4 flex items-center gap-2">
            <TrendingUp :size="18" />
            Performance Metrics
          </h3>
          <div class="space-y-3">
            <div class="border-b pb-2">
              <div class="text-sm text-slate-600">Blade Pitch Accuracy</div>
              <div class="font-semibold">±0.8° (Normal)</div>
            </div>
            <div class="border-b pb-2">
              <div class="text-sm text-slate-600">Yaw Alignment</div>
              <div class="font-semibold">±3.2° (Good)</div>
            </div>
            <div class="border-b pb-2">
              <div class="text-sm text-slate-600">Rotor Speed</div>
              <div :class="['font-semibold', turbineId === '3' ? 'text-red-600' : '']">
                {{ turbineId === '3' ? '0 RPM (Stopped)' : '18.2 RPM' }}
              </div>
            </div>
            <div class="border-b pb-2">
              <div class="text-sm text-slate-600">Generator Torque</div>
              <div class="font-semibold">{{ turbineId === '3' ? '0 kNm' : '425 kNm' }}</div>
            </div>
            <div>
              <div class="text-sm text-slate-600">Efficiency Rating</div>
              <div class="font-semibold">{{ turbineId === '3' ? 'N/A (Stopped)' : '94.2%' }}</div>
            </div>
          </div>
        </div>

        <!-- Recent Maintenance -->
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="font-semibold mb-4 flex items-center gap-2">
            <Calendar :size="18" />
            Recent Maintenance
          </h3>
          <div class="space-y-3 text-sm">
            <div class="border-l-2 border-blue-500 pl-3">
              <div class="font-semibold">Routine Inspection</div>
              <div class="text-slate-600">12 days ago</div>
              <div class="text-slate-600">Tech: J. van der Berg</div>
            </div>
            <div class="border-l-2 border-blue-500 pl-3">
              <div class="font-semibold">Blade Cleaning</div>
              <div class="text-slate-600">28 days ago</div>
              <div class="text-slate-600">Tech: M. Jansen</div>
            </div>
            <div class="border-l-2 border-blue-500 pl-3">
              <div class="font-semibold">Oil Change</div>
              <div class="text-slate-600">45 days ago</div>
              <div class="text-slate-600">Tech: P. de Vries</div>
            </div>
          </div>
        </div>

        <!-- Environmental Data -->
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="font-semibold mb-4 flex items-center gap-2">
            <Cloud :size="18" />
            Environmental Data
          </h3>
          <div class="space-y-3">
            <div class="flex justify-between items-center pb-2 border-b">
              <span class="text-sm text-slate-600">Wind Direction</span>
              <span class="font-semibold">NW (310°)</span>
            </div>
            <div class="flex justify-between items-center pb-2 border-b">
              <span class="text-sm text-slate-600">Temperature</span>
              <span class="font-semibold">14°C</span>
            </div>
            <div class="flex justify-between items-center pb-2 border-b">
              <span class="text-sm text-slate-600">Humidity</span>
              <span class="font-semibold">72%</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-sm text-slate-600">Air Pressure</span>
              <span class="font-semibold">1013 hPa</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import { Wind, ChevronLeft, TrendingUp, Calendar, Cloud } from 'lucide-vue-next';

const route = useRoute();
const turbineId = computed(() => route.params.id);

const getStatusColor = () => {
  if (turbineId.value === '3' || turbineId.value === '7') return 'bg-red-500 animate-pulse';
  if (turbineId.value === '5' || turbineId.value === '11') return 'bg-yellow-500';
  return 'bg-green-500';
};

const getStatusTextColor = () => {
  if (turbineId.value === '3' || turbineId.value === '7') return 'text-red-600';
  if (turbineId.value === '5' || turbineId.value === '11') return 'text-yellow-600';
  return 'text-green-600';
};

const getStatus = () => {
  if (turbineId.value === '3' || turbineId.value === '7') return 'CRITICAL';
  if (turbineId.value === '5' || turbineId.value === '11') return 'WARNING';
  return 'NORMAL';
};

const getOperationalStatus = () => {
  if (turbineId.value === '3') return 'Stopped';
  if (turbineId.value === '5') return 'Maintenance';
  return 'Running';
};

const getPowerOutput = () => {
  if (turbineId.value === '3') return '0.0';
  if (turbineId.value === '5') return '2.1';
  return '2.5';
};

const getAvailability = () => {
  if (turbineId.value === '3') return '89.3';
  if (turbineId.value === '5') return '95.8';
  return '98.2';
};

const getAlarms = () => {
  if (turbineId.value === '3') {
    return [
      {
        id: 1,
        severity: 'critical',
        name: 'GEARBOX_OVERHEAT',
        time: '2h 15m ago',
        description: 'Oil temperature: 78°C (Threshold: 75°C fault)',
        sensor: 'PT100 in oil sump',
        action: 'Immediate shutdown required. Inspect cooling system and oil quality. Schedule emergency maintenance within 24 hours.'
      },
      {
        id: 2,
        severity: 'warning',
        name: 'GEARBOX_VIBRATION_HIGH',
        time: '2h 30m ago',
        description: 'Vibration: 5.2 mm/s RMS (Threshold: 4.5 mm/s warning)',
        sensor: 'Triaxial accelerometer - output shaft',
        action: 'Monitor closely. Schedule inspection within 48 hours.'
      }
    ];
  } else if (turbineId.value === '7') {
    return [
      {
        id: 1,
        severity: 'critical',
        name: 'GENERATOR_OVERHEAT',
        time: '45m ago',
        description: 'Generator winding temperature: 96°C (Threshold: 95°C fault)',
        sensor: 'PT100 temperature sensor',
        action: 'Reduce power output immediately. Inspect cooling system and ventilation.'
      }
    ];
  } else if (turbineId.value === '5') {
    return [
      {
        id: 1,
        severity: 'warning',
        name: 'YAW_MISALIGNMENT',
        time: '3h ago',
        description: 'Yaw deviation: 18° (Threshold: 15° warning)',
        sensor: 'Position encoder',
        action: 'Schedule yaw calibration within next maintenance window.'
      }
    ];
  }
  return [];
};

const components = computed(() => {
  if (turbineId.value === '3') {
    return [
      { name: 'Gearbox', status: 'Critical', temp: '78°C', health: 45 },
      { name: 'Generator', status: 'Normal', temp: '68°C', health: 92 },
      { name: 'Blades', status: 'Normal', temp: 'N/A', health: 88 },
      { name: 'Yaw System', status: 'Normal', temp: '42°C', health: 95 },
      { name: 'Main Bearing', status: 'Normal', temp: '52°C', health: 90 }
    ];
  } else if (turbineId.value === '7') {
    return [
      { name: 'Gearbox', status: 'Normal', temp: '62°C', health: 88 },
      { name: 'Generator', status: 'Critical', temp: '96°C', health: 52 },
      { name: 'Blades', status: 'Normal', temp: 'N/A', health: 90 },
      { name: 'Yaw System', status: 'Normal', temp: '38°C', health: 94 },
      { name: 'Main Bearing', status: 'Normal', temp: '48°C', health: 92 }
    ];
  } else if (turbineId.value === '5') {
    return [
      { name: 'Gearbox', status: 'Normal', temp: '58°C', health: 85 },
      { name: 'Generator', status: 'Normal', temp: '72°C', health: 88 },
      { name: 'Blades', status: 'Normal', temp: 'N/A', health: 86 },
      { name: 'Yaw System', status: 'Warning', temp: '45°C', health: 75 },
      { name: 'Main Bearing', status: 'Normal', temp: '50°C', health: 89 }
    ];
  }
  return [
    { name: 'Gearbox', status: 'Normal', temp: '55°C', health: 94 },
    { name: 'Generator', status: 'Normal', temp: '70°C', health: 96 },
    { name: 'Blades', status: 'Normal', temp: 'N/A', health: 92 },
    { name: 'Yaw System', status: 'Normal', temp: '40°C', health: 97 },
    { name: 'Main Bearing', status: 'Normal', temp: '47°C', health: 95 }
  ];
});
</script>