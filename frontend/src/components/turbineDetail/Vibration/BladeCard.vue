<template>
  <div class="flex justify-between items-center bg-gray-900 p-4 rounded-xl shadow">

    <!-- LEFT: Values -->
        <div class="text-gray-100">
      <h2 class="font-semibold text-lg">Blades</h2>
      <p>Blade 1: {{ turbine.blade1_vibration }}</p>
      <p>Blade 2: {{ turbine.blade2_vibration }}</p>
      <p>Blade 3: {{ turbine.blade3_vibration }}</p>
      <p>Status: {{ turbine.blade_status.label }}</p>
    </div>

    <!-- RIGHT: Blade Visualization -->
    <div class="flex flex-col items-center rounded-xl p-2">
      <svg class="w-40 h-40" viewBox="-50 -50 100 100">

        <g v-for="i in 3" :key="i" :transform="`rotate(${134 * i - 2 * i})`">
          <path
            d="M4 1 L45 -6 L20 5 Z"
            class="stroke-gray-700"
            :class="getBladeColour(i)"
          />
        </g>

        <circle cx="0" cy="0" r="6" class="fill-gray-700" />
      </svg>
    </div>

  </div>
</template>

<script setup>
import { computed } from "vue"
/* CHANGE THRESHOLDS OR COLOR LOGIC */
const props = defineProps({ turbine: Object })

const ledColor = computed(() =>
  props.turbine.blade_status.color === "green"
    ? "#00ff00"
    : props.turbine.blade_status.color === "orange"
      ? "orange"
      : "red"
)

function getBladeStatus(vibration) {
  if (vibration >= 0.7) return "fill-red-600"
  if (vibration >= 0.5) return "fill-yellow-500"
  return "fill-green-600"
}

function getBladeColour(i) {
  const t = props.turbine
  switch (i) {
    case 1: return getBladeStatus(t.blade1_vibration)
    case 2: return getBladeStatus(t.blade2_vibration)
    case 3: return getBladeStatus(t.blade3_vibration)
  }
}
</script>
