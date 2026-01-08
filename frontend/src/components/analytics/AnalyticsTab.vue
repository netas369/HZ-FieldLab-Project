<template>
  <div class="flex gap-6">
    <!-- Main Content -->
    <div class="flex-1 space-y-6">
      <!-- Loading State -->
      <div v-if="loading" class="flex items-center justify-center py-20">
        <div class="text-center">
          <div
            class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-indigo-500 border-t-transparent mb-4"
          />
          <p class="text-slate-600 dark:text-slate-400">Loading analytics data...</p>
        </div>
      </div>

      <!-- Error State -->
      <div
        v-else-if="error"
        class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-6"
      >
        <div class="flex items-center gap-3">
          <svg
            class="w-6 h-6 text-red-600 dark:text-red-400"
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
          <div>
            <h3 class="font-semibold text-red-900 dark:text-red-300">Failed to load analytics</h3>
            <p class="text-sm text-red-700 dark:text-red-400 mt-1">
              {{ error }}
            </p>
          </div>
        </div>
      </div>

      <!-- Main Content -->
      <template v-else>
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
          <div class="flex-1">
            <div class="flex items-center gap-3">
              <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Analytics Dashboard</h2>
              <button
                v-if="selectedTimeRange !== 'custom'"
                :disabled="loading"
                class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                title="Refresh data"
                @click="refreshData"
              >
                <svg
                  :class="['w-5 h-5', loading ? 'animate-spin' : '']"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                  />
                </svg>
              </button>
            </div>
            <div class="flex items-center gap-2 mt-1">
              <p class="text-sm text-slate-600 dark:text-slate-400">
                {{ dateRangeDisplay }}
              </p>
              <template v-if="selectedTimeRange !== 'custom'">
                <span v-if="lastFetchTime" class="text-xs text-slate-500 dark:text-slate-500">
                  • Updated {{ lastFetchTime }}
                </span>
                <span
                  v-if="isDataStale"
                  class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400"
                >
                  Data may be outdated
                </span>
              </template>
            </div>
          </div>

          <!-- Time Range Selector -->
          <div class="flex bg-slate-100 dark:bg-slate-800 rounded-lg p-1">
            <button
              v-for="range in timeRanges"
              :key="range"
              :class="[
                'px-4 py-2 rounded-md text-sm font-medium transition-all',
                selectedTimeRange === range
                  ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-sm'
                  : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white',
              ]"
              @click="selectTimeRange(range)"
            >
              {{ range }}
            </button>
            <button
              :class="[
                'px-4 py-2 rounded-md text-sm font-medium transition-all',
                selectedTimeRange === 'custom'
                  ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-sm'
                  : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white',
              ]"
              @click="showCustomRangePicker = true"
            >
              Other
            </button>
          </div>
        </div>

        <!-- Custom Date Range Picker Modal -->
        <div
          v-if="showCustomRangePicker"
          class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50"
          @click.self="showCustomRangePicker = false"
        >
          <div
            class="bg-white dark:bg-slate-800 rounded-xl shadow-2xl p-6 w-full max-w-md border border-slate-200 dark:border-slate-700"
          >
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-semibold text-slate-900 dark:text-white">
                Custom Date Range
              </h3>
              <button
                class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300"
                @click="showCustomRangePicker = false"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M6 18L18 6M6 6l12 12"
                  />
                </svg>
              </button>
            </div>

            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                  Start Date
                </label>
                <input
                  v-model="customStartDate"
                  type="date"
                  class="w-full px-3 py-2 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                  End Date
                </label>
                <input
                  v-model="customEndDate"
                  type="date"
                  class="w-full px-3 py-2 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                />
              </div>

              <div v-if="dateRangeError" class="text-sm text-red-600 dark:text-red-400">
                {{ dateRangeError }}
              </div>

              <div class="flex gap-3 pt-2">
                <button
                  class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-lg transition-colors"
                  @click="applyCustomRange"
                >
                  Apply
                </button>
                <button
                  class="flex-1 bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 font-medium py-2 px-4 rounded-lg transition-colors"
                  @click="showCustomRangePicker = false"
                >
                  Cancel
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- KPI Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <div
            v-for="(kpi, index) in kpiMetrics"
            :key="index"
            class="bg-white dark:bg-slate-800 rounded-xl shadow-sm hover:shadow-md transition-all p-6 border border-slate-200 dark:border-slate-700 group"
          >
            <div class="flex items-start justify-between mb-4">
              <div
                :class="[
                  'p-3 rounded-lg group-hover:scale-110 transition-transform',
                  getKpiColorClass(kpi.color),
                ]"
              >
                <component :is="getIcon(kpi.icon)" class="w-6 h-6" />
              </div>
            </div>
            <div>
              <p class="text-sm text-slate-600 dark:text-slate-400 mb-1">
                {{ kpi.label }}
              </p>
              <p class="text-3xl font-bold text-slate-900 dark:text-white">
                {{ kpi.value }}
              </p>
            </div>
          </div>
        </div>

        <!-- Charts Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Fleet Status Overview -->
          <div
            class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6 border border-slate-200 dark:border-slate-700"
          >
            <div class="mb-4">
              <h3 class="text-lg font-semibold text-slate-900 dark:text-white">
                Fleet Status Overview
              </h3>
              <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Current turbine status distribution
              </p>
            </div>
            <div class="flex items-center justify-center h-64">
              <svg viewBox="0 0 200 200" class="w-48 h-48">
                <circle
                  v-for="(segment, index) in fleetStatusSegments"
                  :key="index"
                  cx="100"
                  cy="100"
                  r="70"
                  fill="none"
                  :stroke="segment.color"
                  stroke-width="40"
                  :stroke-dasharray="`${segment.length} ${440 - segment.length}`"
                  :stroke-dashoffset="segment.offset"
                  class="transition-all duration-300 hover:stroke-width-[45] cursor-pointer"
                  transform="rotate(-90 100 100)"
                />
                <text
                  x="100"
                  y="95"
                  text-anchor="middle"
                  class="text-2xl font-bold fill-slate-900 dark:fill-white"
                >
                  {{ turbines.length }}
                </text>
                <text
                  x="100"
                  y="110"
                  text-anchor="middle"
                  class="text-xs fill-slate-600 dark:fill-slate-400"
                >
                  turbines
                </text>
              </svg>
            </div>
            <div class="space-y-2 mt-4">
              <div
                v-for="item in fleetStatusData"
                :key="item.status"
                class="flex items-center justify-between p-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors"
              >
                <div class="flex items-center gap-2">
                  <div :class="['w-3 h-3 rounded-full', getStatusColorClass(item.status)]" />
                  <span class="text-sm text-slate-700 dark:text-slate-300 capitalize">{{
                    item.status
                  }}</span>
                </div>
                <div class="text-right">
                  <span class="text-sm font-semibold text-slate-900 dark:text-white">{{
                    item.count
                  }}</span>
                  <span class="text-xs text-slate-500 dark:text-slate-400 ml-1"
                    >({{ item.percent }}%)</span
                  >
                </div>
              </div>
            </div>
          </div>

          <!-- Turbine Performance Comparison -->
          <div
            class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6 border border-slate-200 dark:border-slate-700"
          >
            <div class="mb-4">
              <h3 class="text-lg font-semibold text-slate-900 dark:text-white">
                Power Output Comparison
              </h3>
              <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Current power generation per turbine
              </p>
            </div>
            <div class="h-64 flex items-end justify-around gap-2 px-4">
              <div
                v-for="turbine in sortedTurbinesByPower"
                :key="turbine.id"
                class="flex-1 flex flex-col items-center group"
              >
                <div class="w-full relative">
                  <div
                    :style="{ height: `${getPowerHeight(turbine.metrics?.power_mw)}px` }"
                    :class="[
                      'w-full rounded-t-lg transition-all duration-300 group-hover:opacity-80',
                      getPowerColor(turbine.metrics?.power_mw),
                    ]"
                  >
                    <div
                      class="absolute -top-8 left-1/2 -translate-x-1/2 opacity-0 group-hover:opacity-100 transition-opacity"
                    >
                      <div
                        class="bg-slate-900 dark:bg-slate-700 text-white text-xs px-2 py-1 rounded whitespace-nowrap"
                      >
                        {{ (turbine.metrics?.power_mw || 0).toFixed(2) }} MW
                      </div>
                    </div>
                  </div>
                </div>
                <span class="text-xs text-slate-600 dark:text-slate-400 mt-2 font-medium">{{
                  turbine.id
                }}</span>
              </div>
            </div>
          </div>

          <!-- Alarms by Priority -->
          <div
            class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6 border border-slate-200 dark:border-slate-700"
          >
            <div class="mb-4">
              <h3 class="text-lg font-semibold text-slate-900 dark:text-white">
                Alarms by Priority
              </h3>
              <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Active alarm distribution
              </p>
            </div>
            <div class="h-64 flex items-end justify-around gap-4 px-8">
              <div
                v-for="priority in alarmPriorityData"
                :key="priority.level"
                class="flex-1 flex flex-col items-center group"
              >
                <div class="w-full relative">
                  <div
                    :style="{ height: `${getAlarmHeight(priority.count)}px` }"
                    :class="[
                      'w-full rounded-t-lg transition-all duration-300 group-hover:scale-105',
                      getPriorityBarColor(priority.level),
                    ]"
                  >
                    <div
                      class="absolute -top-6 left-1/2 -translate-x-1/2 text-sm font-bold text-slate-900 dark:text-white"
                    >
                      {{ priority.count }}
                    </div>
                  </div>
                </div>
                <span class="text-xs text-slate-600 dark:text-slate-400 mt-3 font-medium">{{
                  priority.level
                }}</span>
              </div>
            </div>
          </div>

          <!-- Average Wind Speed -->
          <div
            class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6 border border-slate-200 dark:border-slate-700"
          >
            <div class="mb-4">
              <h3 class="text-lg font-semibold text-slate-900 dark:text-white">
                Wind Speed Distribution
              </h3>
              <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Current wind speeds across fleet
              </p>
            </div>
            <div class="space-y-3 mt-6">
              <div v-for="turbine in turbines" :key="turbine.id" class="space-y-1.5">
                <div class="flex items-center justify-between text-sm">
                  <span class="font-medium text-slate-700 dark:text-slate-300">{{
                    turbine.id
                  }}</span>
                  <div class="flex items-center gap-2">
                    <span class="font-bold text-slate-900 dark:text-white">
                      {{ (turbine.metrics?.wind_speed_ms || 0).toFixed(1) }} m/s
                    </span>
                    <span
                      :class="[
                        'w-2 h-2 rounded-full',
                        getWindSpeedDotColor(turbine.metrics?.wind_speed_ms),
                      ]"
                    />
                  </div>
                </div>

                <div
                  class="relative h-2 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden"
                >
                  <div
                    :class="[
                      'h-full rounded-full transition-all duration-500',
                      getWindSpeedBarColor(turbine.metrics?.wind_speed_ms),
                    ]"
                    :style="{ width: `${getWindSpeedPercentage(turbine.metrics?.wind_speed_ms)}%` }"
                  />
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Additional Insights -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div
            class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 rounded-xl p-6 border border-blue-200 dark:border-blue-800"
          >
            <div class="flex items-center gap-3 mb-3">
              <div class="p-2 bg-blue-500 rounded-lg">
                <svg
                  class="w-5 h-5 text-white"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M13 10V3L4 14h7v7l9-11h-7z"
                  />
                </svg>
              </div>
              <h4 class="font-semibold text-slate-900 dark:text-white">Peak Power Output</h4>
            </div>
            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400 mb-1">
              {{ maxPowerOutput.toFixed(2) }} MW
            </p>
            <p class="text-sm text-slate-600 dark:text-slate-400">
              From turbine {{ maxPowerTurbine }}
            </p>
          </div>

          <div
            class="bg-gradient-to-br from-emerald-50 to-emerald-100 dark:from-emerald-900/20 dark:to-emerald-800/20 rounded-xl p-6 border border-emerald-200 dark:border-emerald-800"
          >
            <div class="flex items-center gap-3 mb-3">
              <div class="p-2 bg-emerald-500 rounded-lg">
                <svg
                  class="w-5 h-5 text-white"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M14 5l7 7m0 0l-7 7m7-7H3"
                  />
                </svg>
              </div>
              <h4 class="font-semibold text-slate-900 dark:text-white">Average Wind Speed</h4>
            </div>
            <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mb-1">
              {{ averageWindSpeed.toFixed(1) }} m/s
            </p>
            <p class="text-sm text-slate-600 dark:text-slate-400">
              Across {{ turbines.length }} turbines
            </p>
          </div>

          <div
            class="bg-gradient-to-br from-violet-50 to-violet-100 dark:from-violet-900/20 dark:to-violet-800/20 rounded-xl p-6 border border-violet-200 dark:border-violet-800"
          >
            <div class="flex items-center gap-3 mb-3">
              <div class="p-2 bg-violet-500 rounded-lg">
                <svg
                  class="w-5 h-5 text-white"
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
              <h4 class="font-semibold text-slate-900 dark:text-white">Fleet Availability</h4>
            </div>
            <p class="text-2xl font-bold text-violet-600 dark:text-violet-400 mb-1">
              {{ fleetAvailability.toFixed(1) }}%
            </p>
            <p class="text-sm text-slate-600 dark:text-slate-400">
              {{ runningTurbines }} of {{ turbines.length }} running
            </p>
          </div>
        </div>
      </template>
    </div>

    <!-- History Sidebar -->
    <div class="w-80 flex-shrink-0">
      <div
        class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 sticky top-6"
      >
        <div class="p-4 border-b border-slate-200 dark:border-slate-700">
          <div class="flex items-center justify-between mb-2">
            <div class="flex items-center gap-2">
              <h3 class="font-semibold text-slate-900 dark:text-white">Recent Fetches</h3>
              <button
                class="p-1 rounded hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors"
                title="Add custom date range"
                @click="showCustomRangePicker = true"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 4v16m8-8H4"
                  />
                </svg>
              </button>
            </div>
            <button
              v-if="customFetches.length > 0"
              class="text-xs text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300"
              @click="clearHistory"
            >
              Clear All
            </button>
          </div>
          <p class="text-xs text-slate-500 dark:text-slate-400">Custom date range fetches</p>
        </div>

        <div class="max-h-[calc(100vh-200px)] overflow-y-auto">
          <div v-if="customFetches.length === 0" class="p-6 text-center">
            <svg
              class="w-12 h-12 mx-auto text-slate-300 dark:text-slate-600 mb-3"
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
            <p class="text-sm text-slate-600 dark:text-slate-400">No custom fetches</p>
            <p class="text-xs text-slate-500 dark:text-slate-500 mt-1">
              Use "Other" to create custom date ranges
            </p>
          </div>

          <div
            v-for="entry in customFetches"
            :key="entry.id"
            :class="[
              'p-4 border-b border-slate-100 dark:border-slate-700 cursor-pointer transition-colors',
              isActiveEntry(entry)
                ? 'bg-indigo-50 dark:bg-indigo-900/20 border-l-4 border-l-indigo-500'
                : 'hover:bg-slate-50 dark:hover:bg-slate-700/50',
            ]"
            @click="loadHistoryEntry(entry)"
          >
            <div class="flex items-start justify-between gap-2">
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                  <span
                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400"
                  >
                    Custom
                  </span>
                  <span class="text-xs text-slate-500 dark:text-slate-400">{{
                    entry.timestamp
                  }}</span>
                </div>
                <p class="text-xs text-slate-600 dark:text-slate-400 truncate">
                  {{ formatHistoryDate(entry.startDate) }} - {{ formatHistoryDate(entry.endDate) }}
                </p>
              </div>
              <button
                class="text-slate-400 hover:text-red-600 dark:hover:text-red-400 flex-shrink-0"
                @click.stop="removeHistoryEntry(entry.id)"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M6 18L18 6M6 6l12 12"
                  />
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useScadaService } from '@/composables/api.js'

