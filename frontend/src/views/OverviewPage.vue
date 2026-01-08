<template>
  <OverviewTab
    :turbines="turbineStore.turbines"
    :loading="turbineStore.loading"
    :error="turbineStore.error"
    :selected-turbine="turbineStore.selectedTurbine"
    :search-query="searchQuery"
    @select-turbine="$emit('select-turbine', $event)"
  />
</template>

<script setup>
import { ref } from 'vue'
import OverviewTab from '@/components/overview/OverviewTab.vue'
import { useScadaService } from '@/composables/api.js'

const { turbineStore } = useScadaService()

// Local state for search (could also come from route query params)
const searchQuery = ref('')

// Emit events up to AppLayout
defineEmits(['select-turbine'])
</script>
