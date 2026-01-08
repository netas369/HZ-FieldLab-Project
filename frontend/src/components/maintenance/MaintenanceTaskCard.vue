<template>
  <div
    :class="[
      'p-4 rounded-lg border transition-all group',
      isOverdue
        ? 'border-red-300 dark:border-red-700 bg-red-50/50 dark:bg-red-900/10'
        : 'border-slate-200 dark:border-slate-700 hover:border-indigo-300 dark:hover:border-indigo-700 hover:shadow-md',
    ]"
  >
    <div class="flex items-start justify-between mb-3">
      <div class="flex-1">
        <div class="flex items-center gap-2 mb-1 flex-wrap">
          <span :class="['px-2.5 py-0.5 rounded-full text-xs font-semibold', priorityClass]">
            {{ task.priority }}
          </span>
          <span :class="['px-2.5 py-0.5 rounded-full text-xs font-semibold', statusClass]">
            {{ statusLabel }}
          </span>
          <span class="text-sm font-semibold text-slate-900 dark:text-white">
            {{ turbineName }}
          </span>
          <span
            v-if="isOverdue"
            class="flex items-center gap-1 text-xs font-semibold text-red-600 dark:text-red-400"
          >
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
              <path
                fill-rule="evenodd"
                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                clip-rule="evenodd"
              />
            </svg>
            Overdue
          </span>
        </div>
        <h4 class="text-base font-semibold text-slate-900 dark:text-white mb-1">
          {{ task.title }}
        </h4>
        <p v-if="task.description" class="text-sm text-slate-600 dark:text-slate-400">
          {{ task.description }}
        </p>
      </div>
    </div>

    <!-- Meta info -->
    <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
      <div class="flex items-center gap-4 flex-wrap">
        <span v-if="task.scheduled_date" class="flex items-center gap-1">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
            />
          </svg>
          Scheduled: {{ formatDate(task.scheduled_date) }}
        </span>
        <span v-if="task.due_date" class="flex items-center gap-1">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
            />
          </svg>
          Due: {{ formatDate(task.due_date) }}
        </span>
        <span v-if="task.assignee" class="flex items-center gap-1">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
            />
          </svg>
          {{ task.assignee.name }}
        </span>
        <span v-if="task.estimated_duration_minutes" class="flex items-center gap-1">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
            />
          </svg>
          Est: {{ task.estimated_duration_minutes }}min
        </span>
      </div>

      <!-- Actions -->
      <div
        v-if="showActions"
        class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity"
      >
        <button
          v-if="task.status === 'scheduled'"
          class="px-3 py-1 text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-900/20 rounded-lg font-medium transition-colors"
          @click="$emit('start', task.id)"
        >
          Start
        </button>
        <button
          v-if="task.status === 'in_progress'"
          class="px-3 py-1 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 rounded-lg font-medium transition-colors"
          @click="$emit('complete', task.id)"
        >
          Complete
        </button>
        <button
          v-if="task.status !== 'completed' && task.status !== 'canceled'"
          class="px-3 py-1 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg font-medium transition-colors"
          @click="$emit('cancel', task.id)"
        >
          Cancel
        </button>
        <button
          class="px-3 py-1 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg font-medium transition-colors"
          @click="$emit('delete', task.id)"
        >
          Delete
        </button>
      </div>
    </div>

    <!-- Completed info -->
    <div
      v-if="task.status === 'completed' && task.completed_at"
      class="mt-3 pt-3 border-t border-slate-200 dark:border-slate-700"
    >
      <div class="flex items-center gap-4 text-xs text-slate-500 dark:text-slate-400">
        <span class="flex items-center gap-1">
          <svg
            class="w-4 h-4 text-emerald-500"
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
          Completed: {{ formatDate(task.completed_at) }}
        </span>
        <span v-if="task.actual_duration_minutes">
          Duration: {{ task.actual_duration_minutes }}min
        </span>
      </div>
      <p v-if="task.notes" class="mt-2 text-sm text-slate-600 dark:text-slate-400 italic">
        {{ task.notes }}
      </p>
    </div>

    <!-- Alarm link -->
    <div v-if="task.alarm" class="mt-3 pt-3 border-t border-slate-200 dark:border-slate-700">
      <div class="flex items-center gap-2 text-xs">
        <span class="text-slate-500 dark:text-slate-400">Linked alarm:</span>
        <span :class="['px-2 py-0.5 rounded-full font-medium', alarmSeverityClass]">
          {{ task.alarm.severity }}
        </span>
        <span class="text-slate-700 dark:text-slate-300">{{ task.alarm.component }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  task: { type: Object, required: true },
  showActions: { type: Boolean, default: true },
  isOverdue: { type: Boolean, default: false },
})

defineEmits(['start', 'complete', 'cancel', 'edit', 'delete'])

const turbineName = computed(() => {
  if (props.task.turbine?.turbine_id) return props.task.turbine.turbine_id
  return `Turbine ${props.task.turbine_id}`
})

const priorityClass = computed(() => {
  const classes = {
    urgent: 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400',
    high: 'bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400',
    medium: 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400',
    low: 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400',
  }
  return classes[props.task.priority] || classes['medium']
})

const statusClass = computed(() => {
  const classes = {
    scheduled: 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300',
    in_progress: 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400',
    completed: 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400',
    canceled: 'bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400',
    overdue: 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400',
  }
  return classes[props.task.status] || classes['scheduled']
})

const statusLabel = computed(() => {
  const labels = {
    scheduled: 'Scheduled',
    in_progress: 'In Progress',
    completed: 'Completed',
    canceled: 'Canceled',
    overdue: 'Overdue',
  }
  return labels[props.task.status] || props.task.status
})

const alarmSeverityClass = computed(() => {
  if (!props.task.alarm) return ''
  const classes = {
    failed: 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400',
    critical: 'bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400',
    warning: 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400',
  }
  return classes[props.task.alarm.severity] || ''
})

const formatDate = (dateString) => {
  if (!dateString) return ''
  try {
    const date = new Date(dateString)
    return date.toLocaleDateString('en-US', {
      month: 'short',
      day: 'numeric',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    })
  } catch {
    return dateString
  }
}
</script>
