<template>
  <div class="flex justify-between items-center bg-gray-900 p-4 rounded-xl shadow">

    <div class="text-gray-100">
      <h2 class="font-semibold text-lg">Tower</h2>
      <p>Fore-Aft: {{ turbine.tower_vibration_fa }}</p>
      <p>Side-Side: {{ turbine.tower_vibration_ss }}</p>
      <p>Status: {{ turbine.tower_status.label }}</p>
    </div>

    <div class="w-16 h-16 rounded-full border-4" :class="statusColor"></div>

  </div>
</template>

<script setup>
import { computed } from "vue"
const props = defineProps({ turbine: Object })
const statusColor = computed(() => `border-${props.turbine.tower_status.color}-500`)
</script>
