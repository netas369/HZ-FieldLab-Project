<template>
  <div
    class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden shadow-sm flex flex-col h-full"
  >
    <div
      class="p-4 border-b border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 flex flex-col sm:flex-row sm:items-center justify-between gap-4"
    >
      <h3 class="font-bold text-slate-800 dark:text-white flex items-center gap-2">
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
            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
          />
        </svg>
        Detailed Log
      </h3>

      <div class="flex items-center gap-3">
        <div
          class="flex items-center bg-slate-100 dark:bg-slate-800 rounded-lg p-1 border border-slate-200 dark:border-slate-700"
        >
          <button
            class="px-3 py-1 text-xs font-medium rounded-md transition-colors"
            :class="
              filterSeverity === 'all'
                ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-sm'
                : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200'
            "
            @click="filterSeverity = 'all'"
          >
            All
          </button>
          <button
            class="px-3 py-1 text-xs font-medium rounded-md transition-colors flex items-center gap-1"
            :class="
              filterSeverity === 'failed'
                ? 'bg-white dark:bg-slate-700 text-red-600 dark:text-red-400 shadow-sm'
                : 'text-slate-500 dark:text-slate-400 hover:text-red-600 dark:hover:text-red-400'
            "
            @click="filterSeverity = 'failed'"
          >
            <span class="w-1.5 h-1.5 rounded-full bg-red-500" /> Failed
          </button>
          <button
            class="px-3 py-1 text-xs font-medium rounded-md transition-colors flex items-center gap-1"
            :class="
              filterSeverity === 'warning'
                ? 'bg-white dark:bg-slate-700 text-amber-600 dark:text-amber-400 shadow-sm'
                : 'text-slate-500 dark:text-slate-400 hover:text-amber-600 dark:hover:text-amber-400'
            "
            @click="filterSeverity = 'warning'"
          >
            <span class="w-1.5 h-1.5 rounded-full bg-amber-500" /> Warn
          </button>
        </div>

        <div class="relative group">
          <svg
            class="w-4 h-4 absolute left-3 top-2.5 text-slate-400 group-focus-within:text-indigo-500 transition-colors"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
            />
          </svg>
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search logs..."
            class="pl-9 pr-4 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none w-48 transition-all focus:w-64 placeholder:text-slate-400"
          >
        </div>
      </div>
    </div>

    <div class="overflow-x-auto min-h-[400px]">
      <table class="w-full text-left text-sm border-collapse">
        <thead
          class="bg-slate-50/50 dark:bg-slate-900/50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200 dark:border-slate-700 sticky top-0 z-10 backdrop-blur-sm"
        >
          <tr>
            <th
              v-for="col in columns"
              :key="col.key"
              class="px-6 py-3 cursor-pointer select-none hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors group"
              :class="col.align === 'right' ? 'text-right' : 'text-left'"
              @click="sortBy(col.key)"
            >
              <div
                class="flex items-center gap-1"
                :class="col.align === 'right' ? 'justify-end' : 'justify-start'"
              >
                {{ col.label }}
                <span
                  class="flex flex-col w-3 opacity-0 group-hover:opacity-50 transition-opacity"
                  :class="{ 'opacity-100 text-indigo-500': sortKey === col.key }"
                >
                  <svg
                    class="w-2 h-2 -mb-0.5"
                    :class="
                      sortKey === col.key && sortOrder === 1 ? 'text-indigo-600' : 'text-slate-400'
                    "
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="4"
                      d="M5 15l7-7 7 7"
                    />
                  </svg>
                  <svg
                    class="w-2 h-2 -mt-0.5"
                    :class="
                      sortKey === col.key && sortOrder === -1 ? 'text-indigo-600' : 'text-slate-400'
                    "
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="4"
                      d="M19 9l-7 7-7-7"
                    />
                  </svg>
                </span>
              </div>
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-800 bg-white dark:bg-slate-900">
          <tr
            v-for="alarm in paginatedAlarms"
            :key="alarm.id"
            class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors group"
          >
            <td class="px-6 py-4">
              <div class="flex flex-col items-start gap-1">
                <span
                  class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide border"
                  :class="
                    alarm.severity === 'failed'
                      ? 'bg-red-50 text-red-700 border-red-100 dark:bg-red-900/20 dark:text-red-400 dark:border-red-900/30'
                      : 'bg-amber-50 text-amber-700 border-amber-100 dark:bg-amber-900/20 dark:text-amber-400 dark:border-amber-900/30'
                  "
                >
                  {{ alarm.severity }}
                </span>
                <span
                  v-if="alarm.alarm_code"
                  class="text-[10px] font-mono text-slate-400 dark:text-slate-500 group-hover:text-slate-500 dark:group-hover:text-slate-400 transition-colors"
                >
                  #{{ alarm.alarm_code }}
                </span>
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="flex flex-col">
                <span class="text-slate-700 dark:text-slate-200 font-medium font-mono text-xs">{{
                  new Date(alarm.start_time).toLocaleDateString()
                }}</span>
                <span class="text-slate-400 text-[10px]">{{
                  new Date(alarm.start_time).toLocaleTimeString()
                }}</span>
              </div>
            </td>
            <td class="px-6 py-4">
              <span class="text-slate-900 dark:text-white font-medium capitalize text-sm">{{
                alarm.component.replace(/_/g, ' ')
              }}</span>
            </td>
            <td class="px-6 py-4">
              <div
                class="text-slate-500 dark:text-slate-400 text-sm max-w-xs truncate"
                :title="alarm.message"
              >
                {{ alarm.message }}
                <span
                  v-if="alarm.data?.value"
                  class="inline-block ml-2 font-mono text-[10px] bg-slate-100 dark:bg-slate-800 px-1.5 py-0.5 rounded text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700"
                >val: {{ alarm.data.value }}</span>
              </div>
            </td>
            <td class="px-6 py-4 text-right">
              <span
                class="font-mono text-slate-600 dark:text-slate-400 text-sm"
                :class="{ 'text-slate-400 dark:text-slate-600': alarm.duration_minutes === 0 }"
              >{{ alarm.duration_minutes }}m</span>
            </td>
          </tr>
          <tr v-if="paginatedAlarms.length === 0">
            <td
              colspan="5"
              class="px-6 py-12 text-center text-slate-400"
            >
              <div class="flex flex-col items-center justify-center gap-2">
                <svg
                  class="w-8 h-8 text-slate-300 dark:text-slate-600"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                  />
                </svg>
                <p>No logs found matching your filters.</p>
                <button
                  class="text-xs text-indigo-500 hover:text-indigo-600 font-medium mt-1"
                  @click="clearFilters"
                >
                  Clear all filters
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div
      class="p-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 flex flex-col sm:flex-row items-center justify-between gap-4"
    >
      <div class="text-xs text-slate-500 dark:text-slate-400 flex items-center gap-2">
        <span>Showing
          <span class="font-semibold text-slate-700 dark:text-slate-200">{{
            filteredCount > 0 ? startIndex + 1 : 0
          }}</span>
          to
          <span class="font-semibold text-slate-700 dark:text-slate-200">{{
            Math.min(endIndex, filteredCount)
          }}</span>
          of
          <span class="font-semibold text-slate-700 dark:text-slate-200">{{ filteredCount }}</span>
          results</span>
      </div>

      <div class="flex items-center gap-4">
        <div class="flex items-center gap-2">
          <label class="text-xs text-slate-500 dark:text-slate-400 hidden sm:inline">Rows per page:</label>
          <select
            v-model="pageSize"
            class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs rounded-lg px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
          >
            <option :value="10">
              10
            </option>
            <option :value="25">
              25
            </option>
            <option :value="50">
              50
            </option>
            <option :value="100">
              100
            </option>
          </select>
        </div>

        <div class="h-4 w-px bg-slate-200 dark:bg-slate-700 hidden sm:block" />

        <div class="flex items-center gap-2">
          <button
            :disabled="currentPage === 1"
            class="p-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 disabled:opacity-50 disabled:cursor-not-allowed hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors"
            @click="prevPage"
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
                d="M15 19l-7-7 7-7"
              />
            </svg>
          </button>
          <span
            class="text-xs font-medium text-slate-600 dark:text-slate-400 min-w-[3rem] text-center"
          >Page {{ currentPage }} / {{ totalPages || 1 }}</span>
          <button
            :disabled="currentPage === totalPages || totalPages === 0"
            class="p-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 disabled:opacity-50 disabled:cursor-not-allowed hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors"
            @click="nextPage"
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
                d="M9 5l7 7-7 7"
              />
            </svg>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'

