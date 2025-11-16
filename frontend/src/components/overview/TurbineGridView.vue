<template>
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
    <TurbineCard
      v-for="turbine in turbines"
      :key="turbine.id"
      :turbine="turbine"
      @select="$emit('select-turbine', $event)"
    />
  </div>
</template>

<script setup>
import TurbineCard from './TurbineCard.vue'

defineProps({
  turbines: {
    type: Array,
    required: true,
    default: () => []
  }
})

defineEmits(['select-turbine'])
</script>