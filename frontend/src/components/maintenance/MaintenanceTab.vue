<template>
  <div class="space-y-6">
    <!-- Header with Actions -->
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Maintenance Management</h2>
        <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
          Track and schedule turbine maintenance activities
        </p>
      </div>

      <button
          @click="$emit('add-log')"
          class="flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition-colors shadow-lg shadow-indigo-500/30"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Add Maintenance Log
      </button>
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <div class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 rounded-xl p-4 border border-blue-200 dark:border-blue-800">
        <div class="flex items-center justify-between mb-2">
          <span class="text-sm font-medium text-blue-900 dark:text-blue-300">Total Tasks</span>
          <div class="p-2 bg-blue-500 rounded-lg">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
          </div>
        </div>
        <p class="text-3xl font-bold text-blue-900 dark:text-blue-100">{{ stats.total }}</p>
      </div>

      <div class="bg-gradient-to-br from-amber-50 to-amber-100 dark:from-amber-900/20 dark:to-amber-800/20 rounded-xl p-4 border border-amber-200 dark:border-amber-800">
        <div class="flex items-center justify-between mb-2">
          <span class="text-sm font-medium text-amber-900 dark:text-amber-300">Pending</span>
          <div class="p-2 bg-amber-500 rounded-lg">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
        </div>
        <p class="text-3xl font-bold text-amber-900 dark:text-amber-100">{{ stats.pending }}</p>
      </div>

      <div class="bg-gradient-to-br from-emerald-50 to-emerald-100 dark:from-emerald-900/20 dark:to-emerald-800/20 rounded-xl p-4 border border-emerald-200 dark:border-emerald-800">
        <div class="flex items-center justify-between mb-2">
          <span class="text-sm font-medium text-emerald-900 dark:text-emerald-300">Completed</span>
          <div class="p-2 bg-emerald-500 rounded-lg">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
          </div>
        </div>
        <p class="text-3xl font-bold text-emerald-900 dark:text-emerald-100">{{ stats.completed }}</p>
      </div>

      <div class="bg-gradient-to-br from-red-50 to-red-100 dark:from-red-900/20 dark:to-red-800/20 rounded-xl p-4 border border-red-200 dark:border-red-800">
        <div class="flex items-center justify-between mb-2">
          <span class="text-sm font-medium text-red-900 dark:text-red-300">Overdue</span>
          <div class="p-2 bg-red-500 rounded-lg">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
        </div>
        <p class="text-3xl font-bold text-red-900 dark:text-red-100">{{ stats.overdue }}</p>
      </div>
    </div>

    <!-- Tab Navigation -->
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
      <div class="flex border-b border-slate-200 dark:border-slate-700 overflow-x-auto">
        <button
            v-for="tab in tabs"
            :key="tab"
            @click="activeTab = tab"
            :class="[
            'px-6 py-4 text-sm font-medium whitespace-nowrap transition-all',
            activeTab === tab
              ? 'text-indigo-600 dark:text-indigo-400 border-b-2 border-indigo-600 dark:border-indigo-400'
              : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200'
          ]"
        >
          {{ tab }}
          <span
              v-if="getTabCount(tab) > 0"
              :class="[
              'ml-2 px-2 py-0.5 rounded-full text-xs font-bold',
              activeTab === tab
                ? 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400'
                : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-400'
            ]"
          >
            {{ getTabCount(tab) }}
          </span>
        </button>
      </div>

      <!-- Tab Content -->
      <div class="p-6">
        <transition name="fade" mode="out-in">
          <!-- Recent Logs -->
          <div v-if="activeTab === 'Recent'" key="recent" class="space-y-3">
            <div
                v-for="log in recentLogs"
                :key="log.id"
                class="p-4 rounded-lg border border-slate-200 dark:border-slate-700 hover:border-indigo-300 dark:hover:border-indigo-700 hover:shadow-md transition-all group"
            >
              <div class="flex items-start justify-between mb-3">
                <div class="flex-1">
                  <div class="flex items-center gap-2 mb-1">
                    <span :class="['px-2.5 py-0.5 rounded-full text-xs font-semibold', getTypeClass(log.type)]">
                      {{ log.type }}
                    </span>
                    <span class="text-sm font-semibold text-slate-900 dark:text-white">{{ log.turbine }}</span>
                  </div>
                  <p class="text-sm text-slate-600 dark:text-slate-400">{{ log.description }}</p>
                </div>
                <span :class="['px-3 py-1 rounded-lg text-xs font-medium', getStatusClass(log.status)]">
                  {{ log.status }}
                </span>
              </div>
              <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
                <div class="flex items-center gap-4">
                  <span class="flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    {{ formatDate(log.date) }}
                  </span>
                  <span class="flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    {{ log.technician }}
                  </span>
                </div>
              </div>
            </div>
            <div v-if="recentLogs.length === 0" class="text-center py-12 text-slate-500 dark:text-slate-400">
              No recent maintenance logs
            </div>
          </div>

          <!-- Scheduled Tasks -->
          <div v-else-if="activeTab === 'Scheduled'" key="scheduled" class="space-y-3">
            <div
                v-for="task in scheduledTasks"
                :key="task.id"
                :class="[
                'p-4 rounded-lg border transition-all group',
                isOverdue(task.date)
                  ? 'border-red-300 dark:border-red-700 bg-red-50/50 dark:bg-red-900/10'
                  : 'border-slate-200 dark:border-slate-700 hover:border-indigo-300 dark:hover:border-indigo-700 hover:shadow-md'
              ]"
            >
              <div class="flex items-start justify-between mb-3">
                <div class="flex items-start gap-3 flex-1">
                  <input
                      type="checkbox"
                      :checked="task.completed"
                      @change="toggleTaskComplete(task.id)"
                      class="mt-1 w-5 h-5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                  />
                  <div class="flex-1">
                    <div class="flex items-center gap-2 mb-1">
                      <span class="text-sm font-semibold text-slate-900 dark:text-white">{{ task.turbine }}</span>
                      <span :class="['px-2.5 py-0.5 rounded-full text-xs font-semibold', getTypeClass(task.type)]">
                        {{ task.type }}
                      </span>
                      <span v-if="isOverdue(task.date)" class="flex items-center gap-1 text-xs font-semibold text-red-600 dark:text-red-400">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                        Overdue
                      </span>
                    </div>
                    <p class="text-sm text-slate-600 dark:text-slate-400">{{ task.description }}</p>
                  </div>
                </div>
              </div>
              <div class="flex items-center justify-between text-xs ml-8">
                <span class="flex items-center gap-1 text-slate-500 dark:text-slate-400">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                  Scheduled: {{ formatDate(task.date) }}
                </span>
                <button
                    class="px-3 py-1 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 rounded-lg font-medium transition-colors"
                >
                  Reschedule
                </button>
              </div>
            </div>
            <div v-if="scheduledTasks.length === 0" class="text-center py-12 text-slate-500 dark:text-slate-400">
              No scheduled maintenance tasks
            </div>
          </div>

          <!-- Predictive Insights -->
          <div v-else-if="activeTab === 'Predictive'" key="predictive" class="space-y-3">
            <div
                v-for="insight in predictiveInsights"
                :key="insight.id"
                class="p-5 rounded-lg border-2 border-amber-300 dark:border-amber-700 bg-gradient-to-br from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20"
            >
              <div class="flex items-start gap-4">
                <div class="p-3 bg-amber-500 rounded-xl">
                  <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
                <div class="flex-1">
                  <div class="flex items-center gap-2 mb-2">
                    <h4 class="text-lg font-semibold text-slate-900 dark:text-white">{{ insight.component }}</h4>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 dark:bg-amber-900/30 text-amber-900 dark:text-amber-300">
                      {{ insight.confidence }}% Confidence
                    </span>
                  </div>
                  <p class="text-sm text-slate-700 dark:text-slate-300 mb-3">
                    <span class="font-medium">Turbine:</span> {{ insight.turbine }} •
                    <span class="font-medium">Predicted Failure:</span> {{ formatDate(insight.predictedFailure) }}
                  </p>
                  <div class="flex items-center justify-between">
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                      <span class="font-medium">Recommendation:</span> {{ insight.recommendation }}
                    </p>
                    <button class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-sm font-medium transition-colors">
                      Schedule Work
                    </button>
                  </div>
                </div>
              </div>
            </div>
            <div v-if="predictiveInsights.length === 0" class="text-center py-12">
              <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-emerald-100 dark:bg-emerald-900/30 mb-4">
                <svg class="w-8 h-8 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <p class="text-slate-600 dark:text-slate-400">No predictive maintenance alerts</p>
              <p class="text-sm text-slate-500 dark:text-slate-500 mt-1">All systems operating within normal parameters</p>
            </div>
          </div>

          <!-- Component History -->
          <div v-else-if="activeTab === 'Component History'" key="history" class="space-y-4">
            <div
                v-for="component in componentHistory"
                :key="component.name"
                class="p-4 rounded-lg border border-slate-200 dark:border-slate-700 hover:shadow-md transition-all"
            >
              <div class="flex items-center justify-between mb-3">
                <h4 class="text-base font-semibold text-slate-900 dark:text-white">{{ component.name }}</h4>
                <div class="flex items-center gap-2">
                  <span class="text-sm text-slate-600 dark:text-slate-400">Health:</span>
                  <div class="flex items-center gap-2">
                    <div class="w-32 h-2 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                      <div
                          :style="{ width: `${component.health}%` }"
                          :class="[
                          'h-full transition-all',
                          component.health >= 80 ? 'bg-emerald-500' : component.health >= 60 ? 'bg-amber-500' : 'bg-red-500'
                        ]"
                      ></div>
                    </div>
                    <span class="text-sm font-semibold text-slate-900 dark:text-white">{{ component.health }}%</span>
                  </div>
                </div>
              </div>
              <div class="space-y-2">
                <div
                    v-for="(record, index) in component.history"
                    :key="index"
                    class="flex items-center gap-3 text-sm"
                >
                  <div class="w-2 h-2 rounded-full bg-indigo-500"></div>
                  <span class="text-slate-600 dark:text-slate-400">{{ formatDate(record.date) }}</span>
                  <span class="text-slate-900 dark:text-white">{{ record.action }}</span>
                  <span class="text-slate-500 dark:text-slate-500">by {{ record.technician }}</span>
                </div>
              </div>
            </div>
          </div>
        </transition>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  maintenanceLogs: { type: Array, default: () => [] },
  predictiveInsights: { type: Array, default: () => [] },
  maintenanceTabs: { type: Array, default: () => ['Recent', 'Scheduled', 'Predictive', 'Component History'] },
  activeMaintenanceTab: { type: String, default: 'Recent' }
})

