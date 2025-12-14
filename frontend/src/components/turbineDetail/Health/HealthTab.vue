<template>
  <div class="space-y-8 animate-fade-in">

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="md:col-span-2 bg-gradient-to-br from-white to-slate-50 dark:from-slate-800 dark:to-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-700 p-6 flex items-center justify-between shadow-sm relative overflow-hidden">
        <div class="absolute right-0 top-0 w-32 h-32 bg-current opacity-5 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2" :class="getHealthColor(healthData.overall_health.overall_health_score)"></div>
        <div>
          <h3 class="text-lg font-semibold text-slate-700 dark:text-slate-300 mb-1">Overall Turbine Health</h3>
          <p class="text-slate-500 dark:text-slate-400 text-sm mb-4">Aggregated score based on component performance & penalties</p>
          <div class="flex items-center gap-3">
            <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide border bg-white dark:bg-slate-900" :class="getStatusBadgeColor(healthData.overall_health.status)">{{ healthData.overall_health.status }}</span>
            <span v-if="healthData.overall_health.critical_components.length > 0" class="text-xs text-red-500 font-medium flex items-center gap-1">
              {{ healthData.overall_health.critical_components.length }} Critical Issues
            </span>
          </div>
        </div>
        <div class="relative w-24 h-24 flex items-center justify-center">
          <svg class="w-full h-full -rotate-90" viewBox="0 0 36 36">
            <path class="text-slate-200 dark:text-slate-700" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="currentColor" stroke-width="3" />
            <path :class="getHealthColor(healthData.overall_health.overall_health_score)" :stroke-dasharray="`${healthData.overall_health.overall_health_score}, 100`" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" />
          </svg>
          <div class="absolute flex flex-col items-center">
            <span class="text-2xl font-bold text-slate-900 dark:text-white">{{ healthData.overall_health.overall_health_score.toFixed(0) }}</span>
            <span class="text-[10px] uppercase font-bold text-slate-400">Score</span>
          </div>
        </div>
      </div>
      <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6 flex flex-col justify-center">
        <div class="text-sm text-slate-500 dark:text-slate-400 mb-2 font-medium uppercase tracking-wider">Analysis Window</div>
        <div class="text-2xl font-bold text-slate-900 dark:text-white mb-1">{{ healthData.period_days }} Days</div>
        <div class="text-xs text-slate-400">Updated: {{ new Date(healthData.calculation_timestamp).toLocaleDateString() }}</div>
      </div>
    </div>

    <div>
      <h3 class="text-lg font-bold text-slate-800 dark:text-white mb-4 flex items-center gap-2">
        <svg class="w-5 h-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
        Current Component Health
      </h3>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div v-for="(comp, key) in healthData.components" :key="key" class="bg-white dark:bg-slate-800 rounded-xl border transition-all duration-200 overflow-hidden hover:shadow-md group" :class="expandedComponent === key ? 'border-indigo-500 ring-1 ring-indigo-500/20' : 'border-slate-200 dark:border-slate-700'">
          <div class="p-4 cursor-pointer flex items-center justify-between" @click="toggleComponent(key)">
            <div class="flex items-center gap-3">
              <div class="relative w-10 h-10 flex items-center justify-center rounded-full bg-slate-50 dark:bg-slate-900 border" :class="getBorderColor(comp.health_score)">
                <span class="font-bold text-sm" :class="getTextColor(comp.health_score)">{{ comp.health_score.toFixed(0) }}</span>
              </div>
              <div>
                <h4 class="font-semibold text-slate-900 dark:text-white capitalize text-sm">{{ comp.component.replace(/_/g, ' ') }}</h4>
                <div class="flex items-center gap-2 mt-0.5">
                  <span class="text-[10px] font-bold uppercase tracking-wide" :class="getTextColor(comp.health_score)">{{ comp.status }}</span>
                </div>
              </div>
            </div>
            <button class="text-slate-400 group-hover:text-indigo-500 transition-colors">
              <svg class="w-5 h-5 transition-transform duration-200" :class="expandedComponent === key ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
            </button>
          </div>
          <div v-show="expandedComponent === key" class="border-t border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/30 p-4 text-sm animate-in slide-in-from-top-2 duration-200">
            <div class="grid grid-cols-2 gap-4 mb-4">
              <div class="bg-white dark:bg-slate-800 p-2 rounded border border-slate-200 dark:border-slate-700">
                <div class="text-[10px] text-slate-400 uppercase">365 Days Ago</div>
                <div class="font-mono font-medium text-slate-700 dark:text-slate-300">{{ comp.trend_analysis.health_365_days_ago }}</div>
              </div>
              <div class="bg-white dark:bg-slate-800 p-2 rounded border border-slate-200 dark:border-slate-700">
                <div class="text-[10px] text-slate-400 uppercase">Trend</div>
                <div class="font-mono font-medium" :class="comp.trend_analysis.deterioration_level === 'STABLE' ? 'text-green-500' : 'text-red-500'">
                  {{ comp.trend_analysis.deterioration_level }}
                </div>
              </div>
            </div>
            <div v-if="comp.penalties.total > 0" class="mb-4">
              <h5 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase mb-2">Health Penalties</h5>
              <ul class="space-y-1">
                <li v-for="(val, pKey) in comp.penalties" :key="pKey" v-show="val > 0 && pKey !== 'total'" class="flex justify-between text-xs">
                  <span class="text-slate-600 dark:text-slate-300 capitalize">{{ pKey.replace(/_/g, ' ') }}</span>
                  <span class="text-red-500 font-medium">-{{ val }}</span>
                </li>
              </ul>
            </div>
            <div>
              <h5 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase mb-2">Readings</h5>
              <div class="grid grid-cols-2 gap-x-4 gap-y-1">
                <div v-for="(val, dKey) in comp.current_data" :key="dKey" v-show="dKey !== 'timestamp'" class="flex justify-between text-xs py-1 border-b border-slate-200/50 dark:border-slate-700/50 last:border-0">
                  <span class="text-slate-600 dark:text-slate-400 capitalize truncate pr-2">{{ dKey.replace(/_/g, ' ') }}</span>
                  <span class="font-mono text-slate-900 dark:text-white">{{ typeof val === 'number' ? val.toFixed(4) : val }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
  healthData: { type: Object, required: true }
})

