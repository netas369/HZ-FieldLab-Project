<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Settings</h2>
        <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
          Manage application settings, thresholds, and database
        </p>
      </div>
    </div>

    <!-- Tabs -->
    <div class="border-b border-slate-200 dark:border-slate-700">
      <nav class="-mb-px flex space-x-8">
        <button
            @click="activeTab = 'database'"
            :class="[
            'py-4 px-1 border-b-2 font-medium text-sm transition-colors',
            activeTab === 'database'
              ? 'border-primary-500 text-primary-600 dark:text-primary-400'
              : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 dark:text-slate-400 dark:hover:text-slate-300'
          ]"
        >
          <div class="flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
            </svg>
            Database
          </div>
        </button>
        <button
            @click="activeTab = 'thresholds'"
            :class="[
            'py-4 px-1 border-b-2 font-medium text-sm transition-colors',
            activeTab === 'thresholds'
              ? 'border-primary-500 text-primary-600 dark:text-primary-400'
              : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 dark:text-slate-400 dark:hover:text-slate-300'
          ]"
        >
          <div class="flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            Thresholds
          </div>
        </button>
      </nav>
    </div>

    <!-- Database Tab -->
    <DatabaseSettings v-if="activeTab === 'database'" />

    <!-- Thresholds Tab -->
    <ThresholdManager v-if="activeTab === 'thresholds'" />
  </div>
</template>

<script setup>
import { ref } from 'vue'
import DatabaseSettings from '@/components/settings/DatabaseSettings.vue'
import ThresholdManager from '@/components/settings/ThresholdManager.vue'

const activeTab = ref('database')
</script>
