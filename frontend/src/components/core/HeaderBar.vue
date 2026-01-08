<template>
  <header
    class="bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 sticky top-0 z-50 backdrop-blur-lg bg-white/95 dark:bg-slate-900/95"
  >
    <div class="max-w-[1600px] mx-auto px-6 py-4">
      <div class="flex items-center justify-between">
        <!-- Left: Logo & Title -->
        <div class="flex items-center gap-4">
          <!-- Animated Turbine Logo -->
          <div class="relative group">
            <div
              class="absolute inset-0 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-xl blur-lg opacity-30 group-hover:opacity-50 transition-opacity"
            />
            <div
              class="relative bg-gradient-to-br from-indigo-500 to-purple-600 p-2.5 rounded-xl shadow-lg"
            >
              <svg
                viewBox="0 0 100 100"
                class="w-8 h-8 text-white animate-spin-slow"
                aria-hidden="true"
              >
                <circle
                  cx="50"
                  cy="50"
                  r="4"
                  fill="currentColor"
                />
                <path
                  d="M50 10 L50 40 L35 25 M50 40 L65 25"
                  stroke="currentColor"
                  stroke-width="3.5"
                  fill="none"
                  stroke-linecap="round"
                />
                <path
                  d="M50 10 L50 40 L35 25 M50 40 L65 25"
                  stroke="currentColor"
                  stroke-width="3.5"
                  fill="none"
                  stroke-linecap="round"
                  transform="rotate(120 50 50)"
                />
                <path
                  d="M50 10 L50 40 L35 25 M50 40 L65 25"
                  stroke="currentColor"
                  stroke-width="3.5"
                  fill="none"
                  stroke-linecap="round"
                  transform="rotate(240 50 50)"
                />
              </svg>
            </div>
          </div>

          <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">
              Zephyros Fieldlab
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">
              Turbine Monitoring System
            </p>
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
            <svg
              class="w-6 h-6"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
              />
            </svg>
            <span
              v-if="activeAlarmsCount > 0"
              class="absolute -top-1 -right-1 flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-red-500 rounded-full"
            >
              {{ activeAlarmsCount > 9 ? '9+' : activeAlarmsCount }}
            </span>
          </button>

          <div
            v-if="showNotifications"
            class="absolute right-0 top-full w-96 bg-white dark:bg-slate-800 rounded-lg shadow-2xl border border-slate-200 dark:border-slate-700 z-50"
          >
            <button
              v-for="alarm in latestAlarms"
              :key="alarm.id"
              :class="[
                'w-full p-4 transition-colors text-left group border-l-4',
                getPriorityClasses(alarm.priority),
              ]"
              @click="$router.push('/alarms')"
            >
              <div class="flex items-start gap-3">
                <div class="flex-1 min-w-0">
                  <!-- Turbine & Priority Row -->
                  <div class="flex items-center gap-2 mb-2">
                    <span class="text-xs font-semibold opacity-75">
                      Turbine {{ alarm.turbineId }}
                    </span>
                    <span
                      :class="[
                        'px-2 py-0.5 rounded text-xs font-bold uppercase tracking-wider',
                        getPriorityBadge(alarm.priority),
                      ]"
                    >
                      {{ alarm.priority }}
                    </span>
                  </div>

                  <!-- Title -->
                  <h4 class="font-semibold leading-snug">
                    {{ alarm.title }}
                  </h4>
                </div>
              </div>
            </button>
          </div>

          <!-- Theme Toggle -->
          <button
            class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white"
            aria-label="Toggle theme"
            @click="$emit('toggle-theme')"
          >
            <svg
              class="w-6 h-6 hidden dark:block"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"
              />
            </svg>
            <svg
              class="w-6 h-6 dark:hidden"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"
              />
            </svg>
          </button>

          <!-- User Menu -->
          <div class="relative">
            <button
              class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors group"
              @click="showUserMenu = !showUserMenu"
            >
              <!-- Avatar -->
              <div
                class="w-9 h-9 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-semibold shadow-lg"
              >
                {{ getInitials(user.name) }}
              </div>

              <!-- User Info (hidden on mobile) -->
              <div class="hidden md:block text-left">
                <p class="text-sm font-medium text-slate-900 dark:text-white">
                  {{ user.name }}
                </p>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                  {{ user.role }}
                </p>
              </div>

              <!-- Chevron -->
              <svg
                class="w-4 h-4 text-slate-400 transition-transform"
                :class="{ 'rotate-180': showUserMenu }"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M19 9l-7 7-7-7"
                />
              </svg>
            </button>

            <!-- Dropdown Menu -->
            <transition name="dropdown">
              <div
                v-if="showUserMenu"
                v-click-outside="() => (showUserMenu = false)"
                class="absolute right-0 mt-2 w-64 bg-white dark:bg-slate-800 rounded-xl shadow-2xl border border-slate-200 dark:border-slate-700 py-2 z-50"
              >
                <!-- User Info in Dropdown (mobile) -->
                <div class="md:hidden px-4 py-3 border-b border-slate-200 dark:border-slate-700">
                  <p class="text-sm font-medium text-slate-900 dark:text-white">
                    {{ user.name }}
                  </p>
                  <p class="text-xs text-slate-500 dark:text-slate-400">
                    {{ user.role }}
                  </p>
                </div>

                <!-- <button
                    v-for="item in userMenuItems"
                    :key="item.label"
                    @click="handleMenuAction(item.action)"
                    class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors"
                >
                  <component :is="item.icon" class="w-5 h-5" />
                  <span>{{ item.label }}</span>
                </button> -->

                <div class="border-t border-slate-200 dark:border-slate-700 my-2" />

                <button
                  class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                  @click="handleLogout"
                >
                  <svg
                    class="w-5 h-5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
                    />
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
import { useScadaService } from '@/composables/api.js'

