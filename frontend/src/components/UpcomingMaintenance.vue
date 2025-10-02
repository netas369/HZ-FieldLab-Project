<template>
  <div class="bg-white rounded-lg shadow p-6">
    <h3 class="font-semibold mb-4 flex items-center gap-2">
      <Wrench :size="18" />
      Upcoming Maintenance
    </h3>
    <div class="space-y-3">
      <div
          v-for="(item, i) in maintenanceItems"
          :key="i"
          class="flex items-center justify-between p-3 border rounded hover:bg-slate-50 cursor-pointer"
      >
        <div class="flex-1">
          <div class="font-semibold text-sm">{{ item.turbine }}</div>
          <div class="text-xs text-slate-600">{{ item.task }}</div>
        </div>
        <div class="text-right">
          <div class="text-sm font-semibold">{{ item.date }}</div>
          <div :class="[
            'text-xs px-2 py-1 rounded',
            getPriorityClass(item.priority)
          ]">
            {{ item.priority.toUpperCase() }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Wrench } from 'lucide-vue-next';

const maintenanceItems = [
  { turbine: 'WT-3', task: 'Emergency Gearbox Repair', date: 'Today', priority: 'critical' },
  { turbine: 'WT-7', task: 'Generator Inspection', date: 'Tomorrow', priority: 'high' },
  { turbine: 'WT-5', task: 'Yaw System Calibration', date: 'Oct 5', priority: 'medium' },
  { turbine: 'WT-11', task: 'Blade Bearing Check', date: 'Oct 8', priority: 'medium' },
  { turbine: 'WT-2', task: 'Routine Inspection', date: 'Oct 12', priority: 'low' }
];

const getPriorityClass = (priority) => {
  const classes = {
    critical: 'bg-red-100 text-red-700',
    high: 'bg-orange-100 text-orange-700',
    medium: 'bg-yellow-100 text-yellow-700',
    low: 'bg-green-100 text-green-700'
  };
  return classes[priority] || 'bg-gray-100 text-gray-700';
};
</script>
