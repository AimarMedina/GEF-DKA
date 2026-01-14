<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import { useUserStore } from '@/stores/userStore';
import EstanciaDatos from './EstanciaDatos.vue';
import EstanciaHorario from './EstanciaHorario.vue';

const estancia = ref(null);
const userStore = useUserStore();
const alumnoId = userStore.user.id;

async function fetchEstancia() {
  try {
    const response = await axios.get(`http://localhost:8000/api/alumno/${alumnoId}/estancia`);
    estancia.value = response.data;
  } catch (error) {
    console.error(error);
  }
}

onMounted(() => {
  fetchEstancia();
});
</script>

<template>
  <div class="container-fluid">
    <div v-if="estancia" class="row">
      <EstanciaDatos :estancia="estancia" />
      <EstanciaHorario :horario="estancia.horario" />
    </div>
    <div v-else class="row">
      No hay estancia registrada.
    </div>
  </div>
</template>
