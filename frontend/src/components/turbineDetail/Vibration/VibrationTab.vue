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
import MainBearingCard from "./MainBearingCard.vue"
import GearboxCard from "./GearBoxCard.vue"
import GeneratorCard from "./GeneratorCard.vue"
import TowerCard from "./TowerCard.vue"
import BladeCard from "./BladeCard.vue"
import AcousticCard from "./AcousticCard.vue"

defineProps({
  turbine: Object
})
</script>
