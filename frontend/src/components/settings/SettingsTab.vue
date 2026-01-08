<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white">
          Settings
        </h2>
        <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
          Manage application settings, thresholds, and database
        </p>
      </div>
    </div>

    <!-- Tabs -->
    <div class="border-b border-slate-200 dark:border-slate-700">
      <nav class="-mb-px flex space-x-8">
        <button
          :class="[
            'py-4 px-1 border-b-2 font-medium text-sm transition-colors',
            activeTab === 'database'
              ? 'border-primary-500 text-primary-600 dark:text-primary-400'
              : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 dark:text-slate-400 dark:hover:text-slate-300',
          ]"
          @click="activeTab = 'database'"
        >
          Database
        </button>
        <button
          :class="[
            'py-4 px-1 border-b-2 font-medium text-sm transition-colors',
            activeTab === 'thresholds'
              ? 'border-primary-500 text-primary-600 dark:text-primary-400'
              : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 dark:text-slate-400 dark:hover:text-slate-300',
          ]"
          @click="activeTab = 'thresholds'"
        >
          Thresholds
        </button>
      </nav>
    </div>

    <!-- Database Tab -->
    <div
      v-if="activeTab === 'database'"
      class="max-w-3xl"
    >
      <div
        class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6 border border-slate-200 dark:border-slate-700"
      >
        <div class="flex items-start gap-4 mb-6">
          <div class="p-3 bg-red-100 dark:bg-red-900/30 rounded-xl">
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
                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
              />
            </svg>
          </div>
          <div class="flex-1">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-2">
              Database Management
            </h3>
            <p class="text-sm text-slate-600 dark:text-slate-400">
              Control your database and manage stored data
            </p>
          </div>
        </div>

        <!-- Database Actions -->
        <div class="space-y-4">
          <div
            class="p-5 bg-red-50 dark:bg-red-900/10 border-2 border-red-200 dark:border-red-800 rounded-xl"
          >
            <div class="flex items-start justify-between mb-4">
              <div>
                <h4
                  class="font-semibold text-red-900 dark:text-red-400 mb-2 flex items-center gap-2"
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
                      d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                    />
                  </svg>
                  Clear All Data
                </h4>
                <p class="text-sm text-red-700 dark:text-red-300 mb-3">
                  This will permanently delete all data from all database tables
                </p>
              </div>
            </div>

            <div
              v-if="!showConfirmation"
              class="mt-4"
            >
              <button
                class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition-all shadow-lg"
                @click="showConfirmation = true"
              >
                Clear Database
              </button>
            </div>

            <div
              v-else
              class="mt-4 p-4 bg-white dark:bg-slate-900 rounded-lg border-2 border-red-300 dark:border-red-700"
            >
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                Type <span class="font-mono font-bold text-red-600">DELETE</span> to confirm:
              </label>
              <input
                v-model="confirmText"
                type="text"
                placeholder="DELETE"
                class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-800 border-2 rounded-lg font-mono mb-3"
              >
              <div class="flex gap-3">
                <button
                  :disabled="confirmText !== 'DELETE' || isResetting"
                  class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                  @click="resetDatabase"
                >
                  {{ isResetting ? 'Clearing...' : 'Yes, Clear All Data' }}
                </button>
                <button
                  class="px-4 py-2 bg-slate-200 dark:bg-slate-700 rounded-lg font-medium"
                  @click="
                    showConfirmation = false
                    confirmText = ''
                  "
                >
                  Cancel
                </button>
              </div>
            </div>

            <div
              v-if="showSuccess"
              class="mt-4 p-4 bg-green-50 dark:bg-green-900/20 border-2 border-green-200 dark:border-green-800 rounded-lg"
            >
              <p class="text-green-800 dark:text-green-300 font-medium">
                Database cleared successfully!
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Thresholds Tab -->
    <div v-if="activeTab === 'thresholds'">
      <ThresholdManager />
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import ThresholdManager from '@/components/settings/ThresholdManager.vue'

const activeTab = ref('database')
const showConfirmation = ref(false)
const confirmText = ref('')
const isResetting = ref(false)
const showSuccess = ref(false)

const resetDatabase = async () => {
  if (confirmText.value !== 'DELETE') return

  isResetting.value = true

  try {
    const response = await fetch('http://localhost:8000/api/settings/delete-data', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
    })

    if (!response.ok) throw new Error('Failed to reset database')

    showSuccess.value = true
    showConfirmation.value = false
    confirmText.value = ''

    setTimeout(() => {
      showSuccess.value = false
    }, 5000)
  } catch (error) {
    console.error(error)
  } finally {
    isResetting.value = false
  }
}
</script>
