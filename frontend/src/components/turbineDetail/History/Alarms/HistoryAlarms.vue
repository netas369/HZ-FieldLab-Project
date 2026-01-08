<template>
  <div class="space-y-6 animate-fade-in">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <div
        class="bg-slate-50 dark:bg-slate-800 p-4 rounded-xl border border-slate-200 dark:border-slate-700"
      >
        <div class="flex items-center gap-2 mb-1">
          <div class="w-2 h-2 rounded-full bg-red-500" />
          <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">
            Total Alarms
          </div>
        </div>
        <div class="text-2xl font-bold text-slate-900 dark:text-white">
          {{ stats.total }}
        </div>
      </div>

      <div
        class="bg-red-50 dark:bg-red-900/10 p-4 rounded-xl border border-red-100 dark:border-red-900/30"
      >
        <div class="flex items-center gap-2 mb-1">
          <svg
            class="w-4 h-4 text-red-500"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
            />
          </svg>
          <div class="text-xs font-semibold text-red-600 dark:text-red-400 uppercase">
            Critical/Failed
          </div>
        </div>
        <div class="text-2xl font-bold text-slate-900 dark:text-white">
          {{ stats.critical }}
        </div>
      </div>

      <div
        class="bg-amber-50 dark:bg-amber-900/10 p-4 rounded-xl border border-amber-100 dark:border-amber-900/30"
      >
        <div class="flex items-center gap-2 mb-1">
          <svg
            class="w-4 h-4 text-amber-500"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
            />
          </svg>
          <div class="text-xs font-semibold text-amber-600 dark:text-amber-400 uppercase">
            Warnings
          </div>
        </div>
        <div class="text-2xl font-bold text-slate-900 dark:text-white">
          {{ stats.warnings }}
        </div>
      </div>

      <div
        class="bg-orange-50 dark:bg-orange-900/10 p-4 rounded-xl border border-orange-100 dark:border-orange-900/30"
      >
        <div class="flex items-center gap-2 mb-1">
          <svg
            class="w-4 h-4 text-orange-500"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
            />
          </svg>
          <div class="text-xs font-semibold text-orange-600 dark:text-orange-400 uppercase">
            Total Downtime
          </div>
        </div>
        <div class="text-2xl font-bold text-slate-900 dark:text-white">
          {{ stats.downtime }} hrs
        </div>
      </div>
    </div>

    <AlarmTimeline :alarms="alarmData" />

    <AlarmTable :alarms="alarmData" />
  </div>
</template>

<script setup>
import { computed } from 'vue'
import AlarmTimeline from './HistoryAlarmsTimeline.vue'
import AlarmTable from './HistoryAlarmsTable.vue'

const props = defineProps({
  alarms: {
    type: Object,
    required: true,
    default: () => ({ alarms: [], statistics: {} }),
  },
})

// --- Data Accessors ---
const alarmData = computed(() => props.alarms.alarms || [])
const alarmStats = computed(() => props.alarms.statistics || {})

// --- KPI Stats Calculation ---
const stats = computed(() => {
  const s = alarmStats.value
  const topIssueKey = s.most_common_components ? Object.keys(s.most_common_components)[0] : 'None'

  return {
    total: s.total_alarms || 0,
    downtime: (s.total_downtime_hours || 0).toFixed(1),
    topIssue: topIssueKey.replace(/_/g, ' '),
    critical: (s.by_severity?.failed || 0) + (s.by_severity?.critical || 0),
    warnings: s.by_severity?.warning || 0,
  }
})
</script>
