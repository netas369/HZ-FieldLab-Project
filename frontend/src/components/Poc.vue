<template>
  <div class="min-h-screen bg-gradient-to-br from-indigo-500 to-purple-700 font-sans text-gray-800">
    <div class="max-w-7xl mx-auto p-6">
      <!-- Header -->
      <header
        class="bg-white/90 backdrop-blur rounded-2xl p-6 mb-6 shadow-xl flex justify-between items-center"
      >
        <div class="flex items-center gap-4">
          <svg viewBox="0 0 100 100" class="w-10 h-10 animate-spin-slow text-indigo-500">
            <circle cx="50" cy="50" r="3" fill="currentColor" />
            <path
              d="M50 10 L50 40 L35 25 M50 40 L65 25"
              stroke="currentColor"
              stroke-width="3"
              fill="none"
            />
            <path
              d="M50 10 L50 40 L35 25 M50 40 L65 25"
              stroke="currentColor"
              stroke-width="3"
              fill="none"
              transform="rotate(120 50 50)"
            />
            <path
              d="M50 10 L50 40 L35 25 M50 40 L65 25"
              stroke="currentColor"
              stroke-width="3"
              fill="none"
              transform="rotate(240 50 50)"
            />
          </svg>
          <h1
            class="text-2xl font-semibold bg-gradient-to-r from-indigo-500 to-purple-600 bg-clip-text text-transparent"
          >
            Wind Turbine Monitoring System
          </h1>
        </div>
        <div class="flex items-center gap-4">
          <span>Welcome, {{ currentUser.name }}</span>
          <span
            class="px-3 py-1 rounded-full text-sm font-medium text-white bg-gradient-to-r from-indigo-500 to-purple-600"
          >
            {{ currentUser.role }}
          </span>
          <button
            class="px-4 py-2 rounded-xl bg-indigo-500 text-white hover:bg-indigo-600 transition"
            @click="showMaintenanceForm = true"
          >
            Log Maintenance
          </button>
        </div>
      </header>

      <!-- Grid layout -->
      <div class="grid grid-cols-[250px_1fr_320px] gap-6">
        <!-- Sidebar -->
        <aside class="bg-white/90 backdrop-blur rounded-2xl p-4 shadow-xl">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search turbines..."
            class="w-full p-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 mb-4"
          />
          <nav>
            <div
              v-for="item in navItems"
              :key="item.id"
              :class="[
                'flex items-center gap-2 px-3 py-2 rounded-lg cursor-pointer transition',
                activeTab === item.id
                  ? 'bg-gradient-to-r from-indigo-500 to-purple-600 text-white'
                  : 'hover:bg-indigo-50',
              ]"
              @click="activeTab = item.id"
            >
              <span>{{ item.icon }}</span> {{ item.label }}
            </div>
          </nav>
        </aside>

        <!-- Main content -->
        <main class="flex flex-col gap-6"></main>

        <!-- Alarms Panel (right sidebar) -->
        <aside
          class="bg-white/90 backdrop-blur rounded-2xl p-4 shadow-xl max-h-[85vh] overflow-y-auto"
        >
          <h3 class="text-lg font-semibold">Active Alarms ({{ filteredAlarms.length }})</h3>
          <div class="flex flex-wrap gap-2 my-3">
            <button
              v-for="filter in alarmFilters"
              :key="filter"
              :class="[
                'px-3 py-1 rounded-lg text-sm font-medium transition',
                activeAlarmFilter === filter
                  ? 'bg-indigo-500 text-white'
                  : 'border border-gray-300 hover:bg-gray-100',
              ]"
              @click="activeAlarmFilter = filter"
            >
              {{ filter }}
            </button>
          </div>

          <transition-group name="slide-fade">
            <div
              v-for="alarm in filteredAlarms"
              :key="alarm.id"
              class="mb-3 p-4 rounded-lg bg-white shadow cursor-pointer"
              @click="showAlarmDetails(alarm)"
            >
              <div class="flex justify-between items-center">
                <span class="font-semibold">{{ alarm.title }}</span>
                <span
                  :class="[
                    'px-2 py-0.5 rounded text-xs font-medium',
                    alarm.priority === 'Critical'
                      ? 'bg-red-500 text-white'
                      : alarm.priority === 'Major'
                        ? 'bg-orange-500 text-white'
                        : alarm.priority === 'Warning'
                          ? 'bg-yellow-400 text-black'
                          : 'bg-gray-200 text-gray-800',
                  ]"
                >
                  {{ alarm.priority }}
                </span>
              </div>
              <p class="text-sm text-gray-600 mt-1">{{ alarm.description }}</p>
              <div class="flex justify-between text-xs text-gray-500 mt-2">
                <span>{{ alarm.turbine }}</span>
                <span>{{ alarm.time }}</span>
              </div>
            </div>
          </transition-group>
        </aside>
      </div>

      <!-- Alarm Modal -->
      <div
        v-if="showAlarmPopup"
        class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
      >
        <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl">
          <h3 class="text-lg font-semibold text-gray-800 mb-2">{{ popupAlarm.title }}</h3>
          <p class="text-sm text-gray-600 mb-2">
            Priority:
            <span :class="priorityColor(popupAlarm.priority)">
              {{ popupAlarm.priority }}
            </span>
          </p>
          <p class="text-sm text-gray-700 mb-4">{{ popupAlarm.description }}</p>
          <p class="text-xs text-gray-500 mb-4">Turbine: {{ popupAlarm.turbine }}</p>
          <div class="flex justify-end gap-2">
            <button
              class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300 transition"
              @click="showAlarmPopup = false"
            >
              Close
            </button>
            <button
              class="px-4 py-2 bg-indigo-500 text-white rounded-lg hover:bg-indigo-600 transition"
              @click="acknowledgeAlarm(popupAlarm)"
            >
              Acknowledge
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted, onUnmounted } from 'vue'

