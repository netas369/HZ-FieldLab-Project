<template>
  <div
      :class="[
      'group relative overflow-hidden transition-all duration-200',
      'bg-white dark:bg-slate-800 rounded-xl border-2',
      'hover:shadow-xl hover:-translate-y-0.5',
      borderColorClass,
      alarm.acknowledged ? 'opacity-70 hover:opacity-85' : '',
      'cursor-pointer'
    ]"
      @click="$emit('click')"
  >
    <!-- Gradient Background Accent -->
    <div 
      :class="[
        'absolute top-0 left-0 right-0 h-1',
        gradientClass
      ]"
    />

    <!-- Content -->
    <div class="relative p-5">
      <!-- Top Row: Priority + Actions -->
      <div class="flex items-start justify-between mb-3">
        <!-- Priority Badge -->
        <div class="flex items-center gap-2">
          <div 
            :class="[
              'relative flex items-center gap-2 px-3 py-1.5 rounded-lg font-bold text-sm',
              priorityBadgeClass
            ]"
          >
            <!-- Pulsing Dot for Critical/Unacknowledged -->
            <span 
              v-if="!alarm.acknowledged && (alarm.priority === 'Critical' || alarm.priority === 'Major')"
              class="relative flex h-2 w-2"
            >
              <span 
                :class="[
                  'animate-ping absolute inline-flex h-full w-full rounded-full opacity-75',
                  alarm.priority === 'Critical' ? 'bg-red-400' : 'bg-orange-400'
                ]"
              ></span>
              <span 
                :class="[
                  'relative inline-flex rounded-full h-2 w-2',
                  alarm.priority === 'Critical' ? 'bg-red-500' : 'bg-orange-500'
                ]"
              ></span>
            </span>
            <span class="uppercase tracking-wider">{{ alarm.priority }}</span>
          </div>

          <!-- Acknowledged Checkmark -->
          <transition name="scale">
            <div
                v-if="alarm.acknowledged"
                class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 text-xs font-semibold"
            >
              <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
              </svg>
              <span>ACK</span>
            </div>
          </transition>
        </div>

        <!-- Quick Actions -->
        <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
          <button
              v-if="!alarm.acknowledged"
              @click.stop="$emit('acknowledge')"
              class="p-2 rounded-lg hover:bg-green-100 dark:hover:bg-green-900/30 text-green-600 dark:text-green-400 transition-all hover:scale-110"
              title="Acknowledge alarm"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
            </svg>
          </button>

          <button
              v-if="viewMode === 'list'"
              @click.stop="$emit('toggle-details')"
              class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-500 dark:text-slate-400 transition-all"
              :title="showDetails ? 'Hide details' : 'Show details'"
          >
            <svg
                class="w-5 h-5 transition-transform duration-200"
                :class="{ 'rotate-180': showDetails }"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>

          <button
              @click.stop="$emit('click')"
              class="p-2 rounded-lg hover:bg-indigo-100 dark:hover:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 transition-all hover:scale-110"
              title="View details"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Alarm Title -->
      <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2 pr-8 leading-snug">
        {{ alarm.title }}
      </h3>

      <!-- Description -->
      <p class="text-sm text-slate-600 dark:text-slate-400 mb-4 line-clamp-2 leading-relaxed">
        {{ alarm.description }}
      </p>

      <!-- Metadata Footer -->
      <div class="flex items-center justify-between pt-3 border-t border-slate-100 dark:border-slate-700">
        <!-- Left: Turbine & Time -->
        <div class="flex items-center gap-4 text-xs text-slate-500 dark:text-slate-400">
          <!-- Turbine -->
          <div class="flex items-center gap-1.5 font-medium">
            <div :class="['p-1 rounded', priorityIconBgLight]">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
              </svg>
            </div>
            <span class="text-slate-700 dark:text-slate-300">{{ alarm.turbine }}</span>
          </div>

          <!-- Time -->
          <div class="flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ formatTime(alarm.time) }}</span>
          </div>
        </div>

        <!-- Right: Priority Icon -->
        <div 
          :class="[
            'p-2 rounded-lg transition-transform group-hover:scale-110',
            priorityIconBg
          ]"
        >
          <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
          </svg>
        </div>
      </div>

      <!-- Expanded Details (List View Only) -->
      <transition name="expand">
        <div
            v-if="showDetails && viewMode === 'list'"
            class="mt-4 pt-4 border-t-2 border-slate-100 dark:border-slate-700 space-y-4"
        >
          <!-- Full Description -->
          <div>
            <h4 class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
              Full Description
            </h4>
            <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
              {{ alarm.description }}
            </p>
          </div>

          <!-- Info Grid -->
          <div class="grid grid-cols-2 gap-4">
            <div class="bg-slate-50 dark:bg-slate-900 rounded-lg p-3">
              <h4 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">
                Turbine
              </h4>
              <p class="text-sm font-semibold text-slate-900 dark:text-white">
                {{ alarm.turbine }}
              </p>
            </div>
            <div class="bg-slate-50 dark:bg-slate-900 rounded-lg p-3">
              <h4 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">
                Timestamp
              </h4>
              <p class="text-sm font-semibold text-slate-900 dark:text-white">
                {{ formatFullTime(alarm.time) }}
              </p>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex gap-3 pt-2">
            <button
                v-if="!alarm.acknowledged"
                @click.stop="$emit('acknowledge')"
                class="flex-1 px-4 py-2.5 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white rounded-lg font-semibold text-sm transition-all shadow-lg shadow-green-500/30 hover:shadow-xl hover:shadow-green-500/40"
            >
              <span class="flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                </svg>
                Acknowledge
              </span>
            </button>
            <button
                @click.stop="$emit('click')"
                class="flex-1 px-4 py-2.5 bg-gradient-to-r from-indigo-500 to-indigo-600 hover:from-indigo-600 hover:to-indigo-700 text-white rounded-lg font-semibold text-sm transition-all shadow-lg shadow-indigo-500/30 hover:shadow-xl hover:shadow-indigo-500/40"
            >
              <span class="flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                View Full Details
              </span>
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

