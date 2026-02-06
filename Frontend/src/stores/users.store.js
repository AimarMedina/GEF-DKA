import { ref } from 'vue'
import { defineStore } from 'pinia'
import api from '@/services/api.js'

export const useUsersStore = defineStore('users', () => {
  const users = ref([])
  const currentPage = ref(1)
  const totalPages = ref(1)
  const perPage = ref(5)
  const currentUser = ref(null)

// Intenta obtener una clave de cache de sesion fomateado por users_page_numPag_tipo_tipoConsulta_grado_gradoBusqueda_search_palabraClave
  function getCacheKey(page, filters) {
    return `users_page_${page}_tipo_${filters.tipo || ''}_grado_${filters.id_grado || ''}_search_${filters.search || ''}`
  }

  async function fetchUsers(page = 1, filters = {}) {
    currentPage.value = page

    const cacheKey = getCacheKey(page, filters)

    
    if (sessionStorage.getItem(cacheKey)) {
      try {
        // En caso de encontrar la clave hace que el ref sea el valor de cache
        const parsed = JSON.parse(sessionStorage.getItem(cacheKey))
        users.value = parsed.users
        totalPages.value = parsed.totalPages
        return
      } catch {
        sessionStorage.removeItem(cacheKey)
      }
    }

    try {
      const response = await api.get('/api/users', {
        params: {
          page,
          per_page: perPage.value,
          tipo: filters.tipo,
          id_grado: filters.id_grado,
          search: filters.search
        }
      })

      const data = response.data.data.data || []
      const lastPage = response.data.data.last_page

      users.value = data
      totalPages.value = lastPage

      sessionStorage.setItem(
        cacheKey,
        JSON.stringify({ users: data, totalPages: lastPage })
      )
    } catch (error) {
      console.error(error)
    }
  }

  return { users, currentPage, totalPages, perPage, currentUser, fetchUsers }
})
