<template>
  <header class="bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 sticky top-0 z-50 backdrop-blur-lg bg-white/95 dark:bg-slate-900/95">
    <div class="max-w-[1600px] mx-auto px-6 py-4">
      <div class="flex items-center justify-between">
        <!-- Left: Logo & Title -->
        <div class="flex items-center gap-4">
          <!-- Animated Turbine Logo -->
          <div class="relative group">
            <div class="absolute inset-0 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-xl blur-lg opacity-30 group-hover:opacity-50 transition-opacity"></div>
            <div class="relative bg-gradient-to-br from-indigo-500 to-purple-600 p-2.5 rounded-xl shadow-lg">
              <svg viewBox="0 0 100 100" class="w-8 h-8 text-white animate-spin-slow" aria-hidden="true">
                <circle cx="50" cy="50" r="4" fill="currentColor"/>
                <path d="M50 10 L50 40 L35 25 M50 40 L65 25" stroke="currentColor" stroke-width="3.5" fill="none" stroke-linecap="round"/>
                <path d="M50 10 L50 40 L35 25 M50 40 L65 25" stroke="currentColor" stroke-width="3.5" fill="none" stroke-linecap="round" transform="rotate(120 50 50)"/>
                <path d="M50 10 L50 40 L35 25 M50 40 L65 25" stroke="currentColor" stroke-width="3.5" fill="none" stroke-linecap="round" transform="rotate(240 50 50)"/>
              </svg>
            </div>
          </div>

          <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">
              WindFlow
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">Turbine Monitoring System</p>
          </div>
        </div>

        <!-- Center: Quick Stats (optional) -->
        <div class="hidden lg:flex items-center gap-6">
          <div class="flex items-center gap-2 px-4 py-2 rounded-lg bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700">
            <div class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></div>
            <span class="text-sm font-medium text-slate-700 dark:text-slate-300">All Systems Online</span>
          </div>

          <div v-if="activeAlarmsCount > 0" class="flex items-center gap-2 px-4 py-2 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800">
            <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            <span class="text-sm font-semibold text-red-700 dark:text-red-400">{{ activeAlarmsCount }} Active Alarm{{ activeAlarmsCount > 1 ? 's' : '' }}</span>
          </div>
        </div>

        <!-- Right: Actions & User -->
        <div class="flex items-center gap-3">
          <!-- Notifications -->
          <button
              class="relative p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white"
              aria-label="Notifications"
              @click="showNotifications = !showNotifications"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            <span v-if="activeAlarmsCount > 0" class="absolute -top-1 -right-1 flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-red-500 rounded-full">
              {{ activeAlarmsCount > 9 ? '9+' : activeAlarmsCount }}
            </span>
          </button>

          <!-- Theme Toggle -->
          <button
              @click="$emit('toggle-theme')"
              class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white"
              aria-label="Toggle theme"
          >
            <svg class="w-6 h-6 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <svg class="w-6 h-6 dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
            </svg>
          </button>

          <!-- User Menu -->
          <div class="relative">
            <button
                @click="showUserMenu = !showUserMenu"
                class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors group"
            >
              <!-- Avatar -->
              <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-semibold shadow-lg">
                {{ getInitials(user.name) }}
              </div>

              <!-- User Info (hidden on mobile) -->
              <div class="hidden md:block text-left">
                <p class="text-sm font-medium text-slate-900 dark:text-white">{{ user.name }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400">{{ user.role }}</p>
              </div>

              <!-- Chevron -->
              <svg
                  class="w-4 h-4 text-slate-400 transition-transform"
                  :class="{ 'rotate-180': showUserMenu }"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>

            <!-- Dropdown Menu -->
            <transition name="dropdown">
              <div
                  v-if="showUserMenu"
                  v-click-outside="() => showUserMenu = false"
                  class="absolute right-0 mt-2 w-64 bg-white dark:bg-slate-800 rounded-xl shadow-2xl border border-slate-200 dark:border-slate-700 py-2 z-50"
              >
                <!-- User Info in Dropdown (mobile) -->
                <div class="md:hidden px-4 py-3 border-b border-slate-200 dark:border-slate-700">
                  <p class="text-sm font-medium text-slate-900 dark:text-white">{{ user.name }}</p>
                  <p class="text-xs text-slate-500 dark:text-slate-400">{{ user.role }}</p>
                </div>

                <button
                    v-for="item in userMenuItems"
                    :key="item.label"
                    @click="handleMenuAction(item.action)"
                    class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors"
                >
                  <component :is="item.icon" class="w-5 h-5" />
                  <span>{{ item.label }}</span>
                </button>

                <div class="border-t border-slate-200 dark:border-slate-700 my-2"></div>

                <button
                    @click="handleLogout"
                    class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                  </svg>
                  <span>Logout</span>
                </button>
              </div>
            </transition>
          </div>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  user: {
    type: Object,
    default: () => ({ name: 'John Smith', role: 'Supervisor' })
  },
  activeAlarmsCount: {
    type: Number,
    default: 0
  }
})

const emit = defineEmits(['open-maintenance-form', 'toggle-theme'])

// State
const showUserMenu = ref(false)
const showNotifications = ref(false)

// User menu items
const userMenuItems = [
  {
    label: 'Profile',
    action: 'profile',
    icon: { template: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>' }
  },
  {
    label: 'Settings',
    action: 'settings',
    icon: { template: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>' }
  },
  {
    label: 'Help & Support',
    action: 'help',
    icon: { template: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>' }
  }
]

// Methods
const getInitials = (name) => {
  return name
      .split(' ')
      .map(n => n[0])
      .join('')
      .toUpperCase()
      .slice(0, 2)
}

const handleMenuAction = (action) => {
  showUserMenu.value = false
  console.log(`Menu action: ${action}`)
  // Implement navigation or actions here
}

const handleLogout = () => {
  showUserMenu.value = false
  console.log('Logging out...')
  // Implement logout logic
}

// Click outside directive (simple implementation)
const vClickOutside = {
  mounted(el, binding) {
    el.clickOutsideEvent = (event) => {
      if (!(el === event.target || el.contains(event.target))) {
        binding.value()
      }
    }
    document.addEventListener('click', el.clickOutsideEvent)
  },
  unmounted(el) {
    document.removeEventListener('click', el.clickOutsideEvent)
  }
}
</script>

<style scoped>
.animate-spin-slow {
  animation: spin 10s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Dropdown animation */
.dropdown-enter-active,
.dropdown-leave-active {
  transition: all 0.2s ease;
}

.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-10px) scale(0.95);
}
</style>