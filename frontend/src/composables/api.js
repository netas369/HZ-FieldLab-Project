import { reactive, computed } from 'vue'
import axios from 'axios'

const apiClient = axios.create({
    baseURL: import.meta.env.VITE_API_BASE_URL,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
    },
    withCredentials: true  // Send cookies with requests (needed for Sanctum)
});

// Add auth token and CSRF token to all requests
apiClient.interceptors.request.use((config) => {
    // Add Bearer token if available
    const token = localStorage.getItem('token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }

    // Add CSRF token from cookie if available
    const xsrfToken = document.cookie
        .split('; ')
        .find(row => row.startsWith('XSRF-TOKEN='))
        ?.split('=')[1];
    if (xsrfToken) {
        config.headers['X-XSRF-TOKEN'] = decodeURIComponent(xsrfToken);
    }

    return config;
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
    activeAlarms: computed(() => alarmStore.alarms.filter(a => !a.acknowledged && a.status !== 'resolved')),
    criticalCount: computed(() => alarmStore.alarms.filter(a => a.priority === 'Critical' && !a.acknowledged).length),

    updateAlarmStatus: async (alarmId, turbineId, status, resolutionNotes = null) => {
        const alarm = alarmStore.alarms.find(a => a.id === alarmId)
        const previousStatus = alarm?.status
        const previousAcknowledged = alarm?.acknowledged

        // Optimistic update
        if (alarm) {
            alarm.status = status
            alarm.acknowledged = status === 'acknowledged' || status === 'resolved'
        }

        try {
            const payload = { status }
            if (resolutionNotes) payload.resolution_notes = resolutionNotes
            await apiClient.patch(`/turbines/${turbineId}/alarms/${alarmId}`, payload)
        } catch (err) {
            // Rollback on error
            if (alarm) {
                alarm.status = previousStatus
                alarm.acknowledged = previousAcknowledged
            }
            console.error('Failed to update alarm status:', err)
            alarmStore.error = err.response?.data?.message || err.message
            throw err
        }
    },

    acknowledgeAlarm: async (alarmId, turbineId) => {
        return alarmStore.updateAlarmStatus(alarmId, turbineId, 'acknowledged')
    },

    resolveAlarm: async (alarmId, turbineId, notes = null) => {
        return alarmStore.updateAlarmStatus(alarmId, turbineId, 'resolved', notes)
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
    tasks: [],
    myTasks: [],
    loading: false,
    error: null,

    // Computed-like getters
    getScheduledTasks: () => maintenanceStore.tasks.filter(t => t.status === 'scheduled'),
    getInProgressTasks: () => maintenanceStore.tasks.filter(t => t.status === 'in_progress'),
    getCompletedTasks: () => maintenanceStore.tasks.filter(t => t.status === 'completed'),
    getOverdueTasks: () => maintenanceStore.tasks.filter(t => t.status === 'overdue' ||
        (t.status !== 'completed' && t.status !== 'canceled' && t.due_date && new Date(t.due_date) < new Date())),

    // Fetch all maintenance tasks
    fetchTasks: async (filters = {}) => {
        maintenanceStore.loading = true
        maintenanceStore.error = null
        try {
            const params = new URLSearchParams()
            if (filters.status) params.append('status', filters.status)
            if (filters.turbine_id) params.append('turbine_id', filters.turbine_id)
            if (filters.assigned_to) params.append('assigned_to', filters.assigned_to)
            if (filters.priority) params.append('priority', filters.priority)

            const response = await apiClient.get(`/maintenances?${params.toString()}`)
            maintenanceStore.tasks = response.data.data || response.data
        } catch (err) {
            console.error('Failed to fetch maintenance tasks:', err)
            maintenanceStore.error = err.response?.data?.message || err.message
        } finally {
            maintenanceStore.loading = false
        }
    },

    // Fetch my assigned tasks
    fetchMyTasks: async () => {
        maintenanceStore.loading = true
        try {
            const response = await apiClient.get('/maintenances/my-tasks')
            maintenanceStore.myTasks = response.data.maintenances || []
        } catch (err) {
            console.error('Failed to fetch my tasks:', err)
            maintenanceStore.error = err.response?.data?.message || err.message
        } finally {
            maintenanceStore.loading = false
        }
    },

    // Fetch tasks for a specific turbine
    fetchTurbineTasks: async (turbineId) => {
        try {
            const response = await apiClient.get(`/turbines/${turbineId}/maintenances`)
            return response.data.maintenances || []
        } catch (err) {
            console.error('Failed to fetch turbine tasks:', err)
            throw err
        }
    },

    // Create new maintenance task
    createTask: async (taskData) => {
        maintenanceStore.loading = true
        try {
            const response = await apiClient.post('/maintenances', taskData)
            const newTask = response.data.maintenance
            maintenanceStore.tasks.unshift(newTask)
            return newTask
        } catch (err) {
            console.error('Failed to create maintenance task:', err)
            maintenanceStore.error = err.response?.data?.message || err.message
            throw err
        } finally {
            maintenanceStore.loading = false
        }
    },

    // Create maintenance from alarm
    createFromAlarm: async (alarmId, taskData = {}) => {
        maintenanceStore.loading = true
        try {
            const response = await apiClient.post(`/alarms/${alarmId}/maintenance`, taskData)
            const newTask = response.data.maintenance
            maintenanceStore.tasks.unshift(newTask)
            return newTask
        } catch (err) {
            console.error('Failed to create maintenance from alarm:', err)
            maintenanceStore.error = err.response?.data?.message || err.message
            throw err
        } finally {
            maintenanceStore.loading = false
        }
    },

    // Update maintenance task
    updateTask: async (taskId, updates) => {
        try {
            const response = await apiClient.put(`/maintenances/${taskId}`, updates)
            const updatedTask = response.data.maintenance
            const index = maintenanceStore.tasks.findIndex(t => t.id === taskId)
            if (index !== -1) {
                maintenanceStore.tasks[index] = updatedTask
            }
            return updatedTask
        } catch (err) {
            console.error('Failed to update maintenance task:', err)
            maintenanceStore.error = err.response?.data?.message || err.message
            throw err
        }
    },

    // Delete maintenance task
    deleteTask: async (taskId) => {
        try {
            await apiClient.delete(`/maintenances/${taskId}`)
            maintenanceStore.tasks = maintenanceStore.tasks.filter(t => t.id !== taskId)
        } catch (err) {
            console.error('Failed to delete maintenance task:', err)
            maintenanceStore.error = err.response?.data?.message || err.message
            throw err
        }
    },

    // Quick status updates
    startTask: async (taskId) => {
        return maintenanceStore.updateTask(taskId, { status: 'in_progress' })
    },

    completeTask: async (taskId, notes = null) => {
        const updates = { status: 'completed' }
        if (notes) updates.notes = notes
        return maintenanceStore.updateTask(taskId, updates)
    },

    cancelTask: async (taskId) => {
        return maintenanceStore.updateTask(taskId, { status: 'canceled' })
    }
});

const usersStore = reactive({
    users: [],
    loading: false,
    error: null,

    fetchUsers: async () => {
        usersStore.loading = true
        usersStore.error = null
        try {
            const response = await apiClient.get('/users')
            usersStore.users = response.data
        } catch (err) {
            console.error('Failed to fetch users:', err)
            usersStore.error = err.response?.data?.message || err.message
        } finally {
            usersStore.loading = false
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
                alarmSummary: { total: 0, counts: {} }, // Default to 0
                _api_id: apiTurbine.id
            };

            // Metrics processing...
            turbineData.metrics.power_mw = turbineData.scadaData?.power_kw ? (turbineData.scadaData.power_kw / 1000) : 0;
            turbineData.metrics.wind_ms = turbineData.scadaData?.wind_speed_ms ? parseFloat(turbineData.scadaData.wind_speed_ms) : 0;
            turbineData.metrics.wind_speed_ms = turbineData.scadaData?.wind_speed_ms ? parseFloat(turbineData.scadaData.wind_speed_ms) : 0;
            turbineData.metrics.rotor_rpm = turbineData.scadaData?.rotor_speed_rpm ? parseFloat(turbineData.scadaData.rotor_speed_rpm) : 0;
            turbineData.metrics.generator_rpm = turbineData.scadaData?.generator_speed_rpm ? parseFloat(turbineData.scadaData.generator_speed_rpm) : 0;
            turbineData.metrics.pitch_deg = turbineData.scadaData?.pitch_angle_deg ? parseFloat(turbineData.scadaData.pitch_angle_deg) : 0;
            turbineData.metrics.ambient_temp_c = turbineData.scadaData?.ambient_temp_c ? parseFloat(turbineData.scadaData.ambient_temp_c) : 0;

            // --- CHANGED SECTION STARTS HERE ---
            if (apiTurbine.alarms && apiTurbine.alarms.alarms?.length > 0) {

                // 1. Process the alarms array first
                const turbineAlarms = apiTurbine.alarms.alarms.map(apiAlarm => ({
                    id: apiAlarm.id,
                    turbineId: apiTurbine.id,
                    title: apiAlarm.message,
                    priority: priorityMap[apiAlarm.severity] || 'Warning',
                    severity: apiAlarm.severity,
                    description: apiAlarm.message,
                    component: apiAlarm.component,
                    turbine: displayId,
                    time: new Date(apiAlarm.detected_at).toLocaleString(),
                    detectedAt: apiAlarm.detected_at,
                    status: apiAlarm.status,
                    acknowledged: apiAlarm.status === 'acknowledged' || apiAlarm.status === 'resolved',
                    resolved: apiAlarm.status === 'resolved'
                }));

                // 2. Filter specifically for 'active' status to get the count
                const activeCount = turbineAlarms.filter(a => a.status === 'active').length;

                // 3. Assign the calculated active count
                turbineData.alarmSummary = {
                    total: activeCount, // Now uses the filtered count
                    counts: apiTurbine.alarms.counts_by_severity
                };

                // 4. Push to global store
                allAlarms.push(...turbineAlarms);
            }
            // --- CHANGED SECTION ENDS HERE ---

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

const fetchTurbineHealth = async (turbineId, daysBack = null) => {
    try {
        const params = daysBack ? { days_back: daysBack } : {}
        const response = await apiClient.get(`/turbines/${turbineId}/component-health`, { params })

        const turbine = turbineStore.turbines.find(t => t._api_id == turbineId)
        if (turbine) {
            turbine.healthData = response.data
        }
        return response.data
    } catch (err) {
        console.error('❌ Failed to fetch turbine health:', err)
        throw err
    }
}

const fetchDeteriorationTrends = async (turbineId, daysBack = null) => {
    try {
        const params = daysBack ? { days_back: daysBack } : {}
        const response = await apiClient.get(`/turbines/${turbineId}/deterioration-trends`, { params })

        const turbine = turbineStore.turbines.find(t => t._api_id == turbineId)
        if (turbine) {
            turbine.deteriorationData = response.data
        }
        return response.data
    } catch (err) {
        console.error('❌ Failed to fetch deterioration trends:', err)
        throw err
    }
}

async function fetchMaintenance(filters = {}) {
    return maintenanceStore.fetchTasks(filters);
}

// Alias for backwards compatibility
const fetchMaintenanceLogs = fetchMaintenance;

async function fetchUsers() {
    return usersStore.fetchUsers();
}

const analyticsStore = reactive({
    data: null,
    loading: false,
    error: null,
    recentFetches: JSON.parse(localStorage.getItem('analytics_history_v1') || '[]'),
    currentFetch: JSON.parse(localStorage.getItem('analytics_current_v1') || 'null'),

    fetchAnalytics: async (timeRange = '7d', startDate = null, endDate = null) => {
        analyticsStore.loading = true
        analyticsStore.error = null
        try {
            const params = {}

            if (timeRange === 'custom' && startDate && endDate) {
                params.start_date = startDate
                params.end_date = endDate
            } else {
                params.time_range = timeRange
            }

            const response = await apiClient.get('/analytics', { params })
            analyticsStore.data = response.data

            // Create history entry
            const historyEntry = {
                id: Date.now(),
                timeRange: timeRange,
                startDate: timeRange === 'custom' ? startDate : response.data.start_date,
                endDate: timeRange === 'custom' ? endDate : response.data.end_date,
                timestamp: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
                fullTimestamp: new Date().toISOString(),
                payload: response.data
            }

            // Only save custom ranges to history (predefined ranges are always available as buttons)
            if (timeRange === 'custom') {
                // Add to recent fetches (limit to 10 custom fetches)
                analyticsStore.recentFetches.unshift(historyEntry)
                if (analyticsStore.recentFetches.length > 10) {
                    analyticsStore.recentFetches.pop()
                }

                // Save to localStorage
                localStorage.setItem('analytics_history_v1', JSON.stringify(analyticsStore.recentFetches))
            }

            // Set as current fetch (for both custom and predefined)
            analyticsStore.currentFetch = historyEntry
            localStorage.setItem('analytics_current_v1', JSON.stringify(historyEntry))

            return response.data
        } catch (err) {
            console.error('Failed to fetch analytics:', err)
            analyticsStore.error = err.response?.data?.message || err.message
            throw err
        } finally {
            analyticsStore.loading = false
        }
    },

    loadFromHistory: (entry) => {
        analyticsStore.data = entry.payload
        analyticsStore.currentFetch = entry
        localStorage.setItem('analytics_current_v1', JSON.stringify(entry))
    },

    removeFromHistory: (entryId) => {
        analyticsStore.recentFetches = analyticsStore.recentFetches.filter(e => e.id !== entryId)
        localStorage.setItem('analytics_history_v1', JSON.stringify(analyticsStore.recentFetches))

        // If we're deleting the currently viewed entry, clear current
        if (analyticsStore.currentFetch?.id === entryId) {
            analyticsStore.currentFetch = null
            localStorage.removeItem('analytics_current_v1')
        }
    },

    clearAllHistory: () => {
        analyticsStore.recentFetches = []
        localStorage.removeItem('analytics_history_v1')
        analyticsStore.currentFetch = null
        localStorage.removeItem('analytics_current_v1')
    }
})

export function useScadaService() {
    return {
        turbineStore,
        alarmStore,
        maintenanceStore,
        historyStore,
        usersStore,
        analyticsStore,
        fetchDashboard,
        fetchMaintenance,
        fetchMaintenanceLogs,
        fetchUsers,
        fetchTurbineHealth,
        fetchDeteriorationTrends
    };
}
