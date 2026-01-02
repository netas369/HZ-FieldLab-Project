<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-900 dark:to-slate-800">
    <div class="h-screen flex flex-col">
      <!-- Header -->
      <HeaderBar
          :user="state.currentUser"
          :active-alarms-count="criticalAlarmsCount"
          @open-maintenance-form="state.showMaintenanceForm = true"
          @toggle-theme="toggleTheme"
      />

      <div class="flex-1 flex overflow-hidden">
        <!-- Sidebar -->
        <SidebarNav
            :active-tab="currentRoute"
            :tabs="navItems"
            :search-query="state.searchQuery"
            @update:active-tab="handleNavigation"
            @update:search-query="state.searchQuery = $event"
            @quick-link="handleQuickLink"
        />

        <!-- Main Content Area -->
        <main class="flex-1 overflow-auto bg-slate-50 dark:bg-slate-900">
          <div class="max-w-[1600px] mx-auto p-6">
            <!-- Router View with transition -->
            <router-view
                v-slot="{ Component }"
                :key="$route.path"
            >
              <transition name="slide-fade" mode="out-in">

                <component
                    :is="Component"
                    @select-turbine="handleTurbineSelect"
                    @show-alarm="handleShowAlarm"
                    @acknowledge-alarm="handleAcknowledgeAlarm"
                    @add-log="state.showMaintenanceForm = true"
                    @add-maintenance="handleAddMaintenance"
                />
              </transition>

            </router-view>
          </div>
        </main>
      </div>
    </div>

    <!-- Modals (Teleported to body) -->
    <Teleport to="body">
      <!-- Alarm Detail Modal -->
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
                <span
                    :class="['inline-block mt-2 px-3 py-1 rounded-full text-sm font-bold', getPriorityClass(state.selectedAlarm.priority)]"
                >
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

              <div class="flex flex-wrap gap-3 pt-4">
                <button
                    v-if="!state.selectedAlarm.acknowledged && state.selectedAlarm.status !== 'resolved'"
                    @click="handleAcknowledgeAlarm(state.selectedAlarm)"
                    class="flex-1 min-w-[140px] px-4 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors"
                >
                  Acknowledge
                </button>
                <button
                    v-if="state.selectedAlarm.status !== 'resolved'"
                    @click="handleResolveAlarm(state.selectedAlarm)"
                    class="flex-1 min-w-[140px] px-4 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-medium transition-colors"
                >
                  Resolve
                </button>
                <button
                    v-if="state.selectedAlarm.status !== 'resolved'"
                    @click="handleCreateMaintenanceFromAlarm(state.selectedAlarm)"
                    class="flex-1 min-w-[140px] px-4 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition-colors"
                >
                  Create Maintenance
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

      <!-- Maintenance Form Modal -->
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

      <!-- Create Maintenance From Alarm Modal -->
      <div
          v-if="state.showMaintenanceModal"
          class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4"
          @click.self="handleCloseMaintenanceModal"
      >
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
          <div class="p-6 border-b border-slate-200 dark:border-slate-700">
            <h3 class="text-xl font-bold text-slate-900 dark:text-white">Create Maintenance Task</h3>
          </div>
          <form @submit.prevent="handleSubmitMaintenanceFromAlarm" class="p-6 space-y-4">
            <!-- Linked Alarm Notice -->
            <div v-if="newMaintenanceTask.alarm_id" class="p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
              <div class="flex items-center gap-2 text-sm text-amber-800 dark:text-amber-300">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span class="font-medium">Creating from alarm</span>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Turbine *</label>
                <select v-model="newMaintenanceTask.turbine_id" required class="w-full px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white">
                  <option value="">Select turbine</option>
                  <option v-for="t in turbineStore.turbines" :key="t._api_id" :value="t._api_id">{{ t.id }}</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Assign To</label>
                <select v-model="newMaintenanceTask.assigned_to" class="w-full px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white">
                  <option value="">Unassigned</option>
                  <option v-for="user in usersStore.users" :key="user.id" :value="user.id">
                    {{ user.name }} ({{ user.role }})
                  </option>
                </select>
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Title *</label>
              <input v-model="newMaintenanceTask.title" required type="text" class="w-full px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white" placeholder="e.g., Gearbox inspection" />
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Description</label>
              <textarea v-model="newMaintenanceTask.description" rows="3" class="w-full px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white" placeholder="Detailed description of the task..."></textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Priority</label>
                <select v-model="newMaintenanceTask.priority" class="w-full px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white">
                  <option value="low">Low</option>
                  <option value="medium">Medium</option>
                  <option value="high">High</option>
                  <option value="urgent">Urgent</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Status</label>
                <select v-model="newMaintenanceTask.status" class="w-full px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white">
                  <option value="scheduled">Scheduled</option>
                  <option value="in_progress">In Progress</option>
                </select>
              </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Scheduled Date</label>
                <input v-model="newMaintenanceTask.scheduled_date" type="datetime-local" class="w-full px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white" />
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Due Date</label>
                <input v-model="newMaintenanceTask.due_date" type="datetime-local" class="w-full px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white" />
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Estimated Duration (minutes)</label>
              <input v-model.number="newMaintenanceTask.estimated_duration_minutes" type="number" min="1" class="w-full px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white" placeholder="e.g., 120" />
            </div>
            <div class="flex justify-end gap-3 pt-4">
              <button type="button" @click="handleCloseMaintenanceModal" class="px-4 py-2 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors">
                Cancel
              </button>
              <button type="submit" :disabled="creatingMaintenance" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition-colors disabled:opacity-50">
                {{ creatingMaintenance ? 'Creating...' : 'Create Task' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { reactive, ref, computed, onMounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import HeaderBar from '@/components/core/HeaderBar.vue'
import SidebarNav from '@/components/core/SidebarNav.vue'
import { useScadaService } from '@/composables/api.js'

const router = useRouter()
const route = useRoute()

// Get stores and methods from service
const {
  turbineStore,
  alarmStore,
  maintenanceStore,
  usersStore,
  fetchDashboard,
  fetchAlarms,
  fetchMaintenanceLogs,
  fetchUsers
} = useScadaService()

// ============================================================================
// STATE
// ============================================================================

const state = reactive({
  currentUser: {
    name: 'John Smith',
    role: JSON.parse(localStorage.getItem('user'))?.role,
    avatar: null
  },
  selectedTurbine: null,
  selectedAlarm: null,
  showMaintenanceForm: false,
  showMaintenanceModal: false,
  maintenancePrefillData: null,
  searchQuery: '',
  theme: localStorage.getItem('theme') || 'dark'
})

const maintenanceForm = reactive({
  turbine: '',
  type: 'Preventive',
  description: '',
  technician: state.currentUser.name
})

// Form for new maintenance task (from alarm)
const newMaintenanceTask = reactive({
  turbine_id: '',
  alarm_id: null,
  assigned_to: '',
  title: '',
  description: '',
  priority: 'medium',
  status: 'scheduled',
  scheduled_date: '',
  due_date: '',
  estimated_duration_minutes: null
})

const creatingMaintenance = ref(false)

// ============================================================================
// COMPUTED
// ============================================================================

const currentRoute = computed(() => {
  // Map route name to tab id for SidebarNav
  const routeMap = {
    'Overview': 'overview',
    'Alarms': 'alarms',
    'Maintenance': 'maintenance',
    'Analytics': 'analytics',
    'DataImport': 'import',
    // 'Reports': 'reports',
    'Settings': 'settings'
  }
  return routeMap[route.name] || 'overview'
})

const navItems = computed(() => [
  { id: 'overview', label: 'Overview', icon: 'dashboard', badge: null },
  { id: 'alarms', label: 'Alarms', icon: 'alert', badge: criticalAlarmsCount.value },
  { id: 'maintenance', label: 'Maintenance', icon: 'wrench', badge: null },
  { id: 'analytics', label: 'Analytics', icon: 'chart', badge: null },
  { id: 'import', label: 'Imports', icon: 'import', badge: null },
  // { id: 'reports', label: 'Reports', icon: 'file', badge: null },
  { id: 'settings', label: 'Settings', icon: 'settings', badge: null }
])

const criticalAlarmsCount = computed(() => alarmStore.criticalCount)

// ============================================================================
// METHODS
// ============================================================================

const handleNavigation = (tabId) => {
  // Convert tab id to route name
  const routeMap = {
    'overview': 'Overview',
    'alarms': 'Alarms',
    'maintenance': 'Maintenance',
    'analytics': 'Analytics',
    'import': 'DataImport',
    'reports': 'Reports',
    'settings': 'Settings'
  }

  const routeName = routeMap[tabId]
  if (routeName && route.name !== routeName) {
    router.push({ name: routeName })
  }
}

const handleTurbineSelect = (turbine) => {
  state.selectedTurbine = turbine
  turbineStore.selectTurbine(turbine._api_id)

  console.log(turbine);


  // Navigate to turbine detail page
  router.push({ name: 'TurbineDetail', params: { id: turbine._api_id } })
}

const handleShowAlarm = (alarm) => {
  state.selectedAlarm = alarm
}

const handleAcknowledgeAlarm = async (alarm) => {
  await alarmStore.acknowledgeAlarm(alarm.id, alarm.turbineId)
  state.selectedAlarm = null
  console.log('Alarm acknowledged')
}

const handleResolveAlarm = async (alarm, notes = null) => {
  await alarmStore.resolveAlarm(alarm.id, alarm.turbineId, notes)
  state.selectedAlarm = null
  console.log('Alarm resolved')
}

const handleCreateMaintenanceFromAlarm = (alarm) => {
  // Map alarm severity to maintenance priority
  const priorityMap = {
    'failed': 'urgent',
    'critical': 'high',
    'warning': 'medium'
  }

  // Populate the form with alarm data
  newMaintenanceTask.turbine_id = alarm.turbineId
  newMaintenanceTask.alarm_id = alarm.id
  newMaintenanceTask.title = `Maintenance: ${alarm.component || alarm.title}`
  newMaintenanceTask.description = alarm.description || alarm.title
  newMaintenanceTask.priority = priorityMap[alarm.severity] || 'medium'
  newMaintenanceTask.assigned_to = ''
  newMaintenanceTask.status = 'scheduled'
  newMaintenanceTask.scheduled_date = ''
  newMaintenanceTask.due_date = ''
  newMaintenanceTask.estimated_duration_minutes = null

  // Close alarm modal and show maintenance modal
  state.selectedAlarm = null
  state.showMaintenanceModal = true
}

const handleCloseMaintenanceModal = () => {
  state.showMaintenanceModal = false
  // Reset form
  Object.assign(newMaintenanceTask, {
    turbine_id: '',
    alarm_id: null,
    assigned_to: '',
    title: '',
    description: '',
    priority: 'medium',
    status: 'scheduled',
    scheduled_date: '',
    due_date: '',
    estimated_duration_minutes: null
  })
}

const handleSubmitMaintenanceFromAlarm = async () => {
  creatingMaintenance.value = true
  try {
    const taskData = { ...newMaintenanceTask }
    // Remove empty values
    if (!taskData.assigned_to) delete taskData.assigned_to
    if (!taskData.alarm_id) delete taskData.alarm_id
    if (!taskData.scheduled_date) delete taskData.scheduled_date
    if (!taskData.due_date) delete taskData.due_date
    if (!taskData.estimated_duration_minutes) delete taskData.estimated_duration_minutes

    await maintenanceStore.createTask(taskData)
    handleCloseMaintenanceModal()
    console.log('Maintenance task created from alarm')
  } catch (err) {
    console.error('Failed to create maintenance from alarm:', err)
  } finally {
    creatingMaintenance.value = false
  }
}

const handleAddMaintenance = (turbine) => {
  state.selectedTurbine = turbine
  maintenanceForm.turbine = turbine.id
  state.showMaintenanceForm = true
}

const handleMaintenanceSubmit = async () => {
  try {
    await maintenanceStore.createTask({
      turbine_id: state.selectedTurbine?._api_id,
      title: maintenanceForm.type + ' maintenance',
      description: maintenanceForm.description,
      priority: 'medium',
      status: 'scheduled'
    })
  } catch (err) {
    console.error('Failed to create maintenance:', err)
  }
  state.showMaintenanceForm = false

  // Reset form
  maintenanceForm.turbine = ''
  maintenanceForm.type = 'Preventive'
  maintenanceForm.description = ''

  console.log('Maintenance task created')
}

const handleQuickLink = (action) => {
  console.log('Quick link:', action)
  // Handle quick link actions (export, docs, support)
}

const toggleTheme = () => {
  state.theme = state.theme === 'light' ? 'dark' : 'light';
  document.documentElement.classList.toggle('dark');
  localStorage.setItem('theme', state.theme);
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
// LIFECYCLE
// ============================================================================

onMounted(async () => {
  // Fetch all data on mount
  await fetchDashboard()
  await Promise.all([
    // fetchAlarms(),
    fetchMaintenanceLogs(),
    fetchUsers()
  ]);
})

// Watch for critical alarms
watch(() => alarmStore.criticalCount, (newCount, oldCount) => {
  if (newCount > oldCount) {
    console.log('⚠️ New critical alarm!')
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
</style>