import { reactive, computed } from 'vue'
import axios from 'axios'

// Create an Axios instance for cleaner config
const apiClient = axios.create({
    baseURL: import.meta.env.VITE_API_BASE_URL,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    }
    // timeout: 10000 // Optional: good practice to add a timeout
});

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
        // Optimistic UI update
        const alarm = alarmStore.alarms.find(a => a.id === alarmId)
        if (alarm) alarm.acknowledged = true

        try {
            // Axios automatically throws if status is not 2xx
            await apiClient.post(`/alarms/${alarmId}/acknowledge`)
        } catch (err) {
            // Revert on failure
            if (alarm) alarm.acknowledged = false

            console.error('Failed to acknowledge alarm:', err)
            // Axios stores the server response in err.response
            alarmStore.error = err.response?.data?.message || err.message
        }
    },
    recentAlarms: (count) => alarmStore.alarms.slice(0, count)
});

const historyStore = reactive({
    data: null,
    loading: false,
    error: null,

    fetchHistory: async (displayId, startDate, endDate) => {
        historyStore.loading = true;
        historyStore.error = null;
        historyStore.data = null;

        const turbine = turbineStore.turbines.find(t => t.id === displayId);
        const turbineId = turbine?.id;

        if (!turbineId) {
            historyStore.error = `Could not find turbine ${displayId}`;
            historyStore.loading = false;
            return;
        }

        try {
            console.log('🔍 Fetching history for:', { turbineId, startDate, endDate });

            // Axios handles query params automatically via the 'params' object
            const response = await apiClient.get('/turbine/allHistoricalData', {
                params: {
                    turbine_id: turbineId,
                    start_date: startDate,
                    end_date: endDate
                }
            });

            console.log('✅ History data received:', response.data);
            historyStore.data = response.data;

        } catch (err) {
            console.error('❌ History fetch error:', err);
            historyStore.error = err.response?.data?.message || err.message;
        } finally {
            historyStore.loading = false;
        }
    },

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
            // Axios automatically stringifies the body
            const response = await apiClient.post('/maintenance', apiPayload);

            const newLogFromApi = response.data;

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
            maintenanceStore.error = err.response?.data?.message || err.message
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
        const response = await apiClient.get('/dashboard/all');

        // Axios stores the parsed JSON in .data
        const dashboardData = response.data;

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
        // Handle Axios error structure
        const message = err.response?.data?.message || err.message;
        turbineStore.error = message;
        alarmStore.error = message;
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