const { analyticsStore } = useScadaService()

// State
const selectedTimeRange = ref('24h')
const timeRanges = ['24h', '7d', '30d', '90d']
const showCustomRangePicker = ref(false)
const customStartDate = ref('')
const customEndDate = ref('')
const dateRangeError = ref('')

// Computed properties for transformed data
const turbines = computed(() => {
  if (!analyticsStore.data?.turbines) return []

  return analyticsStore.data.turbines.map((t) => ({
    id: t.turbine_id,
    _api_id: t.id,
    status: getStatusFromCode(t.status),
    metrics: {
      power_mw: t.aggregated_metrics.avg_power_kw / 1000 || 0,
      wind_speed_ms: t.aggregated_metrics.avg_wind_speed_ms || 0,
      rotor_rpm: t.current_readings.scada?.rotor_speed_rpm || 0,
      generator_rpm: t.current_readings.scada?.generator_speed_rpm || 0,
      pitch_deg: t.current_readings.scada?.pitch_angle_deg || 0,
      ambient_temp_c: t.current_readings.scada?.ambient_temp_c || 0,
    },
    location: t.location || 'Unknown',
  }))
})

const alarms = computed(() => {
  if (!analyticsStore.data?.turbines) return []

  const priorityMap = { failed: 'Critical', critical: 'Major', warning: 'Warning' }
  const allAlarms = []

  analyticsStore.data.turbines.forEach((turbine) => {
    turbine.alarms.forEach((alarm) => {
      allAlarms.push({
        id: alarm.id,
        turbineId: turbine.id,
        turbine: turbine.turbine_id,
        title: alarm.message,
        priority: priorityMap[alarm.severity] || 'Warning',
        severity: alarm.severity,
        description: alarm.message,
        component: alarm.component,
        time: new Date(alarm.detected_at).toLocaleString(),
        detectedAt: alarm.detected_at,
        status: alarm.status,
        acknowledged: alarm.status === 'acknowledged' || alarm.status === 'resolved',
        resolved: alarm.status === 'resolved',
      })
    })
  })

  return allAlarms
})

