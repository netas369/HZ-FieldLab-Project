<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Settings</h2>
        <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
          Manage your account and application preferences
        </p>
      </div>

      <button
          v-if="hasChanges"
          @click="saveSettings"
          class="flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition-colors shadow-lg shadow-indigo-500/30"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
        Save Changes
      </button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
      <!-- Settings Navigation -->
      <div class="lg:col-span-1">
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-2">
          <button
              v-for="section in settingsSections"
              :key="section.id"
              @click="activeSection = section.id"
              :class="[
              'w-full flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium transition-all text-left',
              activeSection === section.id
                ? 'bg-indigo-600 text-white'
                : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700'
            ]"
          >
            <component :is="section.icon" class="w-5 h-5" />
            <span>{{ section.label }}</span>
          </button>
        </div>
      </div>

      <!-- Settings Content -->
      <div class="lg:col-span-3 space-y-6">
        <!-- Profile Section -->
        <div v-if="activeSection === 'profile'" class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6 border border-slate-200 dark:border-slate-700">
          <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-6">Profile Information</h3>

          <div class="space-y-6">
            <!-- Avatar -->
            <div class="flex items-center gap-6">
              <div class="w-24 h-24 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-3xl font-bold shadow-lg">
                {{ getInitials(settings.profile.name) }}
              </div>
              <div>
                <button class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition-colors mb-2">
                  Change Avatar
                </button>
                <p class="text-xs text-slate-500 dark:text-slate-400">JPG, PNG or GIF. Max size 2MB.</p>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Full Name</label>
                <input
                    v-model="settings.profile.name"
                    type="text"
                    class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-indigo-500"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Role</label>
                <input
                    v-model="settings.profile.role"
                    type="text"
                    class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-indigo-500"
                />
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Email</label>
              <input
                  v-model="settings.profile.email"
                  type="email"
                  class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-indigo-500"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Phone</label>
              <input
                  v-model="settings.profile.phone"
                  type="tel"
                  class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-indigo-500"
              />
            </div>
          </div>
        </div>

        <!-- Notifications Section -->
        <div v-if="activeSection === 'notifications'" class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6 border border-slate-200 dark:border-slate-700">
          <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-6">Notification Preferences</h3>

          <div class="space-y-4">
            <div
                v-for="notification in settings.notifications"
                :key="notification.id"
                class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-900 rounded-lg"
            >
              <div>
                <h4 class="font-medium text-slate-900 dark:text-white">{{ notification.label }}</h4>
                <p class="text-sm text-slate-600 dark:text-slate-400">{{ notification.description }}</p>
              </div>
              <label class="relative inline-flex items-center cursor-pointer">
                <input
                    type="checkbox"
                    v-model="notification.enabled"
                    class="sr-only peer"
                />
                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-slate-600 peer-checked:bg-indigo-600"></div>
              </label>
            </div>
          </div>
        </div>

        <!-- Appearance Section -->
        <div v-if="activeSection === 'appearance'" class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6 border border-slate-200 dark:border-slate-700">
          <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-6">Appearance</h3>

          <div class="space-y-6">
            <!-- Theme -->
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-3">Theme</label>
              <div class="grid grid-cols-3 gap-4">
                <button
                    v-for="theme in themes"
                    :key="theme.id"
                    @click="settings.appearance.theme = theme.id"
                    :class="[
                    'p-4 rounded-lg border-2 transition-all',
                    settings.appearance.theme === theme.id
                      ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20'
                      : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600'
                  ]"
                >
                  <component :is="theme.icon" class="w-8 h-8 mx-auto mb-2 text-slate-700 dark:text-slate-300" />
                  <p class="text-sm font-medium text-slate-900 dark:text-white">{{ theme.label }}</p>
                </button>
              </div>
            </div>

            <!-- Language -->
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Language</label>
              <select
                  v-model="settings.appearance.language"
                  class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-indigo-500"
              >
                <option value="en">English</option>
                <option value="es">Español</option>
                <option value="fr">Français</option>
                <option value="de">Deutsch</option>
                <option value="ru">Русский</option>
              </select>
            </div>

            <!-- Date Format -->
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Date Format</label>
              <select
                  v-model="settings.appearance.dateFormat"
                  class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-indigo-500"
              >
                <option value="MM/DD/YYYY">MM/DD/YYYY</option>
                <option value="DD/MM/YYYY">DD/MM/YYYY</option>
                <option value="YYYY-MM-DD">YYYY-MM-DD</option>
              </select>
            </div>

            <!-- Timezone -->
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Timezone</label>
              <select
                  v-model="settings.appearance.timezone"
                  class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-indigo-500"
              >
                <option value="UTC">UTC</option>
                <option value="America/New_York">Eastern Time</option>
                <option value="America/Chicago">Central Time</option>
                <option value="America/Los_Angeles">Pacific Time</option>
                <option value="Europe/Amsterdam">Amsterdam</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Security Section -->
        <div v-if="activeSection === 'security'" class="space-y-6">
          <!-- Password -->
          <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6 border border-slate-200 dark:border-slate-700">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-6">Change Password</h3>

            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Current Password</label>
                <input
                    type="password"
                    class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-indigo-500"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">New Password</label>
                <input
                    type="password"
                    class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-indigo-500"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Confirm New Password</label>
                <input
                    type="password"
                    class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-indigo-500"
                />
              </div>
              <button class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition-colors">
                Update Password
              </button>
            </div>
          </div>

          <!-- Two-Factor -->
          <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6 border border-slate-200 dark:border-slate-700">
            <div class="flex items-start justify-between mb-4">
              <div>
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Two-Factor Authentication</h3>
                <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">Add an extra layer of security</p>
              </div>
              <span class="px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">
                Disabled
              </span>
            </div>
            <button class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition-colors">
              Enable 2FA
            </button>
          </div>

          <!-- Sessions -->
          <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6 border border-slate-200 dark:border-slate-700">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">Active Sessions</h3>

            <div class="space-y-3">
              <div class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-900 rounded-lg">
                <div class="flex items-center gap-3">
                  <div class="p-2 bg-green-100 dark:bg-green-900/30 rounded-lg">
                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                  </div>
                  <div>
                    <p class="font-medium text-slate-900 dark:text-white">Chrome on Windows</p>
                    <p class="text-sm text-slate-600 dark:text-slate-400">Amsterdam, Netherlands • Active now</p>
                  </div>
                </div>
                <span class="text-xs font-semibold text-green-600 dark:text-green-400">Current</span>
              </div>
            </div>
          </div>
        </div>

        <!-- System Section -->
        <div v-if="activeSection === 'system'" class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6 border border-slate-200 dark:border-slate-700">
          <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-6">System Preferences</h3>

          <div class="space-y-6">
            <!-- Data Refresh -->
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Data Refresh Rate</label>
              <select
                  v-model="settings.system.refreshRate"
                  class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-indigo-500"
              >
                <option value="5">5 seconds</option>
                <option value="10">10 seconds</option>
                <option value="30">30 seconds</option>
                <option value="60">1 minute</option>
              </select>
            </div>

            <!-- Default View -->
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Default Dashboard View</label>
              <select
                  v-model="settings.system.defaultView"
                  class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-indigo-500"
              >
                <option value="overview">Overview</option>
                <option value="performance">Performance</option>
                <option value="alarms">Alarms</option>
                <option value="analytics">Analytics</option>
              </select>
            </div>

            <!-- Export Settings -->
            <div class="p-4 bg-slate-50 dark:bg-slate-900 rounded-lg">
              <h4 class="font-medium text-slate-900 dark:text-white mb-2">Export Data</h4>
              <p class="text-sm text-slate-600 dark:text-slate-400 mb-4">Download all your settings and data</p>
              <button class="px-4 py-2 bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-900 dark:text-white rounded-lg font-medium transition-colors">
                Export All Data
              </button>
            </div>

            <!-- Danger Zone -->
            <div class="p-4 bg-red-50 dark:bg-red-900/10 border border-red-200 dark:border-red-800 rounded-lg">
              <h4 class="font-medium text-red-900 dark:text-red-400 mb-2">Danger Zone</h4>
              <p class="text-sm text-red-700 dark:text-red-400 mb-4">Irreversible actions</p>
              <div class="flex gap-3">
                <button class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition-colors">
                  Reset All Settings
                </button>
                <button class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition-colors">
                  Delete Account
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'

