<!-- src/views/TurbineMonitoringView.vue -->
<template>
  <div class="turbine-monitoring-view">
    <Prototype />
  </div>
</template>

<script>
import Prototype from '@/components/Prototype.vue'

export default {
  name: 'TurbineMonitoringView',
  components: {
    Prototype
  }
}
</script>

<style scoped>
.turbine-monitoring-view {
  width: 100%;
  height: 100%;
}
</style>
