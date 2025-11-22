<template>
  <div class="turbine-monitor">
    <div class="container">
      <header>
        <div class="logo">
          <svg viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="3" fill="#667eea" />
            <path
              d="M50 10 L50 40 L35 25 M50 40 L65 25"
              stroke="#667eea"
              stroke-width="3"
              fill="none"
            />
            <path
              d="M50 10 L50 40 L35 25 M50 40 L65 25"
              stroke="#667eea"
              stroke-width="3"
              fill="none"
              transform="rotate(120 50 50)"
            />
            <path
              d="M50 10 L50 40 L35 25 M50 40 L65 25"
              stroke="#667eea"
              stroke-width="3"
              fill="none"
              transform="rotate(240 50 50)"
            />
          </svg>
          <h1>Wind Turbine Monitoring System</h1>
        </div>
        <div class="user-info">
          <span>Welcome, {{ currentUser.name }}</span>
          <span class="user-role">{{ currentUser.role }}</span>
          <button class="btn" @click="showMaintenanceForm = true">Log Maintenance</button>
        </div>
      </header>

      <div class="main-grid">
        <aside class="sidebar">
          <input
            v-model="searchQuery"
            type="text"
            class="search-bar"
            placeholder="Search turbines..."
          />
          <nav>
            <div
              v-for="item in navItems"
              :key="item.id"
              :class="['nav-item', { active: activeTab === item.id }]"
              @click="activeTab = item.id"
            >
              <span>{{ item.icon }}</span> {{ item.label }}
            </div>
          </nav>
        </aside>

        <main class="main-content">
          <transition name="fade" mode="out-in">
            <!-- Overview Tab -->
            <div v-if="activeTab === 'overview'" key="overview">
              <div class="turbine-overview">
                <h2>Turbine Fleet Status</h2>
                <div class="turbine-grid">
                  <div
                    v-for="turbine in filteredTurbines"
                    :key="turbine.id"
                    :class="['turbine-card', { selected: selectedTurbine.id === turbine.id }]"
                    @click="selectTurbine(turbine)"
                  >
                    <div class="turbine-icon">
                      <svg viewBox="0 0 100 100">
                        <circle cx="50" cy="50" r="2" fill="#333" />
                        <path
                          d="M50 20 L50 40 L40 30 M50 40 L60 30"
                          stroke="#333"
                          stroke-width="2"
                          fill="none"
                        />
                        <path
                          d="M50 20 L50 40 L40 30 M50 40 L60 30"
                          stroke="#333"
                          stroke-width="2"
                          fill="none"
                          transform="rotate(120 50 50)"
                        />
                        <path
                          d="M50 20 L50 40 L40 30 M50 40 L60 30"
                          stroke="#333"
                          stroke-width="2"
                          fill="none"
                          transform="rotate(240 50 50)"
                        />
                        <line x1="50" y1="50" x2="50" y2="80" stroke="#333" stroke-width="2" />
                      </svg>
                      <div :class="['status-indicator', 'status-' + turbine.status]"></div>
                    </div>
                    <div>
                      <strong>{{ turbine.id }}</strong>
                    </div>
                    <div class="location-text">{{ turbine.location }}</div>
                    <div :style="{ fontSize: '14px', color: getStatusColor(turbine.status) }">
                      {{ turbine.statusText }}
                    </div>
                  </div>
                </div>
              </div>

              <div class="metrics-panel">
                <h3>{{ selectedTurbine.id }} Performance Metrics</h3>
                <div class="metric-cards">
                  <div
                    v-for="metric in selectedTurbineMetrics"
                    :key="metric.label"
                    class="metric-card"
                  >
                    <div class="metric-label">{{ metric.label }}</div>
                    <div class="metric-value">{{ metric.value }}</div>
                    <div class="metric-trend">{{ metric.trend }}</div>
                  </div>
                </div>

                <h4 class="component-health-title">Component Health</h4>
                <div class="component-health">
                  <div
                    v-for="component in componentHealth"
                    :key="component.name"
                    class="health-item"
                  >
                    <span>{{ component.name }}</span>
                    <div class="health-bar">
                      <div class="health-fill" :style="{ width: component.health + '%' }"></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Maintenance Tab -->
            <div v-else-if="activeTab === 'maintenance'" key="maintenance">
              <div class="metrics-panel">
                <h3>Maintenance History - {{ selectedTurbine.id }}</h3>
                <div class="tabs">
                  <button
                    v-for="tab in maintenanceTabs"
                    :key="tab"
                    :class="['tab', { active: activeMaintenanceTab === tab }]"
                    @click="activeMaintenanceTab = tab"
                  >
                    {{ tab }}
                  </button>
                </div>
                <div v-for="log in maintenanceLogs" :key="log.date" class="maintenance-log">
                  <div class="log-header">
                    <span class="log-date">{{ log.date }}</span>
                    <span class="log-tech">Tech: {{ log.technician }}</span>
                  </div>
                  <div class="log-actions" v-html="log.actions"></div>
                </div>
              </div>
            </div>

            <!-- Analytics Tab -->
            <div v-else-if="activeTab === 'analytics'" key="analytics">
              <div class="metrics-panel">
                <h3>Predictive Maintenance Insights</h3>
                <div class="insights-grid">
                  <div
                    v-for="insight in predictiveInsights"
                    :key="insight.component"
                    class="insight-card"
                    :style="{ borderLeftColor: insight.color }"
                  >
                    <h4>{{ insight.component }} - {{ insight.turbine }}</h4>
                    <p class="rul-text">
                      Remaining Useful Life: <strong>{{ insight.rul }} days</strong>
                    </p>
                    <p class="recommendation-text">{{ insight.recommendation }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Reports Tab -->
            <div v-else-if="activeTab === 'reports'" key="reports">
              <div class="metrics-panel">
                <h3>System Reports</h3>
                <p class="info-text">Generate and download system reports for analysis.</p>
                <div class="report-buttons">
                  <button class="btn">Generate Monthly Report</button>
                  <button class="btn btn-outline">Export Data to CSV</button>
                </div>
              </div>
            </div>

            <!-- Settings Tab -->
            <div v-else-if="activeTab === 'settings'" key="settings">
              <div class="metrics-panel">
                <h3>System Settings</h3>
                <p class="info-text">Configure system parameters and alarm thresholds.</p>
              </div>
            </div>
          </transition>
        </main>

        <aside class="alarms-panel">
          <h3>Active Alarms ({{ filteredAlarms.length }})</h3>
          <div class="alarm-filters">
            <button
              v-for="filter in alarmFilters"
              :key="filter"
              :class="['btn', 'btn-filter', { 'btn-outline': activeAlarmFilter !== filter }]"
              @click="activeAlarmFilter = filter"
            >
              {{ filter }}
            </button>
          </div>

          <transition-group name="slide-fade">
            <div
              v-for="alarm in filteredAlarms"
              :key="alarm.id"
              :class="['alarm-item', 'alarm-' + alarm.priority.toLowerCase()]"
              @click="showAlarmDetails(alarm)"
            >
              <div class="alarm-header">
                <span class="alarm-title">{{ alarm.title }}</span>
                <span :class="['alarm-priority', 'priority-' + alarm.priority.toLowerCase()]">
                  {{ alarm.priority }}
                </span>
              </div>
              <div class="alarm-description">{{ alarm.description }}</div>
              <div class="alarm-meta">
                <span>{{ alarm.turbine }}</span>
                <span>{{ alarm.time }}</span>
              </div>
            </div>
          </transition-group>
        </aside>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted, onUnmounted } from 'vue'

