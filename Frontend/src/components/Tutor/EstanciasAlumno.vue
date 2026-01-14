<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import { useUserStore } from '@/stores/userStore';

const estancias = ref([]);
const userStore = useUserStore();
const alumnoId = userStore.user.id;

async function fetchEstancias() {
  try {
    const response = await axios.get(`http://localhost:8000/api/tutor/alumno/${alumnoId}/estancias`);
    estancias.value = response.data;
  } catch (error) {
    console.error(error);
  }
}

onMounted(() => {
  fetchEstancias();
});
</script>

<template>
  <div>
    <h3>Historial de estancias del alumno</h3>
    <table class="table table-striped">
      <thead>
        <tr>
          <th>Grado</th>
          <th>Empresa</th>
          <th>Instructor</th>
          <th>Tutor</th>
          <th>Fecha inicio</th>
          <th>Fecha fin</th>
          <th>Horario</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="estancia in estancias" :key="estancia.ID">
          <td>{{ estancia.alumno.grado?.Nombre }}</td>
          <td>{{ estancia.empresa?.Nombre }}</td>
          <td>{{ estancia.alumno.instructor?.user?.Nombre }} {{ estancia.alumno.instructor?.user?.Apellidos }}</td>
          <td>{{ estancia.alumno.tutor?.user?.Nombre }} {{ estancia.alumno.tutor?.user?.Apellidos }}</td>
          <td>{{ estancia.Fecha_inicio }}</td>
          <td>{{ estancia.Fecha_fin ?? '—' }}</td>
          <td>
            <div v-for="h in estancia.horario" :key="h.id"  :class="{ 'table-success': !estancia.Fecha_fin }">
              {{ h.Dias }} {{ h.Horario1 }} {{ h.Horario2 }}
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>