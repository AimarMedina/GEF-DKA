<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useUserStore } from '@/stores/userStore';

import ListaCuadernos from './ListaCuadernos.vue';

const entregas = ref([]);
const userStore = useUserStore();

async function fetchEntregas() {
  try {
    const instructorId = userStore.user.id;
    const res = await axios.get(`http://localhost:8000/api/instructor/${instructorId}/entregas`);
    entregas.value = res.data.flatMap(e => e.entregas_alumno);
  } catch (err) {
    console.error(err);
  }
}

onMounted(fetchEntregas);
</script>

<template>
  <div>
    <h3>Cuadernos de mis alumnos</h3>
    <ListaCuadernos :entregas="entregas" />
  </div>
</template>
