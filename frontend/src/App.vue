<template>
  <div id="app">
    <navbar />

    <router-view />
  </div>
</template>

<script>
import Navbar from "@/components/NavBar.vue";

export default {
  name: 'App',
  components: {Navbar}
}
</script>

<style>
/* Your existing styles */
</style>