const props = defineProps({
  alarms: { type: Array, required: true, default: () => [] },
})

// --- STATE ---
const searchQuery = ref('')
const filterSeverity = ref('all')
const currentPage = ref(1)
const pageSize = ref(10) // Changed default to ref so v-model works
const sortKey = ref('start_time')
const sortOrder = ref(-1)

// --- COLUMNS ---
const columns = [
  { key: 'severity', label: 'Status' },
  { key: 'start_time', label: 'Detected At' },
  { key: 'component', label: 'Component' },
  { key: 'message', label: 'Message' },
  { key: 'duration_minutes', label: 'Duration', align: 'right' },
]

// --- LOGIC ---
const processedAlarms = computed(() => {
  let result = [...props.alarms]
  if (filterSeverity.value !== 'all')
    result = result.filter((a) => a.severity === filterSeverity.value)
  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase()
    result = result.filter(
      (a) =>
        a.component.toLowerCase().includes(q) ||
        a.message.toLowerCase().includes(q) ||
        (a.alarm_code && String(a.alarm_code).includes(q))
    )
  }
  result.sort((a, b) => {
    let valA = a[sortKey.value],
      valB = b[sortKey.value]
    if (typeof valA === 'string') valA = valA.toLowerCase()
    if (typeof valB === 'string') valB = valB.toLowerCase()
    return valA < valB ? -1 * sortOrder.value : valA > valB ? 1 * sortOrder.value : 0
  })
  return result
})

const filteredCount = computed(() => processedAlarms.value.length)
const totalPages = computed(() => Math.ceil(filteredCount.value / pageSize.value))
const startIndex = computed(() => (currentPage.value - 1) * pageSize.value)
const endIndex = computed(() => startIndex.value + pageSize.value)
const paginatedAlarms = computed(() =>
  processedAlarms.value.slice(startIndex.value, endIndex.value)
)

const sortBy = (key) => {
  if (sortKey.value === key) sortOrder.value *= -1
  else {
    sortKey.value = key
    sortOrder.value = key === 'start_time' || key === 'duration_minutes' ? -1 : 1
  }
}

const prevPage = () => {
  if (currentPage.value > 1) currentPage.value--
}
const nextPage = () => {
  if (currentPage.value < totalPages.value) currentPage.value++
}
const clearFilters = () => {
  searchQuery.value = ''
  filterSeverity.value = 'all'
}

// Reset to page 1 if filters OR page size change
watch([searchQuery, filterSeverity, pageSize], () => (currentPage.value = 1))
</script>
