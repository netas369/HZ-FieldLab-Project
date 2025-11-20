<!-- BladeViz.vue -->
<!-- CHANGE: Replace props.turbine with your turbine data structure -->
<!-- CHANGE: Edit ledColor() to use your real vibration status -->

<template>
  <div class="flex flex-col items-center gap-6 p-2">
    <div class="flex flex-col items-center bg-gray-100 rounded-2xl shadow-md">
      
      <svg class="w-64 h-64" viewBox="-50 -50 100 100">
        
        <!-- LED ring -->
        <circle cx="0" cy="0" r="14" :fill="ledColor" />
        <circle cx="0" cy="0" r="14" fill="none" stroke="#222" stroke-width="0.6" />

        <!-- blades -->
        <g v-for="i in 3" :key="i" :transform="`rotate(${134 * i - i * 2})`">
          <path
            d="M4 1 L45 -6 L20 5 Z"
            class="stroke-gray-700"
            :class="getBladeColour(i, turbine.liveCMSData)"
          />
        </g>

        <!-- hub -->
        <circle cx="0" cy="0" r="6" class="fill-gray-700" />

      </svg>

    </div>
  </div>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
  turbine: Object,
});

function getStatusforBlades(vibration) {
  if (vibration >= 0.7) return "fill-red-600";
  if (vibration >= 0.5) return "fill-yellow-500";
  return "fill-green-600";
}

function getBladeColour(bladeIndex, cms) {
  if (!cms) return "fill-gray-300";

  switch (bladeIndex) {
    case 1:
      return getStatusforBlades(cms.blade1_vibration);
    case 2:
      return getStatusforBlades(cms.blade2_vibration);
    case 3:
      return getStatusforBlades(cms.blade3_vibration);
  }
}

const ledColor = computed(() => {
  // 🔧 CHANGE THIS TO YOUR REAL STATUS MAPPING
  return "green";
});
</script>
