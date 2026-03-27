import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'

export const useTutoresStore = defineStore('tutores', () => {
  const alumnos = ref([])

  async function fetchAlumnosTutor(tutorId, tipo = 'tutor', forceRefresh = false) {
    const cacheKey = `tutor_${tutorId}_alumnos_${tipo}`

    // Intentar obtener del cache primero
    if (!forceRefresh) {
      const cached = sessionStorage.getItem(cacheKey)
      if (cached) {
        alumnos.value = JSON.parse(cached)
        return alumnos.value
      }
    }

    // Si no hay cache, traer de API
    const endpoint = tipo === 'tutor' 
      ? `/api/tutores/${tutorId}/alumnos`
      : `/api/tutores/${tutorId}/alumnos-clases`
    
    const response = await api.get(endpoint)
    alumnos.value = response.data
    
    // Guardar en cache
    sessionStorage.setItem(cacheKey, JSON.stringify(response.data))
    
    return alumnos.value
  }

  function invalidateCache(tutorId, tipo = 'tutor') {
    const cacheKey = `tutor_${tutorId}_alumnos_${tipo}`
    sessionStorage.removeItem(cacheKey)
  }

  function invalidateAllCache(tutorId) {
    invalidateCache(tutorId, 'tutor')
    invalidateCache(tutorId, 'clase')
  }

  return {
    alumnos,
    fetchAlumnosTutor,
    invalidateCache,
    invalidateAllCache
  }
})
