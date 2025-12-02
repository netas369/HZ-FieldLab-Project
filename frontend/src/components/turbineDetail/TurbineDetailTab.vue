<template>
  <div class="space-y-6">
    <!-- Loading State -->
    <div v-if="loading" class="flex items-center justify-center py-20">
      <div class="text-center">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-indigo-500 border-t-transparent mb-4"></div>
        <p class="text-slate-600 dark:text-slate-400">Loading turbine data...</p>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-6">
      <div class="flex items-center gap-3">
        <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div>
          <h3 class="font-semibold text-red-900 dark:text-red-300">Failed to load turbine data</h3>
          <p class="text-sm text-red-700 dark:text-red-400 mt-1">{{ error }}</p>
        </div>
      </div>
    </div>

    <!-- Turbine Not Found -->
    <div v-else-if="!turbineData" class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-xl p-6">
      <div class="flex items-center gap-3">
        <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
        <div>
          <h3 class="font-semibold text-yellow-900 dark:text-yellow-300">Turbine not found</h3>
          <p class="text-sm text-yellow-700 dark:text-yellow-400 mt-1">Turbine ID "{{ turbineId }}" does not exist</p>
        </div>
      </div>
    </div>

    <!-- Turbine Details -->
    <div v-else>
      <!-- Header Card -->
      <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
        <div class="flex items-start justify-between">
          <div class="flex items-start gap-4">
            <!-- Animated Turbine Icon -->
            <div class="relative w-32 h-32 flex-shrink-0">
              <svg viewBox="0 0 100 100" :class="['w-full h-full', iconColor]">
                <rect x="47" y="45" width="6" height="45" fill="currentColor" opacity="0.3"/>
                <ellipse cx="50" cy="42" rx="6" ry="6" fill="currentColor" opacity="0.4"/>
                <circle cx="50" cy="42" r="3.5" fill="currentColor"/>
                <g :class="{ 'animate-spin-slow': turbineData.status === 'running' }" style="transform-origin: 50px 42px">
                  <ellipse cx="50" cy="20" rx="3" ry="18" fill="currentColor" opacity="0.9"/>
                  <ellipse cx="50" cy="20" rx="3" ry="18" fill="currentColor" opacity="0.9" transform="rotate(120 50 42)"/>
                  <ellipse cx="50" cy="20" rx="3" ry="18" fill="currentColor" opacity="0.9" transform="rotate(240 50 42)"/>
                </g>
                <rect x="44" y="88" width="12" height="3" fill="currentColor" opacity="0.3" rx="1"/>
              </svg>
            </div>

            <div>
              <div class="flex items-center gap-3 mb-2">
                <h1 class="text-3xl font-bold text-slate-900 dark:text-white">{{ turbineData.id }}</h1>
                <span :class="['px-3 py-1 rounded-full text-sm font-bold capitalize', getStatusClass(turbineData.status)]">
                  {{ turbineData.status }}
                </span>
              </div>
              <p class="text-slate-600 dark:text-slate-400 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                {{ turbineData.location }}
              </p>
              <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Last updated: {{ formatTime(turbineData.lastUpdate) }}
              </p>
            </div>
          </div>

          <div class="flex gap-2">
            <button
                @click="$emit('add-maintenance', turbineData)"
                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition-colors flex items-center gap-2"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              Log Maintenance
            </button>
          </div>
        </div>
      </div>

      <!-- Tabbed Sections with Anchors -->
      <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
        <!-- Tab Headers with Status Indicators -->
        <div class="flex border-b border-slate-200 dark:border-slate-700 overflow-x-auto">
          <a
              v-for="tab in tabs"
              :key="tab.key"
              :href="`#${tab.key}`"
              @click.prevent="navigateToTab(tab.key)"
              :class="[
              'relative flex-shrink-0 px-6 py-4 font-medium text-sm transition-all',
              currentTab === tab.key
                ? 'text-indigo-600 dark:text-indigo-400 bg-slate-50 dark:bg-slate-700/50'
                : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-slate-700/30'
            ]"
          >
            <span class="flex items-center gap-2">
              {{ tab.label }}
              <!-- Status Indicator Dot -->
              <span
                  v-if="getTabStatus(tab.key)"
                  :class="[
                  'w-2 h-2 rounded-full',
                  getTabStatus(tab.key) === 'green' ? 'bg-green-500' : '',
                  getTabStatus(tab.key) === 'yellow' ? 'bg-yellow-500' : '',
                  getTabStatus(tab.key) === 'red' ? 'bg-red-500 animate-pulse' : ''
                ]"
              ></span>
            </span>
            <!-- Active tab indicator -->
            <div
                v-if="currentTab === tab.key"
                class="absolute bottom-0 left-0 right-0 h-0.5 bg-indigo-600 dark:bg-indigo-400"
            ></div>
          </a>
        </div>

        <!-- Tab Content Sections -->
        <div class="p-6">
          <!-- SCADA Section -->
          <section :id="'scada'" v-show="currentTab === 'scada'" class="space-y-4">
            <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-4">SCADA Data</h3>
            <ScadaTab v-if="turbineData?.scadaData" :scada="turbineData.scadaData" />
            <div v-else class="bg-slate-50 dark:bg-slate-900 rounded-lg p-6 text-center text-slate-500">
              No SCADA data available
            </div>
          </section>

          <!-- Hydraulic Section -->
          <section :id="'hydraulic'" v-show="currentTab === 'hydraulic'" class="space-y-4">
            <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-4">Hydraulic Data</h3>
            <HydraulicTab v-if="turbineData?.hydraulicData" :hydraulic="turbineData.hydraulicData" />
            <div v-else class="bg-slate-50 dark:bg-slate-900 rounded-lg p-6 text-center text-slate-500">
              No hydraulic data available
            </div>
          </section>

          <!-- Vibration Section -->
          <section :id="'vibration'" v-show="currentTab === 'vibration'" class="space-y-4">
            <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-4">Vibration Data</h3>
            <VibrationTab v-if="turbineData?.vibrationData" :turbine="turbineData.vibrationData" />
            <div v-else class="bg-slate-50 dark:bg-slate-900 rounded-lg p-6 text-center text-slate-500">
              No vibration data available
            </div>
          </section>

          <!-- Temperature Section -->
          <section :id="'temperature'" v-show="currentTab === 'temperature'" class="space-y-4">
            <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-4">Temperature Data</h3>
            <TemparatureTab v-if="turbineData?.temperatureData" :temperature="turbineData.temperatureData" />
            <div v-else class="bg-slate-50 dark:bg-slate-900 rounded-lg p-6 text-center text-slate-500">
              No temperature data available
            </div>
          </section>

          <!-- Alarms Section -->
