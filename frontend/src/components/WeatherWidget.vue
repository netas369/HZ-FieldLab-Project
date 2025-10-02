<template>
  <div class="bg-gradient-to-br from-blue-400 to-blue-600 text-white p-4 rounded-lg shadow">
    <div class="flex items-center justify-between mb-3">
      <h3 class="font-semibold flex items-center gap-2">
        <Cloud :size="20" />
        Weather Conditions
      </h3>
      <span class="text-xs">Vlissingen, NL</span>
    </div>
    <div class="grid grid-cols-4 gap-3">
      <div>
        <div class="text-xs opacity-80">Wind Speed</div>
        <div class="text-xl font-bold">12.4 m/s</div>
      </div>
      <div>
        <div class="text-xs opacity-80">Direction</div>
        <div class="text-xl font-bold">NW</div>
      </div>
      <div>
        <div class="text-xs opacity-80">Temperature</div>
        <div class="text-xl font-bold">14°C</div>
      </div>
      <div>
        <div class="text-xs opacity-80">Conditions</div>
        <div class="text-sm font-semibold">Partly Cloudy</div>
      </div>
    </div>
    <div class="mt-3 pt-3 border-t border-white/20 text-xs">
      <div class="flex justify-between">
        <span>Maintenance Window: Good (3 days)</span>
        <span class="flex items-center gap-1">
          <CheckCircle :size="14" /> Safe Operations
        </span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Cloud, CheckCircle } from 'lucide-vue-next';
</script>
