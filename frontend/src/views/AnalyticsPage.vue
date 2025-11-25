<template>
  <AnalyticsTab
    :turbines="turbineStore.turbines"
    :alarms="alarmStore.alarms"
    :loading="loading"
    :error="error"
  />
</template>

<script setup>
import { computed } from 'vue'
import AnalyticsTab from '@/components/analytics/AnalyticsTab.vue'
import { useScadaService } from '@/composables/api.js'

const { turbineStore, alarmStore } = useScadaService()

const loading = computed(() => turbineStore.loading || alarmStore.loading)
const error = computed(() => turbineStore.error || alarmStore.error)
</script>