const loading = computed(() => analyticsStore.loading)
const error = computed(() => analyticsStore.error)

// Filter to only show custom fetches in history
const customFetches = computed(() => {
  return analyticsStore.recentFetches.filter((fetch) => fetch.timeRange === 'custom')
})

// Display the selected date range
const dateRangeDisplay = computed(() => {
  if (!analyticsStore.data) {
    return 'Loading data...'
  }

  const formatDate = (dateString) => {
    const date = new Date(dateString)
    return date.toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    })
  }

  if (selectedTimeRange.value === 'custom' && customStartDate.value && customEndDate.value) {
    const start = new Date(customStartDate.value)
    const end = new Date(customEndDate.value)
    return `Custom Range: ${start.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })} - ${end.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}`
  }

  if (analyticsStore.data.start_date && analyticsStore.data.end_date) {
    return `${formatDate(analyticsStore.data.start_date)} - ${formatDate(analyticsStore.data.end_date)}`
  }

  return 'Real-time performance metrics and insights'
})

// Force update every minute to keep relative timestamps fresh
const now = ref(new Date())

// Display last fetch time in relative format
const lastFetchTime = computed(() => {
  if (!analyticsStore.currentFetch?.fullTimestamp) return null

  // Reference now.value to make this reactive to time updates
  const currentTime = now.value
  const fetchTime = new Date(analyticsStore.currentFetch.fullTimestamp)
  const diffMs = currentTime - fetchTime
  const diffMins = Math.floor(diffMs / 60000)
  const diffHours = Math.floor(diffMs / 3600000)
  const diffDays = Math.floor(diffMs / 86400000)

  if (diffMins < 1) return 'just now'
  if (diffMins < 60) return `${diffMins} min ago`
  if (diffHours < 24) return `${diffHours}h ago`
  return `${diffDays}d ago`
})

