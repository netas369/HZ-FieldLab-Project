<template>
  <div class="flex justify-between items-center bg-gray-900 p-4 rounded-xl shadow">

    <div class="text-gray-100">
      <h2 class="font-semibold text-lg">Acoustic</h2>
      <p>Level: {{ turbine.acoustic_level_db }} dB</p>
      <p>Status: {{ turbine.acoustic_status.label }}</p>
    </div>

    <!-- Right: Simple sound bar -->
    <div class="w-20 h-16 flex items-end gap-1">
      <div v-for="n in bars" :style="{ height: n + 'px' }" class="w-3 bg-gray-500 rounded"
      :class="getBarColour()"
      ></div>
    </div>

  </div>
</template>

<script setup>
import { computed } from "vue"
const props = defineProps({ turbine: Object })

const bars = [6, 12, 18, 24, 30, 36];

function getBarColour() {
  if (props.turbine?.acoustic_status?.label == "Loud") {
  return "bg-yellow-500";
  } else {
  return "bg-green-500";
  }
}
</script>
