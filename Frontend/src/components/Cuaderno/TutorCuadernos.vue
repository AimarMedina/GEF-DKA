<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useUserStore } from '@/stores/userStore';

const userStore = useUserStore();
const tutorId = userStore.user?.ID;

const entregas = ref([]);
const mensaje = ref('');

async function fetchEntregas() {
  if (!tutorId) return;

  try {
    // Obtener los grados que tutorea
    const { data: grados } = await axios.get(`http://localhost:8000/api/tutor/${tutorId}/grados`);
    entregas.value = [];

    // Obtener entregas de cada grado
    for (const grado of grados) {
      const { data: res } = await axios.get(`http://localhost:8000/api/grado/${grado.ID}/entregas`);
      // Añadir info del alumno para mostrar en la tabla
      res.forEach(e => {
        e.alumnoEntrega = e.alumnoEntrega || [];
      });
      entregas.value.push(...res);
    }

  } catch (err) {
    console.error(err);
    mensaje.value = 'Error cargando entregas';
  }
}

async function guardarNota(entregaAlumno) {
  try {
    await axios.post('http://localhost:8000/api/nota-cuaderno', {
      ID_Cuaderno: entregaAlumno.ID,
      Nota: entregaAlumno.nota?.Nota || 0
    });
    alert('Nota guardada correctamente');
  } catch (err) {
    console.error(err);
    alert('Error al guardar la nota');
  }
}

onMounted(fetchEntregas);
</script>

<template>
  <div>
    <h3>Cuadernos de Alumnos</h3>
    <div v-if="mensaje">{{ mensaje }}</div>

    <table class="table table-striped" v-if="entregas.length">
      <thead>
        <tr>
          <th>Alumno</th>
          <th>PDF</th>
          <th>Nota</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="entrega in entregas" :key="entrega.ID">
          <td>{{ entrega.alumnoEntrega[0]?.alumno?.usuario?.nombre ?? '—' }}</td>
          <td>
            <a v-if="entrega.alumnoEntrega[0]?.URL_Cuaderno"
               :href="entrega.alumnoEntrega[0].URL_Cuaderno"
               target="_blank">
              Ver PDF
            </a>
            <span v-else>No entregado</span>
          </td>
          <td>
            <input v-model="entrega.alumnoEntrega[0].nota.Nota" type="number" min="0" max="10" />
            <button @click="guardarNota(entrega.alumnoEntrega[0])" class="btn btn-success btn-sm">Guardar</button>
          </td>
        </tr>
      </tbody>
    </table>

    <div v-else>No hay entregas asignadas para tus grados.</div>
  </div>
</template>