// Check if data is stale (older than 5 minutes)
const isDataStale = computed(() => {
  if (!analyticsStore.currentFetch?.fullTimestamp) return false

  // Reference now.value to make this reactive to time updates
  const currentTime = now.value
  const fetchTime = new Date(analyticsStore.currentFetch.fullTimestamp)
  const diffMs = currentTime - fetchTime
  const diffMins = Math.floor(diffMs / 60000)

  return diffMins >= 5
})

// Helper function to map status codes to names
const getStatusFromCode = (statusCode) => {
  const statusMap = {
    100: 'running',
    200: 'idle',
    300: 'maintenance',
    400: 'stopped',
    500: 'stopped',
  }
  return statusMap[statusCode] || 'error'
}

// Handle predefined time range selection
const selectTimeRange = (range) => {
  selectedTimeRange.value = range
  customStartDate.value = ''
  customEndDate.value = ''
}

// Refresh data - force fetch with current time range
const refreshData = () => {
  fetchAnalytics()
}

// Validate and apply custom date range
const applyCustomRange = () => {
  dateRangeError.value = ''

  if (!customStartDate.value || !customEndDate.value) {
    dateRangeError.value = 'Please select both start and end dates'
    return
  }

  const start = new Date(customStartDate.value)
  const end = new Date(customEndDate.value)

  if (start > end) {
    dateRangeError.value = 'Start date must be before end date'
    return
  }

  if (end > new Date()) {
    dateRangeError.value = 'End date cannot be in the future'
    return
  }

  selectedTimeRange.value = 'custom'
  showCustomRangePicker.value = false
  fetchAnalytics()
}