defineProps({
  user: {
    type: Object,
    default: () => ({ name: 'John Smith', role: 'user' }),
  },
  activeAlarmsCount: {
    type: Number,
    default: 0,
  },
})

const { alarmStore } = useScadaService()

defineEmits(['open-maintenance-form', 'toggle-theme'])

// State
const showUserMenu = ref(false)
const showNotifications = ref(false)

// Methods
const getInitials = (name) => {
  return name
    .split(' ')
    .map((n) => n[0])
    .join('')
    .toUpperCase()
    .slice(0, 2)
}

const latestAlarms = computed(() => {
  const alarms = alarmStore.alarms || []
  return [...alarms].sort((a, b) => new Date(b.detectedAt) - new Date(a.detectedAt)).slice(0, 3)
})

const handleLogout = async () => {
  showUserMenu.value = false

  try {
    const csrfToken = getCsrfTokenFromCookie()

    // Call Laravel logout endpoint
    const response = await fetch('http://localhost:8000/user/logout', {
      method: 'POST',
      headers: {
        Accept: 'application/json',
        'X-XSRF-TOKEN': csrfToken,
        'X-Requested-With': 'XMLHttpRequest',
      },
      credentials: 'include', // Important: sends cookies
    })

    if (response.ok) {
      console.log('Logout successful')
    }
  } catch (error) {
    console.error('Logout error:', error)
  } finally {
    localStorage.clear()
    window.location.href = 'http://localhost:5173/login'
  }
}

const getCsrfTokenFromCookie = () => {
  const value = `; ${document.cookie}`
  const parts = value.split(`; XSRF-TOKEN=`)
  if (parts.length === 2) {
    return decodeURIComponent(parts.pop().split(';').shift())
  }
  return ''
}

// Click outside directive (simple implementation)
const vClickOutside = {
  mounted(el, binding) {
    setTimeout(() => {
      el.clickOutsideEvent = (event) => {
        if (!(el === event.target || el.contains(event.target))) {
          binding.value()
        }
      }
      document.addEventListener('click', el.clickOutsideEvent)
    }, 0)
  },
  unmounted(el) {
    document.removeEventListener('click', el.clickOutsideEvent)
  },
}

const getPriorityClasses = (priority) => {
  switch (priority.toLowerCase()) {
    case 'critical':
      return 'bg-red-50 dark:bg-red-950/30 hover:bg-red-100 dark:hover:bg-red-950/50 text-red-900 dark:text-red-100 border-red-500'
    case 'major':
      return 'bg-orange-50 dark:bg-orange-950/30 hover:bg-orange-100 dark:hover:bg-orange-950/50 text-orange-900 dark:text-orange-100 border-orange-500'
    case 'warning':
      return 'bg-yellow-50 dark:bg-yellow-950/30 hover:bg-yellow-100 dark:hover:bg-yellow-950/50 text-yellow-900 dark:text-yellow-100 border-yellow-500'
    case 'minor':
      return 'bg-blue-50 dark:bg-blue-950/30 hover:bg-blue-100 dark:hover:bg-blue-950/50 text-blue-900 dark:text-blue-100 border-blue-500'
    default:
      return 'bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-900 dark:text-slate-100 border-slate-300'
  }
}

const getPriorityBadge = (priority) => {
  switch (priority.toLowerCase()) {
    case 'critical':
      return 'bg-red-500 text-white'
    case 'major':
      return 'bg-orange-500 text-white'
    case 'warning':
      return 'bg-yellow-500 text-yellow-900'
    case 'minor':
      return 'bg-blue-500 text-white'
    default:
      return 'bg-slate-500 text-white'
  }
}
</script>

<style scoped>
.animate-spin-slow {
  animation: spin 10s linear infinite;
}

@keyframes spin {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
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
