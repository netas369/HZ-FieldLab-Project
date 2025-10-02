<template>
  <div class="absolute right-0 mt-2 w-80 bg-white text-slate-800 rounded-lg shadow-xl border z-50">
    <div class="p-4 border-b font-semibold">Notifications ({{ notifications.length }})</div>
    <div class="max-h-96 overflow-y-auto">
      <div
          v-for="(notif, i) in notifications"
          :key="i"
          class="p-3 border-b hover:bg-slate-50 cursor-pointer"
      >
        <div class="flex items-start gap-2">
          <component
              :is="getIcon(notif.type)"
              :class="getIconColor(notif.type)"
              class="flex-shrink-0"
              :size="16"
          />
          <div class="flex-1">
            <div class="text-sm">{{ notif.msg }}</div>
            <div class="text-xs text-slate-500 mt-1">{{ notif.time }}</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { AlertTriangle, AlertCircle, CheckCircle, Bell } from 'lucide-vue-next';

const notifications = [
  { type: 'critical', msg: 'WT-3 Gearbox temperature critical', time: '2 min ago' },
  { type: 'warning', msg: 'Weather alert: High winds forecasted', time: '15 min ago' },
  { type: 'info', msg: 'WT-8 maintenance scheduled tomorrow', time: '1 hour ago' },
  { type: 'success', msg: 'WT-12 returned to operation', time: '2 hours ago' },
  { type: 'info', msg: 'Weekly report generated', time: '3 hours ago' }
];

const getIcon = (type) => {
  const icons = { critical: AlertTriangle, warning: AlertCircle, success: CheckCircle, info: Bell };
  return icons[type] || Bell;
};

const getIconColor = (type) => {
  const colors = { critical: 'text-red-600', warning: 'text-yellow-600', success: 'text-green-600', info: 'text-blue-600' };
  return colors[type] || 'text-blue-600';
};
</script>
