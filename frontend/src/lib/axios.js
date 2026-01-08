// src/lib/axios.js
import axios from "axios";

const api = axios.create({
    baseURL: "http://localhost:8000", // Your Backend URL
    withCredentials: true, // IMPORTANT: Requests cookies to be sent
    headers: {
        "X-Requested-With": "XMLHttpRequest",
        "Content-Type": "application/json",
        "Accept": "application/json",
    },
});

export default api;