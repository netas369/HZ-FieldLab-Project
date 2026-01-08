<template>
  <div class="space-y-6">
    <div
      v-if="loading"
      class="flex items-center justify-center py-20"
    >
      <div class="text-center">
        <div
          class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-indigo-500 border-t-transparent mb-4"
        />
        <p class="text-slate-600 dark:text-slate-400">
          Loading turbine data...
        </p>
      </div>
    </div>

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
          <h3 class="font-semibold text-red-900 dark:text-red-300">
            Failed to load turbine data
          </h3>
          <p class="text-sm text-red-700 dark:text-red-400 mt-1">
            {{ error }}
          </p>
        </div>
      </div>
    </div>

    <div
      v-else-if="!turbineData"
      class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-xl p-6"
    >
      <div class="flex items-center gap-3">
        <svg
          class="w-6 h-6 text-yellow-600 dark:text-yellow-400"
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
        <div>
          <h3 class="font-semibold text-yellow-900 dark:text-yellow-300">
            Turbine not found
          </h3>
          <p class="text-sm text-yellow-700 dark:text-yellow-400 mt-1">
            Turbine ID "{{ turbineId }}" does not exist
          </p>
        </div>
      </div>
    </div>

    <div v-else>
      <div
        class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 mb-6"
      >
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
          <div class="flex items-start gap-4">
            <div class="relative w-24 h-24 flex-shrink-0">
              <svg
                viewBox="0 0 100 100"
                :class="['w-full h-full', iconColor]"
              >
                <rect
                  x="47"
                  y="45"
                  width="6"
                  height="45"
                  fill="currentColor"
                  opacity="0.3"
                />
                <ellipse
                  cx="50"
                  cy="42"
                  rx="6"
                  ry="6"
                  fill="currentColor"
                  opacity="0.4"
                />
                <circle
                  cx="50"
                  cy="42"
                  r="3.5"
                  fill="currentColor"
                />
                <g
                  :class="{ 'animate-spin-slow': turbineData.status === 'running' }"
                  style="transform-origin: 50px 42px"
                >
                  <ellipse
                    cx="50"
                    cy="20"
                    rx="3"
                    ry="18"
                    fill="currentColor"
                    opacity="0.9"
                  />
                  <ellipse
                    cx="50"
                    cy="20"
                    rx="3"
                    ry="18"
                    fill="currentColor"
                    opacity="0.9"
                    transform="rotate(120 50 42)"
                  />
                  <ellipse
                    cx="50"
                    cy="20"
                    rx="3"
                    ry="18"
                    fill="currentColor"
                    opacity="0.9"
                    transform="rotate(240 50 42)"
                  />
                </g>
                <rect
                  x="44"
                  y="88"
                  width="12"
                  height="3"
                  fill="currentColor"
                  opacity="0.3"
                  rx="1"
                />
              </svg>
            </div>

            <div>
              <div class="flex items-center gap-3 mb-2">
                <h1 class="text-3xl font-bold text-slate-900 dark:text-white">
                  {{ turbineData.id }}
                </h1>
                <span
                  :class="[
                    'px-3 py-1 rounded-full text-sm font-bold capitalize',
                    getStatusClass(turbineData.status),
                  ]"
                >
                  {{ turbineData.status }}
                </span>
              </div>
              <p class="text-slate-600 dark:text-slate-400 flex items-center gap-2">
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
                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                  />
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                  />
                </svg>
                {{ turbineData.location }}
              </p>
            </div>
          </div>

          <div class="flex flex-col sm:flex-row items-end sm:items-center gap-3">
            <div class="bg-slate-100 dark:bg-slate-700 p-1 rounded-lg flex items-center">
              <button
                :class="[
                  'px-4 py-1.5 rounded-md text-sm font-medium transition-all duration-200',
                  viewMode === 'live'
                    ? 'bg-white dark:bg-slate-600 text-indigo-600 dark:text-indigo-400 shadow-sm'
                    : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300',
                ]"
                @click="switchView('live')"
              >
                Live Monitor
              </button>
              <button
                :class="[
                  'px-4 py-1.5 rounded-md text-sm font-medium transition-all duration-200',
                  viewMode === 'history'
                    ? 'bg-white dark:bg-slate-600 text-indigo-600 dark:text-indigo-400 shadow-sm'
                    : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300',
                ]"
                @click="switchView('history')"
              >
                History Analysis
              </button>
            </div>
            <button
              class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition-colors flex items-center gap-2 shadow-sm"
              @click="$emit('add-maintenance', turbineData)"
            >
              <svg
                class="w-4 h-4"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M12 4v16m8-8H4"
                />
              </svg>
              Log Maintenance
            </button>
          </div>
        </div>
      </div>

      <div
        v-if="viewMode === 'live'"
        class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden"
      >
        <div class="flex border-b border-slate-200 dark:border-slate-700 overflow-x-auto">
          <a
            v-for="tab in tabs"
            :key="tab.key"
            :href="`#${tab.key}`"
            :class="[
              'relative flex-shrink-0 px-6 py-4 font-medium text-sm transition-all',
              currentTab === tab.key
                ? 'text-indigo-600 dark:text-indigo-400 bg-slate-50 dark:bg-slate-700/50'
                : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50',
            ]"
            @click.prevent="navigateToTab(tab.key)"
          >
            {{ tab.label }}
            <div
              v-if="currentTab === tab.key"
              class="absolute bottom-0 left-0 right-0 h-0.5 bg-indigo-600 dark:bg-indigo-400"
            />
          </a>
        </div>

        <div class="p-6">
          <section
            v-show="currentTab === 'scada'"
            class="space-y-4"
          >
            <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-4">
              SCADA Data
            </h3>
            <ScadaTab
              v-if="turbineData?.scadaData"
              :scada="turbineData.scadaData"
            />
            <div
              v-else
              class="text-center py-8 text-slate-500"
            >
              No SCADA data available
            </div>
          </section>

          <section
            v-show="currentTab === 'health'"
            class="space-y-4"
          >
            <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-4">
              Health Analysis
            </h3>
            <HealthTab
              v-if="turbineData?.healthData"
              :health-data="turbineData.healthData"
            />
            <div
              v-else
              class="text-center py-12 text-slate-500 bg-slate-50 dark:bg-slate-800/50 rounded-lg border border-slate-100 dark:border-slate-700 border-dashed"
            >
              <div
                v-if="loadingHealth"
                class="flex flex-col items-center gap-3"
              >
                <div
                  class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-indigo-500 border-t-transparent"
                />
                <span class="text-sm">Analyzing health...</span>
              </div>
              <span v-else>No health analysis available.</span>
            </div>
          </section>

          <section
            v-show="currentTab === 'predictive'"
            class="space-y-4"
          >
            <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-4">
              Predictive Forecast
            </h3>
            <PredictiveTab
              v-if="turbineData?.deteriorationData"
              :deterioration-data="turbineData.deteriorationData"
            />
            <div
              v-else
              class="text-center py-12 text-slate-500 bg-slate-50 dark:bg-slate-800/50 rounded-lg border border-slate-100 dark:border-slate-700 border-dashed"
            >
              <div
                v-if="loadingHealth"
                class="flex flex-col items-center gap-3"
              >
                <div
                  class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-indigo-500 border-t-transparent"
                />
                <span class="text-sm">Calculating deterioration trends...</span>
              </div>
              <span v-else>No predictive data available.</span>
            </div>
          </section>

          <section v-show="currentTab === 'hydraulic'">
            <HydraulicTab
              v-if="turbineData?.hydraulicData"
              :hydraulic="turbineData.hydraulicData"
            />
          </section>
          <section v-show="currentTab === 'vibration'">
            <VibrationTab
              v-if="turbineData?.vibrationData"
              :turbine="turbineData.vibrationData"
            />
          </section>
          <section v-show="currentTab === 'temperature'">
            <TemperatureTab
              v-if="turbineData?.temperatureData"
              :temperature="turbineData.temperatureData"
            />
          </section>
        </div>
      </div>

      <div v-else>
        <HistoryTab :turbine-id="turbineId" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useScadaService } from '@/composables/api.js'
