<template>
  <div class="space-y-6 animate-fade-in">

    <div class="flex items-center justify-between">
      <div>
        <h3 class="text-lg font-bold text-slate-800 dark:text-white flex items-center gap-2">
          <svg class="w-5 h-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
          Predictive Maintenance Forecast
        </h3>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
          AI-driven deterioration analysis based on the last {{ deteriorationData.period_days }} days of operation.
        </p>
      </div>
      <div class="text-xs font-mono text-slate-400 bg-slate-100 dark:bg-slate-800 px-2 py-1 rounded">
        Model Updated: {{ new Date(deteriorationData.calculation_timestamp).toLocaleDateString() }}
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

      <div class="bg-gradient-to-br from-red-50 to-white dark:from-red-900/20 dark:to-slate-800 border border-red-100 dark:border-red-900/30 rounded-xl p-6 relative overflow-hidden shadow-sm">
        <div class="absolute -right-6 -top-6 w-32 h-32 bg-red-500/10 rounded-full blur-2xl pointer-events-none"></div>
        <div class="relative z-10">
          <div class="flex items-center gap-2 mb-3">
              <span class="flex h-2 w-2 relative">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
              </span>
            <div class="text-xs font-bold text-red-600 dark:text-red-400 uppercase tracking-wide">Attention Required</div>
          </div>

          <div class="text-2xl font-bold text-slate-900 dark:text-white capitalize mb-1">
            {{ deteriorationData.fastest_deteriorating.component.replace(/_/g, ' ') }}
          </div>
          <div class="text-sm text-slate-500 dark:text-slate-400 mb-6">Fastest deteriorating component</div>

          <div class="space-y-3">
            <div class="flex items-center justify-between p-3 bg-white/60 dark:bg-slate-900/50 rounded-lg border border-red-100 dark:border-red-900/20">
              <span class="text-sm text-slate-600 dark:text-slate-400">Deterioration Rate</span>
              <span class="font-mono font-bold text-red-600 dark:text-red-400 flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
                    {{ deteriorationData.fastest_deteriorating.deterioration_rate_per_year }} pts/yr
                 </span>
            </div>
            <div class="flex items-center justify-between p-3 bg-white/60 dark:bg-slate-900/50 rounded-lg border border-red-100 dark:border-red-900/20">
              <span class="text-sm text-slate-600 dark:text-slate-400">Estimated Criticality</span>
              <span class="font-mono font-bold text-slate-900 dark:text-white">
                    {{ deteriorationData.fastest_deteriorating.days_to_critical }} days
                 </span>
            </div>
          </div>
        </div>
      </div>

      <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden shadow-sm flex flex-col">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/20">
          <h4 class="font-semibold text-slate-800 dark:text-white text-sm">Component Forecasts</h4>
        </div>
        <div class="overflow-x-auto flex-1">
          <table class="w-full text-sm text-left">
            <thead class="bg-slate-50 dark:bg-slate-900/50 text-xs text-slate-500 uppercase font-semibold">
            <tr>
              <th class="px-6 py-3">Component</th>
              <th class="px-6 py-3 text-right">Current Score</th>
              <th class="px-6 py-3 text-right">Annual Rate</th>
              <th class="px-6 py-3 text-center">Trend</th>
              <th class="px-6 py-3 text-right">Days to Critical</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
            <tr v-for="trend in sortedTrends" :key="trend.component" class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
              <td class="px-6 py-3 font-medium text-slate-900 dark:text-white capitalize">
                <div class="flex items-center gap-2">
                  <div class="w-2 h-2 rounded-full" :class="getHealthDot(trend.health_score)"></div>
                  {{ trend.component.replace(/_/g, ' ') }}
                </div>
              </td>
              <td class="px-6 py-3 text-right font-mono text-slate-600 dark:text-slate-400">
                {{ trend.health_score.toFixed(1) }}
              </td>
              <td class="px-6 py-3 text-right font-mono" :class="trend.deterioration_rate_per_year > 0 ? 'text-red-500' : 'text-slate-400'">
                {{ trend.deterioration_rate_per_year > 0 ? '+' : ''}}{{ trend.deterioration_rate_per_year }}
              </td>
              <td class="px-6 py-3 text-center">
                       <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase border"
                             :class="trend.deterioration_level === 'STABLE'
                               ? 'bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-900/20 dark:text-emerald-400 dark:border-emerald-900/30'
                               : 'bg-red-50 text-red-700 border-red-100 dark:bg-red-900/20 dark:text-red-400 dark:border-red-900/30'">
                          {{ trend.deterioration_level }}
                       </span>
              </td>
              <td class="px-6 py-3 text-right font-mono">
                       <span :class="trend.days_to_critical && trend.days_to_critical < 90 ? 'text-red-600 font-bold' : 'text-slate-600 dark:text-slate-400'">
                         {{ trend.days_to_critical ? trend.days_to_critical + ' days' : '-' }}
                       </span>
              </td>
            </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  deteriorationData: { type: Object, required: true }
})

const sortedTrends = computed(() => {
  if (!props.deteriorationData?.trends) return [];
  // Sort by deterioration rate descending
  return [...props.deteriorationData.trends].sort((a, b) => b.deterioration_rate_per_year - a.deterioration_rate_per_year);
})

const getHealthDot = (score) => {
  if (score >= 90) return 'bg-green-500'
  if (score >= 70) return 'bg-amber-500'
  return 'bg-red-500'
}
</script>