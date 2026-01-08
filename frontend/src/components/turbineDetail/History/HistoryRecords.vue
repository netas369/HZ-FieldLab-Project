<template>
  <div class="mb-6">
    <div class="flex items-center justify-between mb-3 px-1">
      <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
        Recent History
      </span>
      <button
        class="text-[10px] font-bold text-red-500 hover:text-red-600 flex items-center gap-1 transition-colors uppercase tracking-tight bg-red-50 dark:bg-red-900/10 px-2 py-1 rounded"
        @click="showClearConfirm = true"
      >
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
          />
        </svg>
        Clear All
      </button>
    </div>

    <div class="relative group">
      <button
        class="absolute left-0 top-1/2 -translate-y-1/2 z-30 bg-white/90 dark:bg-slate-700/90 shadow-lg rounded-full p-1.5 border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:bg-white transition-all opacity-0 group-hover:opacity-100"
        @click="scrollHistory(-300)"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path
            d="M15 19l-7-7 7-7"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
          />
        </svg>
      </button>

      <div ref="scrollContainer" class="flex overflow-x-auto gap-3 pb-4 no-scrollbar scroll-smooth">
        <div
          v-for="entry in records"
          :key="entry.id"
          class="relative flex-shrink-0 group/item pt-3 first:ml-12 last:mr-12"
        >
          <button
            class="absolute top-3 right-0 z-20 bg-red-500 text-white rounded-full p-1 shadow-md opacity-0 group-hover/item:opacity-100 transition-opacity hover:bg-red-600 transform translate-x-1/2 -translate-y-1/2 border border-white dark:border-slate-800"
            title="Remove from history"
            @click.stop="$emit('remove', entry.id)"
          >
            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="3"
                d="M6 18L18 6M6 6l12 12"
              />
            </svg>
          </button>

          <button
            class="flex flex-col items-start px-3 py-2 rounded-lg border transition-all text-left w-44 md:w-48 h-full"
            :class="
              activeEntryId === entry.id
                ? 'bg-indigo-50 border-indigo-200 text-indigo-700 dark:bg-indigo-900/30 dark:border-indigo-500/50 dark:text-indigo-300 ring-2 ring-indigo-500/20 shadow-sm'
                : 'bg-white border-slate-200 text-slate-500 hover:border-indigo-300 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-400'
            "
            @click="$emit('select', entry)"
          >
            <div class="flex justify-between w-full items-center mb-1">
              <span class="text-[10px] font-bold">{{ entry.timestamp }}</span>
              <div
                v-if="activeEntryId === entry.id"
                class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"
              />
            </div>
            <span class="text-[10px] leading-tight font-medium"
              >{{ entry.startDate }} to<br />{{ entry.endDate }}</span
            >
          </button>
        </div>
      </div>

      <button
        class="absolute right-0 top-1/2 -translate-y-1/2 z-30 bg-white/90 dark:bg-slate-700/90 shadow-lg rounded-full p-1.5 border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:bg-white transition-all opacity-0 group-hover:opacity-100"
        @click="scrollHistory(300)"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path d="M9 5l7 7-7 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
      </button>
    </div>

    <Teleport to="body">
      <div
        v-if="showClearConfirm"
        class="fixed inset-0 z-[100] flex items-center justify-center p-4"
      >
        <div
          class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"
          @click="showClearConfirm = false"
        />

        <div
          class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 max-w-sm w-full p-6 transform transition-all"
        >
          <div class="flex items-center gap-4 mb-4 text-red-500">
            <div class="p-3 bg-red-100 dark:bg-red-900/30 rounded-full">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                />
              </svg>
            </div>
            <h4 class="text-lg font-bold text-slate-900 dark:text-white">Clear History?</h4>
          </div>

          <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">
            Permanently delete all history records?
          </p>

          <div class="flex gap-3">
            <button
              class="flex-1 px-4 py-2 text-sm font-semibold text-slate-700 dark:text-slate-200 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 rounded-xl transition-colors"
              @click="showClearConfirm = false"
            >
              Cancel
            </button>
            <button
              class="flex-1 px-4 py-2 text-sm font-semibold text-white bg-red-500 hover:bg-red-600 rounded-xl shadow-md transition-colors active:scale-95"
              @click="confirmClear"
            >
              Clear All
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref } from 'vue'

defineProps({
  turbineId: { type: String, required: true },
  records: { type: Array, required: true },
  activeEntryId: { type: [Number, String], default: null },
})

const emit = defineEmits(['select', 'remove', 'clearAll'])

const scrollContainer = ref(null)
const showClearConfirm = ref(false)

const scrollHistory = (amount) => {
  if (scrollContainer.value) {
    scrollContainer.value.scrollBy({ left: amount, behavior: 'smooth' })
  }
}

const confirmClear = () => {
  emit('clearAll')
  showClearConfirm.value = false
}
</script>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
  display: none;
}
.no-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
