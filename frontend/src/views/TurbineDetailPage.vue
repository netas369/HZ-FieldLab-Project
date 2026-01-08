<template>
  <div class="space-y-6">
    <!-- Back Button -->
    <div>
      <button
        class="flex items-center gap-2 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors"
        @click="$router.push({ name: 'Overview' })"
      >
        <svg
          class="w-5 h-5"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M15 19l-7-7 7-7"
          />
        </svg>
        <span class="font-medium">Back to Overview</span>
      </button>
    </div>

    <!-- Turbine Detail Component -->
    <TurbineDetailTab
      :turbine-id="id"
      @show-alarm="$emit('show-alarm', $event)"
      @add-maintenance="$emit('add-maintenance', $event)"
    />
  </div>
</template>

<script setup>
import TurbineDetailTab from '@/components/turbineDetail/TurbineDetailTab.vue'

defineProps({
  id: {
    type: String,
    required: true,
  },
})

defineEmits(['show-alarm', 'add-maintenance'])
</script>
