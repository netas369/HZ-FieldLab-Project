<template>
  <div class="grid grid-cols-1 md:grid-cols-2 gap-6 w-full p-4 bg-white rounded-xl shadow-md">
    
    <!-- LEFT SIDE (values) -->
    <div class="flex flex-col gap-4 justify-center text-gray-800">
      <h2 class="text-xl font-semibold">{{ title }}</h2>

      <div v-for="(val, key) in values" :key="key" class="flex justify-between border-b pb-1">
        <span class="font-medium">{{ key }}</span>
        <span>{{ val }}</span>
      </div>
    </div>

    <!-- RIGHT SIDE (visualization) -->
    <div class="flex justify-center items-center">
      <slot />
    </div>

  </div>
</template>

<script setup>
defineProps({
  title: String,
  values: Object
});
</script>
