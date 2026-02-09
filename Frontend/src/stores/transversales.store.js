import { defineStore } from 'pinia';
import { ref } from 'vue';
import api from '@/services/api.js';

export const useTransversalesStore = defineStore('transversales', () => {
  const lista = ref([]);

  const cacheKey = 'transversales_list_v1';

  async function fetchAll() {
    if (sessionStorage.getItem(cacheKey)) {
      try {
        lista.value = JSON.parse(sessionStorage.getItem(cacheKey));
        return;
      } catch (e) {
        // fallthrough
      }
    }

    const res = await api.get('/api/transversales');
    lista.value = res.data || [];

    try {
      sessionStorage.setItem(cacheKey, JSON.stringify(lista.value));
    } catch (e) {}
  }

  return { lista, fetchAll };
});