const expandedComponent = ref(null)

const toggleComponent = (key) => { expandedComponent.value = expandedComponent.value === key ? null : key }
const getHealthColor = (score) => score >= 90 ? 'text-green-500' : score >= 70 ? 'text-amber-500' : 'text-red-500'
const getTextColor = (score) => score >= 90 ? 'text-green-600 dark:text-green-400' : score >= 70 ? 'text-amber-600 dark:text-amber-400' : 'text-red-600 dark:text-red-400'
const getBorderColor = (score) => score >= 90 ? 'border-green-200 dark:border-green-900' : score >= 70 ? 'border-amber-200 dark:border-amber-900' : 'border-red-200 dark:border-red-900'
const getStatusBadgeColor = (status) => {
  switch (status) {
    case 'EXCELLENT': return 'bg-green-100 text-green-700 border-green-200 dark:bg-green-900/30 dark:text-green-400 dark:border-green-900'
    case 'GOOD': return 'bg-blue-100 text-blue-700 border-blue-200 dark:bg-blue-900/30 dark:text-blue-400 dark:border-blue-900'
    case 'FAIR': return 'bg-amber-100 text-amber-700 border-amber-200 dark:bg-amber-900/30 dark:text-amber-400 dark:border-amber-900'
    default: return 'bg-red-100 text-red-700 border-red-200 dark:bg-red-900/30 dark:text-red-400 dark:border-red-900'
  }
}
</script>