// Fetch analytics data
const fetchAnalytics = async () => {
  try {
    if (selectedTimeRange.value === 'custom' && customStartDate.value && customEndDate.value) {
      await analyticsStore.fetchAnalytics('custom', customStartDate.value, customEndDate.value)
    } else {
      await analyticsStore.fetchAnalytics(selectedTimeRange.value)
    }
  } catch (err) {
    console.error('Failed to fetch analytics:', err)
  }
}

// Track if we're loading from cache to avoid auto-fetch
const isLoadingFromCache = ref(false)

// Watch for time range changes and refetch
watch(selectedTimeRange, (newVal) => {
  // Skip auto-fetch if we're loading from cache or switching to custom
  if (isLoadingFromCache.value || newVal === 'custom') {
    isLoadingFromCache.value = false
    return
  }

  // Auto-fetch for predefined ranges when user clicks the button
  if (newVal !== 'custom') {
    fetchAnalytics()
  }
})

// History management
const loadHistoryEntry = (entry) => {
  analyticsStore.loadFromHistory(entry)

  // Set flag to prevent auto-fetch when updating selectedTimeRange
  isLoadingFromCache.value = true

  // Update UI state to match the loaded entry
  selectedTimeRange.value = entry.timeRange
  if (entry.timeRange === 'custom') {
    // Extract date portion (YYYY-MM-DD) from ISO string or date string
    customStartDate.value = entry.startDate.includes('T')
      ? entry.startDate.split('T')[0]
      : entry.startDate
    customEndDate.value = entry.endDate.includes('T') ? entry.endDate.split('T')[0] : entry.endDate
  }
}

