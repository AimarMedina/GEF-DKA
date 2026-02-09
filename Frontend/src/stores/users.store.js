import { ref } from 'vue';
import { defineStore } from 'pinia';
import api from '../services/api.js';

export const useUsersStore = defineStore('users', () => {
  const users = ref([]);          
  const currentPage = ref(1);     
  const totalPages = ref(1);      
  const perPage = ref(5);         
  const currentUser = ref(null);  

  // Devuelve la cache key según página y filtros de usrTable
  function getCacheKey(page, filters) {
    const grado = filters.tipo === 'alumno' ? filters.id_grado || '' : '';
    return `users_page_${page}_tipo_${filters.tipo || ''}_grado_${grado}_search_${filters.search || ''}`;
  }

  function initCurrentUser(user = null) {
    currentUser.value = user || {
      nombre: '',
      apellidos: '',
      email: '',
      n_tel: '',
      password: '',
      tipo: '',
      alumno: null,
      id: null
    }
    
    console.log(currentUser.value);
  }

  function removeUserFromCache(userId) {
    // Busca todas las instancias de user por el ID y en caso de encontrar lo borra de cache para que se actualice la view
    for (let i = 0; i < sessionStorage.length; i++) {
      const key = sessionStorage.key(i);
      if (!key || !key.startsWith('users_page_')) continue;

      try {
        const parsed = JSON.parse(sessionStorage.getItem(key));
        if (parsed?.users) {
          const filteredUsers = parsed.users.filter(u => u.id !== userId);
        //   En caso de solo encontrar una linea borra la key, si no lo hace varias veces
          if (filteredUsers.length !== parsed.users.length) {
            if (filteredUsers.length > 0) {
              sessionStorage.setItem(key, JSON.stringify({ ...parsed, users: filteredUsers }));
            } else {
              sessionStorage.removeItem(key);
            }
          }
        }
      } catch {
        // en caso de cualquier error, salta de linea y ya
        continue;
      }
    }
  }

  async function fetchUsers(page = 1, filters = {}) {
    currentPage.value = page;
    const cacheKey = getCacheKey(page, filters);
    
    // Busca si existen keys y en caso de que si lo sacan a la view, si no hace consulta a BD
    if (sessionStorage.getItem(cacheKey)) {
        const parsed = JSON.parse(sessionStorage.getItem(cacheKey));
        users.value = parsed.users;
        totalPages.value = parsed.totalPages;
        return;
    }

    const response = await api.get('/api/users', {
        
        params: {
            page,
            per_page: perPage.value,
            tipo: filters.tipo,
            ...(filters.tipo === 'alumno' && filters.id_grado ? { id_grado: filters.id_grado } : {}),
            search: filters.search
        }
    });

    const userResponse = response.data.data.data || [];
    const lastPage = response.data.data.last_page;

    users.value = userResponse;
    totalPages.value = lastPage;

    sessionStorage.setItem(cacheKey, JSON.stringify({ users: userResponse, totalPages: lastPage }));
  }

  // Crea o actualiza el user en base a que si en la data hay o no id
  async function guardarUsuario(data, filters = {}) {
    try {
        if (data.id) {
            await api.put(`/api/users/${data.id}`, data);
            alert('Usuario actualizado correctamente');
        } else {
            await api.post('/api/users', data);
            alert('Usuario creado correctamente');
        }
        // Actualiza la cache
        removeUserFromCache(data.id);
        await fetchUsers(currentPage.value, filters);
    } catch (e) {
        console.error(e);
        alert('Error al guardar usuario');
    }
  }

  async function handleConfirmDelete(confirm, filters = {}) {
    if (!confirm || !currentUser.value) return;

    try {
        await api.delete(`/api/users/${currentUser.value.id}`);
        removeUserFromCache(currentUser.value.id);
        await fetchUsers(currentPage.value, filters);
        alert('Usuario eliminado correctamente');
    } catch (e) {
        console.error(e);
        alert('Error al eliminar usuario');
    } finally {
        currentUser.value = null;
    }
  }

  function getUserById(id) {
    return users.value.find(u => u.id === id) || null;
  }

  return { users, currentPage, totalPages, perPage,  currentUser, fetchUsers, getUserById, guardarUsuario, handleConfirmDelete, getCacheKey, removeUserFromCache, initCurrentUser };
});
