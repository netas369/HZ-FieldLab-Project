<template>
  <aside
    class="w-64 bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 flex flex-col h-full"
  >
    <div class="flex flex-col h-full overflow-hidden">
      <!-- Navigation -->
      <nav class="flex-1 overflow-y-auto py-4 px-3 scrollbar-thin" aria-label="Main navigation">
        <ul class="space-y-1">
          <li v-for="tab in visibleTabs" :key="tab.id">
            <button
              type="button"
              :class="[
                'w-full group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all',
                activeTab === tab.id
                  ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30'
                  : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white',
              ]"
              :aria-current="activeTab === tab.id ? 'page' : false"
              @click="selectTab(tab.id)"
            >
              <!-- Icon -->
              <div
                :class="[
                  'flex items-center justify-center',
                  activeTab === tab.id
                    ? ''
                    : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-600 dark:group-hover:text-slate-400',
                ]"
              >
                <component :is="getIcon(tab.icon)" class="w-5 h-5" />
              </div>

              <!-- Label -->
              <span class="flex-1 text-left">{{ tab.label }}</span>

              <!-- Badge -->
              <span
                v-if="tab.badge && tab.badge > 0"
                :class="[
                  'px-2 py-0.5 rounded-full text-xs font-bold',
                  activeTab === tab.id
                    ? 'bg-white/20 text-white'
                    : 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400',
                ]"
              >
                {{ tab.badge > 99 ? '99+' : tab.badge }}
              </span>

              <!-- Active Indicator -->
              <div
                v-if="activeTab === tab.id"
                class="w-1 h-6 bg-white rounded-full absolute right-0"
              />
            </button>
          </li>
        </ul>

        <!-- Divider -->
        <div class="my-4 border-t border-slate-200 dark:border-slate-800" />

        <!-- Secondary Navigation -->
        <!--        <div class="space-y-1">-->
        <!--          <p class="px-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">-->
        <!--            Quick Links-->
        <!--          </p>-->

        <!--          <button-->
        <!--              v-for="link in quickLinks"-->
        <!--              :key="link.id"-->
        <!--              type="button"-->
        <!--              @click="handleQuickLink(link.action)"-->
        <!--              class="w-full group flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white transition-all"-->
        <!--          >-->
        <!--            <div class="text-slate-400 dark:text-slate-500 group-hover:text-slate-600 dark:group-hover:text-slate-400">-->
        <!--              <component :is="getIcon(link.icon)" class="w-5 h-5" />-->
        <!--            </div>-->
        <!--            <span class="flex-1 text-left">{{ link.label }}</span>-->
        <!--          </button>-->
        <!--        </div>-->
      </nav>

      <!-- Footer Info -->
      <!-- <div class="p-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50">
        <div class="flex items-center gap-3 mb-3">
          <div class="flex-1">
            <div class="flex items-center gap-2 mb-1">
              <div class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></div>
              <span class="text-xs font-medium text-slate-700 dark:text-slate-300">Live Data</span>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400">Updated just now</p>
          </div>
        </div>
      </div> -->
    </div>
  </aside>
</template>

<script setup>
import { ref, watch, computed } from 'vue'
import { useAuth } from '@/composables/useAuth'
const { hasRole } = useAuth()

const props = defineProps({
  searchQuery: {
    type: String,
    default: '',
  },
  activeTab: {
    type: String,
    default: 'overview',
  },
  tabs: {
    type: Array,
    default: () => [
      { id: 'overview', label: 'Overview', icon: 'dashboard', badge: null },
      { id: 'alarms', label: 'Alarms', icon: 'alert', badge: 3 },
      { id: 'maintenance', label: 'Maintenance', icon: 'wrench', badge: null },
      { id: 'analytics', label: 'Analytics', icon: 'chart', badge: null },
      { id: 'import', label: 'Imports', icon: 'import', badge: null },
      // { id: 'reports', label: 'Reports', icon: 'file', badge: null },
      { id: 'settings', label: 'Settings', icon: 'settings', badge: null },
    ],
  },
})

const emit = defineEmits(['update:search-query', 'update:active-tab', 'quick-link'])

// Local search state
const localSearchQuery = ref(props.searchQuery)

// Watch search input and emit updates
watch(localSearchQuery, (val) => {
  emit('update:search-query', val)
})

// Sync with parent prop changes
watch(
  () => props.searchQuery,
  (val) => {
    localSearchQuery.value = val
  }
)

// Methods
const selectTab = (tabId) => {
  emit('update:active-tab', tabId)
}

const getIcon = (iconName) => {
  const icons = {
    dashboard: {
      template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v3a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 16a1 1 0 011-1h4a1 1 0 011 1v3a1 1 0 01-1 1H5a1 1 0 01-1-1v-3zM14 12a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1h-4a1 1 0 01-1-1v-7z" />
      </svg>`,
    },
    zap: {
      template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
      </svg>`,
    },
    alert: {
      template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
      </svg>`,
    },
    wrench: {
      template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
      </svg>`,
    },
    chart: {
      template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
      </svg>`,
    },
    file: {
      template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
      </svg>`,
    },
    settings: {
      template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
      </svg>`,
    },
    download: {
      template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
      </svg>`,
    },
    book: {
      template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
      </svg>`,
    },
    help: {
      template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>`,
    },
  }

  return icons[iconName] || icons.dashboard
}

const visibleTabs = computed(() => {
  return props.tabs.filter((item) => {
    if (!item.roles) return true

    return hasRole(item.roles)
  })
})
</script>

<style scoped>
@reference 'tailwindcss';
/* Scrollbar styling */
.scrollbar-thin::-webkit-scrollbar {
  width: 6px;
}

.scrollbar-thin::-webkit-scrollbar-track {
  @apply bg-transparent;
}

.scrollbar-thin::-webkit-scrollbar-thumb {
  @apply bg-slate-300 dark:bg-slate-700 rounded-full hover:bg-slate-400 dark:hover:bg-slate-600;
}
</style>
