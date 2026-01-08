<!-- src/components/FooterBar.vue -->
<template>
  <footer
    class="bg-white/80 backdrop-blur rounded-t-2xl shadow-inner py-4 mt-6 text-center text-gray-600 text-sm"
  >
    <div class="flex flex-col md:flex-row items-center justify-center gap-2 md:gap-6">
      <span>© 2025 Wind Turbine Monitoring Inc.</span>
      <span>Version 1.0.0</span>
      <span>Last update: {{ lastUpdate }}</span>
    </div>
  </footer>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const lastUpdate = ref('')

onMounted(() => {
  const date = new Date()
  lastUpdate.value = date.toLocaleDateString() + ' ' + date.toLocaleTimeString()
})
</script>
