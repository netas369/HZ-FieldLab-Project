<template>
  <div class="space-y-6 animate-fade-in">
    <!-- Header -->
    <div class="flex items-center justify-between flex-wrap gap-4">
      <div>
        <h3 class="text-lg font-bold text-slate-800 dark:text-white flex items-center gap-2">
          <svg
            class="w-5 h-5 text-indigo-500"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"
            />
          </svg>
          Predictive Maintenance Forecast
        </h3>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
          Statistical trend analysis with R² confidence scoring
        </p>
      </div>
      <div class="flex items-center gap-3">
        <span
          v-if="analysisConfidence"
          class="text-xs px-2 py-1 rounded-full font-medium"
          :class="getConfidenceBadge(analysisConfidence)"
        >
          {{ analysisConfidence }} Confidence
        </span>
        <span
          class="text-xs font-mono text-slate-400 bg-slate-100 dark:bg-slate-800 px-2 py-1 rounded"
        >
          {{ formatDate(deteriorationData.calculation_timestamp) }}
        </span>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Fastest Deteriorating Component Card -->
      <div
        v-if="fastestDeteriorating"
        class="bg-gradient-to-br from-red-50 to-white dark:from-red-900/20 dark:to-slate-800 border border-red-100 dark:border-red-900/30 rounded-xl p-6 relative overflow-hidden shadow-sm"
      >
        <div
          class="absolute -right-6 -top-6 w-32 h-32 bg-red-500/10 rounded-full blur-2xl pointer-events-none"
        />
        <div class="relative z-10">
          <div class="flex items-center gap-2 mb-3">
            <span class="flex h-2 w-2 relative">
              <span
                class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"
              />
              <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500" />
            </span>
            <div class="text-xs font-bold text-red-600 dark:text-red-400 uppercase tracking-wide">
              Attention Required
            </div>
          </div>

          <div class="text-2xl font-bold text-slate-900 dark:text-white capitalize mb-1">
            {{ formatComponentName(fastestDeteriorating.component) }}
          </div>
          <div class="text-sm text-slate-500 dark:text-slate-400 mb-4">
            Fastest deteriorating component
          </div>

          <div class="space-y-3">
            <!-- Health Score -->
            <div
              class="flex items-center justify-between p-3 bg-white/60 dark:bg-slate-900/50 rounded-lg border border-red-100 dark:border-red-900/20"
            >
              <span class="text-sm text-slate-600 dark:text-slate-400">Health Score</span>
              <span
                class="font-mono font-bold text-lg"
                :class="getHealthTextColor(fastestDeteriorating.health_score)"
              >
                {{ fastestDeteriorating.health_score?.toFixed(1) ?? '-' }}
              </span>
            </div>

            <!-- Deterioration Level -->
            <div
              class="flex items-center justify-between p-3 bg-white/60 dark:bg-slate-900/50 rounded-lg border border-red-100 dark:border-red-900/20"
            >
              <span class="text-sm text-slate-600 dark:text-slate-400">Trend</span>
              <span
                class="px-2 py-0.5 rounded text-xs font-bold uppercase"
                :class="getDeteriorationBadge(fastestDeteriorating.deterioration_level)"
              >
                {{ fastestDeteriorating.deterioration_level ?? 'UNKNOWN' }}
              </span>
            </div>

            <!-- Days to Critical -->
            <div
              class="flex items-center justify-between p-3 bg-white/60 dark:bg-slate-900/50 rounded-lg border border-red-100 dark:border-red-900/20"
            >
              <span class="text-sm text-slate-600 dark:text-slate-400">Est. Days to Critical</span>
              <span
                class="font-mono font-bold text-slate-900 dark:text-white flex items-center gap-1"
              >
                <svg
                  v-if="
                    fastestDeteriorating.days_to_critical &&
                      fastestDeteriorating.days_to_critical < 90
                  "
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
                {{ fastestDeteriorating.days_to_critical ?? '—' }}
                <span
                  v-if="fastestDeteriorating.days_to_critical"
                  class="text-slate-400 text-sm font-normal"
                >days</span>
              </span>
            </div>

            <!-- Analysis Confidence -->
            <div
              v-if="fastestDeteriorating.analysis_confidence"
              class="flex items-center justify-between p-3 bg-white/60 dark:bg-slate-900/50 rounded-lg border border-red-100 dark:border-red-900/20"
            >
              <span class="text-sm text-slate-600 dark:text-slate-400">Analysis Confidence</span>
              <span
                class="px-2 py-0.5 rounded text-xs font-medium"
                :class="getConfidenceBadge(fastestDeteriorating.analysis_confidence)"
              >
                {{ fastestDeteriorating.analysis_confidence }}
              </span>
            </div>
          </div>

          <!-- Recommendation -->
          <div
            v-if="fastestDeteriorating.recommendation"
            class="mt-4 p-3 bg-red-100/50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800"
          >
            <p class="text-xs text-red-700 dark:text-red-300">
              💡 {{ fastestDeteriorating.recommendation }}
            </p>
          </div>
        </div>
      </div>

      <!-- No Issues Card (when nothing needs attention) -->
      <div
        v-else
        class="bg-gradient-to-br from-green-50 to-white dark:from-green-900/20 dark:to-slate-800 border border-green-100 dark:border-green-900/30 rounded-xl p-6 relative overflow-hidden shadow-sm"
      >
        <div
          class="absolute -right-6 -top-6 w-32 h-32 bg-green-500/10 rounded-full blur-2xl pointer-events-none"
        />
        <div class="relative z-10 text-center py-6">
          <svg
            class="w-16 h-16 mx-auto text-green-500 mb-4"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
            />
          </svg>
          <div class="text-xl font-bold text-slate-900 dark:text-white mb-2">
            All Systems Healthy
          </div>
          <p class="text-sm text-slate-500 dark:text-slate-400">
            No components require immediate attention
          </p>
        </div>
      </div>

      <!-- Component Forecasts Table -->
      <div
        class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden shadow-sm flex flex-col"
      >
        <div
          class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/20 flex items-center justify-between"
        >
          <h4 class="font-semibold text-slate-800 dark:text-white text-sm">
            Component Forecasts
          </h4>
          <span class="text-xs text-slate-400">{{ sortedTrends.length }} components</span>
        </div>
        <div class="overflow-x-auto flex-1">
          <table class="w-full text-sm text-left">
            <thead
              class="bg-slate-50 dark:bg-slate-900/50 text-xs text-slate-500 uppercase font-semibold sticky top-0"
            >
              <tr>
                <th class="px-6 py-3">
                  Component
                </th>
                <th class="px-6 py-3 text-right">
                  Health
                </th>
                <th class="px-6 py-3 text-center">
                  Status
                </th>
                <th class="px-6 py-3 text-center">
                  Trend
                </th>
                <th class="px-6 py-3 text-center">
                  Confidence
                </th>
                <th class="px-6 py-3 text-right">
                  Days to Critical
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
              <tr
                v-for="trend in sortedTrends"
                :key="trend.component"
                class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors"
                :class="{
                  'bg-red-50/50 dark:bg-red-900/10':
                    trend.days_to_critical && trend.days_to_critical < 60,
                }"
              >
                <td class="px-6 py-3 font-medium text-slate-900 dark:text-white capitalize">
                  <div class="flex items-center gap-2">
                    <div
                      class="w-2 h-2 rounded-full"
                      :class="getHealthDot(trend.health_score)"
                    />
                    {{ formatComponentName(trend.component) }}
                  </div>
                </td>
                <td class="px-6 py-3 text-right">
                  <span
                    class="font-mono font-medium"
                    :class="getHealthTextColor(trend.health_score)"
                  >
                    {{ trend.health_score?.toFixed(1) ?? '-' }}
                  </span>
                </td>
                <td class="px-6 py-3 text-center">
                  <span
                    class="px-2 py-0.5 rounded text-[10px] font-bold uppercase"
                    :class="getStatusBadge(trend.status)"
                  >
                    {{ trend.status }}
                  </span>
                </td>
                <td class="px-6 py-3 text-center">
                  <span
                    class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase border"
                    :class="getDeteriorationBadge(trend.deterioration_level)"
                  >
                    {{ trend.deterioration_level ?? 'UNKNOWN' }}
                  </span>
                </td>
                <td class="px-6 py-3 text-center">
                  <span
                    v-if="trend.analysis_confidence"
                    class="px-2 py-0.5 rounded text-[10px] font-medium"
                    :class="getConfidenceBadge(trend.analysis_confidence)"
                  >
                    {{ trend.analysis_confidence }}
                  </span>
                  <span
                    v-else
                    class="text-slate-400"
                  >—</span>
                </td>
                <td class="px-6 py-3 text-right font-mono">
                  <div class="flex items-center justify-end gap-2">
                    <span :class="getDaysToCriticalColor(trend.days_to_critical)">
                      {{ trend.days_to_critical ? trend.days_to_critical + ' days' : '—' }}
                    </span>
                    <svg
                      v-if="trend.days_to_critical && trend.days_to_critical < 60"
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
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Legend -->
    <div
      class="flex flex-wrap gap-4 text-xs text-slate-500 dark:text-slate-400 pt-4 border-t border-slate-200 dark:border-slate-700"
    >
      <div class="flex items-center gap-2">
        <span class="font-semibold">Trend Levels:</span>
      </div>
      <div class="flex items-center gap-1">
        <span class="w-3 h-3 rounded bg-emerald-500" />
        <span>STABLE (safe)</span>
      </div>
      <div class="flex items-center gap-1">
        <span class="w-3 h-3 rounded bg-amber-500" />
        <span>SLOW/MODERATE DECLINE</span>
      </div>
      <div class="flex items-center gap-1">
        <span class="w-3 h-3 rounded bg-red-500" />
        <span>RAPID/CRITICAL DECLINE</span>
      </div>
      <div class="ml-auto flex items-center gap-1">
        <span class="font-semibold">R² Confidence:</span>
        <span>HIGH (≥0.8)</span>
        <span>MEDIUM (≥0.5)</span>
        <span>LOW (&lt;0.5)</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  deteriorationData: { type: Object, required: true },
})

