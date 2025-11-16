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
      
      const response = await fetch(`${apiUrl}/alarms/${alarmId}/acknowledge`, { // <-- Needs correct route
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
      const response = await fetch(`${apiUrl}/maintenance`, { // <-- Needs correct route
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
 * Fetches the main list of turbines and their details.
 */
async function fetchTurbines() {
  turbineStore.loading = true
  turbineStore.error = null
  try {
    const response = await fetch(`${apiUrl}/turbines`)
    if (!response.ok) throw new Error(`Failed to fetch turbines: ${response.status}`)
    const apiTurbineList = await response.json()

    // Now fetch details for all turbines in parallel
    const detailPromises = apiTurbineList.map(apiTurbine => fetchTurbineDetails(apiTurbine))
    const populatedTurbines = await Promise.all(detailPromises)
    
    turbineStore.turbines = [...populatedTurbines]

  } catch (err) {
    turbineStore.error = err.message
    console.error(err)
  } finally {
    turbineStore.loading = false
  }
}

/**
 * Helper function to fetch all details for a *single* turbine
 */
async function fetchTurbineDetails(apiTurbine) {
  const apiId = apiTurbine.id
  const displayId = apiTurbine.turbine_id

  try {
    // ... fetch all data in parallel (unchanged) ...
    const [scadaRes, hydraulicRes, vibrationRes, temperatureRes] = await Promise.all([
      fetch(`${apiUrl}/turbine/${apiId}/latestScadaData`),
      fetch(`${apiUrl}/turbine/${apiId}/latestHydraulicReadings`),
      fetch(`${apiUrl}/turbine/${apiId}/vibrations`),
      fetch(`${apiUrl}/turbine/${apiId}/latestTemperatures`)
    ]);

    // --- Base Turbine Object (unchanged) ---
    const turbineData = {
      id: displayId,
      location: apiTurbine.location || 'Unknown Field',
      status: 'unknown',
      metrics: {
        power_mw: null,
        wind_ms: null,
        rotor_rpm: null,
        generator_rpm: null,
        pitch_deg: null,
        ambient_temp_c: null,
      },
      hydraulicData: null,
      vibrationData: null,
      temperatureData: null,
      _api_id: apiId
    };

    // --- Populate Data from SCADA ---
    if (scadaRes.ok) {
      const scadaData = await scadaRes.json()
      
      // Map metrics (unchanged)
      turbineData.metrics.power_mw = scadaData.power_kw ? (scadaData.power_kw / 1000) : 0;
      turbineData.metrics.wind_ms = scadaData.wind_speed_ms ? parseFloat(scadaData.wind_speed_ms) : 0;
      turbineData.metrics.rotor_rpm = scadaData.rotor_speed_rpm ? parseFloat(scadaData.rotor_speed_rpm) : 0;
      turbineData.metrics.generator_rpm = scadaData.generator_speed_rpm ? parseFloat(scadaData.generator_speed_rpm) : 0;
      turbineData.metrics.pitch_deg = scadaData.pitch_angle_deg ? parseFloat(scadaData.pitch_angle_deg) : 0;
      turbineData.metrics.ambient_temp_c = scadaData.ambient_temp_c ? parseFloat(scadaData.ambient_temp_c) : 0;

      // --- *** UPDATED STATUS MAP *** ---
      // Map the numeric status_code to a human-friendly string
      const statusCodeMap = {
        100: 'running',     // Normal Operation
        200: 'idle',        // Idle Conditions
        300: 'maintenance', // Maintenance Mode
        400: 'stopped',     // Fault Conditions
        500: 'stopped',     // Grid Faults
      };

      turbineData.status = statusCodeMap[scadaData.status_code] || 'error';
      
    } else {
      turbineData.status = 'error';
    }
    
    // --- Populate other data (unchanged) ---
    if (hydraulicRes.ok) {
      turbineData.hydraulicData = await hydraulicRes.json();
    }
    if (vibrationRes.ok) {
      turbineData.vibrationData = await vibrationRes.json();
    }
    if (temperatureRes.ok) {
      turbineData.temperatureData = await temperatureRes.json();
    }

    return turbineData;

  } catch (err) {
    console.error(`Failed to get details for turbine ${displayId}:`, err)
    return { 
      id: displayId,
      location: 'Unknown',
      status: 'error',
      metrics: {},
      _api_id: apiId
    }
  }
}

/**
 * Fetches alarms for ALL turbines.
 * This function *must* run after fetchTurbines has populated the store.
 *
 * It will:
 * 1. Populate the global `alarmStore.alarms` list.
 * 2. Update each turbine in `turbineStore.turbines` with its alarm summary.
 */
async function fetchAlarms() {
  alarmStore.loading = true;
  alarmStore.error = null;
  try {
    if (turbineStore.turbines.length === 0) {
      console.warn("fetchAlarms called before turbines were loaded. Skipping.");
      return;
    }

    const priorityMap = {
      'failed': 'Critical',
      'critical': 'Major',
      'warning': 'Warning'
      // Add any other severities you have
    };

    const allAlarms = []; // A temporary array to hold all alarms

    // Create an array of fetch promises, one for each turbine
    const alarmPromises = turbineStore.turbines.map(async (turbine) => {
      try {
        const response = await fetch(`${apiUrl}/turbine/${turbine._api_id}/alarms`);
        if (!response.ok) {
          console.error(`Failed to fetch alarms for turbine ${turbine.id}`);
          return; // Skip this turbine on failure
        }
        
        const apiAlarmData = await response.json(); 
        
        // 1. Add alarm summary data directly to the turbine object
        turbine.alarmSummary = {
          total: apiAlarmData.total_alarms,
          counts: apiAlarmData.counts_by_severity 
        };

        // --- THIS IS THE UPDATED MAPPING ---

        // 2. Map alarms for this turbine
        const turbineAlarms = apiAlarmData.alarms.map(apiAlarm => {
          // Create a more detailed description
          let description = apiAlarm.message;
          if (apiAlarm.data && apiAlarm.data.description) {
            description += ` - ${apiAlarm.data.description}`;
          }
          if (apiAlarm.data && apiAlarm.data.value) {
            description += ` (Current Value: ${parseFloat(apiAlarm.data.value).toFixed(2)})`;
          }

          return {
            id: apiAlarm.id,
            title: apiAlarm.message, // Use the message as the title
            priority: priorityMap[apiAlarm.severity] || 'Warning',
            description: description, // Use the new detailed description
            turbine: turbine.id, // Use the display ID
            time: new Date(apiAlarm.detected_at).toLocaleString(), // Use detected_at
            acknowledged: !!apiAlarm.acknowledged_at // Convert null/date to boolean
          };
        });

        // Add this turbine's alarms to the master list
        allAlarms.push(...turbineAlarms);

      } catch (err) {
        console.error(`Error processing alarms for turbine ${turbine.id}:`, err);
      }
    });

    // Wait for all alarm fetches to complete
    await Promise.all(alarmPromises);
    
    // Now update the reactive alarmStore with the complete list
    alarmStore.alarms = allAlarms;

  } catch (err) {
    alarmStore.error = err.message;
    console.error(err);
  } finally {
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
  maintenanceStore.loading = true
  maintenanceStore.error = null
  try {
    const response = await fetch(`${apiUrl}/maintenance`) // <-- Needs correct route
    if (!response.ok) throw new Error('Failed to fetch maintenance logs (route may be incorrect)')
    
    const apiLogs = await response.json()

    const getTurbineDisplayId = (apiTurbineId) => {
      const turbine = turbineStore.turbines.find(t => t._api_id === apiTurbineId)
      return turbine ? turbine.id : 'Unknown'
    }

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
    maintenanceStore.error = err.message
    console.error(err)
  } finally {
    maintenanceStore.loading = false
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
    
    fetchTurbines,
    fetchAlarms,
    fetchMaintenanceLogs
  }
}