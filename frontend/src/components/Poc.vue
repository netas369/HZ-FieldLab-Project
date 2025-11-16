<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-900 dark:to-slate-800">
    <div class="h-screen flex flex-col">
      <HeaderBar
        :user="state.currentUser"
        :active-alarms-count="criticalAlarmsCount"
        @open-maintenance-form="state.showMaintenanceForm = true"
        @toggle-theme="toggleTheme"
      />

      <div class="flex-1 flex overflow-hidden">
        <SidebarNav
          :active-tab="state.activeRoute"
          :tabs="navItems"
          :search-query="state.searchQuery"
          @update:active-tab="handleNavigation"
          @update:search-query="state.searchQuery = $event"
          @quick-link="handleQuickLink"
        />

        <main class="flex-1 overflow-auto bg-slate-50 dark:bg-slate-900">
          <div class="max-w-[1600px] mx-auto p-6">
            <transition name="slide-fade" mode="out-in">
              <component
                :is="currentView"
                v-bind="currentViewProps"
                @select-turbine="handleTurbineSelect"
                @show-alarm="handleShowAlarm"
                @acknowledge-alarm="handleAcknowledgeAlarm"
                @add-log="state.showMaintenanceForm = true"
                @add-maintenance="handleAddMaintenance"
                :key="state.activeRoute"
              />
            </transition>
          </div>
        </main>

        <aside
          v-if="showRightSidebar"
          class="w-80 bg-white dark:bg-slate-800 border-l border-slate-200 dark:border-slate-700 overflow-auto"
        >
          <QuickActionsPanel
            :alarms="activeAlarms"
            :recent-activity="recentActivity"
            @show-alarm="handleShowAlarm"
            @quick-action="handleQuickAction"
          />
        </aside>
      </div>
    </div>

    <Teleport to="body">
      <div
        v-if="state.selectedAlarm"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4"
        @click.self="state.selectedAlarm = null"
      >
         <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-auto">
          <div class="p-6">
            <div class="flex items-start justify-between mb-4">
              <div>
                <h3 class="text-2xl font-bold text-slate-900 dark:text-white">
                  {{ state.selectedAlarm.title }}
                </h3>
                <span :class="['inline-block mt-2 px-3 py-1 rounded-full text-sm font-bold', getPriorityClass(state.selectedAlarm.priority)]">
                  {{ state.selectedAlarm.priority }}
                </span>
              </div>
              <button
                @click="state.selectedAlarm = null"
                class="p-2 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors"
              >
                <svg class="w-6 h-6 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <div class="space-y-4">
              <div>
                <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Description</h4>
                <p class="text-slate-600 dark:text-slate-400">{{ state.selectedAlarm.description }}</p>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div>
                  <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Turbine</h4>
                  <p class="text-slate-900 dark:text-white font-medium">{{ state.selectedAlarm.turbine }}</p>
                </div>
                <div>
                  <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Time</h4>
                  <p class="text-slate-900 dark:text-white font-medium">{{ state.selectedAlarm.time }}</p>
                </div>
              </div>

              <div class="flex gap-3 pt-4">
                <button
                  v-if="!state.selectedAlarm.acknowledged"
                  @click="handleAcknowledgeAlarm(state.selectedAlarm)"
                  class="flex-1 px-4 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors"
                >
                  Acknowledge Alarm
                </button>
                <button
                  @click="state.selectedAlarm = null"
                  class="px-6 py-3 bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-900 dark:text-white rounded-lg font-medium transition-colors"
                >
                  Close
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

       <div
        v-if="state.showMaintenanceForm"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4"
        @click.self="state.showMaintenanceForm = false"
      >
         <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl max-w-2xl w-full">
          <div class="p-6">
            <div class="flex items-center justify-between mb-6">
              <h3 class="text-2xl font-bold text-slate-900 dark:text-white">Log Maintenance</h3>
              <button
                @click="state.showMaintenanceForm = false"
                class="p-2 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors"
              >
                <svg class="w-6 h-6 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <form @submit.prevent="handleMaintenanceSubmit" class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                  Turbine
                </label>
                <select
                  v-model="maintenanceForm.turbine"
                  class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-indigo-500"
                  required
                >
                  <option value="">Select a turbine</option>
                  <option v-for="turbine in turbineStore.turbines" :key="turbine.id" :value="turbine.id">
                    {{ turbine.id }} - {{ turbine.location }}
                  </option>
                </select>
              </div>
              
               <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                  Type
                </label>
                <select
                  v-model="maintenanceForm.type"
                  class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-indigo-500"
                  required
                >
                  <option value="Preventive">Preventive</option>
                  <option value="Corrective">Corrective</option>
                  <option value="Emergency">Emergency</option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                  Description
                </label>
                <textarea
                  v-model="maintenanceForm.description"
                  rows="4"
                  class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-indigo-500"
                  placeholder="Describe the maintenance work..."
                  required
                ></textarea>
              </div>

              <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                  Technician
                </label>
                <input
                  v-model="maintenanceForm.technician"
                  type="text"
                  class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-indigo-500"
                  placeholder="Technician name"
                  required
                />
              </div>

              <div class="flex gap-3 pt-4">
                <button
                  type="submit"
                  class="flex-1 px-4 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition-colors"
                >
                  Save Log
                </button>
                <button
                  type="button"
                  @click="state.showMaintenanceForm = false"
                  class="px-6 py-3 bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-900 dark:text-white rounded-lg font-medium transition-colors"
                >
                  Cancel
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { reactive, computed, onMounted, watch, ref } from 'vue'