const removeHistoryEntry = (entryId) => {
  analyticsStore.removeFromHistory(entryId)

  // If we deleted the current entry, fetch fresh data
  if (!analyticsStore.currentFetch) {
    fetchAnalytics()
  }
}

const clearHistory = () => {
  if (confirm('Are you sure you want to clear all custom date range history?')) {
    // Remove only custom fetches from history
    const customIds = customFetches.value.map((f) => f.id)
    customIds.forEach((id) => analyticsStore.removeFromHistory(id))

    // If current fetch was a custom one, fetch fresh data
    if (analyticsStore.currentFetch?.timeRange === 'custom') {
      fetchAnalytics()
    }
  }
}

const isActiveEntry = (entry) => {
  return analyticsStore.currentFetch?.id === entry.id
}

const formatHistoryDate = (dateString) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
}

// Setup interval for timestamp updates
let intervalId = null

// Fetch initial data
onMounted(() => {
  // If we have a saved fetch, load it from cache
  if (analyticsStore.currentFetch && analyticsStore.currentFetch.payload) {
    analyticsStore.data = analyticsStore.currentFetch.payload

    // Set flag to prevent auto-fetch when updating selectedTimeRange
    isLoadingFromCache.value = true
    selectedTimeRange.value = analyticsStore.currentFetch.timeRange

    if (analyticsStore.currentFetch.timeRange === 'custom') {
      // Extract date portion (YYYY-MM-DD) from ISO string or date string
      customStartDate.value = analyticsStore.currentFetch.startDate.includes('T')
        ? analyticsStore.currentFetch.startDate.split('T')[0]
        : analyticsStore.currentFetch.startDate
      customEndDate.value = analyticsStore.currentFetch.endDate.includes('T')
        ? analyticsStore.currentFetch.endDate.split('T')[0]
        : analyticsStore.currentFetch.endDate
    }
  } else {
    // No cache - fetch fresh data with default time range
    fetchAnalytics()
  }

  // Update time every minute to keep relative timestamps accurate
  intervalId = setInterval(() => {
    now.value = new Date()
  }, 60000)
})