const emit = defineEmits(['add-log'])

// State
const activeTab = ref(props.activeMaintenanceTab)
const tabs = ref(props.maintenanceTabs)

// Sample data
const recentLogs = ref([
  {
    id: 1,
    turbine: 'WT-003',
    type: 'Preventive',
    description: 'Annual gearbox inspection and oil change',
    date: '2025-10-01',
    technician: 'Mike Johnson',
    status: 'completed'
  },
  {
    id: 2,
    turbine: 'WT-001',
    type: 'Corrective',
    description: 'Replace nacelle humidity sensor',
    date: '2025-09-28',
    technician: 'Sarah Williams',
    status: 'completed'
  },
  {
    id: 3,
    turbine: 'WT-005',
    type: 'Emergency',
    description: 'Blade imbalance correction - emergency stop initiated',
    date: '2025-10-02',
    technician: 'John Davis',
    status: 'in-progress'
  }
])

const scheduledTasks = ref([
  {
    id: 101,
    turbine: 'WT-002',
    type: 'Preventive',
    description: 'Quarterly blade inspection',
    date: '2025-10-15',
    completed: false
  },
  {
    id: 102,
    turbine: 'WT-004',
    type: 'Preventive',
    description: 'Generator bearing lubrication',
    date: '2025-10-20',
    completed: false
  },
  {
    id: 103,
    turbine: 'WT-001',
    type: 'Corrective',
    description: 'Yaw system realignment',
    date: '2025-10-05',
    completed: false
  }
])