// Import our redesigned components
import HeaderBar from './HeaderBar.vue'
import SidebarNav from './SidebarNav.vue'

// Import tab/view components
import OverviewTab from './overview/OverviewTab.vue'
import AnalyticsTab from './AnalyticsTab.vue'
import AlarmTab from './AlarmTab.vue'
import MaintenanceTab from './MaintenanceTab.vue'
import ReportsTab from "@/components/ReportsTab.vue";
import SettingsTab from "@/components/SettingsTab.vue";

// *** IMPORT THE NEW SERVICE ***
import { useScadaService } from '../composables/api.js'

// Placeholder components
const QuickActionsPanel = { template: '<div class="p-4"><h3 class="font-bold mb-4">Quick Actions</h3><p class="text-sm text-slate-600">Recent activity will appear here</p></div>' }

// *** GET ALL STORES AND METHODS FROM THE SERVICE ***
const { 
  turbineStore, 
  alarmStore, 
  maintenanceStore,
  fetchTurbines,
  fetchAlarms,
  fetchMaintenanceLogs
} = useScadaService()

// ============================================================================
// COMPONENT-LOCAL STATE
// This state is only for this component (view state)
// ============================================================================

const state = reactive({
  activeRoute: 'overview',
  currentUser: {
    name: 'John Smith',
    role: 'Supervisor',
    avatar: null
  },
  selectedTurbine: null,
  selectedAlarm: null,
  showMaintenanceForm: false,
  searchQuery: '',
  theme: 'light'
})

// Maintenance form state remains local
const maintenanceForm = reactive({
  turbine: '',
  type: 'Preventive',
  description: '',
  technician: state.currentUser.name
})

// ============================================================================
// ALL API-DRIVEN STORES AND FETCH LOGIC ARE NOW GONE
// They live in useScadaService.js
// ============================================================================

// ============================================================================
// COMPUTED PROPERTIES (Unchanged)
// These just work, because they read from the imported stores.
// ============================================================================

// Navigation items
const navItems = computed(() => [
  { id: 'overview', label: 'Overview', icon: 'dashboard', badge: null },
  { id: 'alarms', label: 'Alarms', icon: 'alert', badge: criticalAlarmsCount.value },
  { id: 'maintenance', label: 'Maintenance', icon: 'wrench', badge: null },
  { id: 'analytics', label: 'Analytics', icon: 'chart', badge: null },
  { id: 'reports', label: 'Reports', icon: 'file', badge: null },
  { id: 'settings', label: 'Settings', icon: 'settings', badge: null }
])

const currentView = computed(() => {
  const viewMap = {
    overview: OverviewTab,
    performance: OverviewTab,
    alarms: AlarmTab,
    maintenance: MaintenanceTab,
    analytics: AnalyticsTab,
    reports: ReportsTab,
    settings: SettingsTab
  }
  return viewMap[state.activeRoute] || OverviewTab
})