// Cleanup interval on unmount
onUnmounted(() => {
  if (intervalId) {
    clearInterval(intervalId)
  }
})

// Computed - Real KPIs from Data
const totalEnergyOutput = computed(() => {
  const total = turbines.value.reduce((sum, t) => sum + (t.metrics?.power_mw || 0), 0)
  return `${total.toFixed(1)} MW`
})

const averageWindSpeed = computed(() => {
  if (turbines.value.length === 0) return 0
  const total = turbines.value.reduce((sum, t) => sum + (t.metrics?.wind_speed_ms || 0), 0)
  return total / turbines.value.length
})

const fleetAvailability = computed(() => {
  if (turbines.value.length === 0) return 0
  const running = turbines.value.filter((t) => t.status === 'running').length
  return (running / turbines.value.length) * 100
})

const runningTurbines = computed(() => {
  return turbines.value.filter((t) => t.status === 'running').length
})

const activeAlarmsCount = computed(() => {
  return alarms.value.filter((a) => !a.acknowledged).length
})

const kpiMetrics = computed(() => [
  {
    label: 'Total Power Output',
    value: totalEnergyOutput.value,
    icon: 'zap',
    color: 'emerald',
  },
  {
    label: 'Average Wind Speed',
    value: `${averageWindSpeed.value.toFixed(1)} m/s`,
    icon: 'wind',
    color: 'blue',
  },
  {
    label: 'Fleet Availability',
    value: `${fleetAvailability.value.toFixed(1)}%`,
    icon: 'activity',
    color: 'violet',
  },
  {
    label: 'Active Alarms',
    value: activeAlarmsCount.value,
    icon: 'alert',
    color: 'orange',
  },
])

// Fleet Status Distribution
const fleetStatusData = computed(() => {
  const statusCounts = turbines.value.reduce((acc, t) => {
    acc[t.status] = (acc[t.status] || 0) + 1
    return acc
  }, {})

  return Object.entries(statusCounts).map(([status, count]) => ({
    status,
    count,
    percent: ((count / turbines.value.length) * 100).toFixed(0),
  }))
})

const fleetStatusSegments = computed(() => {
  const circumference = 2 * Math.PI * 70
  let offset = 0

  return fleetStatusData.value.map((item) => {
    const length = (item.percent / 100) * circumference
    const segment = {
      length,
      offset: -offset,
      color: getStatusColor(item.status),
    }
    offset += length
    return segment
  })
})

