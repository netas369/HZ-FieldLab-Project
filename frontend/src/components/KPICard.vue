<template>
  <div class="bg-white p-4 rounded-lg shadow">
    <div class="flex items-center justify-between mb-2">
      <component :is="icon" :class="iconColor" :size="24" />
      <span :class="['text-xs px-2 py-1 rounded', badgeColor]">{{ badge }}</span>
    </div>
    <div class="text-sm text-slate-600 mb-1">{{ label }}</div>
    <div :class="['text-3xl font-bold', valueColor]">
      {{ value }}
      <span v-if="unit" class="text-lg">{{ unit }}</span>
    </div>
  </div>
</template>

<script setup>
defineProps({
  icon: [Object, Function],  // Changed from just Object
  label: String,
  value: String,
  unit: String,
  badge: String,
  iconColor: String,
  badgeColor: String,
  valueColor: { type: String, default: '' }
});
</script>