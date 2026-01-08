<template>
  <div class="space-y-6">
    <!-- Key Metrics Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <!-- Power Output -->
      <div
        class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6"
      >
        <div class="flex items-center justify-between mb-3">
          <p class="text-sm font-medium text-slate-600 dark:text-slate-400">Power Output</p>
          <div class="p-2 bg-violet-100 dark:bg-violet-900/30 rounded-lg">
            <svg
              class="w-5 h-5 text-violet-600 dark:text-violet-400"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M13 10V3L4 14h7v7l9-11h-7z"
              />
            </svg>
          </div>
        </div>
        <p class="text-3xl font-bold text-slate-900 dark:text-white mb-1">
          {{ formatNumber(scada.power_kw / 1000, 2) }}
          <span class="text-xl text-slate-500 ml-1">MW</span>
        </p>
        <p class="text-xs text-slate-500 dark:text-slate-400">
          {{ formatNumber(scada.power_kw, 0) }} kW
        </p>
      </div>

      <!-- Wind Speed -->
      <div
        class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6"
      >
        <div class="flex items-center justify-between mb-3">
          <p class="text-sm font-medium text-slate-600 dark:text-slate-400">Wind Speed</p>
          <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
            <svg
              class="w-5 h-5 text-blue-600 dark:text-blue-400"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M14 5l7 7m0 0l-7 7m7-7H3"
              />
            </svg>
          </div>
        </div>
        <p class="text-3xl font-bold text-slate-900 dark:text-white mb-1">
          {{ formatNumber(scada.wind_speed_ms, 1) }}
          <span class="text-xl text-slate-500 ml-1">m/s</span>
        </p>
        <p class="text-xs text-slate-500 dark:text-slate-400">
          Direction: {{ formatNumber(scada.wind_direction_deg, 0) }}°
        </p>
      </div>

      <!-- Rotor Speed -->
      <div
        class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6"
      >
        <div class="flex items-center justify-between mb-3">
          <p class="text-sm font-medium text-slate-600 dark:text-slate-400">Rotor Speed</p>
          <div class="p-2 bg-green-100 dark:bg-green-900/30 rounded-lg">
            <svg
              class="w-5 h-5 text-green-600 dark:text-green-400"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
              />
            </svg>
          </div>
        </div>
        <p class="text-3xl font-bold text-slate-900 dark:text-white mb-1">
          {{ formatNumber(scada.rotor_speed_rpm, 1) }}
          <span class="text-xl text-slate-500 ml-1">RPM</span>
        </p>
      </div>

      <!-- Generator Speed -->
      <div
        class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6"
      >
        <div class="flex items-center justify-between mb-3">
          <p class="text-sm font-medium text-slate-600 dark:text-slate-400">Generator Speed</p>
          <div class="p-2 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg">
            <svg
              class="w-5 h-5 text-emerald-600 dark:text-emerald-400"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M13 10V3L4 14h7v7l9-11h-7z"
              />
            </svg>
          </div>
        </div>
        <p class="text-3xl font-bold text-slate-900 dark:text-white mb-1">
          {{ formatNumber(scada.generator_speed_rpm, 0) }}
          <span class="text-xl text-slate-500 ml-1">RPM</span>
        </p>
      </div>

      <!-- Pitch Angle -->
      <div
        class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6"
      >
        <div class="flex items-center justify-between mb-3">
          <p class="text-sm font-medium text-slate-600 dark:text-slate-400">Pitch Angle</p>
          <div class="p-2 bg-amber-100 dark:bg-amber-900/30 rounded-lg">
            <svg
              class="w-5 h-5 text-amber-600 dark:text-amber-400"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"
              />
            </svg>
          </div>
        </div>
        <p class="text-3xl font-bold text-slate-900 dark:text-white mb-1">
          {{ formatNumber(scada.pitch_angle_deg, 1) }}
          <span class="text-xl text-slate-500 ml-1">°</span>
        </p>
        <p class="text-xs text-slate-500 dark:text-slate-400">
          {{ getPitchStatus(scada.pitch_angle_deg) }}
        </p>
      </div>

      <!-- Ambient Temperature -->
      <div
        class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6"
      >
        <div class="flex items-center justify-between mb-3">
          <p class="text-sm font-medium text-slate-600 dark:text-slate-400">Ambient Temp</p>
          <div class="p-2 bg-orange-100 dark:bg-orange-900/30 rounded-lg">
            <svg
              class="w-5 h-5 text-orange-600 dark:text-orange-400"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
              />
            </svg>
          </div>
        </div>
        <p class="text-3xl font-bold text-slate-900 dark:text-white mb-1">
          {{ formatNumber(scada.ambient_temp_c, 1) }}
          <span class="text-xl text-slate-500 ml-1">°C</span>
        </p>
      </div>
    </div>

    <!-- Orientation Section -->
    <div
      class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6"
    >
      <h4 class="text-lg font-semibold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"
          />
        </svg>
        Turbine Orientation
      </h4>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Yaw Angle -->
        <div class="bg-slate-50 dark:bg-slate-900 rounded-lg p-4">
          <p class="text-sm text-slate-600 dark:text-slate-400 mb-2">Yaw Angle</p>
          <div class="flex items-center gap-3">
            <div class="relative w-16 h-16">
              <!-- Compass circle -->
              <svg class="w-16 h-16 transform -rotate-90" viewBox="0 0 100 100">
                <circle
                  cx="50"
                  cy="50"
                  r="45"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2"
                  class="text-slate-300 dark:text-slate-600"
                />
                <!-- Arrow pointing to yaw direction -->
                <line
                  x1="50"
                  y1="50"
                  :x2="50 + 40 * Math.cos(((scada.yaw_angle_deg - 90) * Math.PI) / 180)"
                  :y2="50 + 40 * Math.sin(((scada.yaw_angle_deg - 90) * Math.PI) / 180)"
                  stroke="currentColor"
                  stroke-width="3"
                  class="text-indigo-500"
                />
              </svg>
            </div>
            <div>
              <p class="text-2xl font-bold text-slate-900 dark:text-white">
                {{ formatNumber(scada.yaw_angle_deg, 1) }}°
              </p>
              <p class="text-xs text-slate-500 dark:text-slate-400">
                {{ getCardinalDirection(scada.yaw_angle_deg) }}
              </p>
            </div>
          </div>
        </div>

        <!-- Nacelle Direction -->
        <div class="bg-slate-50 dark:bg-slate-900 rounded-lg p-4">
          <p class="text-sm text-slate-600 dark:text-slate-400 mb-2">Nacelle Direction</p>
          <div class="flex items-center gap-3">
            <div class="relative w-16 h-16">
              <svg class="w-16 h-16 transform -rotate-90" viewBox="0 0 100 100">
                <circle
                  cx="50"
                  cy="50"
                  r="45"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2"
                  class="text-slate-300 dark:text-slate-600"
                />
                <line
                  x1="50"
                  y1="50"
                  :x2="50 + 40 * Math.cos(((scada.nacelle_direction_deg - 90) * Math.PI) / 180)"
                  :y2="50 + 40 * Math.sin(((scada.nacelle_direction_deg - 90) * Math.PI) / 180)"
                  stroke="currentColor"
                  stroke-width="3"
                  class="text-purple-500"
                />
              </svg>
            </div>
            <div>
              <p class="text-2xl font-bold text-slate-900 dark:text-white">
                {{ formatNumber(scada.nacelle_direction_deg, 1) }}°
              </p>
              <p class="text-xs text-slate-500 dark:text-slate-400">
                {{ getCardinalDirection(scada.nacelle_direction_deg) }}
              </p>
            </div>
          </div>
        </div>

        <!-- Wind Direction -->
        <div class="bg-slate-50 dark:bg-slate-900 rounded-lg p-4">
          <p class="text-sm text-slate-600 dark:text-slate-400 mb-2">Wind Direction</p>
          <div class="flex items-center gap-3">
            <div class="relative w-16 h-16">
              <svg class="w-16 h-16 transform -rotate-90" viewBox="0 0 100 100">
                <circle
                  cx="50"
                  cy="50"
                  r="45"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2"
                  class="text-slate-300 dark:text-slate-600"
                />
                <line
                  x1="50"
                  y1="50"
                  :x2="50 + 40 * Math.cos(((scada.wind_direction_deg - 90) * Math.PI) / 180)"
                  :y2="50 + 40 * Math.sin(((scada.wind_direction_deg - 90) * Math.PI) / 180)"
                  stroke="currentColor"
                  stroke-width="3"
                  class="text-blue-500"
                />
              </svg>
            </div>
            <div>
              <p class="text-2xl font-bold text-slate-900 dark:text-white">
                {{ formatNumber(scada.wind_direction_deg, 1) }}°
              </p>
              <p class="text-xs text-slate-500 dark:text-slate-400">
                {{ getCardinalDirection(scada.wind_direction_deg) }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Alignment Status -->
      <div
        class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800"
      >
        <div class="flex items-start gap-2">
          <svg
            class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
            />
          </svg>
          <div class="text-sm">
            <p class="font-medium text-blue-900 dark:text-blue-300">
              {{ getAlignmentStatus() }}
            </p>
            <p class="text-blue-700 dark:text-blue-400 mt-1">
              Nacelle misalignment:
              {{
                formatNumber(Math.abs(scada.nacelle_direction_deg - scada.wind_direction_deg), 1)
              }}°
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  scada: {
    type: Object,
    required: true,
  },
})

// Methods
const formatNumber = (value, decimals = 2) => {
  if (value === null || value === undefined) return 'N/A'
  return parseFloat(value).toFixed(decimals)
}

const getPitchStatus = (angle) => {
  const a = parseFloat(angle)
  if (a >= 85) return 'Feathered (Stopped)'
  if (a <= 5) return 'Optimal'
  if (a <= 15) return 'Normal'
  return 'Reduced Power'
}

const getCardinalDirection = (degrees) => {
  const dirs = ['N', 'NE', 'E', 'SE', 'S', 'SW', 'W', 'NW']
  const index = Math.round((parseFloat(degrees) % 360) / 45) % 8
  return dirs[index]
}

const getAlignmentStatus = () => {
  const misalignment = Math.abs(
    parseFloat(props.scada.nacelle_direction_deg) - parseFloat(props.scada.wind_direction_deg)
  )

  if (misalignment < 10) return 'Excellent alignment with wind'
  if (misalignment < 20) return 'Good alignment'
  if (misalignment < 30) return 'Minor misalignment detected'
  return 'Significant misalignment - Yaw correction may be needed'
}
</script>
