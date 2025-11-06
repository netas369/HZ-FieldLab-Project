<template>
  <div class="p-6 bg-gray-50 min-h-screen">
    <h1 class="text-2xl font-bold mb-4">Monthly changes</h1>

    <div v-if="loading" class="text-gray-500">Loading changes...</div>

    <div v-else>
      <div v-if="!changes && changes.length === 0" class="text-gray-500">
        No changes detected this month.
      </div>

   
      <div
        v-for="(item, index) in visibleChanges" :key="index"
        class="bg-white border border-gray-200 rounded-lg p-3 mb-2 shadow-sm hover:shadow transition"
      >
        <div class="flex items-center justify-between mb-1">
          <span class="font-medium text-gray-800 text-sm">
            Reading #{{ item.reading_id }}
          </span>
          <span class="text-xs text-gray-500">
            {{ formatDate(item.changes.status_code?.changed_at || item.changes.alarm_code?.changed_at) }}
          </span>
        </div>

        <!-- Status Code Change -->
        <div v-if="item.changes.status_code" class="text-xs border-l-2 border-blue-400 pl-2 mb-1">
          <span class="font-semibold text-blue-600">Status</span>:
          <span class="text-gray-600">
            {{ item.changes.status_code.old.code }}
            → 
            <span class="font-semibold text-gray-800">{{ item.changes.status_code.new.code }}</span>
          </span>
          <span class="ml-1 text-gray-500">
            ({{ item.changes.status_code.new.description }},
            <span :class="severityClass(item.changes.status_code.new.severity)">
              {{ item.changes.status_code.new.severity }}
            </span>)
          </span>
        </div>

        <!-- Alarm Code Change -->
        <div v-if="item.changes.alarm_code" class="text-xs border-l-2 border-red-400 pl-2">
          <span class="font-semibold text-red-600">Alarm</span>:
          <span class="text-gray-600">
            {{ item.changes.alarm_code.old.code }}
            → 
            <span class="font-semibold text-gray-800">{{ item.changes.alarm_code.new.code }}</span>
          </span>
          <span class="ml-1 text-gray-500">
            ({{ item.changes.alarm_code.new.description }},
            <span :class="severityClass(item.changes.alarm_code.new.severity)">
              {{ item.changes.alarm_code.new.severity }}
            </span>)
          </span>
        </div>
      </div>
          <div v-if="changes.length > 5" class="text-center mt-2">
              <button
                @click="showAll = !showAll"
                 class="px-3 py-1 text-xs bg-gray-200 rounded hover:bg-gray-300">
               {{ showAll ? 'Show Less' : 'Show More' }}
              </button>
    </div>
    </div>
  </div>
</template>


<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'

const turbineId = 1
const changes = ref([])
const loading = ref(true)
const showAll = ref(false)  

const formatDate = (date) => {
  return new Date(date).toLocaleString()
}

const severityClass = (severity) => {
  switch (severity?.toLowerCase()) {
    case 'high':
      return 'text-red-600 font-medium'
    case 'medium':
      return 'text-yellow-600 font-medium'
    case 'low':
      return 'text-green-600 font-medium'
    default:
      return 'text-gray-500'
  }
}


const visibleChanges = computed(() => {
  return showAll.value ? changes.value : changes.value.slice(0, 5)
})


onMounted(async () => {
  try {
    const response = await axios.get(`http://localhost:8000/api/turbine/${turbineId}/MonthScadaData`)
    changes.value = (response.data.data || []).sort((a, b) => {
    const timeA = new Date(a.changes.status_code?.changed_at || a.changes.alarm_code?.changed_at)
    const timeB = new Date(b.changes.status_code?.changed_at || b.changes.alarm_code?.changed_at)
     return timeB - timeA 
    })
  } catch (error) {
    console.error('Error fetching SCADA data:', error)
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
/* Optional hover/transition styling */
.bg-white:hover {
  transition: all 0.2s ease-in-out;
  box-shadow: 0px 2px 8px rgba(0, 0, 0, 0.15);
}
</style>
