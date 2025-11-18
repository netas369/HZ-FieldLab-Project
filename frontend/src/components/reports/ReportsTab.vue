<template>
  <div class="space-y-6">
    <!-- Header with Actions -->
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Reports & Analytics</h2>
        <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
          Generate and download operational reports
        </p>
      </div>

      <button
          @click="showGenerateModal = true"
          class="flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition-colors shadow-lg shadow-indigo-500/30"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Generate Report
      </button>
    </div>

    <!-- Quick Report Templates -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <div
          v-for="template in reportTemplates"
          :key="template.id"
          @click="generateQuickReport(template)"
          class="group bg-white dark:bg-slate-800 rounded-xl p-6 border border-slate-200 dark:border-slate-700 hover:border-indigo-500 dark:hover:border-indigo-500 transition-all cursor-pointer hover:shadow-lg"
      >
        <div class="flex items-start justify-between mb-4">
          <div :class="['p-3 rounded-lg', template.bgColor]">
            <component :is="template.icon" class="w-6 h-6 text-white" />
          </div>
          <span class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ template.frequency }}</span>
        </div>
        <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-2">{{ template.name }}</h3>
        <p class="text-sm text-slate-600 dark:text-slate-400 mb-4">{{ template.description }}</p>
        <div class="flex items-center gap-2 text-sm text-indigo-600 dark:text-indigo-400 font-medium">
          <span>Generate now</span>
          <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </div>
      </div>
    </div>

    <!-- Recent Reports -->
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6 border border-slate-200 dark:border-slate-700">
      <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Recent Reports</h3>
        <div class="flex items-center gap-2">
          <button
              v-for="filter in ['All', 'Performance', 'Maintenance', 'Financial']"
              :key="filter"
              @click="activeFilter = filter"
              :class="[
              'px-3 py-1.5 rounded-lg text-sm font-medium transition-all',
              activeFilter === filter
                ? 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400'
                : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700'
            ]"
          >
            {{ filter }}
          </button>
        </div>
      </div>

      <div class="space-y-3">
        <div
            v-for="report in filteredReports"
            :key="report.id"
            class="flex items-center gap-4 p-4 rounded-lg border border-slate-200 dark:border-slate-700 hover:border-indigo-300 dark:hover:border-indigo-700 hover:bg-slate-50 dark:hover:bg-slate-900 transition-all group"
        >
          <!-- Icon -->
          <div :class="['p-3 rounded-lg flex-shrink-0', getReportTypeColor(report.type)]">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </div>

          <!-- Info -->
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 mb-1">
              <h4 class="font-semibold text-slate-900 dark:text-white">{{ report.name }}</h4>
              <span :class="['text-xs font-medium px-2 py-0.5 rounded-full', getStatusBadge(report.status)]">
                {{ report.status }}
              </span>
            </div>
            <div class="flex items-center gap-4 text-sm text-slate-600 dark:text-slate-400">
              <span class="flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                {{ formatDate(report.date) }}
              </span>
              <span class="flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                {{ report.author }}
              </span>
              <span class="flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                {{ report.size }}
              </span>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
            <button
                @click="viewReport(report)"
                class="p-2 hover:bg-indigo-100 dark:hover:bg-indigo-900/30 rounded-lg text-indigo-600 dark:text-indigo-400 transition-colors"
                title="View"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
            </button>
            <button
                @click="downloadReport(report)"
                class="p-2 hover:bg-green-100 dark:hover:bg-green-900/30 rounded-lg text-green-600 dark:text-green-400 transition-colors"
                title="Download"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
              </svg>
            </button>
            <button
                @click="deleteReport(report)"
                class="p-2 hover:bg-red-100 dark:hover:bg-red-900/30 rounded-lg text-red-600 dark:text-red-400 transition-colors"
                title="Delete"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </button>
          </div>
        </div>
      </div>

      <div v-if="filteredReports.length === 0" class="text-center py-12 text-slate-500 dark:text-slate-400">
        No reports found
      </div>
    </div>

    <!-- Scheduled Reports -->
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6 border border-slate-200 dark:border-slate-700">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Scheduled Reports</h3>
        <button class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline font-medium">
          Manage Schedules
        </button>
      </div>

      <div class="space-y-3">
        <div
            v-for="schedule in scheduledReports"
            :key="schedule.id"
            class="flex items-center justify-between p-4 rounded-lg border border-slate-200 dark:border-slate-700"
        >
          <div class="flex items-center gap-3">
            <div class="p-2 bg-slate-100 dark:bg-slate-700 rounded-lg">
              <svg class="w-5 h-5 text-slate-600 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div>
              <h4 class="font-medium text-slate-900 dark:text-white">{{ schedule.name }}</h4>
              <p class="text-sm text-slate-600 dark:text-slate-400">{{ schedule.frequency }} • Next: {{ schedule.nextRun }}</p>
            </div>
          </div>
          <label class="relative inline-flex items-center cursor-pointer">
            <input type="checkbox" :checked="schedule.enabled" class="sr-only peer">
            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-slate-600 peer-checked:bg-indigo-600"></div>
          </label>
        </div>
      </div>
    </div>

    <!-- Generate Report Modal -->
    <Teleport to="body">
      <div
          v-if="showGenerateModal"
          class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4"
          @click.self="showGenerateModal = false"
      >
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl max-w-2xl w-full">
          <div class="p-6">
            <div class="flex items-center justify-between mb-6">
              <h3 class="text-2xl font-bold text-slate-900 dark:text-white">Generate Custom Report</h3>
              <button
                  @click="showGenerateModal = false"
                  class="p-2 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors"
              >
                <svg class="w-6 h-6 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <form @submit.prevent="handleGenerate" class="space-y-4">
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                    Report Type
                  </label>
                  <select
                      v-model="generateForm.type"
                      class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-indigo-500"
                  >
                    <option value="performance">Performance</option>
                    <option value="maintenance">Maintenance</option>
                    <option value="financial">Financial</option>
                    <option value="custom">Custom</option>
                  </select>
                </div>

                <div>
                  <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                    Time Period
                  </label>
                  <select
                      v-model="generateForm.period"
                      class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-indigo-500"
                  >
                    <option value="day">Last 24 Hours</option>
                    <option value="week">Last Week</option>
                    <option value="month">Last Month</option>
                    <option value="quarter">Last Quarter</option>
                    <option value="year">Last Year</option>
                  </select>
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                  Report Name
                </label>
                <input
                    v-model="generateForm.name"
                    type="text"
                    class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-indigo-500"
                    placeholder="My Custom Report"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                  Include Sections
                </label>
                <div class="grid grid-cols-2 gap-3">
                  <label class="flex items-center gap-2 p-3 bg-slate-50 dark:bg-slate-900 rounded-lg cursor-pointer hover:bg-slate-100 dark:hover:bg-slate-800">
                    <input type="checkbox" v-model="generateForm.sections" value="summary" class="rounded">
                    <span class="text-sm text-slate-700 dark:text-slate-300">Executive Summary</span>
                  </label>
                  <label class="flex items-center gap-2 p-3 bg-slate-50 dark:bg-slate-900 rounded-lg cursor-pointer hover:bg-slate-100 dark:hover:bg-slate-800">
                    <input type="checkbox" v-model="generateForm.sections" value="metrics" class="rounded">
                    <span class="text-sm text-slate-700 dark:text-slate-300">Key Metrics</span>
                  </label>
                  <label class="flex items-center gap-2 p-3 bg-slate-50 dark:bg-slate-900 rounded-lg cursor-pointer hover:bg-slate-100 dark:hover:bg-slate-800">
                    <input type="checkbox" v-model="generateForm.sections" value="charts" class="rounded">
                    <span class="text-sm text-slate-700 dark:text-slate-300">Charts & Graphs</span>
                  </label>
                  <label class="flex items-center gap-2 p-3 bg-slate-50 dark:bg-slate-900 rounded-lg cursor-pointer hover:bg-slate-100 dark:hover:bg-slate-800">
                    <input type="checkbox" v-model="generateForm.sections" value="details" class="rounded">
                    <span class="text-sm text-slate-700 dark:text-slate-300">Detailed Data</span>
                  </label>
                </div>
              </div>

              <div class="flex gap-3 pt-4">
                <button
                    type="submit"
                    class="flex-1 px-4 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition-colors"
                >
                  Generate Report
                </button>
                <button
                    type="button"
                    @click="showGenerateModal = false"
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
import { ref, computed, reactive } from 'vue'

