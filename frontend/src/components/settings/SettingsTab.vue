<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Settings</h2>
        <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
          Manage application settings and database
        </p>
      </div>
    </div>

    <!-- Database Management Section -->
    <div class="max-w-3xl">
      <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6 border border-slate-200 dark:border-slate-700">
        <div class="flex items-start gap-4 mb-6">
          <div class="p-3 bg-red-100 dark:bg-red-900/30 rounded-xl">
            <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
          </div>
          <div class="flex-1">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-2">Database Management</h3>
            <p class="text-sm text-slate-600 dark:text-slate-400">
              Control your database and manage stored data
            </p>
          </div>
        </div>

        <!-- Database Actions -->
        <div class="space-y-4">
          <!-- Reset Database -->
          <div class="p-5 bg-red-50 dark:bg-red-900/10 border-2 border-red-200 dark:border-red-800 rounded-xl">
            <div class="flex items-start justify-between mb-4">
              <div>
                <h4 class="font-semibold text-red-900 dark:text-red-400 mb-2 flex items-center gap-2">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                  </svg>
                  Clear All Data
                </h4>
                <p class="text-sm text-red-700 dark:text-red-300 mb-3">
                  This will permanently delete all data from all database tables including:
                </p>
                <ul class="text-sm text-red-700 dark:text-red-300 space-y-1 ml-4">
                  <li class="flex items-center gap-2">
                    <span class="w-1.5 h-1.5 bg-red-600 dark:bg-red-400 rounded-full"></span>
                    All turbine data and readings
                  </li>
                  <li class="flex items-center gap-2">
                    <span class="w-1.5 h-1.5 bg-red-600 dark:bg-red-400 rounded-full"></span>
                    Alarm history and notifications
                  </li>
                  <li class="flex items-center gap-2">
                    <span class="w-1.5 h-1.5 bg-red-600 dark:bg-red-400 rounded-full"></span>
                    Component status and maintenance records
                  </li>
                  <li class="flex items-center gap-2">
                    <span class="w-1.5 h-1.5 bg-red-600 dark:bg-red-400 rounded-full"></span>
                    All performance metrics and analytics
                  </li>
                </ul>
              </div>
            </div>

            <!-- Confirmation Section -->
            <div v-if="!showConfirmation" class="mt-4">
              <button
                  @click="showConfirmation = true"
                  class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition-all shadow-lg shadow-red-500/30 hover:shadow-xl hover:shadow-red-500/40 flex items-center gap-2"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                Clear Database
              </button>
            </div>

            <!-- Confirmation Dialog -->
            <div v-else class="mt-4 p-4 bg-white dark:bg-slate-900 rounded-lg border-2 border-red-300 dark:border-red-700">
              <div class="flex items-start gap-3 mb-4">
                <svg class="w-6 h-6 text-red-600 dark:text-red-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div>
                  <p class="font-semibold text-slate-900 dark:text-white mb-2">
                    Are you absolutely sure?
                  </p>
                  <p class="text-sm text-slate-600 dark:text-slate-400 mb-4">
                    This action cannot be undone. All data will be permanently deleted from the database.
                  </p>

                  <!-- Type to confirm -->
                  <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                      Type <span class="font-mono font-bold text-red-600 dark:text-red-400">DELETE</span> to confirm:
                    </label>
                    <input
                        v-model="confirmText"
                        type="text"
                        placeholder="DELETE"
                        class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-800 border-2 border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 font-mono"
                    />
                  </div>

                  <div class="flex gap-3">
                    <button
                        @click="resetDatabase"
                        :disabled="confirmText !== 'DELETE' || isResetting"
                        :class="[
                          'px-4 py-2 rounded-lg font-medium transition-all flex items-center gap-2',
                          confirmText === 'DELETE' && !isResetting
                            ? 'bg-red-600 hover:bg-red-700 text-white shadow-lg'
                            : 'bg-slate-300 dark:bg-slate-700 text-slate-500 dark:text-slate-500 cursor-not-allowed'
                        ]"
                    >
                      <svg v-if="!isResetting" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                      </svg>
                      <svg v-else class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                      </svg>
                      {{ isResetting ? 'Clearing...' : 'Yes, Clear All Data' }}
                    </button>
                    <button
                        @click="showConfirmation = false; confirmText = ''"
                        :disabled="isResetting"
                        class="px-4 py-2 bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-900 dark:text-white rounded-lg font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                      Cancel
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Success Message -->
            <div v-if="showSuccess" class="mt-4 p-4 bg-green-50 dark:bg-green-900/20 border-2 border-green-200 dark:border-green-800 rounded-lg">
              <div class="flex items-center gap-3">
                <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-green-800 dark:text-green-300 font-medium">
                  Database cleared successfully! All data has been removed.
                </p>
              </div>
            </div>

            <!-- Error Message -->
            <div v-if="errorMessage" class="mt-4 p-4 bg-red-100 dark:bg-red-900/30 border-2 border-red-300 dark:border-red-700 rounded-lg">
              <div class="flex items-start gap-3">
                <svg class="w-6 h-6 text-red-600 dark:text-red-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                <div>
                  <p class="text-red-800 dark:text-red-300 font-medium mb-1">Error clearing database</p>
                  <p class="text-sm text-red-700 dark:text-red-400">{{ errorMessage }}</p>
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
  user: { type: Object, default: () => ({}) },
  preferences: { type: Object, default: () => ({}) }
})

// State
const showConfirmation = ref(false)
const confirmText = ref('')
const isResetting = ref(false)
const showSuccess = ref(false)
const errorMessage = ref('')

// Methods
const resetDatabase = async () => {
  if (confirmText.value !== 'DELETE') return

  isResetting.value = true
  errorMessage.value = ''
  showSuccess.value = false

  try {
    const response = await fetch('http://localhost:8000/api/settings/delete-data', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',}
    })
    if (!response.ok) throw new Error('Failed to reset database')

    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 2000))

    showSuccess.value = true
    showConfirmation.value = false
    confirmText.value = ''

    // Hide success message after 5 seconds
    setTimeout(() => {
      showSuccess.value = false
    }, 5000)
  } catch (error) {
    errorMessage.value = error.message || 'An unexpected error occurred'
  } finally {
    isResetting.value = false
  }
}
</script>

<style scoped>
/* Custom styles if needed */
</style>
