<template>
  <div class="space-y-6">
    <!-- Info Bar -->
    <div class="flex items-center justify-between text-sm">
      <span class="text-slate-600 dark:text-slate-300 font-medium">
        Retrieving {{ dayCount }} days of telemetry...
      </span>
      <span class="text-slate-500 dark:text-slate-400">
        Estimated wait:
        <span class="font-mono text-indigo-600 dark:text-indigo-400 font-bold">{{ estimatedWait }}s</span>
      </span>
    </div>

    <!-- Progress Bar -->
    <div class="h-2 w-full bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
      <div
        class="h-full bg-indigo-500 transition-all ease-out duration-300 rounded-full relative overflow-hidden"
        :style="{ width: `${progress}%` }"
      >
        <div
          class="absolute top-0 left-0 bottom-0 right-0 bg-gradient-to-r from-transparent via-white/30 to-transparent w-full -translate-x-full animate-[shimmer_1.5s_infinite]"
        />
      </div>
    </div>

    <!-- Graph Skeleton -->
    <div
      class="relative h-[400px] w-full bg-slate-50 dark:bg-slate-900/50 rounded-xl border border-slate-100 dark:border-slate-700 overflow-hidden flex flex-col"
    >
      <!-- Grid Background -->
      <div
        class="absolute inset-0"
        style="
          background-image:
            linear-gradient(to right, rgba(148, 163, 184, 0.1) 1px, transparent 1px),
            linear-gradient(to bottom, rgba(148, 163, 184, 0.1) 1px, transparent 1px);
          background-size: 40px 40px;
        "
      />

      <!-- Pulse Animation -->
      <div class="absolute inset-0 flex items-center justify-center">
        <svg
          class="w-full h-32 text-slate-200 dark:text-slate-700 opacity-50"
          viewBox="0 0 400 100"
          preserveAspectRatio="none"
        >
          <path
            d="M0,50 Q20,60 40,50 T80,50 T120,50 T160,50 T200,50 T240,50 T280,50 T320,50 T360,50 T400,50"
            fill="none"
            stroke="currentColor"
            stroke-width="4"
            class="animate-[pulse_1.5s_ease-in-out_infinite]"
          />
        </svg>
      </div>

      <!-- Overlay Text -->
      <div class="absolute inset-0 flex flex-col items-center justify-center z-10">
        <div
          class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm px-6 py-3 rounded-full shadow-sm border border-slate-200 dark:border-slate-700 flex items-center gap-3"
        >
          <span class="relative flex h-3 w-3">
            <span
              class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"
            />
            <span class="relative inline-flex rounded-full h-3 w-3 bg-indigo-500" />
          </span>
          <span class="text-sm font-medium text-slate-700 dark:text-slate-200">Synchronizing Timestamp {{ progress.toFixed(0) }}%</span>
        </div>
      </div>
    </div>

    <!-- Stats Skeleton -->
    <div class="grid grid-cols-4 gap-4 animate-pulse">
      <div
        v-for="i in 4"
        :key="i"
        class="h-16 bg-slate-100 dark:bg-slate-800 rounded-lg"
      />
    </div>
  </div>
</template>

<script setup>
defineProps({
  dayCount: {
    type: Number,
    required: true,
  },
  estimatedWait: {
    type: [Number, String],
    required: true,
  },
  progress: {
    type: Number,
    default: 0,
  },
})
</script>

<style scoped>
@keyframes shimmer {
  0% {
    transform: translateX(-100%);
  }
  100% {
    transform: translateX(100%);
  }
}
</style>
