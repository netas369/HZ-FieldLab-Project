<template>
  <nav class="bg-blue-600 shadow-lg">
    <div class="max-w-7xl mx-auto px-4">
      <div class="flex justify-between items-center h-16">

        <!-- Logo/Brand -->
        <div class="flex-shrink-0">
          <h1 class="text-white text-2xl font-bold">
            Wind Turbine Dashboard
          </h1>
        </div>

        <!-- Navigation Buttons -->
        <div class="flex space-x-4">
          <router-link
              to="/"
              class="px-6 py-2 rounded-lg font-semibold transition-all duration-200"
              :class="isActive('/') ? 'bg-white text-blue-600' : 'bg-blue-700 text-white hover:bg-blue-800'"
          >
            Live
          </router-link>

          <router-link
              to="/history"
              class="px-6 py-2 rounded-lg font-semibold transition-all duration-200"
              :class="isActive('/history') ? 'bg-white text-blue-600' : 'bg-blue-700 text-white hover:bg-blue-800'"
          >
            History
          </router-link>
        </div>
      </div>
    </div>
  </nav>
</template>

<script>
export default {
  name: 'Navbar',

  methods: {
    // Check if current route is active
    isActive(path) {
      return this.$route.path === path;
    }
  }
}
</script>