<!--          <section :id="'alarms'" v-show="currentTab === 'alarms'" class="space-y-4">-->
<!--            <div class="flex items-center justify-between mb-4">-->
<!--              <h3 class="text-xl font-bold text-slate-900 dark:text-white">Active Alarms</h3>-->
<!--            </div>-->

<!--            <div v-if="turbineAlarms.length > 0" class="space-y-3">-->
<!--              <div-->
<!--                  v-for="alarm in turbineAlarms"-->
<!--                  :key="alarm.id"-->
<!--                  class="bg-slate-50 dark:bg-slate-900 rounded-lg p-4 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"-->
<!--                  @click="$emit('show-alarm', alarm)"-->
<!--              >-->
<!--                <div class="flex items-start justify-between">-->
<!--                  <div class="flex-1">-->
<!--                    <div class="flex items-center gap-2 mb-2">-->
<!--                      <span :class="['px-2 py-1 rounded text-xs font-bold', getPriorityClass(alarm.priority)]">-->
<!--                        {{ alarm.priority }}-->
<!--                      </span>-->
<!--                      <span class="text-xs text-slate-500 dark:text-slate-400">{{ alarm.time }}</span>-->
<!--                    </div>-->
<!--                    <h4 class="font-semibold text-slate-900 dark:text-white mb-1">{{ alarm.title }}</h4>-->
<!--                    <p class="text-sm text-slate-600 dark:text-slate-400">{{ alarm.description }}</p>-->
<!--                  </div>-->
<!--                  <svg class="w-5 h-5 text-slate-400 flex-shrink-0 ml-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">-->
<!--                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />-->
<!--                  </svg>-->
<!--                </div>-->
<!--              </div>-->
<!--            </div>-->

