<template>
  <div
    class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6 h-full flex flex-col"
  >
    <div class="flex items-center justify-between mb-4">
      <p class="text-sm font-medium text-slate-600 dark:text-slate-400">
        {{ title }}
      </p>

      <div :class="['p-2 rounded-lg transition-colors', bgColors[statusColor]]">
        <svg
          class="w-5 h-5 transition-colors"
          :class="textColors[statusColor]"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <slot name="icon" />
        </svg>
      </div>
    </div>

    <div class="flex-1">
      <slot />
    </div>

    <div
      v-if="statusLabel"
      class="mt-auto pt-3 border-t border-slate-100 dark:border-slate-700 flex justify-end"
    >
      <span :class="['text-xs font-bold', textColors[statusColor]]">
        {{ statusLabel }}
      </span>
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: { type: String, default: '' },
  statusColor: {
    type: String,
    default: 'gray',
  },
  statusLabel: { type: String, default: '' },
})

const bgColors = {
  green: 'bg-green-100 dark:bg-green-900/30',
  orange: 'bg-orange-100 dark:bg-orange-900/30',
  red: 'bg-red-100 dark:bg-red-900/30',
  yellow: 'bg-yellow-100 dark:bg-yellow-900/30',
  gray: 'bg-slate-100 dark:bg-slate-700',
}

const textColors = {
  green: 'text-green-600 dark:text-green-400',
  orange: 'text-orange-600 dark:text-orange-400',
  red: 'text-red-600 dark:text-red-400',
  yellow: 'text-yellow-600 dark:text-yellow-400',
  gray: 'text-slate-600 dark:text-slate-400',
}
</script>
