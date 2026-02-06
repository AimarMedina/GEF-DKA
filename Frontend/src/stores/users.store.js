import { ref } from 'vue';
import { defineStore } from 'pinia';
import api from '../services/api.js';

export const useUsersStore = defineStore('users', () => {
  const users = ref([]);
  const currentPage = ref(1);
  const totalPages = ref(1);
  const perPage = ref(5);
  const currentUser = ref(null);

  // -----------------------
  // CACHE
  // -----------------------
  function getCacheKey(page, filters) {
    const grado = filters.tipo === 'alumno' ? filters.id_grado || '' : '';
    return `users_page_${page}_tipo_${filters.tipo || ''}_grado_${grado}_search_${filters.search || ''}`;
  }

  function removeUserFromCache(userId) {
    for (let i = 0; i < sessionStorage.length; i++) {
      const key = sessionStorage.key(i);
      if (!key || !key.startsWith('users_page_')) continue;

      try {
        const parsed = JSON.parse(sessionStorage.getItem(key));
        if (parsed?.users) {
          const filteredUsers = parsed.users.filter(u => u.id !== userId);
          if (filteredUsers.length !== parsed.users.length) {
            if (filteredUsers.length > 0) {
              sessionStorage.setItem(key, JSON.stringify({ ...parsed, users: filteredUsers }));
            } else {
              sessionStorage.removeItem(key);
            }
          }
        }
      } catch {
        continue;
      }
    }
  }

  // -----------------------
  // FETCH USUARIOS
  // -----------------------
  async function fetchUsers(page = 1, filters = {}) {
    currentPage.value = page;
    const cacheKey = getCacheKey(page, filters);

    // Usar cache si existe
    if (sessionStorage.getItem(cacheKey)) {
      const parsed = JSON.parse(sessionStorage.getItem(cacheKey));
      users.value = parsed.users;
      totalPages.value = parsed.totalPages;
      return;
    }

    // Llamada a API
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

    // Guardar en cache
    sessionStorage.setItem(
      cacheKey,
      JSON.stringify({ users: userResponse, totalPages: lastPage })
    );
  }

  // -----------------------
  // GUARDAR USUARIO
  // -----------------------
  async function guardarUsuario(data, filters = {}) {
    try {
      if (data.id) {
        await api.put(`/api/users/${data.id}`, data);
        alert('Usuario actualizado correctamente');
      } else {
        await api.post(`/api/users`, data);
        alert('Usuario creado correctamente');
      }

      // Limpiar cache del usuario modificado
      removeUserFromCache(data.id);

      // Refrescar usuarios
      await fetchUsers(currentPage.value, filters);
    } catch (e) {
      console.error(e);
      alert('Error al guardar usuario');
    }
  }

  // -----------------------
  // ELIMINAR USUARIO
  // -----------------------
  async function handleConfirmDelete(confirm, filters = {}) {
    if (!confirm || !currentUser.value) return;

    try {
      await api.delete(`/api/users/${currentUser.value.id}`);

      // Limpiar cache del usuario eliminado
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

  return { users, currentPage, totalPages, perPage, currentUser, fetchUsers, guardarUsuario, handleConfirmDelete, getCacheKey, removeUserFromCache };
});