export default {
  name: 'TurbineMonitoringPrototype',
  setup() {
    // Reactive data
    const activeTab = ref('overview')
    const activeMaintenanceTab = ref('Recent')
    const activeAlarmFilter = ref('All')
    const searchQuery = ref('')
    const selectedTurbine = ref({})
    const showMaintenanceForm = ref(false)

    const currentUser = ref({
      name: 'John Smith',
      role: 'Supervisor',
    })

    const navItems = ref([
      { id: 'overview', icon: '📊', label: 'Overview' },
      { id: 'maintenance', icon: '🔧', label: 'Maintenance' },
      { id: 'alarms', icon: '⚠️', label: 'Active Alarms' },
      { id: 'analytics', icon: '📈', label: 'Analytics' },
      { id: 'reports', icon: '📄', label: 'Reports' },
      { id: 'settings', icon: '⚙️', label: 'Settings' },
    ])

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

    const componentHealth = ref([
      { name: 'Gearbox', health: 85 },
      { name: 'Generator', health: 92 },
      { name: 'Blades', health: 78 },
      { name: 'Main Bearing', health: 88 },
    ])

    const predictiveInsights = ref([
      {
        component: 'Gearbox',
        turbine: 'WT-004',
        rul: 45,
        color: '#10b981',
        recommendation: 'Schedule oil analysis within 2 weeks',
      },
      {
        component: 'Main Bearing',
        turbine: 'WT-003',
        rul: 120,
        color: '#f59e0b',
        recommendation: 'Monitor vibration trends weekly',
      },
      {
        component: 'Blade Pitch System',
        turbine: 'WT-002',
        rul: 200,
        color: '#3b82f6',
        recommendation: 'Normal degradation pattern observed',
      },
    ])

    const maintenanceTabs = ref(['Recent', 'Scheduled', 'Component History'])
    const alarmFilters = ref(['All', 'Critical', 'Major', 'Minor', 'Warning'])

    // Initialize with first turbine selected
    selectedTurbine.value = turbines.value[0]

    // Computed properties
    const filteredTurbines = computed(() => {
      if (!searchQuery.value) return turbines.value

      return turbines.value.filter(
        turbine =>
          turbine.id.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
          turbine.location.toLowerCase().includes(searchQuery.value.toLowerCase())
      )
    })

    const filteredAlarms = computed(() => {
      if (activeAlarmFilter.value === 'All') return alarms.value

      return alarms.value.filter(alarm => alarm.priority === activeAlarmFilter.value)
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
      const colors = {
        running: '#10b981',
        maintenance: '#f59e0b',
        stopped: '#ef4444',
        warning: '#8b5cf6',
      }
      return colors[status] || '#666'
    }

    const showAlarmDetails = alarm => {
      alert(
        `Alarm Details:\n\n${alarm.title}\nPriority: ${alarm.priority}\nTurbine: ${alarm.turbine}\n\n${alarm.description}\n\nRecommended Action: Schedule immediate inspection`
      )
    }

    // Simulate real-time updates
    let updateInterval
    onMounted(() => {
      updateInterval = setInterval(() => {
        // Update random turbine metrics
        const turbineIds = Object.keys(turbineMetrics.value)
        const randomTurbine = turbineIds[Math.floor(Math.random() * turbineIds.length)]

        // Update wind speed
        const wind = 10 + Math.floor(Math.random() * 8)
        turbineMetrics.value[randomTurbine].wind = wind + ' m/s'

        // Update power based on wind
        if (randomTurbine !== 'WT-003' && randomTurbine !== 'WT-005') {
          const power = (1.5 + Math.random() * 1.5).toFixed(1)
          turbineMetrics.value[randomTurbine].power = power + ' MW'
        }

        // Randomly update component health
        componentHealth.value.forEach(component => {
          const change = Math.random() > 0.5 ? 1 : -1
          const newHealth = component.health + change
          if (newHealth >= 0 && newHealth <= 100) {
            component.health = newHealth
          }
        })
      }, 5000)
    })

    onUnmounted(() => {
      if (updateInterval) clearInterval(updateInterval)
    })

    return {
      activeTab,
      activeMaintenanceTab,
      activeAlarmFilter,
      searchQuery,
      selectedTurbine,
      showMaintenanceForm,
      currentUser,
      navItems,
      turbines,
      alarms,
      maintenanceLogs,
      componentHealth,
      predictiveInsights,
      maintenanceTabs,
      alarmFilters,
      filteredTurbines,
      filteredAlarms,
      selectedTurbineMetrics,
      selectTurbine,
      getStatusColor,
      showAlarmDetails,
    }
  },
}
</script>

<style scoped>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

.turbine-monitor {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  min-height: 100vh;
  color: #333;
}

.container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 20px;
}

