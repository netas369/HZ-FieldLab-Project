<template>
  <div class="fixed inset-0 z-50 overflow-y-auto" @click.self="$emit('close')">
    <div class="flex min-h-screen items-center justify-center p-4">
      <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" />

      <div
        class="relative bg-white dark:bg-slate-800 rounded-xl shadow-2xl max-w-2xl w-full p-6 border border-slate-200 dark:border-slate-700"
      >
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
          <div>
            <h3 class="text-xl font-bold text-slate-900 dark:text-white">Edit Threshold</h3>
            <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
              {{ formatComponentName(localThreshold.component_name) }} ({{ localThreshold.unit }})
            </p>
          </div>
          <button
            class="p-2 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors"
            @click="$emit('close')"
          >
            <svg
              class="w-5 h-5 text-slate-600 dark:text-slate-400"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
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

        <!-- Form -->
        <div class="space-y-4">
          <!-- Normal Range -->
          <div
            class="p-4 bg-gradient-to-br from-emerald-50 to-emerald-100/50 dark:from-emerald-900/20 dark:to-emerald-800/10 rounded-lg border border-emerald-200 dark:border-emerald-800"
          >
            <h4 class="font-semibold text-emerald-900 dark:text-emerald-300 mb-3">Normal Range</h4>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1"
                  >Minimum</label
                >
                <input
                  v-model.number="localThreshold.normal_min"
                  type="number"
                  step="0.01"
                  class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 dark:focus:ring-emerald-400 focus:border-transparent"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1"
                  >Maximum</label
                >
                <input
                  v-model.number="localThreshold.normal_max"
                  type="number"
                  step="0.01"
                  class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 dark:focus:ring-emerald-400 focus:border-transparent"
                />
              </div>
            </div>
          </div>

          <!-- Warning Range -->
          <div
            class="p-4 bg-gradient-to-br from-amber-50 to-amber-100/50 dark:from-amber-900/20 dark:to-amber-800/10 rounded-lg border border-amber-200 dark:border-amber-800"
          >
            <h4 class="font-semibold text-amber-900 dark:text-amber-300 mb-3">Warning Range</h4>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1"
                  >Minimum</label
                >
                <input
                  v-model.number="localThreshold.warning_min"
                  type="number"
                  step="0.01"
                  class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-amber-500 dark:focus:ring-amber-400 focus:border-transparent"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1"
                  >Maximum</label
                >
                <input
                  v-model.number="localThreshold.warning_max"
                  type="number"
                  step="0.01"
                  class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-amber-500 dark:focus:ring-amber-400 focus:border-transparent"
                />
              </div>
            </div>
          </div>

          <!-- Critical Range -->
          <div
            class="p-4 bg-gradient-to-br from-orange-50 to-orange-100/50 dark:from-orange-900/20 dark:to-orange-800/10 rounded-lg border border-orange-200 dark:border-orange-800"
          >
            <h4 class="font-semibold text-orange-900 dark:text-orange-300 mb-3">Critical Range</h4>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1"
                  >Minimum</label
                >
                <input
                  v-model.number="localThreshold.critical_min"
                  type="number"
                  step="0.01"
                  class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-orange-500 dark:focus:ring-orange-400 focus:border-transparent"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1"
                  >Maximum</label
                >
                <input
                  v-model.number="localThreshold.critical_max"
                  type="number"
                  step="0.01"
                  class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-orange-500 dark:focus:ring-orange-400 focus:border-transparent"
                />
              </div>
            </div>
          </div>

          <!-- Failed Range -->
          <div
            class="p-4 bg-gradient-to-br from-red-50 to-red-100/50 dark:from-red-900/20 dark:to-red-800/10 rounded-lg border border-red-200 dark:border-red-800"
          >
            <h4 class="font-semibold text-red-900 dark:text-red-300 mb-3">Failed Range</h4>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1"
                  >Minimum</label
                >
                <input
                  v-model.number="localThreshold.failed_min"
                  type="number"
                  step="0.01"
                  class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-red-500 dark:focus:ring-red-400 focus:border-transparent"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1"
                  >Maximum</label
                >
                <input
                  v-model.number="localThreshold.failed_max"
                  type="number"
                  step="0.01"
                  class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-red-500 dark:focus:ring-red-400 focus:border-transparent"
                />
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex gap-3 pt-4 border-t border-slate-200 dark:border-slate-700">
            <button
              class="flex-1 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-600 dark:hover:bg-indigo-700 text-white rounded-lg font-medium transition-colors"
              @click="handleSave"
            >
              Save Changes
            </button>
            <button
              class="px-4 py-2 bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 rounded-lg font-medium transition-colors"
              @click="$emit('close')"
            >
              Cancel
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
  threshold: { type: Object, required: true },
})

const emit = defineEmits(['close', 'save'])

const localThreshold = ref({ ...props.threshold })

const formatComponentName = (name) => {
  return name
    .split('_')
    .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
    .join(' ')
}

const handleSave = () => {
  emit('save', localThreshold.value)
}
</script>