const predictiveInsights = ref([
  {
    id: 1,
    component: 'Gearbox',
    turbine: 'WT-005',
    predictedFailure: '2025-11-15',
    confidence: 82,
    recommendation: 'Schedule inspection within 2 weeks'
  },
  {
    id: 2,
    component: 'Generator Bearing',
    turbine: 'WT-003',
    predictedFailure: '2025-12-01',
    confidence: 74,
    recommendation: 'Monitor vibration levels, schedule replacement'
  }
])

const componentHistory = ref([
  {
    name: 'Gearbox - WT-003',
    health: 85,
    history: [
      { date: '2025-10-01', action: 'Oil change and inspection', technician: 'Mike Johnson' },
      { date: '2025-07-15', action: 'Bearing replacement', technician: 'Sarah Williams' },
      { date: '2025-04-10', action: 'Routine inspection', technician: 'John Davis' }
    ]
  },
  {
    name: 'Blade Set - WT-001',
    health: 92,
    history: [
      { date: '2025-09-20', action: 'Visual inspection and cleaning', technician: 'Mike Johnson' },
      { date: '2025-06-05', action: 'Leading edge repair', technician: 'Sarah Williams' }
    ]
  }
])

// Computed
const stats = computed(() => ({
  total: recentLogs.value.length + scheduledTasks.value.length,
  pending: scheduledTasks.value.filter(t => !t.completed && !isOverdue(t.date)).length,
  completed: recentLogs.value.filter(l => l.status === 'completed').length,
  overdue: scheduledTasks.value.filter(t => !t.completed && isOverdue(t.date)).length
}))

// Methods
const getTabCount = (tab) => {
  switch (tab) {
    case 'Recent':
      return recentLogs.value.length
    case 'Scheduled':
      return scheduledTasks.value.length
    case 'Predictive':
      return predictiveInsights.value.length
    case 'Component History':
      return componentHistory.value.length
    default:
      return 0
  }
}

const getTypeClass = (type) => {
  const classes = {
    'Preventive': 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400',
    'Corrective': 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400',
    'Emergency': 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400'
  }
  return classes[type] || 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300'
}

const getStatusClass = (status) => {
  const classes = {
    'completed': 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400',
    'in-progress': 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400',
    'pending': 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400'
  }
  return classes[status] || 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300'
}

const formatDate = (dateString) => {
  try {
    const date = new Date(dateString)
    return date.toLocaleDateString('en-US', {
      month: 'short',
      day: 'numeric',
      year: 'numeric'
    })
  } catch (e) {
    return dateString
  }
}

const isOverdue = (dateString) => {
  const date = new Date(dateString)
  const today = new Date()
  today.setHours(0, 0, 0, 0)
  return date < today
}

const toggleTaskComplete = (taskId) => {
  const task = scheduledTasks.value.find(t => t.id === taskId)
  if (task) {
    task.completed = !task.completed
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