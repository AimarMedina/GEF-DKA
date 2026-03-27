import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'

export const useInstructoresStore = defineStore('instructores', () => {
  const alumnos = ref([])

  async function fetchAlumnosInstructor(instructorId, forceRefresh = false) {
    const cacheKey = `instructor_${instructorId}_alumnos`

    // Intentar obtener del cache primero
    if (!forceRefresh) {
      const cached = sessionStorage.getItem(cacheKey)
      if (cached) {
        console.log('📦 Cache hit para instructor', instructorId)
        alumnos.value = JSON.parse(cached)
        return alumnos.value
      }
    }

    // Si no hay cache, traer de API
    console.log('🔄 Fetching alumnos desde API para instructor', instructorId)
    const response = await api.get(`/api/instructores/${instructorId}/alumnos`)
    alumnos.value = response.data
    
    // Guardar en cache
    sessionStorage.setItem(cacheKey, JSON.stringify(response.data))
    console.log('💾 Cache guardado para instructor', instructorId)
    
    return alumnos.value
  }

  function invalidateCache(instructorId) {
    const cacheKey = `instructor_${instructorId}_alumnos`
    sessionStorage.removeItem(cacheKey)
    console.log('🗑️ Cache invalidado para instructor', instructorId)
  }

  return {
    alumnos,
    fetchAlumnosInstructor,
    invalidateCache
  }
})
