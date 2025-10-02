<template>
  <div class="bg-white rounded-lg shadow p-6">
    <h3 class="font-semibold mb-4 flex items-center gap-2">
      <Activity :size="18" />
      Real-Time Monitoring
    </h3>
    <div class="space-y-3">
      <div class="flex items-center justify-between pb-2 border-b">
        <span class="text-sm text-slate-600">Data Feed Status</span>
        <span class="text-sm font-semibold text-green-600 flex items-center gap-1">
          <Radio :size="14" class="animate-pulse" />
          Live
        </span>
      </div>
      <div class="flex items-center justify-between pb-2 border-b">
        <span class="text-sm text-slate-600">Last Update</span>
        <span class="text-sm font-semibold">2 seconds ago</span>
      </div>
      <div class="flex items-center justify-between pb-2 border-b">
        <span class="text-sm text-slate-600">Fleet Efficiency</span>
        <span class="text-sm font-semibold text-green-600">94.2%</span>
      </div>
      <div class="flex items-center justify-between">
        <span class="text-sm text-slate-600">Avg Wind Speed</span>
        <span class="text-sm font-semibold">12.8 m/s</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Activity, Radio } from 'lucide-vue-next';
</script>
