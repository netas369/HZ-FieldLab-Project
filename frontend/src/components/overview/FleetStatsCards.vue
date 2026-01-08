<template>
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
    <div
      v-for="stat in stats"
      :key="stat.label"
      class="bg-white dark:bg-slate-800 rounded-xl shadow-sm hover:shadow-md transition-all p-6 border border-slate-200 dark:border-slate-700"
    >
      <div class="flex items-start justify-between mb-3">
        <div :class="['p-3 rounded-lg', stat.bgColor]">
          <component
            :is="stat.icon"
            class="w-6 h-6 text-white"
          />
        </div>
        <span :class="['text-xs font-semibold px-2.5 py-1 rounded-full', stat.badgeColor]">
          {{ stat.badge }}
        </span>
      </div>
      <div>
        <p class="text-sm text-slate-600 dark:text-slate-400 mb-1">
          {{ stat.label }}
        </p>
        <p class="text-3xl font-bold text-slate-900 dark:text-white">
          {{ stat.value }}
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  stats: {
    type: Array,
    required: true,
    default: () => [],
  },
})
</script>