// State
const showGenerateModal = ref(false)
const activeFilter = ref('All')

const generateForm = reactive({
  type: 'performance',
  period: 'month',
  name: '',
  sections: ['summary', 'metrics', 'charts']
})

// Data
const reportTemplates = [
  {
    id: 1,
    name: 'Daily Performance',
    description: 'Power generation and efficiency metrics for the last 24 hours',
    frequency: 'Daily',
    bgColor: 'bg-blue-500',
    icon: { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>' }
  },
  {
    id: 2,
    name: 'Maintenance Summary',
    description: 'Completed maintenance tasks and scheduled work',
    frequency: 'Weekly',
    bgColor: 'bg-amber-500',
    icon: { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /></svg>' }
  },
  {
    id: 3,
    name: 'Financial Report',
    description: 'Revenue, costs, and ROI analysis',
    frequency: 'Monthly',
    bgColor: 'bg-green-500',
    icon: { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>' }
  },
  {
    id: 4,
    name: 'Alarm History',
    description: 'Critical events and response times',
    frequency: 'On-demand',
    bgColor: 'bg-red-500',
    icon: { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>' }
  },
  {
    id: 5,
    name: 'Efficiency Analysis',
    description: 'Turbine-by-turbine performance comparison',
    frequency: 'Weekly',
    bgColor: 'bg-violet-500',
    icon: { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>' }
  },
  {
    id: 6,
    name: 'Compliance Report',
    description: 'Regulatory compliance and certifications',
    frequency: 'Quarterly',
    bgColor: 'bg-indigo-500',
    icon: { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" /></svg>' }
  }
]

const reports = ref([
  {
    id: 1,
    name: 'Monthly Performance Report - September 2025',
    type: 'Performance',
    date: '2025-09-30',
    author: 'System',
    size: '2.4 MB',
    status: 'Ready'
  },
  {
    id: 2,
    name: 'Maintenance Summary - Week 40',
    type: 'Maintenance',
    date: '2025-10-01',
    author: 'John Smith',
    size: '1.1 MB',
    status: 'Ready'
  },
  {
    id: 3,
    name: 'Q3 Financial Analysis',
    type: 'Financial',
    date: '2025-09-28',
    author: 'System',
    size: '3.2 MB',
    status: 'Ready'
  },
  {
    id: 4,
    name: 'Critical Alarms - October',
    type: 'Performance',
    date: '2025-10-02',
    author: 'System',
    size: '890 KB',
    status: 'Processing'
  }
])

const scheduledReports = [
  { id: 1, name: 'Weekly Performance Summary', frequency: 'Every Monday', nextRun: 'Oct 7, 2025', enabled: true },
  { id: 2, name: 'Monthly Financial Report', frequency: 'Last day of month', nextRun: 'Oct 31, 2025', enabled: true },
  { id: 3, name: 'Daily Alarm Digest', frequency: 'Every day at 9:00 AM', nextRun: 'Tomorrow', enabled: false }
]

// Computed
const filteredReports = computed(() => {
  if (activeFilter.value === 'All') return reports.value
  return reports.value.filter(r => r.type === activeFilter.value)
})

// Methods
const generateQuickReport = (template) => {
  console.log('Generating quick report:', template.name)
  // Implement report generation
}

const viewReport = (report) => {
  console.log('Viewing report:', report.name)
}

const downloadReport = (report) => {
  console.log('Downloading report:', report.name)
}

const deleteReport = (report) => {
  const index = reports.value.findIndex(r => r.id === report.id)
  if (index > -1) {
    reports.value.splice(index, 1)
  }
}

const handleGenerate = () => {
  console.log('Generating custom report:', generateForm)
  showGenerateModal.value = false

  // Add to reports list
  reports.value.unshift({
    id: Date.now(),
    name: generateForm.name || `${generateForm.type} Report`,
    type: generateForm.type.charAt(0).toUpperCase() + generateForm.type.slice(1),
    date: new Date().toISOString().split('T')[0],
    author: 'John Smith',
    size: '1.5 MB',
    status: 'Processing'
  })
}

const getReportTypeColor = (type) => {
  const colors = {
    Performance: 'bg-blue-500',
    Maintenance: 'bg-amber-500',
    Financial: 'bg-green-500'
  }
  return colors[type] || 'bg-slate-500'
}

const getStatusBadge = (status) => {
  const badges = {
    Ready: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
    Processing: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
    Failed: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'
  }
  return badges[status] || 'bg-slate-100 text-slate-700'
}

const formatDate = (dateString) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}
</script>

<style scoped>
/* Custom animations */
.group:hover .group-hover\:translate-x-1 {
  transform: translateX(0.25rem);
}
</style>