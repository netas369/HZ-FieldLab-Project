<template>
  <div class="flex justify-between items-center bg-gray-900 p-4 rounded-xl shadow">

    <!-- LEFT: Values -->
      <div class="text-gray-100">
      <h2 class="font-semibold text-lg">Main Bearing</h2>
      <p>RMS: {{ turbine.main_bearing_vibration_rms }}</p>
      <p>Peak: {{ turbine.main_bearing_vibration_peak }}</p>
      <p>Status: {{ turbine.main_bearing_status.label }}</p>
    </div>

    <!-- RIGHT: Visualization -->
    <div>
      <div
        class="w-16 h-16 rounded-full border-4"
        :class="statusColor"
      ></div>
    </div>

  </div>
</template>

<script setup>
import { computed } from "vue"
const props = defineProps({ turbine: Object })

const statusColor = computed(() => {
  switch (props.turbine.main_bearing_status.color) {
    case "green": return "border-green-500"
    case "yellow": return "border-yellow-400"
    case "red": return "border-red-500"
    default: return "border-gray-400"
  }
})
</script>