import ScadaTab from '@/components/turbineDetail/Scada/ScadaTab.vue'
import HydraulicTab from '@/components/turbineDetail/Hydraulic/HydraulicTab.vue'
import VibrationTab from '@/components/turbineDetail/Vibration/VibrationTab.vue'
import TemperatureTab from '@/components/turbineDetail/Temperature/TemperatureTab.vue'
import HistoryTab from '@/components/turbineDetail/History/HistoryTab.vue'
import HealthTab from '@/components/turbineDetail/Health/HealthTab.vue'
import PredictiveTab from '@/components/turbineDetail/Predictive/PredictiveTab.vue' // Import New Tab

const props = defineProps({ turbineId: { type: String, required: true } })
defineEmits(['show-alarm', 'add-maintenance'])
const route = useRoute()
const router = useRouter()
const { turbineStore, fetchTurbineHealth, fetchDeteriorationTrends } = useScadaService()

const currentTab = ref('scada')
const viewMode = ref('live')
const loadingHealth = ref(false)

const tabs = [
  { key: 'scada', label: 'SCADA' },
  { key: 'hydraulic', label: 'Hydraulic' },
  { key: 'vibration', label: 'Vibration' },
  { key: 'temperature', label: 'Temperature' },
  { key: 'health', label: 'Health' },
  { key: 'predictive', label: 'Predictive' },
]

const turbineData = computed(
  () =>
    turbineStore.turbines.find((t) => t.id == props.turbineId) ||
    turbineStore.turbines.find((t) => t._api_id == props.turbineId)
)

const loading = computed(() => turbineStore.loading)
const error = computed(() => turbineStore.error)

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

const getStatusClass = (status) => {
  const classes = {
    running: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
    idle: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
    maintenance: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
    stopped: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    error: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
  }
  return classes[status] || 'bg-slate-100 text-slate-700'
}

const navigateToTab = (tabKey) => {
  currentTab.value = tabKey
  router.replace({ hash: `#${tabKey}` })
}
const switchView = (mode) => {
  viewMode.value = mode
  router.replace({ hash: mode === 'live' ? `#${currentTab.value}` : '' })
}

const loadHealthData = async () => {
  const apiId = turbineData.value?._api_id
  if (apiId) {
    loadingHealth.value = true
    const promises = []
    if (!turbineData.value.healthData) promises.push(fetchTurbineHealth(apiId))
    if (!turbineData.value.deteriorationData) promises.push(fetchDeteriorationTrends(apiId))
    await Promise.all(promises)
    loadingHealth.value = false
  }
}

onMounted(() => {
  if (route.hash) {
    const hash = route.hash.replace('#', '')
    if (tabs.find((t) => t.key === hash)) {
      currentTab.value = hash
      viewMode.value = 'live'
    }
  }
  loadHealthData()
})

watch(
  () => props.turbineId,
  () => loadHealthData()
)
watch(
  () => route.hash,
  (newHash) => {
    if (newHash) {
      const hash = newHash.replace('#', '')
      if (tabs.find((t) => t.key === hash)) {
        currentTab.value = hash
        viewMode.value = 'live'
      }
    }
  }
)
</script>

<style scoped>
.animate-spin-slow {
  animation: spin 3s linear infinite;
}
@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}
</style>
