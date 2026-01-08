<template>
  <div
    class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden shadow-sm flex flex-col relative select-none"
    @click="closePopover"
  >
    <div
      class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center bg-white dark:bg-slate-900 shrink-0 z-30 relative shadow-sm h-16"
    >
      <div class="flex items-center gap-3">
        <h3 class="font-bold text-slate-800 dark:text-white flex items-center gap-2 text-sm">
          <svg
            class="w-4 h-4 text-indigo-500"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M13 10V3L4 14h7v7l9-11h-7z"
            />
          </svg>
          Timeline Analysis
        </h3>

        <div
          v-if="focusedComponent"
          class="flex items-center gap-2 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 px-2 py-0.5 rounded text-xs font-medium border border-indigo-100 dark:border-indigo-800 animate-in fade-in slide-in-from-left-2"
        >
          <span>Focus: {{ focusedComponent.replace(/_/g, ' ') }}</span>
          <button
            class="hover:text-indigo-800 dark:hover:text-indigo-200"
            @click="clearFocus"
          >
            <svg
              class="w-3 h-3"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M6 18L18 6M6 6l12 12"
              />
            </svg>
          </button>
        </div>
        <div
          v-else
          class="text-xs text-slate-500 mt-0.5 font-mono hidden sm:block"
        >
          Window: {{ (totalHours / 24).toFixed(1) }} days
        </div>
      </div>

      <div
        class="flex items-center gap-1 bg-slate-100 dark:bg-slate-800 p-1 rounded-lg border border-slate-200 dark:border-slate-700"
      >
        <button
          :disabled="zoomIndex === 0"
          class="p-1.5 hover:bg-white dark:hover:bg-slate-700 rounded-md disabled:opacity-30 disabled:cursor-not-allowed transition-all text-slate-600 dark:text-slate-400"
          @click.stop="zoomOut"
        >
          <svg
            class="w-3.5 h-3.5"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M20 12H4"
            />
          </svg>
        </button>
        <span
          class="text-[10px] font-bold font-mono w-10 text-center text-slate-600 dark:text-slate-300 select-none"
        >{{ Math.round(currentZoom * 100) }}%</span>
        <button
          :disabled="zoomIndex === zoomLevels.length - 1"
          class="p-1.5 hover:bg-white dark:hover:bg-slate-700 rounded-md disabled:opacity-30 disabled:cursor-not-allowed transition-all text-slate-600 dark:text-slate-400"
          @click.stop="zoomIn"
        >
          <svg
            class="w-3.5 h-3.5"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M12 4v16m8-8H4"
            />
          </svg>
        </button>
        <div class="w-px h-4 bg-slate-300 dark:bg-slate-600 mx-1" />
        <button
          class="p-1.5 hover:bg-white dark:hover:bg-slate-700 rounded-md transition-all text-slate-600 dark:text-slate-400"
          @click.stop="resetZoom"
        >
          <svg
            class="w-3.5 h-3.5"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
            />
          </svg>
        </button>
      </div>
    </div>

    <div
      ref="containerRef"
      class="h-auto max-h-[600px] overflow-auto relative custom-scrollbar bg-slate-50 dark:bg-slate-950/50"
      :class="isDragging ? 'cursor-grabbing' : 'cursor-grab'"
      @wheel.prevent="handleWheel"
      @mousedown="startDrag"
      @mouseleave="stopDrag"
      @mouseup="stopDrag"
      @mousemove="onDrag"
    >
      <div
        class="relative min-w-full h-full"
        :style="{ width: `${currentZoom * 100}%` }"
      >
        <div class="absolute inset-0 pointer-events-none z-0">
          <div
            v-for="(tick, i) in axisTicks"
            :key="i"
            class="absolute top-0 bottom-0 border-l border-slate-300/50 dark:border-slate-700/50 border-dashed"
            :style="{ left: getTickPosition(tick) + '%' }"
          >
            <div
              class="absolute top-2 font-medium text-slate-400 font-mono bg-slate-50 dark:bg-slate-900 px-2 py-1 rounded border border-slate-200 dark:border-slate-800 shadow-sm whitespace-nowrap flex flex-col items-center leading-tight"
              :class="{
                '-translate-x-0': i === 0,
                '-translate-x-full': i === axisTicks.length - 1,
                '-translate-x-1/2': i > 0 && i < axisTicks.length - 1,
              }"
            >
              <span class="text-xs text-slate-600 dark:text-slate-300">{{
                formatTickDate(tick)
              }}</span>
              <span class="text-[10px] text-slate-500 dark:text-slate-500">{{
                formatTickTime(tick)
              }}</span>
            </div>
          </div>
        </div>

        <div class="pt-20 pb-8 space-y-4 z-10 relative min-h-[250px]">
          <div
            v-for="comp in visibleComponents"
            :key="comp"
            class="flex items-center h-16 group transition-all duration-300"
            :class="
              focusedComponent && focusedComponent !== comp
                ? 'opacity-20 blur-[1px]'
                : 'hover:bg-black/5 dark:hover:bg-white/5'
            "
            @dblclick="toggleFocus(comp)"
          >
            <div
              class="sticky left-0 w-[160px] h-full shrink-0 z-20 flex items-center justify-end pr-4 backdrop-blur-sm border-r border-slate-200 dark:border-slate-700 shadow-[4px_0_12px_-4px_rgba(0,0,0,0.1)] transition-colors cursor-pointer"
              :class="
                focusedComponent === comp
                  ? 'bg-indigo-50/95 dark:bg-indigo-900/40 border-r-indigo-200'
                  : 'bg-slate-50/95 dark:bg-slate-900/95'
              "
            >
              <div class="text-right leading-tight">
                <div
                  class="text-sm font-bold truncate capitalize"
                  :class="
                    focusedComponent === comp
                      ? 'text-indigo-700 dark:text-indigo-300'
                      : 'text-slate-700 dark:text-slate-300'
                  "
                >
                  {{ comp.replace(/_/g, ' ') }}
                </div>
                <div
                  class="text-[10px] font-normal mt-0.5"
                  :class="focusedComponent === comp ? 'text-indigo-400' : 'text-slate-400'"
                >
                  {{ focusedComponent === comp ? 'Focused' : 'Double-click' }}
                </div>
              </div>
            </div>

            <div
              class="flex-1 h-12 mx-4 rounded-full bg-slate-200 dark:bg-slate-800/80 relative overflow-hidden ring-1 ring-slate-300 dark:ring-slate-700"
            >
              <div
                v-for="alarm in getAlarmsForComponent(comp)"
                :key="alarm.id"
                class="absolute inset-y-[3px] rounded-sm cursor-pointer transition-all z-10 border-r border-black/10 shadow-sm opacity-90 hover:opacity-100 hover:brightness-110 hover:z-30"
                :class="getSeverityClass(alarm.severity)"
                :style="getBarStyle(alarm)"
                :title="`${alarm.message} (${alarm.duration_minutes} min)`"
                @click.stop="handleBarClick($event, alarm)"
              />
            </div>
          </div>

          <div
            v-if="visibleComponents.length === 0"
            class="text-center py-10 text-slate-400"
          >
            No data found
          </div>
        </div>
      </div>
    </div>

    <div
      v-if="selectedAlarm"
      class="absolute z-50 w-72 bg-white dark:bg-slate-800 rounded-xl shadow-2xl border border-slate-200 dark:border-slate-700 p-4 animate-in fade-in zoom-in duration-200"
      :style="popoverStyle"
      @click.stop
    >
      <div class="flex justify-between items-start mb-3">
        <div class="flex items-center gap-2">
          <span
            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold uppercase tracking-wide"
            :class="
              selectedAlarm.severity === 'failed'
                ? 'bg-red-100 text-red-700'
                : 'bg-amber-100 text-amber-700'
            "
          >
            {{ selectedAlarm.severity }}
          </span>
          <span
            v-if="selectedAlarm.alarm_code"
            class="text-xs font-mono text-slate-500 dark:text-slate-400"
          >
            #{{ selectedAlarm.alarm_code }}
          </span>
        </div>
        <button
          class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
          @click="closePopover"
        >
          <svg
            class="w-4 h-4"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M6 18L18 6M6 6l12 12"
            />
          </svg>
        </button>
      </div>

      <p class="text-sm font-medium text-slate-800 dark:text-slate-200 mb-3 leading-snug">
        {{ selectedAlarm.message }}
      </p>

      <div class="bg-slate-50 dark:bg-slate-900 rounded-lg p-3 space-y-2 text-xs">
        <div class="flex justify-between border-b border-slate-200 dark:border-slate-700 pb-2">
          <span class="text-slate-500">Detected:</span>
          <span class="font-mono text-slate-700 dark:text-slate-300">{{
            new Date(selectedAlarm.start_time).toLocaleString()
          }}</span>
        </div>
        <div class="flex justify-between pt-1">
          <span class="text-slate-500">Duration:</span>
          <span class="font-mono text-slate-700 dark:text-slate-300">{{ selectedAlarm.duration_minutes }} min</span>
        </div>
        <div
          v-if="selectedAlarm.data?.value"
          class="flex justify-between pt-1"
        >
          <span class="text-slate-500">Value:</span>
          <span class="font-mono font-bold text-slate-900 dark:text-white">{{
            selectedAlarm.data.value
          }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'

const props = defineProps({
  alarms: { type: Array, required: true, default: () => [] },
})

// --- Focus Mode Logic ---
const focusedComponent = ref(null)
const toggleFocus = (comp) => {
  focusedComponent.value = focusedComponent.value === comp ? null : comp
}
const clearFocus = () => {
  focusedComponent.value = null
}
const timelineMeta = computed(() => {
  const data = props.alarms
  if (!data.length) return { min: 0, max: 0, totalRange: 0, components: [] }
  const timestamps = data.flatMap((a) => [
    new Date(a.start_time).getTime(),
    new Date(a.end_time).getTime(),
  ])
  const min = Math.min(...timestamps)
  const max = Math.max(...timestamps)
  const buffer = 7200000
  return {
    min: min - buffer,
    max: max + buffer,
    totalRange: max + buffer - (min - buffer),
    components: [...new Set(data.map((a) => a.component))].sort(),
  }
})
const visibleComponents = computed(() =>
  focusedComponent.value ? [focusedComponent.value] : timelineMeta.value.components
)

// --- Zoom Logic ---
const zoomLevels = [1, 1.5, 2, 4, 8, 16, 24, 32]
const zoomIndex = ref(0)
const currentZoom = computed(() => zoomLevels[zoomIndex.value])
const zoomIn = () => {
  if (zoomIndex.value < zoomLevels.length - 1) zoomIndex.value++
}
const zoomOut = () => {
  if (zoomIndex.value > 0) zoomIndex.value--
}
const resetZoom = () => {
  zoomIndex.value = 0
}
const handleWheel = (e) => {
  if (e.target.closest('.overflow-auto')) {
    if (e.deltaY < 0) zoomIn()
    else zoomOut()
  }
}

// --- Drag-to-Scroll Logic ---
const containerRef = ref(null)
const isDragging = ref(false)
const startX = ref(0)
const scrollLeftStart = ref(0)
const hasMoved = ref(false)

const startDrag = (e) => {
  if (e.target.closest('button') || e.target.closest('.absolute.z-50')) return
  if (!containerRef.value) return
  isDragging.value = true
  hasMoved.value = false
  startX.value = e.pageX - containerRef.value.offsetLeft
  scrollLeftStart.value = containerRef.value.scrollLeft
}

const onDrag = (e) => {
  if (!isDragging.value || !containerRef.value) return
  e.preventDefault()
  const x = e.pageX - containerRef.value.offsetLeft
  const walk = (x - startX.value) * 1.5
  containerRef.value.scrollLeft = scrollLeftStart.value - walk
  if (Math.abs(walk) > 5) hasMoved.value = true
}
const stopDrag = () => {
  isDragging.value = false
}

// --- Popover Logic ---
const selectedAlarm = ref(null)
const popoverPosition = ref({ x: 0, y: 0 })

const handleBarClick = (event, alarm) => {
  if (hasMoved.value) return
  openPopover(event, alarm)
}

const openPopover = (event, alarm) => {
  const container = event.target.closest('.relative.min-w-full').parentNode
  const containerRect = container.getBoundingClientRect()
  let x = event.clientX - containerRect.left + 20
  let y = event.clientY - containerRect.top
  const popoverWidth = 300
  const popoverHeight = 250
  if (x + popoverWidth > containerRect.width)
    x = event.clientX - containerRect.left - popoverWidth - 20
  if (y + popoverHeight > containerRect.height)
    y = event.clientY - containerRect.top - popoverHeight - 10
  if (x < 10) x = 10
  if (y < 10) y = 10
  popoverPosition.value = { x, y }
  selectedAlarm.value = alarm
}

const closePopover = () => {
  selectedAlarm.value = null
}
const popoverStyle = computed(() => ({
  top: `${popoverPosition.value.y}px`,
  left: `${popoverPosition.value.x}px`,
}))

// --- Timeline Math ---
const totalHours = computed(() => timelineMeta.value.totalRange / 3600000)
const axisTicks = computed(() => {
  const { min, max } = timelineMeta.value
  if (!min || !max) return []
  const totalSteps = 6 * currentZoom.value
  const stepSize = (max - min) / totalSteps
  const ticks = []
  for (let t = min; t <= max; t += stepSize) ticks.push(t)
  return ticks
})

// --- Helpers ---
const getTickPosition = (ts) =>
  ((ts - timelineMeta.value.min) / timelineMeta.value.totalRange) * 100
const getAlarmsForComponent = (c) => props.alarms.filter((a) => a.component === c)
const getBarStyle = (alarm) => {
  const { min, totalRange } = timelineMeta.value
  const start = new Date(alarm.start_time).getTime()
  const end = new Date(alarm.end_time).getTime()
  const left = ((start - min) / totalRange) * 100
  let width = ((end - start) / totalRange) * 100
  const minWidth = 0.4 / currentZoom.value
  if (width < minWidth) width = minWidth
  return { left: `${left}%`, width: `${width}%` }
}
const getSeverityClass = (s) => {
  switch (s) {
    case 'failed':
      return 'bg-red-500'
    case 'critical':
      return 'bg-red-600'
    case 'warning':
      return 'bg-amber-400'
    default:
      return 'bg-blue-400'
  }
}
const formatTickDate = (ts) =>
  new Date(ts).toLocaleDateString(undefined, { month: 'short', day: 'numeric' })
const formatTickTime = (ts) =>
  new Date(ts).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  height: 10px;
  width: 10px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 5px;
  border: 2px solid #f8fafc;
}
.dark .custom-scrollbar::-webkit-scrollbar-thumb {
  background: #334155;
  border-color: #020617;
}
</style>
