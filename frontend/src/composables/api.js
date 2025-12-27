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
    activeDataMap: {}, // Format: { turbineId: { id: entryId, payload: responseData } }
    recentFetches: JSON.parse(localStorage.getItem('scada_history_v1') || '{}'),
    loading: false,
    error: null,

    fetchHistory: async (displayId, startDate, endDate) => {
        historyStore.loading = true;
        historyStore.error = null;

        const turbine = turbineStore.turbines.find(t => t._api_id == displayId);
        const turbineId = turbine?.id || displayId;

        try {
            const response = await apiClient.get('/turbine/allHistoricalData', {
                params: { turbine_id: turbineId, start_date: startDate, end_date: endDate }
            });

            const newEntry = {
                id: Date.now(),
                turbineId: displayId,
                startDate,
                endDate,
                timestamp: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
                payload: response.data
            };

            if (!historyStore.recentFetches[displayId]) {
                historyStore.recentFetches[displayId] = [];
            }

            historyStore.recentFetches[displayId].unshift(newEntry);
            if (historyStore.recentFetches[displayId].length > 10) {
                historyStore.recentFetches[displayId].pop();
            }

            localStorage.setItem('scada_history_v1', JSON.stringify(historyStore.recentFetches));

            // Set as active record
            historyStore.activeDataMap[displayId] = { id: newEntry.id, payload: response.data };
        } catch (err) {
            console.error('❌ History fetch error:', err);
            historyStore.error = err.response?.data?.message || err.message;
        } finally {
            historyStore.loading = false;
        }
    },

    selectHistoryEntry: (turbineId, entry) => {
        historyStore.activeDataMap[turbineId] = { id: entry.id, payload: entry.payload };
    },

    removeFromHistory: (turbineId, entryId) => {
        if (historyStore.recentFetches[turbineId]) {
            historyStore.recentFetches[turbineId] = historyStore.recentFetches[turbineId].filter(e => e.id !== entryId);
            localStorage.setItem('scada_history_v1', JSON.stringify(historyStore.recentFetches));

            // Fix: Only clear active data if we are deleting the CURRENTLY viewed record
            if (historyStore.activeDataMap[turbineId]?.id === entryId) {
                delete historyStore.activeDataMap[turbineId];
            }
        }
    },

    clearAllForTurbine: (turbineId) => {
        if (historyStore.recentFetches[turbineId]) {
            delete historyStore.recentFetches[turbineId];
            localStorage.setItem('scada_history_v1', JSON.stringify(historyStore.recentFetches));
        }
        delete historyStore.activeDataMap[turbineId];
    },

    clear: () => {
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

        await fetchHealthSummary();

    } catch (err) {
        console.error('Dashboard fetch error:', err);
        turbineStore.error = err.response?.data?.message || err.message;
    } finally {
        turbineStore.loading = false;
        alarmStore.loading = false;
    }
}

async function fetchHealthSummary() {
    try {
        const response = await apiClient.get('/turbines/component-health/summary');
        if (response.data?.turbines) {
            response.data.turbines.forEach(summaryItem => {
                const turbine = turbineStore.turbines.find(t => t.id === summaryItem.turbine_id);
                if (turbine) {
                    turbine.healthData = {
                        overall_health: summaryItem.overall_health,
                        components: {},
                        period_days: response.data.period_days,
                        calculation_timestamp: response.data.calculation_timestamp
                    };
                }
            });
        }
    } catch (err) {
        console.error('❌ Failed to fetch health summary:', err);
    }
}

async function fetchTurbineHealth(displayId) {
    const turbine = turbineStore.turbines.find(t => t._api_id == displayId);
    if (!turbine) return;
    try {
        const response = await apiClient.get(`/turbines/${turbine._api_id}/component-health`);
        turbine.healthData = response.data;
    } catch (err) { console.error(err); }
}

async function fetchDeteriorationTrends(displayId) {
    const turbine = turbineStore.turbines.find(t => t._api_id == displayId);
    if (!turbine) return;
    try {
        const response = await apiClient.get(`/turbines/${turbine._api_id}/deterioration-trends`);
        turbine.deteriorationData = response.data;
    } catch (err) { console.error(err); }
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