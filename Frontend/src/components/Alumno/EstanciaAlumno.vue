<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import { useUserStore } from '@/stores/userStore';

const estancia = ref(null);
const userStore = useUserStore();
const alumnoId = userStore.user.id;

async function fetchEstancia() {
  try {
    const response = await axios.get(`http://127.0.0.1:8000/api/alumno/${alumnoId}/estancia`);
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
  <div v-if="estancia">
    <table class="table table-striped">
      <thead>
        <tr>
          <th>Empresa</th>
          <th>Instructor</th>
          <th>Tutor</th>
          <th>Fecha inicio</th>
          <th>Fecha fin</th>
          <th>Horario</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>{{ estancia.empresa?.Nombre }}</td>
          <td>{{ estancia.alumno?.instructor?.usuario?.Nombre }} {{ estancia.alumno?.instructor?.usuario?.Apellidos }}</td>
          <td>{{ estancia.alumno?.tutor?.usuario?.Nombre }} {{ estancia.alumno?.tutor?.usuario?.Apellidos }}</td>
          <td>{{ estancia.Fecha_inicio }}</td>
          <td>{{ estancia.Fecha_fin ?? '—' }}</td>
          <td>
            <div v-for="h in estancia.horario" :key="h.ID">
              {{ h.Dias }} {{ h.Horario1 }} {{ h.Horario2 }}
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
  <div v-else>
    No hay estancia registrada.
  </div>
</template>
