<template>
  <AnalyticsTab
    :kpis="kpis"
    :charts="charts"
  />
</template>

<script setup>
import { ref } from 'vue'
import AnalyticsTab from '@/components/analytics/AnalyticsTab.vue'

// Placeholder data - replace with real data later
const kpis = ref([])
const charts = ref([])
</script>