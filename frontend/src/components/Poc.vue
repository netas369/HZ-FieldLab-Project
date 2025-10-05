<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-900 dark:to-slate-800">
    <div class="h-screen flex flex-col">
      <!-- Header -->
      <AppHeader
          :user="state.currentUser"
          :active-alarms-count="criticalAlarmsCount"
          @toggle-theme="toggleTheme"
      />

      <div class="flex-1 flex overflow-hidden">
        <!-- Sidebar Navigation -->
        <AppSidebar
            :active-route="state.activeRoute"
            :nav-items="navItems"
            @navigate="handleNavigation"
        />

        <!-- Main Content Area -->
        <main class="flex-1 overflow-auto">
          <div class="max-w-[1600px] mx-auto p-6">
            <transition name="slide-fade" mode="out-in">
              <component
                  :is="currentView"
                  v-bind="currentViewProps"
                  @select-turbine="handleTurbineSelect"
                  @show-alarm="handleShowAlarm"
                  @acknowledge-alarm="handleAcknowledgeAlarm"
                  @add-maintenance="handleAddMaintenance"
                  :key="state.activeRoute"
              />
            </transition>
          </div>
        </main>

        <!-- Right Sidebar - Notifications & Quick Actions -->
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

    <!-- Modal System -->
    <Teleport to="body">
      <AlarmDetailModal
          v-if="state.selectedAlarm"
          :alarm="state.selectedAlarm"
          @close="state.selectedAlarm = null"
          @acknowledge="handleAcknowledgeAlarm"
      />

      <MaintenanceFormModal
          v-if="state.showMaintenanceForm"
          :turbine="state.selectedTurbine"
          @close="state.showMaintenanceForm = false"
          @submit="handleMaintenanceSubmit"
      />
    </Teleport>
  </div>
</template>

<script setup>
import { reactive, computed, provide, onMounted, watch } from 'vue'
import { useTurbineStore } from '../stores/useTurbineStore.js'
import { useAlarmStore } from '../stores/useAlarmStore.js'
import { useMaintenanceStore } from '../stores/useMaintenanceStore.js'

// Component imports (placeholder - implement these separately)
import AppHeader from './components/AppHeader.vue'
import AppSidebar from './components/AppSidebar.vue'
import QuickActionsPanel from './components/QuickActionsPanel.vue'
import AlarmDetailModal from './components/AlarmDetailModal.vue'
import MaintenanceFormModal from './components/MaintenanceFormModal.vue'

// View imports
import DashboardView from './views/DashboardView.vue'
import PerformanceView from './views/PerformanceView.vue'
import AlarmsView from './views/AlarmsView.vue'
import MaintenanceView from './views/MaintenanceView.vue'
import AnalyticsView from './views/AnalyticsView.vue'
import ReportsView from './views/ReportsView.vue'
import SettingsView from './views/SettingsView.vue'

// ============================================================================
// STATE MANAGEMENT with Composables
// ============================================================================

const turbineStore = useTurbineStore()
const alarmStore = useAlarmStore()
const maintenanceStore = useMaintenanceStore()

const state = reactive({
  activeRoute: 'dashboard',
  currentUser: {
    name: 'John Smith',
    role: 'Supervisor',
    avatar: null
  },
  selectedTurbine: null,
  selectedAlarm: null,
  showMaintenanceForm: false,
  theme: 'light'
})

// Navigation configuration
const navItems = [
  { id: 'dashboard', icon: 'LayoutDashboard', label: 'Dashboard', badge: null },
  { id: 'performance', icon: 'Zap', label: 'Performance', badge: null },
  { id: 'alarms', icon: 'AlertTriangle', label: 'Alarms', badge: computed(() => criticalAlarmsCount.value) },
  { id: 'maintenance', icon: 'Wrench', label: 'Maintenance', badge: null },
  { id: 'analytics', icon: 'TrendingUp', label: 'Analytics', badge: null },
  { id: 'reports', icon: 'FileText', label: 'Reports', badge: null },
  { id: 'settings', icon: 'Settings', label: 'Settings', badge: null }
]

// ============================================================================
// COMPUTED PROPERTIES
// ============================================================================

const currentView = computed(() => {
  const viewMap = {
    dashboard: DashboardView,
    performance: PerformanceView,
    alarms: AlarmsView,
    maintenance: MaintenanceView,
    analytics: AnalyticsView,
    reports: ReportsView,
    settings: SettingsView
  }
  return viewMap[state.activeRoute] || DashboardView
})

