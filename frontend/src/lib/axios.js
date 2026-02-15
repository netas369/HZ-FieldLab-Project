// src/lib/axios.js
import axios from "axios";

// Determine API URL based on hostname (more reliable than env vars)
function getApiBaseUrl() {
    if (typeof window !== 'undefined' && window.location.hostname === 'zephyroscontrolroom.nl') {
        return 'https://api.zephyroscontrolroom.nl';
    }
    return import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000';
}

const api = axios.create({
    baseURL: getApiBaseUrl(),

    withCredentials: true, // IMPORTANT: Requests cookies to be sent
    headers: {
        "X-Requested-With": "XMLHttpRequest",
        "Content-Type": "application/json",
        "Accept": "application/json",
    },
});

export default api