const borderColorClass = computed(() => {
  const colors = {
    Critical: 'border-red-200 dark:border-red-900/50 hover:border-red-400 dark:hover:border-red-700',
    Major: 'border-orange-200 dark:border-orange-900/50 hover:border-orange-400 dark:hover:border-orange-700',
    Warning: 'border-amber-200 dark:border-amber-900/50 hover:border-amber-400 dark:hover:border-amber-700',
    Minor: 'border-blue-200 dark:border-blue-900/50 hover:border-blue-400 dark:hover:border-blue-700'
  }
  return colors[props.alarm.priority] || 'border-slate-200 dark:border-slate-700'
})

const gradientClass = computed(() => {
  const gradients = {
    Critical: 'bg-gradient-to-r from-red-500 via-red-400 to-red-500',
    Major: 'bg-gradient-to-r from-orange-500 via-orange-400 to-orange-500',
    Warning: 'bg-gradient-to-r from-amber-500 via-amber-400 to-amber-500',
    Minor: 'bg-gradient-to-r from-blue-500 via-blue-400 to-blue-500'
  }
  return gradients[props.alarm.priority] || 'bg-gradient-to-r from-slate-400 to-slate-500'
})

const priorityBadgeClass = computed(() => {
  const classes = {
    Critical: 'bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300 ring-2 ring-red-200 dark:ring-red-800',
    Major: 'bg-orange-100 dark:bg-orange-900/40 text-orange-700 dark:text-orange-300 ring-2 ring-orange-200 dark:ring-orange-800',
    Warning: 'bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 ring-2 ring-amber-200 dark:ring-amber-800',
    Minor: 'bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 ring-2 ring-blue-200 dark:ring-blue-800'
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
  return classes[props.alarm.priority] || 'bg-slate-100 dark:bg-slate-700 text-slate-600'
})

const priorityIconBgLight = computed(() => {
  const classes = {
    Critical: 'bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400',
    Major: 'bg-orange-50 dark:bg-orange-900/20 text-orange-600 dark:text-orange-400',
    Warning: 'bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400',
    Minor: 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400'
  }
  return classes[props.alarm.priority] || 'bg-slate-50 dark:bg-slate-800 text-slate-600'
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
      day: 'numeric'
    })
  } catch (e) {
    return timeString
  }
}

const formatFullTime = (timeString) => {
  try {
    const date = new Date(timeString)
    return date.toLocaleString('en-US', {
      month: 'short',
      day: 'numeric',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    })
  } catch (e) {
    return timeString
  }
}
</script>

<style scoped>
/* Line clamp utility */
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Expand transition */
.expand-enter-active,
.expand-leave-active {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  overflow: hidden;
}

.expand-enter-from,
.expand-leave-to {
  max-height: 0;
  opacity: 0;
  transform: translateY(-10px);
}

.expand-enter-to,
.expand-leave-from {
  max-height: 600px;
  opacity: 1;
  transform: translateY(0);
}

/* Scale transition */
.scale-enter-active,
.scale-leave-active {
  transition: all 0.2s ease;
}

.scale-enter-from,
.scale-leave-to {
  opacity: 0;
  transform: scale(0.8);
}
</style>