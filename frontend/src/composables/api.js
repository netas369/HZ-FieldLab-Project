import { reactive, computed } from 'vue'
import axios from 'axios'

const apiClient = axios.create({
    baseURL: import.meta.env.VITE_API_BASE_URL,
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' }
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
        const alarm = alarmStore.alarms.find(a => a.id === alarmId)
        if (alarm) alarm.acknowledged = true
        try {
            await apiClient.post(`/alarms/${alarmId}/acknowledge`)
        } catch (err) {
            if (alarm) alarm.acknowledged = false
            console.error('Failed to acknowledge alarm:', err)
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
            const response = await apiClient.get('/turbine/allHistoricalData', {
                params: { turbine_id: turbineId, start_date: startDate, end_date: endDate }
            });
            console.log('✅ History data received');
            historyStore.data = response.data;
        } catch (err) {
            console.error('❌ History fetch error:', err);
            historyStore.error = err.response?.data?.message || err.message;
        } finally {
            historyStore.loading = false;
        }
    },
    clear: () => { historyStore.data = null; historyStore.error = null; }
});

const maintenanceStore = reactive({
    logs: [],
    loading: false,
    error: null,
    addLog: async (log) => {
        const turbine = turbineStore.turbines.find(t => t.id === log.turbine)
        const apiTurbineId = turbine ? turbine._api_id : null

        if (!apiTurbineId) return;

        const apiPayload = {
            turbine_id: apiTurbineId,
            type: log.type.toLowerCase(),
            notes: log.description,
            user: log.technician,
            log_date: new Date().toISOString(),
            status: 'completed'
        }

        try {
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
// API FETCHING LOGIC
// ============================================================================

const statusCodeMap = { 100: 'running', 200: 'idle', 300: 'maintenance', 400: 'stopped', 500: 'stopped' };
const priorityMap = { 'failed': 'Critical', 'critical': 'Major', 'warning': 'Warning' };

async function fetchDashboard() {
    turbineStore.loading = true;
    alarmStore.loading = true;
    try {
        const response = await apiClient.get('/dashboard/all');
        const turbines = [];
        const allAlarms = [];

        for (const apiTurbine of response.data) {
            const displayId = apiTurbine.turbine_id;
            const turbineData = {
                id: displayId,
                location: apiTurbine.location || 'Unknown Field',
                status: statusCodeMap[apiTurbine.status] || 'error',
                metrics: {},
                hydraulicData: apiTurbine.hydraulic || null,
                vibrationData: apiTurbine.vibration || null,
                temperatureData: apiTurbine.temperature || null,
                scadaData: apiTurbine.scada || null,
                healthData: null,
                deteriorationData: null,
                alarmSummary: null,
                _api_id: apiTurbine.id
            };

            turbineData.metrics.power_mw = turbineData.scadaData?.power_kw ? (turbineData.scadaData.power_kw / 1000) : 0;
            turbineData.metrics.wind_ms = turbineData.scadaData?.wind_speed_ms ? parseFloat(turbineData.scadaData.wind_speed_ms) : 0;
            turbineData.metrics.wind_speed_ms = turbineData.scadaData?.wind_speed_ms ? parseFloat(turbineData.scadaData.wind_speed_ms) : 0;
            turbineData.metrics.rotor_rpm = turbineData.scadaData?.rotor_speed_rpm ? parseFloat(turbineData.scadaData.rotor_speed_rpm) : 0;
            turbineData.metrics.generator_rpm = turbineData.scadaData?.generator_speed_rpm ? parseFloat(turbineData.scadaData.generator_speed_rpm) : 0;
            turbineData.metrics.pitch_deg = turbineData.scadaData?.pitch_angle_deg ? parseFloat(turbineData.scadaData.pitch_angle_deg) : 0;
            turbineData.metrics.ambient_temp_c = turbineData.scadaData?.ambient_temp_c ? parseFloat(turbineData.scadaData.ambient_temp_c) : 0;

            if (apiTurbine.alarms) {
                turbineData.alarmSummary = {
                    total: apiTurbine.alarms.total_alarms,
                    counts: apiTurbine.alarms.counts_by_severity
                };
                if (apiTurbine.alarms.alarms?.length > 0) {
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
        console.error('Dashboard fetch error:', err);
        turbineStore.error = err.response?.data?.message || err.message;
    } finally {
        turbineStore.loading = false;
        alarmStore.loading = false;
    }
}

// === 1. COMPONENT HEALTH ===
async function fetchTurbineHealth(displayId) {
    // ID FIX APPLIED:
    const turbine = turbineStore.turbines.find(t => t._api_id == displayId);

    if (!turbine) {
        console.warn(`Turbine with API ID ${displayId} not found`);
        return;
    }

    try {
        console.log(`🏥 Fetching health for API ID: ${turbine._api_id}...`);
        const response = await apiClient.get(`/turbines/${turbine._api_id}/component-health`);
        turbine.healthData = response.data;
        console.log('✅ Health data loaded');
    } catch (err) {
        console.error(`❌ Failed to load health data:`, err);
    }
}

// === 2. DETERIORATION TRENDS ===
async function fetchDeteriorationTrends(displayId) {
    // ID FIX APPLIED:
    const turbine = turbineStore.turbines.find(t => t._api_id == displayId);

    if (!turbine) {
        console.warn(`Turbine with API ID ${displayId} not found for trends`);
        return;
    }

    try {
        console.log(`📉 Fetching deterioration trends for API ID: ${turbine._api_id}...`);
        const response = await apiClient.get(`/turbines/${turbine._api_id}/deterioration-trends`);
        turbine.deteriorationData = response.data;
        console.log('✅ Trend data loaded');
    } catch (err) {
        console.error(`❌ Failed to load trends:`, err);
    }
}

async function fetchMaintenanceLogs() { return []; }

export function useScadaService() {
    return {
        turbineStore,
        alarmStore,
        maintenanceStore,
        historyStore,
        fetchDashboard,
        fetchMaintenanceLogs,
        fetchTurbineHealth,
        fetchDeteriorationTrends
    };
}