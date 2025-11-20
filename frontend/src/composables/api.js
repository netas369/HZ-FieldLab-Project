import { reactive, computed } from 'vue'

const apiUrl = import.meta.env.VITE_API_BASE_URL;

// ============================================================================
// STORES
// ============================================================================

const turbineStore = reactive({
  turbines: [],
  loading: false,
  error: null,
  selectTurbine: (id) => console.log('Selected turbine:', id)
});

const alarmStore = reactive({
  alarms: [],
  loading: false,
  error: null,
  activeAlarms: computed(() => alarmStore.alarms.filter(a => !a.acknowledged)),
  criticalCount: computed(() => alarmStore.alarms.filter(a => a.priority === 'Critical' && !a.acknowledged).length),
  
  acknowledgeAlarm: async (alarmId) => {
    // !! REQUIRES API ROUTE !!
    // Assuming a route like 'POST /alarms/{alarmId}/acknowledge'
    try {
      const alarm = alarmStore.alarms.find(a => a.id === alarmId)
      if (alarm) alarm.acknowledged = true
      
      const response = await fetch(`${apiUrl}/alarms/${alarmId}/acknowledge`, {
        method: 'POST', 
      })
      
      if (!response.ok) {
        if (alarm) alarm.acknowledged = false
        throw new Error('Failed to acknowledge alarm (route may be incorrect)')
      }
      console.log(`Alarm ${alarmId} acknowledged via API`)
    } catch (err) {
      console.error(err)
      alarmStore.error = err.message
    }
  },
  recentAlarms: (count) => alarmStore.alarms.slice(0, count)
});

const maintenanceStore = reactive({
  logs: [],
  loading: false,
  error: null,
  
  addLog: async (log) => {
    // !! REQUIRES API ROUTE !!
    // Assuming a route like 'POST /maintenance'
    
    const turbine = turbineStore.turbines.find(t => t.id === log.turbine)
    const apiTurbineId = turbine ? turbine._api_id : null
    
    if (!apiTurbineId) {
      console.error(`Could not find API ID for turbine ${log.turbine}`)
      return
    }

    const apiPayload = {
      turbine_id: apiTurbineId,
      type: log.type.toLowerCase(),
      notes: log.description,
      user: log.technician,
      log_date: new Date().toISOString(),
      status: 'completed'
    }

    try {
      const response = await fetch(`${apiUrl}/maintenance`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(apiPayload)
      })
      if (!response.ok) throw new Error('Failed to save maintenance log (route may be incorrect)')
      
      const newLogFromApi = await response.json()
      
      const newLogForStore = {
        id: newLogFromApi.id,
        turbine: log.turbine,
        type: log.type,
        description: newLogFromApi.notes,
        date: new Date(newLogFromApi.log_date).toISOString(),
        status: newLogFromApi.status,
        technician: newLogFromApi.user
      }
      maintenanceStore.logs.unshift(newLogForStore)
    } catch (err) {
      console.error('Failed to add maintenance log:', err)
      maintenanceStore.error = err.message
    }
  }
});

// ============================================================================
// API FETCHING LOGIC
// ============================================================================

/**
 * Status code mapping
 */
const statusCodeMap = {
  100: 'running',     // Normal Operation
  200: 'idle',        // Idle Conditions
  300: 'maintenance', // Maintenance Mode
  400: 'stopped',     // Fault Conditions
  500: 'stopped',     // Grid Faults
};

/**
 * Priority mapping for alarms
 */
const priorityMap = {
  'failed': 'Critical',
  'critical': 'Major',
  'warning': 'Warning'
};

/**
 * Fetches all dashboard data using the consolidated endpoint.
 * This replaces fetchTurbines() and fetchAlarms() with a single call.
 */
