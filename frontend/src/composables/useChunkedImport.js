// composables/useChunkedImport.js
// Place this in your Vue project's src/composables/ directory

import { ref } from 'vue'
import axios from 'axios'

export function useChunkedImport() {
    const isImporting = ref(false)
    const progress = ref(0)
    const currentChunk = ref(0)
    const totalChunks = ref(0)
    const error = ref(null)
    const importStats = ref(null)

    // Configuration
    const CHUNK_SIZE = 5000 // Rows per chunk
    const FILE_SIZE_THRESHOLD = 50 * 1024 * 1024 // 50MB threshold for chunking

    /**
     * Determine if file should be chunked based on size
     */
    const shouldUseChunking = (rowCount, estimatedSizeBytes) => {
        return estimatedSizeBytes > FILE_SIZE_THRESHOLD || rowCount > 10000
    }

    /**
     * Regular import (for small files)
     */
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

    /**
     * Chunked import (for large files)
     */
    const chunkedImport = async (keyColumns, sensorMapping, data, fileName) => {
        try {
            // Step 1: Initialize import session
            const initResponse = await axios.post('http://localhost:8000/api/data-import/chunked/init', {
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
                const chunkResponse = await axios.post('http://localhost:8000/api/data-import/chunked/process', {
                    import_id: importId,
                    chunk_number: i + 1,
                    data: chunks[i]
                })

                currentChunk.value = i + 1
                progress.value = chunkResponse.data.progress_percent

                // If this was the last chunk, get final stats
                if (chunkResponse.data.is_complete) {
                    importStats.value = chunkResponse.data.stats
                }
            }

            // Step 4: Get final status
            const statusResponse = await axios.get(`http://localhost:8000/api/data-import/chunked/status/${importId}`)

            return statusResponse.data

        } catch (err) {
            throw new Error(err.response?.data?.message || 'Chunked import failed')
        }
    }

    /**
     * Main import function - automatically chooses method
     */
    const startImport = async (keyColumns, sensorMapping, data, fileName, estimatedSize) => {
        isImporting.value = true
        progress.value = 0
        error.value = null
        importStats.value = null

        try {
            const useChunking = shouldUseChunking(data.length, estimatedSize)

            console.log(`Import mode: ${useChunking ? 'CHUNKED' : 'REGULAR'}`)
            console.log(`Rows: ${data.length}, Estimated size: ${(estimatedSize / 1024 / 1024).toFixed(2)}MB`)

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
