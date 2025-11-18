<template>
  <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-4">

    <!-- Main Bearing -->
    <MainBearingCard :turbine="turbine" />

    <!-- Gearbox -->
    <GearboxCard :turbine="turbine" />

    <!-- Generator -->
    <GeneratorCard :turbine="turbine" />

    <!-- Tower -->
    <TowerCard :turbine="turbine" />

    <!-- Blades -->
    <BladeCard :turbine="turbine" />

    <!-- Acoustic -->
    <AcousticCard :turbine="turbine" />

  </div>
</template>

<script setup>
import MainBearingCard from "@/components/singleTurbine/VibrationVisualizaton/MainBearingCard.vue"
import GearboxCard from "@/components/singleTurbine/VibrationVisualizaton/GearBoxCard.vue"
import GeneratorCard from "@/components/singleTurbine/VibrationVisualizaton/GeneratorCard.vue"
import TowerCard from "@/components/singleTurbine/VibrationVisualizaton/TowerCard.vue"
import BladeCard from "@/components/singleTurbine/VibrationVisualizaton/BladeCard.vue"
import AcousticCard from "@/components/singleTurbine/VibrationVisualizaton/AcousticCard.vue"

defineProps({
  turbine: Object
})
</script>
