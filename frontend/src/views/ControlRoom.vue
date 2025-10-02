<template>
  <div class="min-h-screen bg-slate-100">
    <NavigationBar />
    <router-view />
  </div>
</template>

<script setup>
import { provide, ref } from 'vue';
import NavigationBar from '@/components/NavigationBar.vue';

const selectedTurbine = ref(null);
const showNotifications = ref(false);
const selectedAlarmFilter = ref('all');
const weatherView = ref(false);

// Provide state to child components
provide('selectedTurbine', selectedTurbine);
provide('showNotifications', showNotifications);
provide('selectedAlarmFilter', selectedAlarmFilter);
provide('weatherView', weatherView);
</script>