const currentViewProps = computed(() => {
  const baseProps = {
    turbines: turbineStore.turbines,
    selectedTurbine: state.selectedTurbine
  }

  switch (state.activeRoute) {
    case 'dashboard':
      return {
        ...baseProps,
        metrics: turbineStore.aggregateMetrics,
        recentAlarms: alarmStore.recentAlarms(5)
      }

    case 'performance':
      return {
        ...baseProps,
        kpis: turbineStore.kpis,
        historicalData: turbineStore.historicalData
      }

    case 'alarms':
      return {
        alarms: alarmStore.alarms,
        filters: alarmStore.availableFilters
      }

    case 'maintenance':
      return {
        logs: maintenanceStore.logs,
        scheduled: maintenanceStore.scheduled,
        predictions: maintenanceStore.predictions
      }

    case 'analytics':
      return {
        ...baseProps,
        analyticsData: turbineStore.analyticsData
      }

    case 'reports':
      return {
        reports: [] // Implement report generation
      }

    case 'settings':
      return {
        user: state.currentUser,
        preferences: {} // User preferences
      }

    default:
      return baseProps
  }
})

const activeAlarms = computed(() => alarmStore.activeAlarms)
const criticalAlarmsCount = computed(() => alarmStore.criticalCount)
const showRightSidebar = computed(() => ['dashboard', 'performance'].includes(state.activeRoute))

const recentActivity = computed(() => {
  // Combine recent alarms and maintenance into activity feed
  const activities = []

  alarmStore.recentAlarms(3).forEach(alarm => {
    activities.push({
      type: 'alarm',
      timestamp: alarm.time,
      message: `${alarm.title} on ${alarm.turbine}`,
      severity: alarm.priority
    })
  })

  return activities.sort((a, b) => new Date(b.timestamp) - new Date(a.timestamp))
})

// ============================================================================
// METHODS & EVENT HANDLERS
// ============================================================================

const handleNavigation = (routeId) => {
  state.activeRoute = routeId
  // Could integrate with Vue Router here
}

const handleTurbineSelect = (turbine) => {
  state.selectedTurbine = turbine
  turbineStore.selectTurbine(turbine.id)
}

const handleShowAlarm = (alarm) => {
  state.selectedAlarm = alarm
}

const handleAcknowledgeAlarm = (alarm) => {
  alarmStore.acknowledgeAlarm(alarm.id)
  state.selectedAlarm = null

  // Show success notification
  showNotification('Alarm acknowledged successfully', 'success')
}

const handleAddMaintenance = (turbine) => {
  state.selectedTurbine = turbine
  state.showMaintenanceForm = true
}

const handleMaintenanceSubmit = (maintenanceData) => {
  maintenanceStore.addLog(maintenanceData)
  state.showMaintenanceForm = false

  showNotification('Maintenance log added', 'success')
}

const handleQuickAction = (action) => {
  // Handle quick actions from right sidebar
  console.log('Quick action:', action)
}

const toggleTheme = () => {
  state.theme = state.theme === 'light' ? 'dark' : 'light'
  // Implement theme switching logic
}

const showNotification = (message, type = 'info') => {
  // Implement notification system
  console.log(`[${type}] ${message}`)
}

// ============================================================================
// LIFECYCLE & INITIALIZATION
// ============================================================================

onMounted(async () => {
  // Initialize stores
  await turbineStore.fetchTurbines()
  await alarmStore.fetchAlarms()
  await maintenanceStore.fetchLogs()

  // Select first turbine by default
  if (turbineStore.turbines.length > 0) {
    handleTurbineSelect(turbineStore.turbines[0])
  }

  // Setup real-time updates (WebSocket, polling, etc.)
  setupRealTimeUpdates()
})

const setupRealTimeUpdates = () => {
  // Implement WebSocket or polling for real-time data
  // Example: setInterval(() => alarmStore.fetchAlarms(), 30000)
}

// Provide state to child components if needed
provide('app', {
  state,
  handleNavigation,
  handleTurbineSelect
})

// Watch for critical alarms and show notifications
watch(() => alarmStore.criticalCount, (newCount, oldCount) => {
  if (newCount > oldCount) {
    showNotification('New critical alarm detected!', 'error')
  }
})
</script>

<style scoped>
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

/* Dark mode styles */
:deep(.dark) {
  color-scheme: dark;
}
</style>