export default {
  name: 'TurbineMonitoringPrototype',
  setup() {
    // Reactive state
    const activeTab = ref('overview')
    const activeMaintenanceTab = ref('Recent')
    const activeAlarmFilter = ref('All')
    const searchQuery = ref('')
    const selectedTurbine = ref({})
    const showMaintenanceForm = ref(false)

    const showAlarmPopup = ref(false)
    const popupAlarm = ref({})

    // User & navigation
    const currentUser = ref({ name: 'John Smith', role: 'Supervisor' })
    const navItems = ref([
      { id: 'overview', icon: '📊', label: 'Overview' },
      { id: 'maintenance', icon: '🔧', label: 'Maintenance' },
      { id: 'alarms', icon: '⚠️', label: 'Active Alarms' },
      { id: 'analytics', icon: '📈', label: 'Analytics' },
      { id: 'reports', icon: '📄', label: 'Reports' },
      { id: 'settings', icon: '⚙️', label: 'Settings' },
    ])

    // Turbines and metrics
    const turbines = ref([
      { id: 'WT-001', location: 'North Field', status: 'running', statusText: 'Running' },
      { id: 'WT-002', location: 'North Field', status: 'running', statusText: 'Running' },
      { id: 'WT-003', location: 'East Field', status: 'maintenance', statusText: 'Maintenance' },
      { id: 'WT-004', location: 'East Field', status: 'warning', statusText: 'Warning' },
      { id: 'WT-005', location: 'South Field', status: 'stopped', statusText: 'Stopped' },
      { id: 'WT-006', location: 'South Field', status: 'running', statusText: 'Running' },
    ])

    const turbineMetrics = ref({
      'WT-001': { power: '2.8 MW', wind: '14 m/s', availability: '98.5%', rotor: '15 RPM' },
      'WT-002': { power: '2.6 MW', wind: '13 m/s', availability: '97.2%', rotor: '14 RPM' },
      'WT-003': { power: '0 MW', wind: '12 m/s', availability: '85.3%', rotor: '0 RPM' },
      'WT-004': { power: '2.4 MW', wind: '11 m/s', availability: '94.1%', rotor: '13 RPM' },
      'WT-005': { power: '0 MW', wind: '10 m/s', availability: '0%', rotor: '0 RPM' },
      'WT-006': { power: '2.9 MW', wind: '15 m/s', availability: '99.1%', rotor: '16 RPM' },
    })

    const componentHealth = ref([
      { name: 'Gearbox', health: 85 },
      { name: 'Generator', health: 92 },
      { name: 'Blades', health: 88 },
      { name: 'Main Bearing', health: 88 },
      { name: 'Tower', health: 95 },
      { name: 'Yaw System', health: 90 },
    ])

    // Alarms
    const alarms = ref([
      {
        id: 1,
        title: 'GEARBOX_VIBRATION_HIGH',
        priority: 'Warning',
        description: 'Vibration level: 4.2 mm/s RMS (Threshold: 4.5)',
        turbine: 'WT-004',
        time: '2 hours ago',
      },
      {
        id: 2,
        title: 'GENERATOR_OVERHEAT',
        priority: 'Major',
        description: 'Winding temp: 88°C (Warning: 85°C, Fault: 95°C)',
        turbine: 'WT-003',
        time: '5 hours ago',
      },
      {
        id: 3,
        title: 'YAW_MISALIGNMENT',
        priority: 'Minor',
        description: 'Misalignment: 12° from optimal (Threshold: 15°)',
        turbine: 'WT-001',
        time: '30 mins ago',
      },
      {
        id: 4,
        title: 'BLADE_IMBALANCE',
        priority: 'Critical',
        description: 'Tower vibration: 2.8 mm amplitude. Immediate inspection required.',
        turbine: 'WT-005',
        time: '1 hour ago',
      },
      {
        id: 5,
        title: 'POWER_FACTOR_LOW',
        priority: 'Minor',
        description: 'Power factor: 0.93 (Target: >0.95)',
        turbine: 'WT-002',
        time: '4 hours ago',
      },
      {
        id: 6,
        title: 'GEARBOX_OIL_PRESSURE_LOW',
        priority: 'Warning',
        description: 'Oil pressure: 1.8 bar (Warning: <2.0 bar)',
        turbine: 'WT-006',
        time: '6 hours ago',
      },
      {
        id: 7,
        title: 'NACELLE_HUMIDITY_HIGH',
        priority: 'Minor',
        description: 'Relative humidity: 87% (Warning: >85%)',
        turbine: 'WT-001',
        time: '12 hours ago',
      },
      {
        id: 8,
        title: 'WIND_SPEED_HIGH',
        priority: 'Warning',
        description: 'Wind speed: 22 m/s (Warning: >20 m/s, Shutdown: >25 m/s)',
        turbine: 'All turbines',
        time: '3 hours ago',
      },
    ])

    const maintenanceLogs = ref([
      {
        date: '2025-09-20',
        technician: 'Mike Johnson',
        actions:
          '• Gearbox oil change completed<br>• Vibration analysis performed<br>• Brake pads inspected - 70% life remaining',
      },
      {
        date: '2025-09-15',
        technician: 'Sarah Williams',
        actions:
          '• Blade pitch motor calibration<br>• Yaw bearing lubrication<br>• Control system firmware update v2.3.1',
      },
      {
        date: '2025-08-28',
        technician: 'David Chen',
        actions:
          '• Annual inspection completed<br>• Tower bolts torque check<br>• Lightning protection system tested',
      },
    ])

    const predictiveInsights = ref([
      {
        turbine: 'WT-001',
        component: 'Gearbox',
        rul: 180,
        recommendation: 'Schedule maintenance within 1 month',
        color: '#f87171',
      },
      {
        turbine: 'WT-003',
        component: 'Generator',
        rul: 90,
        recommendation: 'Monitor temperature daily',
        color: '#fbbf24',
      },
      {
        turbine: 'WT-005',
        component: 'Blades',
        rul: 45,
        recommendation: 'Inspect for cracks immediately',
        color: '#ef4444',
      },
      {
        turbine: 'WT-004',
        component: 'Main Bearing',
        rul: 120,
        recommendation: 'Monitor vibration trends weekly',
        color: '#f59e0b',
      },
    ])

    const maintenanceTabs = ref([
      'Recent',
      'Scheduled',
      'Component History',
      'Critical',
      'Completed',
    ])
    const alarmFilters = ref(['All', 'Critical', 'Major', 'Minor', 'Warning'])

    // Initialize selected turbine
    selectedTurbine.value = turbines.value[0]

    // Computed properties
    const filteredTurbines = computed(() => {
      if (!searchQuery.value) return turbines.value
      return turbines.value.filter(
        t => t.id.includes(searchQuery.value) || t.location.includes(searchQuery.value)
      )
    })

    const filteredAlarms = computed(() => {
      if (activeAlarmFilter.value === 'All') return alarms.value
      return alarms.value.filter(a => a.priority === activeAlarmFilter.value)
    })

    const selectedTurbineMetrics = computed(() => {
      const metrics =
        turbineMetrics.value[selectedTurbine.value.id] || turbineMetrics.value['WT-001']
      return [
        { label: 'Power Output', value: metrics.power, trend: '↑ 5% from yesterday' },
        { label: 'Wind Speed', value: metrics.wind, trend: 'Optimal range' },
        { label: 'Availability', value: metrics.availability, trend: 'Above target' },
        { label: 'Rotor Speed', value: metrics.rotor, trend: 'Normal operation' },
      ]
    })

    // Methods
    const selectTurbine = turbine => {
      selectedTurbine.value = turbine
    }

    const getStatusColor = status => {
      switch (status) {
        case 'running':
          return '#10b981'
        case 'maintenance':
          return '#f59e0b'
        case 'stopped':
          return '#ef4444'
        case 'warning':
          return '#facc15'
        default:
          return '#6b7280'
      }
    }

    const showAlarmDetails = alarm => {
      popupAlarm.value = alarm
      showAlarmPopup.value = true
    }

    const priorityColor = priority => {
      switch (priority) {
        case 'Critical':
          return 'bg-red-500 text-white px-2 py-0.5 rounded'
        case 'Major':
          return 'bg-orange-500 text-white px-2 py-0.5 rounded'
        case 'Warning':
          return 'bg-yellow-400 text-black px-2 py-0.5 rounded'
        case 'Minor':
          return 'bg-gray-200 text-gray-800 px-2 py-0.5 rounded'
        default:
          return 'bg-gray-300 text-gray-800 px-2 py-0.5 rounded'
      }
    }

    const acknowledgeAlarm = alarm => {
      alert('Acknowledged alarm:', alarm)
      showAlarmPopup.value = false
    }

    // Simulate real-time updates
    let updateInterval
    onMounted(() => {
      updateInterval = setInterval(() => {
        const turbine = turbines.value[Math.floor(Math.random() * turbines.value.length)]
        turbine.statusText = turbine.statusText === 'Running' ? 'Running' : 'Updated'
      }, 10000)
    })
    onUnmounted(() => clearInterval(updateInterval))

    return {
      activeTab,
      activeMaintenanceTab,
      activeAlarmFilter,
      searchQuery,
      selectedTurbine,
      showMaintenanceForm,
      showAlarmPopup,
      popupAlarm,
      currentUser,
      navItems,
      turbines,
      turbineMetrics,
      componentHealth,
      alarms,
      maintenanceLogs,
      predictiveInsights,
      maintenanceTabs,
      alarmFilters,
      filteredTurbines,
      filteredAlarms,
      selectedTurbineMetrics,
      selectTurbine,
      getStatusColor,
      showAlarmDetails,
      priorityColor,
      acknowledgeAlarm,
    }
  },
}
</script>

<style>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.slide-fade-enter-active,
.slide-fade-leave-active {
  transition: all 0.3s ease;
}
.slide-fade-enter-from {
  opacity: 0;
  transform: translateY(-10px);
}
.slide-fade-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

.animate-spin-slow {
  animation: spin 10s linear infinite;
}
@keyframes spin {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}
</style>