<!--            <div v-else class="text-center py-12">-->
<!--              <svg class="w-16 h-16 mx-auto text-green-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">-->
<!--                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />-->
<!--              </svg>-->
<!--              <p class="text-lg font-semibold text-slate-900 dark:text-white">No Active Alarms</p>-->
<!--              <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">All systems operating normally</p>-->
<!--            </div>-->
<!--          </section>-->
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useScadaService } from '@/composables/api.js'
import VibrationTab from "@/components/turbineDetail/Vibration/VibrationTab.vue";
import ScadaTab from "@/components/turbineDetail/Scada/ScadaTab.vue";
import HydraulicTab from "@/components/turbineDetail/Hydraulic/HydraulicTab.vue";
import TemparatureTab from "@/components/turbineDetail/Temperature/TemparatureTab.vue";

const props = defineProps({
  turbineId: {
    type: String,
    required: true
  }
})

const emit = defineEmits(['show-alarm', 'add-maintenance'])

const route = useRoute()
const router = useRouter()
const { turbineStore, alarmStore } = useScadaService()

// Local state
const currentTab = ref('scada')

const tabs = [
  { key: 'scada', label: 'SCADA' },
  { key: 'hydraulic', label: 'Hydraulic' },
  { key: 'vibration', label: 'Vibration' },
  { key: 'temperature', label: 'Temperature' },
  // { key: 'alarms', label: 'Alarms' }
]

// Computed
const turbineData = computed(() =>
    turbineStore.turbines.find(t => t._api_id == props.turbineId)
)

const loading = computed(() => turbineStore.loading)
const error = computed(() => turbineStore.error)

const turbineAlarms = computed(() =>
    alarmStore.alarms.filter(alarm => alarm.turbine === props.turbineId)
)

const iconColor = computed(() => {
  const colors = {
    running: 'text-green-600 dark:text-green-500',
    idle: 'text-blue-600 dark:text-blue-500',
    maintenance: 'text-amber-600 dark:text-amber-500',
    stopped: 'text-red-600 dark:text-red-500',
    error: 'text-red-600 dark:text-red-500',
  }
  return colors[turbineData.value?.status] || 'text-slate-400'
})

const statusBadgeColor = computed(() => {
  const colors = {
    running: 'bg-green-500',
    idle: 'bg-blue-500',
    maintenance: 'bg-amber-500',
    stopped: 'bg-red-500',
    error: 'bg-red-500',
  }
  return colors[turbineData.value?.status] || 'bg-slate-400'
})

// Methods
const navigateToTab = (tabKey) => {
  currentTab.value = tabKey
  // Update URL hash without scrolling
  router.replace({ hash: `#${tabKey}` })
}

const formatNumber = (value, decimals = 2) => {
  if (value === null || value === undefined) return 'N/A'
  return parseFloat(value).toFixed(decimals)
}

const formatTime = (dateString) => {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  return date.toLocaleTimeString()
}

const getStatusClass = (status) => {
  const classes = {
    running: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
    idle: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
    maintenance: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
    stopped: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    error: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'
  }
  return classes[status] || 'bg-slate-100 text-slate-700'
}

const getPriorityClass = (priority) => {
  const classes = {
    Critical: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    Major: 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
    Warning: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
    Minor: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'
  }
  return classes[priority] || 'bg-slate-100 text-slate-700'
}

const getTabStatus = (tabKey) => {
  // Placeholder - will implement logic based on actual data
  // For now, return null or 'green'/'yellow'/'red' based on data
  if (tabKey === 'alarms' && turbineAlarms.value.length > 0) {
    const hasCritical = turbineAlarms.value.some(a => a.priority === 'Critical')
    return hasCritical ? 'red' : 'yellow'
  }
  return 'green'
}

// Lifecycle
onMounted(() => {
  // Check URL hash and set initial tab
  if (route.hash) {
    const hash = route.hash.replace('#', '')
    const validTab = tabs.find(t => t.key === hash)
    if (validTab) {
      currentTab.value = hash
    }
  }
})

// Watch for hash changes
watch(() => route.hash, (newHash) => {
  if (newHash) {
    const hash = newHash.replace('#', '')
    const validTab = tabs.find(t => t.key === hash)
    if (validTab) {
      currentTab.value = hash
    }
  }
})
</script>

<style scoped>
@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.animate-spin-slow {
  animation: spin 3s linear infinite;
}
</style>