const props = defineProps({
  user: { type: Object, default: () => ({}) },
  preferences: { type: Object, default: () => ({}) }
})

// State
const activeSection = ref('profile')
const hasChanges = ref(false)

const settingsSections = [
  {
    id: 'profile',
    label: 'Profile',
    icon: { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>' }
  },
  {
    id: 'notifications',
    label: 'Notifications',
    icon: { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>' }
  },
  {
    id: 'appearance',
    label: 'Appearance',
    icon: { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" /></svg>' }
  },
  {
    id: 'security',
    label: 'Security',
    icon: { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>' }
  },
  {
    id: 'system',
    label: 'System',
    icon: { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>' }
  }
]

const themes = [
  {
    id: 'light',
    label: 'Light',
    icon: { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" /></svg>' }
  },
  {
    id: 'dark',
    label: 'Dark',
    icon: { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" /></svg>' }
  },
  {
    id: 'auto',
    label: 'Auto',
    icon: { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>' }
  }
]

const settings = reactive({
  profile: {
    name: props.user.name || 'John Smith',
    role: props.user.role || 'Supervisor',
    email: 'john.smith@windflow.com',
    phone: '+1 (555) 123-4567'
  },
  notifications: [
    { id: 1, label: 'Critical Alarms', description: 'Receive notifications for critical turbine alarms', enabled: true },
    { id: 2, label: 'Maintenance Reminders', description: 'Get reminders for scheduled maintenance', enabled: true },
    { id: 3, label: 'Performance Reports', description: 'Weekly performance summary emails', enabled: false },
    { id: 4, label: 'System Updates', description: 'Notifications about system updates', enabled: true },
    { id: 5, label: 'Email Digest', description: 'Daily email summary of activities', enabled: false }
  ],
  appearance: {
    theme: 'light',
    language: 'en',
    dateFormat: 'MM/DD/YYYY',
    timezone: 'Europe/Amsterdam'
  },
  system: {
    refreshRate: '30',
    defaultView: 'overview'
  }
})

// Methods
const getInitials = (name) => {
  return name
      .split(' ')
      .map(n => n[0])
      .join('')
      .toUpperCase()
      .slice(0, 2)
}

const saveSettings = () => {
  console.log('Saving settings:', settings)
  hasChanges.value = false
  // Implement save logic
}
</script>

<style scoped>
/* Custom styles if needed */
</style>