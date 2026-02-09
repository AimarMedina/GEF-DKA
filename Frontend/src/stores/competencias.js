import { defineStore } from 'pinia';
import { ref } from 'vue';
import api from '@/services/api.js';

export const useCompetenciasStore = defineStore('competencias', () => {
  const lista = ref([]);

  const cacheKey = 'competencias_list_v1';

  async function fetchAll() {
    if (sessionStorage.getItem(cacheKey)) {
      try {
        lista.value = JSON.parse(sessionStorage.getItem(cacheKey));
        return;
      } catch (e) {
        // fallthrough
      }
    }

    const res = await api.get('/api/competencias');
    lista.value = res.data || [];

    try {
      sessionStorage.setItem(cacheKey, JSON.stringify(lista.value));
    } catch (e) {}
  }

  return { lista, fetchAll };
});
