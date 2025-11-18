<template>
  <div
      :class="[
      'group relative overflow-hidden transition-all duration-300',
      'bg-white dark:bg-slate-800 rounded-xl shadow-sm hover:shadow-lg',
      'border-l-4 cursor-pointer',
      borderColor,
      alarm.acknowledged ? 'opacity-60' : ''
    ]"
      @click="$emit('click')"
  >
    <!-- Priority Indicator Glow -->
    <div
        :class="[
        'absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-300',
        glowColor
      ]"
    />

    <div class="relative p-4">
      <!-- Header -->
      <div class="flex items-start justify-between mb-3">
        <div class="flex-1 min-w-0">
          <div class="flex items-center gap-2 mb-1">
            <!-- Priority Badge -->
            <span
                :class="[
                'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wide',
                priorityBadgeClass
              ]"
            >
              <span :class="['w-1.5 h-1.5 rounded-full mr-1.5', alarm.acknowledged ? '' : 'animate-pulse']" :style="{ backgroundColor: 'currentColor' }" />
              {{ alarm.priority }}
            </span>

            <!-- Acknowledged Badge -->
            <span
                v-if="alarm.acknowledged"
                class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400"
            >
              <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
              </svg>
              Acknowledged
            </span>
          </div>

          <h3 class="text-base font-semibold text-slate-900 dark:text-white truncate">
            {{ alarm.title }}
          </h3>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center gap-1 ml-3 opacity-0 group-hover:opacity-100 transition-opacity">
          <button
              v-if="!alarm.acknowledged"
              @click.stop="$emit('acknowledge')"
              class="p-1.5 rounded-lg hover:bg-green-100 dark:hover:bg-green-900/30 text-green-600 dark:text-green-400 transition-colors"
              title="Acknowledge"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
          </button>

          <button
              v-if="viewMode === 'list'"
              @click.stop="$emit('toggle-details')"
              class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-400 transition-colors"
              :title="showDetails ? 'Hide details' : 'Show details'"
          >
            <svg
                class="w-5 h-5 transition-transform"
                :class="{ 'rotate-180': showDetails }"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Description -->
      <p class="text-sm text-slate-600 dark:text-slate-400 line-clamp-2 mb-3">
        {{ alarm.description }}
      </p>

      <!-- Metadata -->
      <div class="flex items-center justify-between text-xs">
        <div class="flex items-center gap-4 text-slate-500 dark:text-slate-400">
          <!-- Turbine -->
          <span class="flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
            </svg>
            <span class="font-medium">{{ alarm.turbine }}</span>
          </span>

          <!-- Time -->
          <span class="flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ formatTime(alarm.time) }}
          </span>
        </div>

        <!-- Priority Icon -->
        <div :class="['p-1.5 rounded-lg', priorityIconBg]">
          <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
          </svg>
        </div>
      </div>

      <!-- Expanded Details (List View Only) -->
      <transition name="expand">
        <div
            v-if="showDetails && viewMode === 'list'"
            class="mt-4 pt-4 border-t border-slate-200 dark:border-slate-700 space-y-3"
        >
          <!-- Full Description -->
          <div>
            <h4 class="text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-1">
              Details
            </h4>
            <p class="text-sm text-slate-600 dark:text-slate-400">
              {{ alarm.description }}
            </p>
          </div>

          <!-- Additional Info -->
          <div class="grid grid-cols-2 gap-3">
            <div>
              <h4 class="text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-1">
                Turbine
              </h4>
              <p class="text-sm text-slate-900 dark:text-white font-medium">
                {{ alarm.turbine }}
              </p>
            </div>
            <div>
              <h4 class="text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-1">
                Timestamp
              </h4>
              <p class="text-sm text-slate-900 dark:text-white font-medium">
                {{ alarm.time }}
              </p>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex gap-2 pt-2">
            <button
                v-if="!alarm.acknowledged"
                @click.stop="$emit('acknowledge')"
                class="flex-1 px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg font-medium text-sm transition-colors"
            >
              Acknowledge
            </button>
            <button
                @click.stop="$emit('click')"
                class="flex-1 px-4 py-2 bg-indigo-500 hover:bg-indigo-600 text-white rounded-lg font-medium text-sm transition-colors"
            >
              View Full Details
            </button>
          </div>
        </div>
      </transition>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  alarm: {
    type: Object,
    required: true
  },
  viewMode: {
    type: String,
    default: 'list',
    validator: (value) => ['list', 'grid'].includes(value)
  },
  showDetails: {
    type: Boolean,
    default: false
  }
})

defineEmits(['click', 'acknowledge', 'toggle-details'])

// ============================================================================
// COMPUTED STYLING
// ============================================================================

const borderColor = computed(() => {
  const colors = {
    Critical: 'border-red-500',
    Major: 'border-orange-500',
    Warning: 'border-amber-500',
    Minor: 'border-blue-500'
  }
  return colors[props.alarm.priority] || 'border-slate-300'
})

const glowColor = computed(() => {
  const colors = {
    Critical: 'bg-gradient-to-r from-red-500/5 to-transparent',
    Major: 'bg-gradient-to-r from-orange-500/5 to-transparent',
    Warning: 'bg-gradient-to-r from-amber-500/5 to-transparent',
    Minor: 'bg-gradient-to-r from-blue-500/5 to-transparent'
  }
  return colors[props.alarm.priority] || 'bg-gradient-to-r from-slate-500/5 to-transparent'
})

const priorityBadgeClass = computed(() => {
  const classes = {
    Critical: 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400',
    Major: 'bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400',
    Warning: 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400',
    Minor: 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400'
  }
  return classes[props.alarm.priority] || 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300'
})

const priorityIconBg = computed(() => {
  const classes = {
    Critical: 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400',
    Major: 'bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400',
    Warning: 'bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400',
    Minor: 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400'
  }
  return classes[props.alarm.priority] || 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-400'
})

// ============================================================================
// UTILITY METHODS
// ============================================================================

const formatTime = (timeString) => {
  try {
    const date = new Date(timeString)
    const now = new Date()
    const diffMs = now - date
    const diffMins = Math.floor(diffMs / 60000)
    const diffHours = Math.floor(diffMins / 60)
    const diffDays = Math.floor(diffHours / 24)

    if (diffMins < 1) return 'Just now'
    if (diffMins < 60) return `${diffMins}m ago`
    if (diffHours < 24) return `${diffHours}h ago`
    if (diffDays < 7) return `${diffDays}d ago`

    return date.toLocaleDateString('en-US', {
      month: 'short',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    })
  } catch (e) {
    return timeString
  }
}
</script>

<style scoped>
/* Expand transition for details */
.expand-enter-active,
.expand-leave-active {
  transition: all 0.3s ease;
  overflow: hidden;
}

.expand-enter-from,
.expand-leave-to {
  max-height: 0;
  opacity: 0;
}

.expand-enter-to,
.expand-leave-from {
  max-height: 500px;
  opacity: 1;
}

/* Line clamp utility */
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>