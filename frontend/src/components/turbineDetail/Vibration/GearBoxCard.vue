<template>
  <div class="flex justify-between items-center bg-gray-900 p-4 rounded-xl shadow">

        <div class="text-gray-100">
      <h2 class="font-semibold text-lg">Gearbox</h2>
      <p>Axial: {{ turbine.gearbox_vibration_axial }}</p>
      <p>Radial: {{ turbine.gearbox_vibration_radial }}</p>
      <p>Status: {{ turbine.gearbox_status.label }}</p>
    </div>

    <div class="w-16 h-16 rounded-full border-4" :class="statusColor"></div>

  </div>
</template>

<script setup>
import { computed } from "vue"
const props = defineProps({ turbine: Object })

const statusColor = computed(() => {
  return "border-" + props.turbine.gearbox_status.color + "-500"
})
</script>
