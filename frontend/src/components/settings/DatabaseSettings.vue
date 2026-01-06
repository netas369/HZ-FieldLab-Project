<template>
  <div class="max-w-3xl">
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6 border border-slate-200 dark:border-slate-700">
      <div class="flex items-start gap-4 mb-6">
        <div class="p-3 bg-gradient-to-br from-red-50 to-red-100 dark:from-red-900/30 dark:to-red-800/20 rounded-xl border border-red-200 dark:border-red-800">
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
        <div class="p-5 bg-gradient-to-br from-red-50 to-red-100/50 dark:from-red-900/10 dark:to-red-800/5 border border-red-200 dark:border-red-800 rounded-xl">
          <div class="flex items-start justify-between mb-4">
            <div>
              <h4 class="font-semibold text-red-900 dark:text-red-300 mb-2 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                Clear All Data
              </h4>
              <p class="text-sm text-slate-700 dark:text-slate-300 mb-3">
                This will permanently delete all data from all database tables including:
              </p>
              <ul class="text-sm text-slate-600 dark:text-slate-400 space-y-1 ml-4">
                <li class="flex items-center gap-2">
                  <span class="w-1.5 h-1.5 bg-red-500 dark:bg-red-400 rounded-full"></span>
                  All turbine data and readings
                </li>
                <li class="flex items-center gap-2">
                  <span class="w-1.5 h-1.5 bg-red-500 dark:bg-red-400 rounded-full"></span>
                  Alarm history and notifications
                </li>
                <li class="flex items-center gap-2">
                  <span class="w-1.5 h-1.5 bg-red-500 dark:bg-red-400 rounded-full"></span>
                  Component status and maintenance records
                </li>
                <li class="flex items-center gap-2">
                  <span class="w-1.5 h-1.5 bg-red-500 dark:bg-red-400 rounded-full"></span>
                  All performance metrics and analytics
                </li>
              </ul>
            </div>
          </div>

          <div v-if="!showConfirmation" class="mt-4">
            <button
                @click="showConfirmation = true"
                class="px-5 py-2.5 bg-red-600 hover:bg-red-700 dark:bg-red-600 dark:hover:bg-red-700 text-white rounded-lg font-medium transition-colors shadow-sm"
            >
              Clear Database
            </button>
          </div>

          <div v-else class="mt-4 p-4 bg-white dark:bg-slate-900 rounded-lg border border-red-300 dark:border-red-700">
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
              Type <span class="font-mono font-bold text-red-600 dark:text-red-400">DELETE</span> to confirm:
            </label>
            <input
                v-model="confirmText"
                type="text"
                placeholder="DELETE"
                class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg font-mono mb-3 text-slate-900 dark:text-white focus:ring-2 focus:ring-red-500 dark:focus:ring-red-400 focus:border-transparent"
            />
            <div class="flex gap-3">
              <button
                  @click="resetDatabase"
                  :disabled="confirmText !== 'DELETE' || isResetting"
                  class="px-4 py-2 bg-red-600 hover:bg-red-700 dark:bg-red-600 dark:hover:bg-red-700 text-white rounded-lg font-medium disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
              >
                {{ isResetting ? 'Clearing...' : 'Yes, Clear All Data' }}
              </button>
              <button
                  @click="showConfirmation = false; confirmText = ''"
                  class="px-4 py-2 bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 rounded-lg font-medium transition-colors"
              >
                Cancel
              </button>
            </div>
          </div>

          <div v-if="showSuccess" class="mt-4 p-4 bg-gradient-to-br from-emerald-50 to-emerald-100/50 dark:from-emerald-900/20 dark:to-emerald-800/10 border border-emerald-200 dark:border-emerald-800 rounded-lg">
            <p class="text-emerald-800 dark:text-emerald-300 font-medium">
              Database cleared successfully!
            </p>
          </div>

          <div v-if="errorMessage" class="mt-4 p-4 bg-gradient-to-br from-red-50 to-red-100/50 dark:from-red-900/30 dark:to-red-800/20 border border-red-300 dark:border-red-700 rounded-lg">
            <p class="text-red-800 dark:text-red-300 font-medium">{{ errorMessage }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const showConfirmation = ref(false)
const confirmText = ref('')
const isResetting = ref(false)
const showSuccess = ref(false)
const errorMessage = ref('')

const resetDatabase = async () => {
  if (confirmText.value !== 'DELETE') return

  isResetting.value = true
  errorMessage.value = ''

  try {
    // Get CSRF token from cookie
    const getCsrfToken = () => {
      const value = `; ${document.cookie}`
      const parts = value.split(`; XSRF-TOKEN=`)
      if (parts.length === 2) {
        return decodeURIComponent(parts.pop().split(';').shift())
      }
      return null
    }

    const token = localStorage.getItem('token')

    const response = await fetch('http://localhost:8000/api/settings/delete-data', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json',
        'X-XSRF-TOKEN': getCsrfToken()
      },
      credentials: 'include'
    })

    if (!response.ok) throw new Error('Failed to reset database')

    showSuccess.value = true
    showConfirmation.value = false
    confirmText.value = ''

    setTimeout(() => {
      showSuccess.value = false
    }, 5000)
  } catch (error) {
    errorMessage.value = error.message
  } finally {
    isResetting.value = false
  }
}
</script>