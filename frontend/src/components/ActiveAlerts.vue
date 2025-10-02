<template>
  <div class="bg-white rounded-lg shadow p-6">
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-lg font-semibold flex items-center gap-2">
        <AlertTriangle :size="20" class="text-red-600" />
        Active Alerts
      </h2>
      <select
          v-model="selectedAlarmFilter"
          class="text-sm border rounded px-2 py-1"
      >
        <option value="all">All</option>
        <option value="critical">Critical</option>
        <option value="major">Major</option>
        <option value="minor">Minor</option>
      </select>
    </div>
    <div class="space-y-2 max-h-96 overflow-y-auto">
      <div
          v-for="(alert, i) in alerts"
          :key="i"
          :class="[
          'border-l-4 p-3 rounded',
          alert.type === 'critical' ? 'border-red-600 bg-red-50' : 'border-yellow-600 bg-yellow-50'
        ]"
      >
        <div class="flex items-center justify-between mb-1">
          <div :class="[
            'font-semibold text-sm',
            alert.type === 'critical' ? 'text-red-700' : 'text-yellow-700'
          ]">
            {{ alert.type.toUpperCase() }}
          </div>
          <button class="text-xs text-blue-600 hover:underline">Acknowledge</button>
        </div>
        <div class="text-sm font-semibold">{{ alert.turbine }}: {{ alert.alarm }}</div>
        <div class="text-xs text-slate-600 mt-1">{{ alert.time }} - {{ alert.detail }}</div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { inject } from 'vue';
import { AlertTriangle } from 'lucide-vue-next';

const selectedAlarmFilter = inject('selectedAlarmFilter');

const alerts = [
  { type: 'critical', turbine: 'WT-3', alarm: 'GEARBOX_OVERHEAT', time: '2 hours ago', detail: 'Temp: 78°C' },
  { type: 'critical', turbine: 'WT-7', alarm: 'GENERATOR_OVERHEAT', time: '45 min ago', detail: 'Temp: 96°C' },
  { type: 'major', turbine: 'WT-5', alarm: 'YAW_MISALIGNMENT', time: '3 hours ago', detail: 'Deviation: 18°' },
  { type: 'major', turbine: 'WT-11', alarm: 'BLADE_IMBALANCE', time: '5 hours ago', detail: 'Vibration: 2.4mm' }
];
</script>