// Computed properties
const sortedTrends = computed(() => {
  if (!props.deteriorationData?.trends) return []

  return [...props.deteriorationData.trends].sort((a, b) => {
    // First sort by days_to_critical (soonest first)
    if (a.days_to_critical !== null && b.days_to_critical === null) return -1
    if (a.days_to_critical === null && b.days_to_critical !== null) return 1
    if (a.days_to_critical !== null && b.days_to_critical !== null) {
      return a.days_to_critical - b.days_to_critical
    }
    // Then by health score (lowest first)
    return (a.health_score ?? 100) - (b.health_score ?? 100)
  })
})

const fastestDeteriorating = computed(() => {
  return props.deteriorationData?.fastest_deteriorating ?? sortedTrends.value[0]
})

const analysisConfidence = computed(() => {
  // Get the most common confidence level across all trends
  const confidences = sortedTrends.value.map((t) => t.analysis_confidence).filter(Boolean)

  if (confidences.length === 0) return null

  const counts = confidences.reduce((acc, c) => {
    acc[c] = (acc[c] || 0) + 1
    return acc
  }, {})

  return Object.entries(counts).sort((a, b) => b[1] - a[1])[0]?.[0]
})

// Methods
const formatDate = (dateStr) => {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

const formatComponentName = (name) => name?.replace(/_/g, ' ') ?? ''

// Style helpers
const getHealthDot = (score) => {
  if (score === null || score === undefined) return 'bg-slate-400'
  if (score >= 80) return 'bg-green-500'
  if (score >= 70) return 'bg-blue-500'
  if (score >= 50) return 'bg-amber-500'
  if (score >= 30) return 'bg-orange-500'
  return 'bg-red-500'
}

const getHealthTextColor = (score) => {
  if (score === null || score === undefined) return 'text-slate-400'
  if (score >= 80) return 'text-green-600 dark:text-green-400'
  if (score >= 70) return 'text-blue-600 dark:text-blue-400'
  if (score >= 50) return 'text-amber-600 dark:text-amber-400'
  if (score >= 30) return 'text-orange-600 dark:text-orange-400'
  return 'text-red-600 dark:text-red-400'
}

const getStatusBadge = (status) => {
  switch (status) {
    case 'EXCELLENT':
      return 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
    case 'GOOD':
      return 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'
    case 'FAIR':
      return 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400'
    case 'POOR':
      return 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400'
    case 'CRITICAL':
      return 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'
    default:
      return 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400'
  }
}

const getDeteriorationBadge = (level) => {
  switch (level) {
    case 'STABLE':
      return 'bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-900/20 dark:text-emerald-400 dark:border-emerald-900/30'
    case 'SLOW_DECLINE':
      return 'bg-blue-50 text-blue-700 border-blue-100 dark:bg-blue-900/20 dark:text-blue-400 dark:border-blue-900/30'
    case 'MODERATE_DECLINE':
      return 'bg-amber-50 text-amber-700 border-amber-100 dark:bg-amber-900/20 dark:text-amber-400 dark:border-amber-900/30'
    case 'RAPID_DECLINE':
      return 'bg-orange-50 text-orange-700 border-orange-100 dark:bg-orange-900/20 dark:text-orange-400 dark:border-orange-900/30'
    case 'CRITICAL_DECLINE':
    case 'CRITICAL_NOW':
    case 'CRITICAL_IMMINENT':
      return 'bg-red-50 text-red-700 border-red-100 dark:bg-red-900/20 dark:text-red-400 dark:border-red-900/30'
    default:
      return 'bg-slate-50 text-slate-600 border-slate-100 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700'
  }
}

const getConfidenceBadge = (confidence) => {
  switch (confidence) {
    case 'HIGH':
      return 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
    case 'MEDIUM':
      return 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400'
    case 'LOW':
      return 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400'
    case 'VERY_LOW':
      return 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400'
    default:
      return 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400'
  }
}

const getDaysToCriticalColor = (days) => {
  if (days === null || days === undefined) return 'text-slate-400'
  if (days <= 30) return 'text-red-600 dark:text-red-400 font-bold'
  if (days <= 90) return 'text-orange-600 dark:text-orange-400 font-semibold'
  if (days <= 180) return 'text-amber-600 dark:text-amber-400'
  return 'text-slate-600 dark:text-slate-400'
}
</script>
