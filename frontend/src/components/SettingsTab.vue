<template>
  <div class="space-y-6">
    <h2 class="text-xl font-semibold mb-4">Settings</h2>

    <!-- User Preferences -->
    <section class="bg-white/80 p-4 rounded-xl shadow space-y-3">
      <h3 class="font-semibold mb-2">User Preferences</h3>
      <div class="flex flex-col md:flex-row gap-4">
        <div class="flex-1">
          <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
          <input v-model="settings.name" type="text" class="w-full p-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500"/>
        </div>
        <div class="flex-1">
          <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
          <input v-model="settings.role" type="text" class="w-full p-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500"/>
        </div>
      </div>
    </section>

    <!-- Notifications -->
    <section class="bg-white/80 p-4 rounded-xl shadow space-y-3">
      <h3 class="font-semibold mb-2">Notifications</h3>
      <div class="flex flex-col gap-2">
        <label class="flex items-center gap-2">
          <input type="checkbox" v-model="settings.notifyEmail" class="rounded"/>
          Email notifications
        </label>
        <label class="flex items-center gap-2">
          <input type="checkbox" v-model="settings.notifySMS" class="rounded"/>
          SMS notifications
        </label>
        <label class="flex items-center gap-2">
          <input type="checkbox" v-model="settings.notifyPush" class="rounded"/>
          Push notifications
        </label>
      </div>
    </section>

    <!-- System Configurations -->
    <section class="bg-white/80 p-4 rounded-xl shadow space-y-3">
      <h3 class="font-semibold mb-2">System Configuration</h3>
      <div class="flex flex-col md:flex-row gap-4">
        <div class="flex-1">
          <label class="block text-sm font-medium text-gray-700 mb-1">Refresh Interval (sec)</label>
          <input v-model.number="settings.refreshInterval" type="number" min="1" class="w-full p-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500"/>
        </div>
        <div class="flex-1">
          <label class="block text-sm font-medium text-gray-700 mb-1">Theme</label>
          <select v-model="settings.theme" class="w-full p-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <option value="light">Light</option>
            <option value="dark">Dark</option>
            <option value="system">System</option>
          </select>
        </div>
      </div>
    </section>

    <!-- Save Button -->
    <div class="mt-4 flex justify-end">
      <button @click="$emit('save-settings', settings)" class="px-4 py-2 bg-indigo-500 text-white rounded-lg hover:bg-indigo-600 transition">
        Save Settings
      </button>
    </div>
  </div>
</template>

<script setup>
import { reactive } from 'vue'

const props = defineProps({
  initialSettings: {
    type: Object,
    default: () => ({
      name: 'John Smith',
      role: 'Supervisor',
      notifyEmail: true,
      notifySMS: false,
      notifyPush: true,
      refreshInterval: 10,
      theme: 'light'
    })
  }
})

const emit = defineEmits(['save-settings'])

const settings = reactive({ ...props.initialSettings })
</script>

<style scoped>
/* Tailwind handles most styling; no additional CSS needed */
</style>
