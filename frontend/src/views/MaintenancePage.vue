<template>
  <MaintenanceTab
    :maintenance-logs="maintenanceStore.logs"
    :loading="maintenanceStore.loading"
    :error="maintenanceStore.error"
    :predictive-insights="predictiveInsights"
    :maintenance-tabs="maintenanceTabs"
    :active-maintenance-tab="activeTab"
    @add-log="$emit('add-log')"
    @add-maintenance="$emit('add-maintenance', $event)"
  />
</template>

<script setup>
import { ref } from 'vue'
import MaintenanceTab from '@/components/maintenance/MaintenanceTab.vue'
import { useScadaService } from '@/composables/api.js'

const { maintenanceStore } = useScadaService()

// Local state
const maintenanceTabs = ['Recent', 'Scheduled', 'Predictive', 'Component History']
const activeTab = ref('Recent')
const predictiveInsights = ref([])

defineEmits(['add-log', 'add-maintenance'])
</script>