// Sorted Turbines by Power
const sortedTurbinesByPower = computed(() => {
  return [...turbines.value].sort((a, b) => (b.metrics?.power_mw || 0) - (a.metrics?.power_mw || 0))
})

// Max Power Stats
const maxPowerOutput = computed(() => {
  if (turbines.value.length === 0) return 0
  return Math.max(...turbines.value.map((t) => t.metrics?.power_mw || 0))
})

const maxPowerTurbine = computed(() => {
  if (turbines.value.length === 0) return 'N/A'
  const maxTurbine = turbines.value.reduce((max, t) =>
    (t.metrics?.power_mw || 0) > (max.metrics?.power_mw || 0) ? t : max
  )
  return maxTurbine.id
})

// Alarms by Priority
const alarmPriorityData = computed(() => {
  const priorities = ['Critical', 'Major', 'Warning', 'Minor']
  return priorities.map((level) => ({
    level,
    count: alarms.value.filter((a) => a.priority === level).length,
  }))
})

// Methods
const getIcon = (iconName) => {
  const icons = {
    zap: {
      template:
        '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>',
    },
    wind: {
      template:
        '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>',
    },
    activity: {
      template:
        '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>',
    },
    alert: {
      template:
        '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>',
    },
  }
  return icons[iconName] || icons.zap
}

const getKpiColorClass = (color) => {
  const classes = {
    emerald: 'bg-emerald-500 text-emerald-50',
    blue: 'bg-blue-500 text-blue-50',
    violet: 'bg-violet-500 text-violet-50',
    orange: 'bg-orange-500 text-orange-50',
  }
  return classes[color] || 'bg-slate-500 text-slate-50'
}

const getStatusColor = (status) => {
  const colors = {
    running: '#10b981',
    idle: '#3b82f6',
    maintenance: '#f59e0b',
    stopped: '#ef4444',
    error: '#dc2626',
  }
  return colors[status] || '#64748b'
}

const getStatusColorClass = (status) => {
  const classes = {
    running: 'bg-green-500',
    idle: 'bg-blue-500',
    maintenance: 'bg-amber-500',
    stopped: 'bg-red-500',
    error: 'bg-red-600',
  }
  return classes[status] || 'bg-slate-500'
}

const getPowerHeight = (power) => {
  const maxHeight = 200
  const maxPower = 3 // Assuming 3MW max
  return Math.min((power / maxPower) * maxHeight, maxHeight)
}

const getPowerColor = (power) => {
  if (power >= 2.5) return 'bg-emerald-500'
  if (power >= 2.0) return 'bg-blue-500'
  if (power >= 1.0) return 'bg-amber-500'
  return 'bg-slate-400'
}

const getAlarmHeight = (count) => {
  const maxHeight = 200
  const maxCount = Math.max(...alarmPriorityData.value.map((p) => p.count), 1)
  return (count / maxCount) * maxHeight
}

const getPriorityBarColor = (level) => {
  const colors = {
    Critical: 'bg-red-500',
    Major: 'bg-orange-500',
    Warning: 'bg-amber-500',
    Minor: 'bg-blue-500',
  }
  return colors[level] || 'bg-slate-500'
}

const getWindSpeedPercentage = (speed) => {
  const maxSpeed = 25 // m/s
  return Math.min((speed / maxSpeed) * 100, 100)
}

const getWindSpeedBarColor = (speed) => {
  if (speed >= 15) return 'bg-emerald-500'
  if (speed >= 10) return 'bg-blue-500'
  if (speed >= 5) return 'bg-amber-500'
  return 'bg-slate-400'
}

const getWindSpeedDotColor = (speed) => {
  if (speed >= 15) return 'bg-emerald-500'
  if (speed >= 10) return 'bg-blue-500'
  if (speed >= 5) return 'bg-amber-500'
  return 'bg-slate-400'
}
</script>
