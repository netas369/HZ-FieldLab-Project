<template>
  <div class="space-y-6">
    <!-- Type Filter -->
    <div class="flex flex-wrap gap-3">
      <button
        v-for="type in types"
        :key="type.value"
        :class="[
          'px-4 py-2 rounded-lg font-medium transition-colors',
          selectedType === type.value
            ? 'bg-indigo-600 text-white shadow-sm'
            : 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600',
        ]"
        @click="selectedType = type.value"
      >
        {{ type.label }}
      </button>
    </div>

    <!-- Loading State -->
    <div
      v-if="loading"
      class="flex justify-center py-12"
    >
      <div
        class="animate-spin rounded-full h-12 w-12 border-4 border-indigo-200 dark:border-indigo-800 border-t-indigo-600 dark:border-t-indigo-400"
      />
    </div>

    <!-- Error State -->
    <div
      v-else-if="error"
      class="p-4 bg-gradient-to-br from-red-50 to-red-100/50 dark:from-red-900/20 dark:to-red-800/10 border border-red-200 dark:border-red-800 rounded-lg"
    >
      <p class="text-red-800 dark:text-red-300 font-medium">
        {{ error }}
      </p>
    </div>

    <!-- Thresholds List -->
    <div
      v-else
      class="grid gap-4"
    >
      <div
        v-for="threshold in filteredThresholds"
        :key="threshold.id"
        class="bg-white dark:bg-slate-800 rounded-xl p-6 border border-slate-200 dark:border-slate-700 hover:shadow-md transition-shadow"
      >
        <div class="flex items-start justify-between mb-4">
          <div>
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">
              {{ formatComponentName(threshold.component_name) }}
            </h3>
            <p class="text-sm text-slate-600 dark:text-slate-400">
              Unit: {{ threshold.unit }}
            </p>
          </div>
          <div class="flex gap-2">
            <button
              class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-600 dark:hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition-colors"
              @click="editThreshold(threshold)"
            >
              Edit
            </button>
            <button
              class="px-3 py-1.5 bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 rounded-lg text-sm font-medium transition-colors"
              @click="resetThreshold(threshold.id)"
            >
              Reset
            </button>
          </div>
        </div>

        <!-- Threshold Ranges Display -->
        <div class="grid grid-cols-2 gap-4">
          <div
            class="p-3 bg-gradient-to-br from-emerald-50 to-emerald-100/50 dark:from-emerald-900/20 dark:to-emerald-800/10 border border-emerald-200 dark:border-emerald-800 rounded-lg"
          >
            <p class="text-xs font-medium text-emerald-700 dark:text-emerald-400 mb-2">
              NORMAL
            </p>
            <div class="flex gap-2">
              <div class="flex-1">
                <span class="text-xs text-slate-600 dark:text-slate-400">Min:</span>
                <span class="ml-1 font-mono text-sm text-slate-900 dark:text-white">{{
                  threshold.normal_min ?? 'N/A'
                }}</span>
              </div>
              <div class="flex-1">
                <span class="text-xs text-slate-600 dark:text-slate-400">Max:</span>
                <span class="ml-1 font-mono text-sm text-slate-900 dark:text-white">{{
                  threshold.normal_max ?? 'N/A'
                }}</span>
              </div>
            </div>
          </div>
          <div
            class="p-3 bg-gradient-to-br from-amber-50 to-amber-100/50 dark:from-amber-900/20 dark:to-amber-800/10 border border-amber-200 dark:border-amber-800 rounded-lg"
          >
            <p class="text-xs font-medium text-amber-700 dark:text-amber-400 mb-2">
              WARNING
            </p>
            <div class="flex gap-2">
              <div class="flex-1">
                <span class="text-xs text-slate-600 dark:text-slate-400">Min:</span>
                <span class="ml-1 font-mono text-sm text-slate-900 dark:text-white">{{
                  threshold.warning_min ?? 'N/A'
                }}</span>
              </div>
              <div class="flex-1">
                <span class="text-xs text-slate-600 dark:text-slate-400">Max:</span>
                <span class="ml-1 font-mono text-sm text-slate-900 dark:text-white">{{
                  threshold.warning_max ?? 'N/A'
                }}</span>
              </div>
            </div>
          </div>
          <div
            class="p-3 bg-gradient-to-br from-orange-50 to-orange-100/50 dark:from-orange-900/20 dark:to-orange-800/10 border border-orange-200 dark:border-orange-800 rounded-lg"
          >
            <p class="text-xs font-medium text-orange-700 dark:text-orange-400 mb-2">
              CRITICAL
            </p>
            <div class="flex gap-2">
              <div class="flex-1">
                <span class="text-xs text-slate-600 dark:text-slate-400">Min:</span>
                <span class="ml-1 font-mono text-sm text-slate-900 dark:text-white">{{
                  threshold.critical_min ?? 'N/A'
                }}</span>
              </div>
              <div class="flex-1">
                <span class="text-xs text-slate-600 dark:text-slate-400">Max:</span>
                <span class="ml-1 font-mono text-sm text-slate-900 dark:text-white">{{
                  threshold.critical_max ?? 'N/A'
                }}</span>
              </div>
            </div>
          </div>
          <div
            class="p-3 bg-gradient-to-br from-red-50 to-red-100/50 dark:from-red-900/20 dark:to-red-800/10 border border-red-200 dark:border-red-800 rounded-lg"
          >
            <p class="text-xs font-medium text-red-700 dark:text-red-400 mb-2">
              FAILED
            </p>
            <div class="flex gap-2">
              <div class="flex-1">
                <span class="text-xs text-slate-600 dark:text-slate-400">Min:</span>
                <span class="ml-1 font-mono text-sm text-slate-900 dark:text-white">{{
                  threshold.failed_min ?? 'N/A'
                }}</span>
              </div>
              <div class="flex-1">
                <span class="text-xs text-slate-600 dark:text-slate-400">Max:</span>
                <span class="ml-1 font-mono text-sm text-slate-900 dark:text-white">{{
                  threshold.failed_max ?? 'N/A'
                }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Modal -->
    <EditThresholdModal
      v-if="showEditModal"
      :threshold="selectedThreshold"
      @close="showEditModal = false"
      @save="saveThreshold"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import EditThresholdModal from './EditThresholdModal.vue'

const types = [
  { label: 'All', value: 'all' },
  { label: 'Vibration', value: 'vibration' },
  { label: 'Temperature', value: 'temperature' },
  { label: 'Pressure', value: 'pressure' },
  { label: 'Grid', value: 'grid' },
  { label: 'Environmental', value: 'environmental' },
]

const selectedType = ref('all')
const thresholds = ref([])
const loading = ref(true)
const error = ref(null)
const showEditModal = ref(false)
const selectedThreshold = ref(null)

const filteredThresholds = computed(() => {
  if (selectedType.value === 'all') return thresholds.value

  const typeMap = {
    vibration: (name) => name.includes('vibration'),
    temperature: (name) => name.includes('temp'),
    pressure: (name) => name.includes('pressure'),
    grid: (name) => name.startsWith('grid_'),
    environmental: (name) => ['wind_speed', 'ambient_temperature', 'rotor_speed'].includes(name),
  }

  return thresholds.value.filter((t) => typeMap[selectedType.value]?.(t.component_name))
})

const formatComponentName = (name) => {
  return name
    .split('_')
    .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
    .join(' ')
}

const fetchThresholds = async () => {
  loading.value = true
  error.value = null

  try {
    const token = localStorage.getItem('token')

    if (!token) {
      error.value = 'No authentication token found. Please log in.'
      return
    }

    const response = await fetch('http://localhost:8000/api/thresholds', {
      headers: {
        Authorization: `Bearer ${token}`,
        Accept: 'application/json',
        'Content-Type': 'application/json',
      },
    })

    if (!response.ok) {
      if (response.status === 401) {
        error.value = 'Unauthorized. Please log in again.'
      } else {
        const errorText = await response.text()
        console.error('API Error:', response.status, errorText)
        error.value = `Failed to fetch thresholds (${response.status})`
      }
      return
    }

    const data = await response.json()
    thresholds.value = data.thresholds || []
  } catch (err) {
    console.error('Failed to fetch thresholds:', err)
    error.value = 'Network error. Please check your connection.'
  } finally {
    loading.value = false
  }
}

const editThreshold = (threshold) => {
  selectedThreshold.value = { ...threshold }
  showEditModal.value = true
}

const saveThreshold = async (updatedThreshold) => {
  try {
    const token = localStorage.getItem('token')

    // Get CSRF token from cookie
    const getCsrfToken = () => {
      const value = `; ${document.cookie}`
      const parts = value.split(`; XSRF-TOKEN=`)
      if (parts.length === 2) {
        return decodeURIComponent(parts.pop().split(';').shift())
      }
      return null
    }

    const response = await fetch(`http://localhost:8000/api/thresholds/${updatedThreshold.id}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        Authorization: `Bearer ${token}`,
        Accept: 'application/json',
        'X-XSRF-TOKEN': getCsrfToken(),
      },
      credentials: 'include',
      body: JSON.stringify(updatedThreshold),
    })

    if (response.ok) {
      await fetchThresholds()
      showEditModal.value = false
    } else {
      const errorText = await response.text()
      console.error('Update failed:', errorText)
      alert('Failed to update threshold')
    }
  } catch (err) {
    console.error('Failed to update threshold:', err)
    alert('Network error')
  }
}

const resetThreshold = async (id) => {
  if (!confirm('Reset this threshold to default values?')) return

  try {
    const token = localStorage.getItem('token')

    // Get CSRF token from cookie
    const getCsrfToken = () => {
      const value = `; ${document.cookie}`
      const parts = value.split(`; XSRF-TOKEN=`)
      if (parts.length === 2) {
        return decodeURIComponent(parts.pop().split(';').shift())
      }
      return null
    }

    const response = await fetch(`http://localhost:8000/api/thresholds/${id}/reset`, {
      method: 'POST',
      headers: {
        Authorization: `Bearer ${token}`,
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-XSRF-TOKEN': getCsrfToken(),
      },
      credentials: 'include',
    })

    if (response.ok) {
      await fetchThresholds()
    } else {
      alert('Failed to reset threshold')
    }
  } catch (err) {
    console.error('Failed to reset threshold:', err)
    alert('Network error')
  }
}

onMounted(() => {
  fetchThresholds()
})
</script>