const currentViewProps = computed(() => {
  switch (state.activeRoute) {
    case 'overview':
    case 'performance':
      return {
        turbines: filteredTurbines.value,
        selectedTurbine: state.selectedTurbine,
        searchQuery: state.searchQuery,
        loading: turbineStore.loading, // Pass loading state
        error: turbineStore.error       // Pass error state
      }

    case 'alarms':
      return {
        alarms: alarmStore.alarms,
        filters: ['All', 'Critical', 'Major', 'Warning', 'Minor'],
        initialFilter: 'All',
        loading: alarmStore.loading,    // Pass loading state
        error: alarmStore.error        // Pass error state
      }

    case 'maintenance':
      return {
        maintenanceLogs: maintenanceStore.logs,
        predictiveInsights: [],
        maintenanceTabs: ['Recent', 'Scheduled', 'Predictive', 'Component History'],
        activeMaintenanceTab: 'Recent',
        loading: maintenanceStore.loading, // Pass loading state
        error: maintenanceStore.error      // Pass error state
      }

    case 'analytics':
      return {
        kpis: [],
        charts: []
      }

    default:
      return {}
  }
})

const filteredTurbines = computed(() => {
  if (!state.searchQuery) return turbineStore.turbines
  const query = state.searchQuery.toLowerCase()
  return turbineStore.turbines.filter(t =>
      t.id.toLowerCase().includes(query) || t.location.toLowerCase().includes(query)
  )
})

const activeAlarms = computed(() => alarmStore.activeAlarms)
const criticalAlarmsCount = computed(() => alarmStore.criticalCount)
const showRightSidebar = computed(() => ['overview', 'performance'].includes(state.activeRoute))

const recentActivity = computed(() => {
  return alarmStore.recentAlarms(3).map(alarm => ({
    type: 'alarm',
    timestamp: alarm.time,
    message: `${alarm.title} on ${alarm.turbine}`,
    severity: alarm.priority
  }))
})

// ============================================================================
// METHODS (Unchanged)
// These also just work, as they call methods on the imported stores.
// ============================================================================

const handleNavigation = (routeId) => {
  state.activeRoute = routeId
}

const handleTurbineSelect = (turbine) => {
  state.selectedTurbine = turbine
  turbineStore.selectTurbine(turbine.id)
}

const handleShowAlarm = (alarm) => {
  state.selectedAlarm = alarm
}

const handleAcknowledgeAlarm = async (alarm) => {
  await alarmStore.acknowledgeAlarm(alarm.id) // Calls the service method
  state.selectedAlarm = null
  console.log('✓ Alarm acknowledged')
}

const handleAddMaintenance = (turbine) => {
  state.selectedTurbine = turbine
  state.showMaintenanceForm = true
}

const handleMaintenanceSubmit = async () => {
  await maintenanceStore.addLog({ ...maintenanceForm }) // Calls the service method
  state.showMaintenanceForm = false

  // Reset form
  maintenanceForm.turbine = ''
  maintenanceForm.type = 'Preventive'
  maintenanceForm.description = ''
  
  console.log('✓ Maintenance log saved')
}

const handleQuickAction = (action) => {
  console.log('Quick action:', action)
}

const handleQuickLink = (action) => {
  console.log('Quick link:', action)
}

const toggleTheme = () => {
  state.theme = state.theme === 'light' ? 'dark' : 'light'
  document.documentElement.classList.toggle('dark')
}

const getPriorityClass = (priority) => {
  const classes = {
    Critical: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    Major: 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
    Warning: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
    Minor: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'
  }
  return classes[priority] || 'bg-slate-100 text-slate-700'
}

// ============================================================================
// LIFECYCLE (Unchanged)
// This now calls the fetch methods from our service.
// ============================================================================

onMounted(async () => {
  // 1. Fetch turbines first. This is now mandatory.
  await fetchTurbines()
  
  // 2. Once turbines are loaded, fetch alarms and maintenance.
  //    fetchAlarms can now loop through the turbineStore.turbines list.
  await Promise.all([
    fetchAlarms(),
    fetchMaintenanceLogs() // This can run in parallel with fetchAlarms
  ])

  // 3. Select the first turbine as default
  if (turbineStore.turbines.length > 0) {
    handleTurbineSelect(turbineStore.turbines[0])
  }
})

// Watcher (Unchanged)
watch(() => alarmStore.criticalCount, (newCount, oldCount) => {
  if (newCount > oldCount) {
    console.log('⚠️ New critical alarm!')
  }
})
</script>

<style scoped>
/* STYLES ARE UNCHANGED */
.slide-fade-enter-active {
  transition: all 0.2s ease-out;
}
.slide-fade-leave-active {
  transition: all 0.15s ease-in;
}
.slide-fade-enter-from {
  transform: translateX(10px);
  opacity: 0;
}
.slide-fade-leave-to {
  transform: translateX(-10px);
  opacity: 0;
}
</style>