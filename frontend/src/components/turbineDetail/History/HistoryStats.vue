<template>
  <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
    <div
      class="bg-slate-50 dark:bg-slate-900 p-4 rounded-lg border border-slate-100 dark:border-slate-700 text-center"
    >
      <p
        class="text-xs text-slate-500 dark:text-slate-400 mb-1 font-medium uppercase tracking-wider"
      >
        Data Points
      </p>
      <p class="text-lg font-bold text-slate-900 dark:text-white">
        {{ displayedPoints }}
        <span class="text-xs text-slate-400 font-normal">/ {{ totalPoints }}</span>
      </p>
    </div>
    <div
      class="bg-slate-50 dark:bg-slate-900 p-4 rounded-lg border border-slate-100 dark:border-slate-700 text-center"
    >
      <p
        class="text-xs text-slate-500 dark:text-slate-400 mb-1 font-medium uppercase tracking-wider"
      >
        Avg Power
      </p>
      <p class="text-lg font-bold text-slate-900 dark:text-white">
        {{ stats.avgPower }} <span class="text-xs text-slate-400 font-normal">kW</span>
      </p>
    </div>
    <div
      class="bg-slate-50 dark:bg-slate-900 p-4 rounded-lg border border-slate-100 dark:border-slate-700 text-center"
    >
      <p
        class="text-xs text-slate-500 dark:text-slate-400 mb-1 font-medium uppercase tracking-wider"
      >
        Max Wind
      </p>
      <p class="text-lg font-bold text-slate-900 dark:text-white">
        {{ stats.maxWind }} <span class="text-xs text-slate-400 font-normal">m/s</span>
      </p>
    </div>
    <div
      class="bg-slate-50 dark:bg-slate-900 p-4 rounded-lg border border-slate-100 dark:border-slate-700 text-center"
    >
      <p
        class="text-xs text-slate-500 dark:text-slate-400 mb-1 font-medium uppercase tracking-wider"
      >
        Avg Temp
      </p>
      <p class="text-lg font-bold text-slate-900 dark:text-white">
        {{ stats.avgTemp }} <span class="text-xs text-slate-400 font-normal">°C</span>
      </p>
    </div>
  </div>
</template>

<script setup>
defineProps({
  stats: {
    type: Object,
    required: true,
    default: () => ({ avgPower: 0, maxWind: 0, avgTemp: 0 }),
  },
  displayedPoints: {
    type: Number,
    default: 0,
  },
  totalPoints: {
    type: Number,
    default: 0,
  },
})
</script>
