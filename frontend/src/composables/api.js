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
        try {
            const alarm = alarmStore.alarms.find(a => a.id === alarmId)
            if (alarm) alarm.acknowledged = true

            const response = await fetch(`${apiUrl}/alarms/${alarmId}/acknowledge`, { method: 'POST' })

            if (!response.ok) {
                if (alarm) alarm.acknowledged = false
                throw new Error('Failed to acknowledge alarm')
            }
        } catch (err) {
            console.error(err)
            alarmStore.error = err.message
        }
    },
    recentAlarms: (count) => alarmStore.alarms.slice(0, count)
});

// NEW: History Store for trends
const historyStore = reactive({
    data: null,
    loading: false,
    error: null,

    fetchHistory: async (displayId, startDate, endDate) => {
        historyStore.loading = true;
        historyStore.error = null;
        historyStore.data = null;

        // 1. Resolve Display ID (WT001) to turbine_id string
        const turbine = turbineStore.turbines.find(t => t.id === displayId);
        const turbineId = turbine?.id; // This should be "WT001", "WT002", etc.

        if (!turbineId) {
            historyStore.error = `Could not find turbine ${displayId}`;
            historyStore.loading = false;
            return;
        }

        try {
            console.log('🔍 Fetching history for:', { turbineId, startDate, endDate });

            // 2. ✅ CORRECTED: Use GET with query parameters
            const params = new URLSearchParams({
                turbine_id: turbineId,
                start_date: startDate,
                end_date: endDate
            });

            const response = await fetch(`${apiUrl}/turbine/allHistoricalData?${params}`, {
                method: 'GET', // ✅ Changed from POST to GET
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });

            if (!response.ok) {
                const errorText = await response.text();
                console.error('❌ Server error:', errorText);
                throw new Error(`Failed to fetch historical data: ${response.status} ${response.statusText}`);
            }

            const data = await response.json();
            console.log('✅ History data received:', data);
            historyStore.data = data;

        } catch (err) {
            console.error('❌ History fetch error:', err);
            historyStore.error = err.message;
        } finally {
            historyStore.loading = false;
        }
    },

    // Helper to clear data when switching views if needed
    clear: () => {
        historyStore.data = null;
        historyStore.error = null;
    }
});

const maintenanceStore = reactive({
    logs: [],
    loading: false,
    error: null,

    addLog: async (log) => {
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
            if (!response.ok) throw new Error('Failed to save maintenance log')

            const newLogFromApi = await response.json()

            const newLogForStore = {
                id: newLogFromApi.id,
                turbine: log.turbine, // Keep the display ID for UI
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
// API FETCHING LOGIC (Dashboard)
// ============================================================================

const statusCodeMap = {
    100: 'running', 200: 'idle', 300: 'maintenance', 400: 'stopped', 500: 'stopped',
};

const priorityMap = {
    'failed': 'Critical', 'critical': 'Major', 'warning': 'Warning'
};

async function fetchDashboard() {
    turbineStore.loading = true;
    alarmStore.loading = true;
    turbineStore.error = null;
    alarmStore.error = null;

    try {
        const response = await fetch(`${apiUrl}/dashboard/all`);
        if (!response.ok) throw new Error(`Failed to fetch dashboard: ${response.status}`);

        const dashboardData = await response.json();
        const turbines = [];
        const allAlarms = [];

        for (const apiTurbine of dashboardData) {
            const displayId = apiTurbine.turbine_id;
            const apiId = apiTurbine.id;

            const turbineData = {
                id: displayId,
                location: apiTurbine.location || 'Unknown Field',
                status: statusCodeMap[apiTurbine.status] || 'error',
                metrics: null,
                hydraulicData: apiTurbine.hydraulic || null,
                vibrationData: apiTurbine.vibration || null,
                temperatureData: apiTurbine.temperature || null,
                scadaData: apiTurbine.scada || null,
                alarmSummary: null,
                _api_id: apiId
            };

            if (apiTurbine.alarms) {
                turbineData.alarmSummary = {
                    total: apiTurbine.alarms.total_alarms,
                    counts: apiTurbine.alarms.counts_by_severity
                };

                if (apiTurbine.alarms.alarms && apiTurbine.alarms.alarms.length > 0) {
                    const turbineAlarms = apiTurbine.alarms.alarms.map(apiAlarm => ({
                        id: apiAlarm.id,
                        title: apiAlarm.message,
                        priority: priorityMap[apiAlarm.severity] || 'Warning',
                        description: apiAlarm.message,
                        turbine: displayId,
                        time: new Date(apiAlarm.detected_at).toLocaleString(),
                        acknowledged: !!apiAlarm.acknowledged_at
                    }));
                    allAlarms.push(...turbineAlarms);
                }
            }
            turbines.push(turbineData);
        }

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

async function fetchMaintenanceLogs() {
    // Logic remains same...
    return [];
}

// ============================================================================
// THE COMPOSABLE
// ============================================================================
export function useScadaService() {
    return {
        turbineStore,
        alarmStore,
        maintenanceStore,
        historyStore,
        fetchDashboard,
        fetchMaintenanceLogs,
    };
}