header {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border-radius: 15px;
  padding: 20px 30px;
  margin-bottom: 20px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.logo {
  display: flex;
  align-items: center;
  gap: 15px;
}

.logo svg {
  width: 40px;
  height: 40px;
  animation: rotate 10s linear infinite;
}

@keyframes rotate {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

h1 {
  font-size: 24px;
  font-weight: 600;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 15px;
}

.user-role {
  padding: 6px 12px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border-radius: 20px;
  font-size: 14px;
  font-weight: 500;
}

.main-grid {
  display: grid;
  grid-template-columns: 250px 1fr 320px;
  gap: 20px;
  margin-bottom: 20px;
}

.sidebar {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border-radius: 15px;
  padding: 20px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px;
  margin: 5px 0;
  border-radius: 10px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.nav-item:hover {
  background: rgba(102, 126, 234, 0.1);
  transform: translateX(5px);
}

.nav-item.active {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.main-content {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.turbine-overview {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border-radius: 15px;
  padding: 25px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
}

.turbine-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 15px;
  margin-top: 20px;
}

.turbine-card {
  background: white;
  border-radius: 12px;
  padding: 15px;
  text-align: center;
  cursor: pointer;
  transition: all 0.3s ease;
  border: 2px solid transparent;
}

.turbine-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
  border-color: #667eea;
}

.turbine-card.selected {
  border-color: #764ba2;
  background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
}

.turbine-icon {
  width: 60px;
  height: 60px;
  margin: 0 auto 10px;
  position: relative;
}

.location-text {
  font-size: 12px;
  color: #666;
}

.status-indicator {
  position: absolute;
  top: 0;
  right: 0;
  width: 18px;
  height: 18px;
  border-radius: 50%;
  border: 3px solid white;
  animation: pulse 2s ease-in-out infinite;
}

.status-running {
  background: #10b981;
}
.status-maintenance {
  background: #f59e0b;
}
.status-stopped {
  background: #ef4444;
}
.status-warning {
  background: #8b5cf6;
}

@keyframes pulse {
  0% {
    box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4);
  }
  70% {
    box-shadow: 0 0 0 10px rgba(16, 185, 129, 0);
  }
  100% {
    box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
  }
}

.metrics-panel {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border-radius: 15px;
  padding: 25px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
}

.metric-cards {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 15px;
  margin-top: 20px;
}

.metric-card {
  background: white;
  padding: 20px;
  border-radius: 12px;
  transition: all 0.3s ease;
}

.metric-card:hover {
  transform: scale(1.02);
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.metric-value {
  font-size: 32px;
  font-weight: bold;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  margin: 10px 0;
}

.metric-label {
  color: #666;
  font-size: 14px;
  margin-bottom: 5px;
}

.metric-trend {
  font-size: 12px;
  color: #10b981;
}

.component-health-title {
  margin-top: 25px;
  margin-bottom: 0;
}

.component-health {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 10px;
  margin-top: 15px;
}

.health-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px;
  background: #f9fafb;
  border-radius: 8px;
}

.health-bar {
  width: 60px;
  height: 6px;
  background: #e5e7eb;
  border-radius: 3px;
  overflow: hidden;
}

.health-fill {
  height: 100%;
  background: linear-gradient(90deg, #10b981, #667eea);
  transition: width 0.5s ease;
}

.alarms-panel {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border-radius: 15px;
  padding: 20px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
  max-height: 85vh;
  overflow-y: auto;
}

.alarm-filters {
  display: flex;
  gap: 10px;
  margin: 15px 0;
}

.alarm-item {
  background: white;
  padding: 15px;
  border-radius: 10px;
  margin-bottom: 10px;
  border-left: 4px solid;
  transition: all 0.3s ease;
  cursor: pointer;
}

.alarm-item:hover {
  transform: translateX(5px);
  box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
}

.alarm-critical {
  border-left-color: #ef4444;
}
.alarm-major {
  border-left-color: #f59e0b;
}
.alarm-minor {
  border-left-color: #3b82f6;
}
.alarm-warning {
  border-left-color: #8b5cf6;
}

.alarm-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
}

.alarm-title {
  font-weight: 600;
  font-size: 14px;
}

.alarm-priority {
  font-size: 12px;
  padding: 2px 8px;
  border-radius: 12px;
  color: white;
}
.priority-critical {
  background: #ef4444;
}
.priority-major {
  background: #f59e0b;
}
.priority-minor {
  background: #3b82f6;
}
.priority-warning {
  background: #8b5cf6;
}

.alarm-description {
  font-size: 13px;
  color: #666;
  margin-bottom: 8px;
}

.alarm-meta {
  display: flex;
  gap: 15px;
  font-size: 12px;
  color: #999;
}

.tabs {
  display: flex;
  gap: 10px;
  margin-bottom: 20px;
  border-bottom: 2px solid #e5e7eb;
}

.tab {
  padding: 10px 20px;
  background: none;
  border: none;
  cursor: pointer;
  font-size: 14px;
  font-weight: 500;
  color: #666;
  transition: all 0.3s ease;
  position: relative;
}

.tab:hover {
  color: #667eea;
}

.tab.active {
  color: #667eea;
}

.tab.active::after {
  content: '';
  position: absolute;
  bottom: -2px;
  left: 0;
  right: 0;
  height: 2px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.maintenance-log {
  background: white;
  padding: 15px;
  border-radius: 10px;
  margin-bottom: 10px;
  transition: all 0.3s ease;
}

.maintenance-log:hover {
  box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
}

.log-header {
  display: flex;
  justify-content: space-between;
  margin-bottom: 10px;
}

.log-date {
  font-weight: 600;
  color: #667eea;
}

.log-tech {
  color: #666;
  font-size: 14px;
}

.log-actions {
  color: #333;
  font-size: 14px;
  line-height: 1.5;
}

.btn {
  padding: 10px 20px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.3s ease;
}

.btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
}

.btn-outline {
  background: white;
  color: #667eea;
  border: 1px solid #667eea;
}

.search-bar {
  width: 100%;
  padding: 12px 20px;
  border: 2px solid #e5e7eb;
  border-radius: 10px;
  font-size: 14px;
  margin-bottom: 20px;
  transition: all 0.3s ease;
}

.search-bar:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.component-health {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 10px;
  margin-top: 15px;
}

.health-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px;
  background: #f9fafb;
  border-radius: 8px;
}

.health-bar {
  width: 60px;
  height: 6px;
  background: #e5e7eb;
  border-radius: 3px;
  overflow: hidden;
}

.health-fill {
  height: 100%;
  background: linear-gradient(90deg, #10b981, #667eea);
  transition: width 0.5s ease;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.slide-fade-enter-active {
  transition: all 0.3s ease-out;
}

.slide-fade-leave-active {
  transition: all 0.3s cubic-bezier(1, 0.5, 0.8, 1);
}

.slide-fade-enter-from,
.slide-fade-leave-to {
  transform: translateX(10px);
  opacity: 0;
}

@media (max-width: 1200px) {
  .main-grid {
    grid-template-columns: 1fr;
  }
}
</style>