async function fetchDashboard() {
  turbineStore.loading = true;
  alarmStore.loading = true;
  turbineStore.error = null;
  alarmStore.error = null;
  
  try {
    const response = await fetch(`${apiUrl}/dashboard/all`);
    if (!response.ok) throw new Error(`Failed to fetch dashboard: ${response.status}`);
    
    const dashboardData = await response.json();
    
    // Process each turbine from the dashboard response
    const turbines = [];
    const allAlarms = [];
    
    for (const apiTurbine of dashboardData) {
      const displayId = apiTurbine.turbine_id;
      const apiId = apiTurbine.id;
      
      // --- Build Turbine Object ---
      const turbineData = {
        id: displayId,
        location: apiTurbine.location || 'Unknown Field',
        status: statusCodeMap[apiTurbine.status] || 'error',
        metrics: null,
        hydraulicData: null,
        vibrationData: null,
        temperatureData: null,
        alarmSummary: null,
        _api_id: apiId
      };
      
      // --- Populate SCADA Data ---
      if (apiTurbine.scada) {
        turbineData.scadaData = apiTurbine.scada;
      }
      
      // --- Populate Hydraulic Data ---
      if (apiTurbine.hydraulic) {
        turbineData.hydraulicData = apiTurbine.hydraulic;
      }
      
      // --- Populate Vibration Data ---
      if (apiTurbine.vibration) {
        turbineData.vibrationData = apiTurbine.vibration;
      }
      
      // --- Populate Temperature Data ---
      if (apiTurbine.temperature) {
        turbineData.temperatureData = apiTurbine.temperature;
      }
      
      // --- Populate Alarm Summary ---
      if (apiTurbine.alarms) {
        turbineData.alarmSummary = {
          total: apiTurbine.alarms.total_alarms,
          counts: apiTurbine.alarms.counts_by_severity
        };
        
        // --- Map Individual Alarms ---
        if (apiTurbine.alarms.alarms && apiTurbine.alarms.alarms.length > 0) {
          const turbineAlarms = apiTurbine.alarms.alarms.map(apiAlarm => {
            // Create detailed description
            let description = apiAlarm.message;
            if (apiAlarm.data && apiAlarm.data.description) {
              description += ` - ${apiAlarm.data.description}`;
            }
            if (apiAlarm.data && apiAlarm.data.value) {
              description += ` (Current Value: ${parseFloat(apiAlarm.data.value).toFixed(2)})`;
            }

            return {
              id: apiAlarm.id,
              title: apiAlarm.message,
              priority: priorityMap[apiAlarm.severity] || 'Warning',
              description: description,
              turbine: displayId,
              time: new Date(apiAlarm.detected_at).toLocaleString(),
              acknowledged: !!apiAlarm.acknowledged_at
            };
          });
          
          allAlarms.push(...turbineAlarms);
        }
      }
      
      turbines.push(turbineData);
    }
    
    // Update stores
    turbineStore.turbines = turbines;
    alarmStore.alarms = allAlarms;
    
  } catch (err) {
    turbineStore.error = err.message;
    alarmStore.error = err.message;
    console.error('Dashboard fetch error:', err);
  } finally {
    turbineStore.loading = false;
    alarmStore.loading = false;
  }
}

/**
 * Fetches maintenance logs.
 */
async function fetchMaintenanceLogs() {
    return [];
  // !! REQUIRES API ROUTE !!
  // Assuming a route like 'GET /maintenance'
  maintenanceStore.loading = true;
  maintenanceStore.error = null;
  
  try {
    const response = await fetch(`${apiUrl}/maintenance`);
    if (!response.ok) throw new Error('Failed to fetch maintenance logs (route may be incorrect)');
    
    const apiLogs = await response.json();

    const getTurbineDisplayId = (apiTurbineId) => {
      const turbine = turbineStore.turbines.find(t => t._api_id === apiTurbineId);
      return turbine ? turbine.id : 'Unknown';
    };

    maintenanceStore.logs = apiLogs.map(apiLog => ({
      id: apiLog.id,
      turbine: getTurbineDisplayId(apiLog.turbine_id),
      type: apiLog.type.charAt(0).toUpperCase() + apiLog.type.slice(1),
      description: apiLog.notes || apiLog.description,
      date: new Date(apiLog.log_date).toISOString(),
      status: apiLog.status,
      technician: apiLog.user || 'N/A'
    })).sort((a, b) => new Date(b.date) - new Date(a.date));

  } catch (err) {
    maintenanceStore.error = err.message;
    console.error(err);
  } finally {
    maintenanceStore.loading = false;
  }
}

// ============================================================================
// THE COMPOSABLE
// ============================================================================
export function useScadaService() {
  return {
    turbineStore,
    alarmStore,
    maintenanceStore,
    
    // Updated API - single dashboard fetch replaces fetchTurbines + fetchAlarms
    fetchDashboard,
    fetchMaintenanceLogs,
    
    // Legacy function names for backward compatibility (optional)
    fetchTurbines: fetchDashboard,
    fetchAlarms: () => Promise.resolve() // No-op since alarms are now fetched with dashboard
  };
}