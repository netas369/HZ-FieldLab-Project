// composables/useChunkedImport.js
import { ref } from 'vue'

// ✅ CHANGE: Import your global axios instance
// This ensures we reuse the same session, cookies, and base URL config
import axios from '@/lib/axios'
// If your global axios file is named 'api.js' or located elsewhere, update the path above
// e.g., import axios from '@/services/api'

export function useChunkedImport() {
    const isImporting = ref(false)
    const progress = ref(0)
    const currentChunk = ref(0)
    const totalChunks = ref(0)
    const error = ref(null)
    const importStats = ref(null)

    // Configuration
    const CHUNK_SIZE = 5000
    const FILE_SIZE_THRESHOLD = 50 * 1024 * 1024

    const shouldUseChunking = (rowCount, estimatedSizeBytes) => {
        return estimatedSizeBytes > FILE_SIZE_THRESHOLD || rowCount > 10000
    }

    /**
     * Helper to get CSRF cookie before sensitive requests
     * Uses the global axios instance to ensure the token is set on the correct client
     */
    const ensureSanctumCookie = async () => {
        try {
            await axios.get('/sanctum/csrf-cookie');
        } catch (e) {
            console.warn('CSRF cookie fetch failed, continuing anyway...', e);
        }
    }

    const regularImport = async (keyColumns, sensorMapping, data, fileName) => {
        try {
            const response = await axios.post('/api/data-import', {
                key_columns: keyColumns,
                sensor_mapping: sensorMapping,
                data: data,
                file_name: fileName
            })
            return response.data
        } catch (err) {
            throw new Error(err.response?.data?.message || 'Import failed')
        }
    }

    const chunkedImport = async (keyColumns, sensorMapping, data, fileName) => {
        try {
            // Step 1: Initialize import session
            // URLs are relative because baseURL is already configured in @/lib/axios
            const initResponse = await axios.post('/api/data-import/chunked/init', {
                key_columns: keyColumns,
                sensor_mapping: sensorMapping,
                file_name: fileName,
                total_rows: data.length,
                chunk_size: CHUNK_SIZE
            })

            const { import_id: importId } = initResponse.data

            // Step 2: Split data into chunks
            const chunks = []
            for (let i = 0; i < data.length; i += CHUNK_SIZE) {
                chunks.push(data.slice(i, i + CHUNK_SIZE))
            }

            totalChunks.value = chunks.length
            currentChunk.value = 0

            // Step 3: Process chunks sequentially
            for (let i = 0; i < chunks.length; i++) {
                const chunkResponse = await axios.post('/api/data-import/chunked/process', {
                    import_id: importId,
                    chunk_number: i + 1,
                    data: chunks[i]
                })

                currentChunk.value = i + 1
                progress.value = chunkResponse.data.progress_percent

                if (chunkResponse.data.is_complete) {
                    importStats.value = chunkResponse.data.stats
                }
            }

            // Step 4: Get final status
            const statusResponse = await axios.get(`/api/data-import/chunked/status/${importId}`)

            return statusResponse.data

        } catch (err) {
            console.error('Chunked import error detail:', err.response || err);

            // Handle specific 419 error specifically (Session expired)
            if (err.response && err.response.status === 419) {
                throw new Error('Session expired. Please refresh the page and log in again.');
            }
            // Handle 401 Unauthorized
            if (err.response && err.response.status === 401) {
                throw new Error('You are not logged in. Please log in and try again.');
            }

            throw new Error(err.response?.data?.message || 'Chunked import failed')
        }
    }

    const startImport = async (keyColumns, sensorMapping, data, fileName, estimatedSize) => {
        isImporting.value = true
        progress.value = 0
        error.value = null
        importStats.value = null

        try {
            // Ensure we have the CSRF token before sending data
            await ensureSanctumCookie();

            const useChunking = shouldUseChunking(data.length, estimatedSize)

            console.log(`Import mode: ${useChunking ? 'CHUNKED' : 'REGULAR'}`)

            let result
            if (useChunking) {
                result = await chunkedImport(keyColumns, sensorMapping, data, fileName)
            } else {
                result = await regularImport(keyColumns, sensorMapping, data, fileName)
                progress.value = 100
            }

            return result

        } catch (err) {
            error.value = err.message
            throw err
        } finally {
            isImporting.value = false
        }
    }

    return {
        isImporting,
        progress,
        currentChunk,
        totalChunks,
        error,
        importStats,
        startImport,
        shouldUseChunking
    }
}