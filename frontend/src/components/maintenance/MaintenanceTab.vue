<template>
  <div class="space-y-6">
    <!-- Header with Actions -->
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white">
          Maintenance Management
        </h2>
        <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
          Track and schedule turbine maintenance activities
        </p>
      </div>

      <button
        class="flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition-colors shadow-lg shadow-indigo-500/30"
        @click="showCreateModal = true"
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
            d="M12 4v16m8-8H4"
          />
        </svg>
        New Maintenance Task
      </button>
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
      <div
        class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 rounded-xl p-4 border border-blue-200 dark:border-blue-800"
      >
        <div class="flex items-center justify-between mb-2">
          <span class="text-sm font-medium text-blue-900 dark:text-blue-300">Total Tasks</span>
          <div class="p-2 bg-blue-500 rounded-lg">
            <svg
              class="w-4 h-4 text-white"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
              />
            </svg>
          </div>
        </div>
        <p class="text-3xl font-bold text-blue-900 dark:text-blue-100">
          {{ stats.total }}
        </p>
      </div>

      <div
        class="bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-900/20 dark:to-slate-800/20 rounded-xl p-4 border border-slate-200 dark:border-slate-800"
      >
        <div class="flex items-center justify-between mb-2">
          <span class="text-sm font-medium text-slate-900 dark:text-slate-300">Scheduled</span>
          <div class="p-2 bg-slate-500 rounded-lg">
            <svg
              class="w-4 h-4 text-white"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
              />
            </svg>
          </div>
        </div>
        <p class="text-3xl font-bold text-slate-900 dark:text-slate-100">
          {{ stats.scheduled }}
        </p>
      </div>

      <div
        class="bg-gradient-to-br from-amber-50 to-amber-100 dark:from-amber-900/20 dark:to-amber-800/20 rounded-xl p-4 border border-amber-200 dark:border-amber-800"
      >
        <div class="flex items-center justify-between mb-2">
          <span class="text-sm font-medium text-amber-900 dark:text-amber-300">In Progress</span>
          <div class="p-2 bg-amber-500 rounded-lg">
            <svg
              class="w-4 h-4 text-white"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
              />
            </svg>
          </div>
        </div>
        <p class="text-3xl font-bold text-amber-900 dark:text-amber-100">
          {{ stats.inProgress }}
        </p>
      </div>

      <div
        class="bg-gradient-to-br from-emerald-50 to-emerald-100 dark:from-emerald-900/20 dark:to-emerald-800/20 rounded-xl p-4 border border-emerald-200 dark:border-emerald-800"
      >
        <div class="flex items-center justify-between mb-2">
          <span class="text-sm font-medium text-emerald-900 dark:text-emerald-300">Completed</span>
          <div class="p-2 bg-emerald-500 rounded-lg">
            <svg
              class="w-4 h-4 text-white"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M5 13l4 4L19 7"
              />
            </svg>
          </div>
        </div>
        <p class="text-3xl font-bold text-emerald-900 dark:text-emerald-100">
          {{ stats.completed }}
        </p>
      </div>

      <div
        class="bg-gradient-to-br from-red-50 to-red-100 dark:from-red-900/20 dark:to-red-800/20 rounded-xl p-4 border border-red-200 dark:border-red-800"
      >
        <div class="flex items-center justify-between mb-2">
          <span class="text-sm font-medium text-red-900 dark:text-red-300">Overdue</span>
          <div class="p-2 bg-red-500 rounded-lg">
            <svg
              class="w-4 h-4 text-white"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
              />
            </svg>
          </div>
        </div>
        <p class="text-3xl font-bold text-red-900 dark:text-red-100">
          {{ stats.overdue }}
        </p>
      </div>
    </div>

    <!-- Tab Navigation -->
    <div
      class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700"
    >
      <div class="flex border-b border-slate-200 dark:border-slate-700 overflow-x-auto">
        <button
          v-for="tab in tabs"
          :key="tab.key"
          :class="[
            'px-6 py-4 text-sm font-medium whitespace-nowrap transition-all',
            activeTab === tab.key
              ? 'text-indigo-600 dark:text-indigo-400 border-b-2 border-indigo-600 dark:border-indigo-400'
              : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200',
          ]"
          @click="activeTab = tab.key"
        >
          {{ tab.label }}
          <span
            v-if="tab.count > 0"
            :class="[
              'ml-2 px-2 py-0.5 rounded-full text-xs font-bold',
              activeTab === tab.key
                ? 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400'
                : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-400',
            ]"
          >
            {{ tab.count }}
          </span>
        </button>
      </div>

      <!-- Loading State -->
      <div
        v-if="maintenanceStore.loading"
        class="p-12 text-center"
      >
        <div
          class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-indigo-500 border-t-transparent"
        />
        <p class="mt-4 text-slate-600 dark:text-slate-400">
          Loading maintenance tasks...
        </p>
      </div>

      <!-- Tab Content -->
      <div
        v-else
        class="p-6"
      >
        <transition
          name="fade"
          mode="out-in"
        >
          <!-- All Tasks -->
          <div
            v-if="activeTab === 'all'"
            key="all"
            class="space-y-3"
          >
            <MaintenanceTaskCard
              v-for="task in allTasks"
              :key="task.id"
              :task="task"
              @start="handleStartTask"
              @complete="handleCompleteTask"
              @cancel="handleCancelTask"
              @edit="handleEditTask"
              @delete="handleDeleteTask"
            />
            <div
              v-if="allTasks.length === 0"
              class="text-center py-12 text-slate-500 dark:text-slate-400"
            >
              No maintenance tasks found
            </div>
          </div>

          <!-- Scheduled Tasks -->
          <div
            v-else-if="activeTab === 'scheduled'"
            key="scheduled"
            class="space-y-3"
          >
            <MaintenanceTaskCard
              v-for="task in scheduledTasks"
              :key="task.id"
              :task="task"
              @start="handleStartTask"
              @complete="handleCompleteTask"
              @cancel="handleCancelTask"
              @edit="handleEditTask"
              @delete="handleDeleteTask"
            />
            <div
              v-if="scheduledTasks.length === 0"
              class="text-center py-12 text-slate-500 dark:text-slate-400"
            >
              No scheduled maintenance tasks
            </div>
          </div>

          <!-- In Progress Tasks -->
          <div
            v-else-if="activeTab === 'in_progress'"
            key="in_progress"
            class="space-y-3"
          >
            <MaintenanceTaskCard
              v-for="task in inProgressTasks"
              :key="task.id"
              :task="task"
              @start="handleStartTask"
              @complete="handleCompleteTask"
              @cancel="handleCancelTask"
              @edit="handleEditTask"
              @delete="handleDeleteTask"
            />
            <div
              v-if="inProgressTasks.length === 0"
              class="text-center py-12 text-slate-500 dark:text-slate-400"
            >
              No tasks in progress
            </div>
          </div>

          <!-- Completed Tasks -->
          <div
            v-else-if="activeTab === 'completed'"
            key="completed"
            class="space-y-3"
          >
            <MaintenanceTaskCard
              v-for="task in completedTasks"
              :key="task.id"
              :task="task"
              :show-actions="false"
            />
            <div
              v-if="completedTasks.length === 0"
              class="text-center py-12 text-slate-500 dark:text-slate-400"
            >
              No completed tasks
            </div>
          </div>

          <!-- Overdue Tasks -->
          <div
            v-else-if="activeTab === 'overdue'"
            key="overdue"
            class="space-y-3"
          >
            <MaintenanceTaskCard
              v-for="task in overdueTasks"
              :key="task.id"
              :task="task"
              :is-overdue="true"
              @start="handleStartTask"
              @complete="handleCompleteTask"
              @cancel="handleCancelTask"
              @edit="handleEditTask"
              @delete="handleDeleteTask"
            />
            <div
              v-if="overdueTasks.length === 0"
              class="text-center py-12"
            >
              <div
                class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-emerald-100 dark:bg-emerald-900/30 mb-4"
              >
                <svg
                  class="w-8 h-8 text-emerald-600 dark:text-emerald-400"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                  />
                </svg>
              </div>
              <p class="text-slate-600 dark:text-slate-400">
                No overdue tasks
              </p>
              <p class="text-sm text-slate-500 dark:text-slate-500 mt-1">
                All tasks are on schedule
              </p>
            </div>
          </div>
        </transition>
      </div>
    </div>

    <!-- Create Task Modal -->
    <Teleport to="body">
      <div
        v-if="showCreateModal"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
        @click.self="showCreateModal = false"
      >
        <div
          class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto"
        >
          <div class="p-6 border-b border-slate-200 dark:border-slate-700">
            <h3 class="text-xl font-bold text-slate-900 dark:text-white">
              Create Maintenance Task
            </h3>
          </div>
          <form
            class="p-6 space-y-4"
            @submit.prevent="handleCreateTask"
          >
            <!-- Linked Alarm Notice -->
            <div
              v-if="newTask.alarm_id"
              class="p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg"
            >
              <div class="flex items-center gap-2 text-sm text-amber-800 dark:text-amber-300">
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
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                  />
                </svg>
                <span class="font-medium">Creating from alarm</span>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Turbine *</label>
                <select
                  v-model="newTask.turbine_id"
                  required
                  class="w-full px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white"
                >
                  <option value="">
                    Select turbine
                  </option>
                  <option
                    v-for="t in turbines"
                    :key="t._api_id"
                    :value="t._api_id"
                  >
                    {{ t.id }}
                  </option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Assign To</label>
                <select
                  v-model="newTask.assigned_to"
                  class="w-full px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white"
                >
                  <option value="">
                    Unassigned
                  </option>
                  <option
                    v-for="user in users"
                    :key="user.id"
                    :value="user.id"
                  >
                    {{ user.name }} ({{ user.role }})
                  </option>
                </select>
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Title *</label>
              <input
                v-model="newTask.title"
                required
                type="text"
                class="w-full px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white"
                placeholder="e.g., Gearbox inspection"
              >
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Description</label>
              <textarea
                v-model="newTask.description"
                rows="3"
                class="w-full px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white"
                placeholder="Detailed description of the task..."
              />
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Priority</label>
                <select
                  v-model="newTask.priority"
                  class="w-full px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white"
                >
                  <option value="low">
                    Low
                  </option>
                  <option value="medium">
                    Medium
                  </option>
                  <option value="high">
                    High
                  </option>
                  <option value="urgent">
                    Urgent
                  </option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Status</label>
                <select
                  v-model="newTask.status"
                  class="w-full px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white"
                >
                  <option value="scheduled">
                    Scheduled
                  </option>
                  <option value="in_progress">
                    In Progress
                  </option>
                </select>
              </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Scheduled Date</label>
                <input
                  v-model="newTask.scheduled_date"
                  type="datetime-local"
                  class="w-full px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white"
                >
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Due Date</label>
                <input
                  v-model="newTask.due_date"
                  type="datetime-local"
                  class="w-full px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white"
                >
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Estimated Duration (minutes)</label>
              <input
                v-model.number="newTask.estimated_duration_minutes"
                type="number"
                min="1"
                class="w-full px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white"
                placeholder="e.g., 120"
              >
            </div>
            <div class="flex justify-end gap-3 pt-4">
              <button
                type="button"
                class="px-4 py-2 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors"
                @click="showCreateModal = false"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="creating"
                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition-colors disabled:opacity-50"
              >
                {{ creating ? 'Creating...' : 'Create Task' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useScadaService } from '@/composables/api.js'
import MaintenanceTaskCard from './MaintenanceTaskCard.vue'

const props = defineProps({
  // For prefilling from alarm
  prefillData: { type: Object, default: null },
  showModal: { type: Boolean, default: false },
})

const emit = defineEmits(['close-modal'])

const { maintenanceStore, turbineStore, usersStore, fetchUsers } = useScadaService()

// State
const activeTab = ref('all')
const showCreateModal = ref(false)
const creating = ref(false)
const newTask = ref({
  turbine_id: '',
  alarm_id: null,
  assigned_to: '',
  title: '',
  description: '',
  priority: 'medium',
  status: 'scheduled',
  scheduled_date: '',
  due_date: '',
  estimated_duration_minutes: null,
})

// Watch for external modal trigger (from alarm)
watch(
  () => props.showModal,
  (val) => {
    if (val) {
      if (props.prefillData) {
        // Prefill from alarm data
        newTask.value = {
          turbine_id: props.prefillData.turbineId || '',
          alarm_id: props.prefillData.alarmId || null,
          assigned_to: '',
          title: props.prefillData.title || '',
          description: props.prefillData.description || '',
          priority: props.prefillData.priority || 'medium',
          status: 'scheduled',
          scheduled_date: '',
          due_date: '',
          estimated_duration_minutes: null,
        }
      }
      showCreateModal.value = true
    }
  }
)

// Watch for modal close to emit event
watch(showCreateModal, (val) => {
  if (!val && props.showModal) {
    emit('close-modal')
  }
})

// Computed
const turbines = computed(() => turbineStore.turbines)

const allTasks = computed(() => maintenanceStore.tasks)
const scheduledTasks = computed(() => maintenanceStore.getScheduledTasks())
const inProgressTasks = computed(() => maintenanceStore.getInProgressTasks())
const completedTasks = computed(() => maintenanceStore.getCompletedTasks())
const overdueTasks = computed(() => maintenanceStore.getOverdueTasks())

const stats = computed(() => ({
  total: allTasks.value.length,
  scheduled: scheduledTasks.value.length,
  inProgress: inProgressTasks.value.length,
  completed: completedTasks.value.length,
  overdue: overdueTasks.value.length,
}))

const tabs = computed(() => [
  { key: 'all', label: 'All Tasks', count: stats.value.total },
  { key: 'scheduled', label: 'Scheduled', count: stats.value.scheduled },
  { key: 'in_progress', label: 'In Progress', count: stats.value.inProgress },
  { key: 'completed', label: 'Completed', count: stats.value.completed },
  { key: 'overdue', label: 'Overdue', count: stats.value.overdue },
])

// Computed for users
const users = computed(() => usersStore.users)

// Lifecycle
onMounted(async () => {
  await Promise.all([maintenanceStore.fetchTasks(), fetchUsers()])
})

// Methods
const handleCreateTask = async () => {
  creating.value = true
  try {
    const taskData = { ...newTask.value }
    // Remove empty values
    if (!taskData.assigned_to) delete taskData.assigned_to
    if (!taskData.alarm_id) delete taskData.alarm_id

    await maintenanceStore.createTask(taskData)
    showCreateModal.value = false
    resetNewTask()
  } catch (err) {
    console.error('Failed to create task:', err)
  } finally {
    creating.value = false
  }
}

const resetNewTask = () => {
  newTask.value = {
    turbine_id: '',
    alarm_id: null,
    assigned_to: '',
    title: '',
    description: '',
    priority: 'medium',
    status: 'scheduled',
    scheduled_date: '',
    due_date: '',
    estimated_duration_minutes: null,
  }
}

const handleStartTask = async (taskId) => {
  try {
    await maintenanceStore.startTask(taskId)
  } catch (err) {
    console.error('Failed to start task:', err)
  }
}

const handleCompleteTask = async (taskId, notes = null) => {
  try {
    await maintenanceStore.completeTask(taskId, notes)
  } catch (err) {
    console.error('Failed to complete task:', err)
  }
}

const handleCancelTask = async (taskId) => {
  try {
    await maintenanceStore.cancelTask(taskId)
  } catch (err) {
    console.error('Failed to cancel task:', err)
  }
}

const handleEditTask = (task) => {
  // Could emit to parent or open edit modal
  console.log('Edit task:', task)
}

const handleDeleteTask = async (taskId) => {
  if (confirm('Are you sure you want to delete this task?')) {
    try {
      await maintenanceStore.deleteTask(taskId)
    } catch (err) {
      console.error('Failed to delete task:', err)
    }
  }
}
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
