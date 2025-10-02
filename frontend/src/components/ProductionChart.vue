<template>
  <div class="bg-white rounded-lg shadow p-6">
    <h3 class="font-semibold mb-4 flex items-center gap-2">
      <TrendingUp :size="18" />
      Production Today
    </h3>
    <div class="h-48 border rounded bg-slate-50 flex items-end justify-between p-4 gap-1">
      <div
          v-for="(val, i) in productionData"
          :key="i"
          class="flex-1 flex flex-col items-center gap-1"
      >
        <span class="text-xs text-slate-600">{{ val }}MW</span>
        <div
            class="w-full bg-gradient-to-t from-blue-500 to-blue-300 rounded-t"
            :style="{ height: val + '%' }"
        />
      </div>
    </div>
    <div class="flex justify-between text-xs text-slate-600 mt-2">
      <span>00:00</span>
      <span>06:00</span>
      <span>12:00</span>
      <span>18:00</span>
    </div>
  </div>
</template>

<script setup>
import { TrendingUp } from 'lucide-vue-next';

const productionData = [20, 35, 42, 58, 72, 85, 78, 82, 90, 88, 92, 87];
</script>
