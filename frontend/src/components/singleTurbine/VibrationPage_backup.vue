<template>
  <div class="p-6 space-y-8">

    <!-- MAIN BEARING -->
    <DataGroup
      title="Main Bearing"
      :values="{
        vibration: turbine.mainBearing.vibration,
        temperature: turbine.mainBearing.temperature
      }"
    >
      <!-- TODO: Replace this with your own visualization component -->
      <div class="text-gray-500">Main Bearing Visualization Here</div>
    </DataGroup>

    <!-- GEARBOX -->
    <DataGroup
      title="Gearbox"
      :values="{
        vibration: turbine.gearbox.vibration,
        temperature: turbine.gearbox.temperature
      }"
    >
      <div class="text-gray-500">Gearbox Visualization</div>
    </DataGroup>

    <!-- GENERATOR -->
    <DataGroup
      title="Generator"
      :values="{
        vibration: turbine.generator.vibration,
        temperature: turbine.generator.temperature
      }"
    >
      <div class="text-gray-500">Generator Visualization</div>
    </DataGroup>

    <!-- TOWER -->
    <DataGroup
      title="Tower"
      :values="{
        sway: turbine.tower.sway
      }"
    >
      <div class="text-gray-500">Tower Visualization</div>
    </DataGroup>

    <!-- BLADE -->
    <DataGroup
      title="Blade"
      :values="{
        blade1_vibration: turbine.liveCMSData.blade1_vibration,
        blade2_vibration: turbine.liveCMSData.blade2_vibration,
        blade3_vibration: turbine.liveCMSData.blade3_vibration
      }"
    >
      <BladeViz :turbine="turbine" />
    </DataGroup>

    <!-- ACOUSTIC -->
    <DataGroup
      title="Acoustic"
      :values="{
        noise: turbine.acoustic.noise_level
      }"
    >
      <div class="text-gray-500">Acoustic Visualization</div>
    </DataGroup>

  </div>
</template>

<script setup>
import DataGroup from "@/components/singleTurbine/VibrationVisualizaton/DataGroup.vue";
import BladeViz from "@/components/singleTurbine/VibrationVisualizaton/Blade.vue";

// CHANGE THESE VALUES — USE YOUR REAL TURBINE OBJECT
const turbine = {
  mainBearing: { vibration: 0.3, temperature: 62 },
  gearbox: { vibration: 0.5, temperature: 70 },
  generator: { vibration: 0.4, temperature: 65 },
  tower: { sway: 1.2 },
  liveCMSData: {
    blade1_vibration: 0.4,
    blade2_vibration: 0.6,
    blade3_vibration: 0.8,
  },
  acoustic: { noise_level: 42 }
};
</script>
