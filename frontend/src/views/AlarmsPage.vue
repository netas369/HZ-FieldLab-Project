<template>
  <AlarmTab
    :alarms="alarmStore.alarms"
    :loading="alarmStore.loading"
    :error="alarmStore.error"
    :filters="['All', 'Critical', 'Major', 'Warning', 'Minor']"
    :initial-filter="initialFilter"
    @show-alarm="$emit('show-alarm', $event)"
    @acknowledge-alarm="$emit('acknowledge-alarm', $event)"
  />
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import AlarmTab from '@/components/alarms/AlarmTab.vue'
import { useScadaService } from '@/composables/api.js'

const route = useRoute()
const { alarmStore } = useScadaService()

// Support filter via query params: /alarms?filter=Critical
const initialFilter = computed(() => {
  const queryFilter = route.query.filter
  const validFilters = ['All', 'Critical', 'Major', 'Warning', 'Minor']
  return validFilters.includes(queryFilter) ? queryFilter : 'All'
})

defineEmits(['show-alarm', 'acknowledge-alarm'])
</script>