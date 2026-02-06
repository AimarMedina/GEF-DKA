import {ref} from 'vue';
import {defineStore} from 'pinia';
import api from '../services/api.js';

export const useUsersStore = defineStore('users', () => {
  const users = ref([])
  const currentPage = ref(1)
  const totalPages = ref(1)
  const perPage = ref(5)
  const currentUser = ref(null)

  function getCacheKey(page, filters) {
    // en caso de que los filtros vengan con grado lo guarda para crear la cache key, en otro caso se guarda como 'grado__search..'
    const grado = filters.tipo === 'alumno' ? filters.id_grado || '' : ''
    return `users_page_${page}_tipo_${filters.tipo || ''}_grado_${grado}_search_${filters.search || ''}`
  }

  async function fetchUsers(page = 1, filters = {}) {
    currentPage.value = page
    const cacheKey = getCacheKey(page, filters)

    if (sessionStorage.getItem(cacheKey)) {
        const parsed = JSON.parse(sessionStorage.getItem(cacheKey))
        users.value = parsed.users
        totalPages.value = parsed.totalPages
        return
    }

    const response = await api.get('/api/users', {  
    //    Mira si tiene que mandar un body con id_grado o nada en base a si es alumno el usuario (ya que profe se buscan todos o x nombre)
        params: {
            page,
            per_page: perPage.value,
            tipo: filters.tipo,
            ...(filters.tipo === 'alumno' && filters.id_grado
            ? { id_grado: filters.id_grado }
            : {}),
            search: filters.search
        }
    });

    const userResponse = response.data.data.data || [];
    const lastPage = response.data.data.last_page;

    users.value = userResponse
    totalPages.value = lastPage

    sessionStorage.setItem(
        cacheKey,
        JSON.stringify({ users: userResponse, totalPages: lastPage })
    )
  }

  async function guardarUsuario(data, filters = {}) {
    try {
        if(data.id){
            await api.put(`/api/users/${data.id}`, data)
                alert('Usuario actualizado correctamente')
        } else {
            await api.post(`/api/users`, data)
                alert('Usuario creado correctamente')
        }

        // Limpiar cache de la página actual
        const cacheKey = getCacheKey(currentPage.value, props.filters)
        sessionStorage.removeItem(cacheKey)

        // Refrescar usuarios
        await fetchUsers(currentPage.value, props.filters)

        cerrarModalUsuario()
    } catch (e) {
        console.error(e)
        alert('Error al guardar usuario')
    }
    }

    async function handleConfirmDelete(confirm) {
        if (!confirm || !currentUser.value) return
        
        console.log(currentUser.value);

        try {
            await api.delete(`/api/users/${currentUser.value.id}`)

            const cacheKey = getCacheKey(currentPage.value, props.filters)
            sessionStorage.removeItem(cacheKey)

            await fetchUsers(currentPage.value, props.filters)

            alert('Usuario eliminado correctamente')
        } catch (e) {
            console.error(e)
            alert('Error al eliminar usuario')
        } finally {
            mostrarConfirmarModal.value = false
            currentUser.value = null
        }
    }


  return { users, currentPage, totalPages, perPage, currentUser, fetchUsers, guardarUsuario, handleConfirmDelete, getCacheKey };
})
