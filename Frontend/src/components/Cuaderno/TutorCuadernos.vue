<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useUserStore } from '@/stores/userStore';

const userStore = useUserStore();
const tutorId = userStore.user?.id;

const entregas = ref([]);
const mensaje = ref('');

async function fetchEntregas() {
  if (!tutorId) return;

  try {
    // Obtener los grados que tutorea
    const gradosResponse = await axios.get(`http://localhost:8000/api/tutor/${tutorId}/grados`);
    const grados = gradosResponse.data;

    entregas.value = [];

    // Obtener entregas de cada grado
    for (const grado of grados) {
      const resResponse = await axios.get(`http://localhost:8000/api/grado/${grado.id}/entregas`);
      const res = resResponse.data;

      // Mapear alumno_entrega a alumnoEntrega
      res.forEach(function(e) {
        e.alumnoEntrega = e.alumno_entrega || [];
        // Asegurar que cada entrega tenga una nota
        e.alumnoEntrega.forEach(a => {
          if (!a.nota) {
            a.nota = { Nota: 0 };
          }
        });
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
        <!-- Para cada entrega -->
        <tr v-for="entrega in entregas" :key="entrega.id">
          <td>
            <!-- Mostrar cada alumno de la entrega -->
            <span v-for="alumnoEntrega in entrega.alumnoEntrega" :key="alumnoEntrega.id">
              {{ alumnoEntrega.alumno?.usuario?.nombre ?? '—' }}
            </span>
          </td>
          <td>
            <span v-for="alumnoEntrega in entrega.alumnoEntrega" :key="alumnoEntrega.id">
              <a v-if="alumnoEntrega.URL_Cuaderno" :href="alumnoEntrega.URL_Cuaderno" target="_blank">
                Ver PDF
              </a>
              <span v-else>No entregado</span>
            </span>
          </td>
          <td>
            <span v-for="alumnoEntrega in entrega.alumnoEntrega" :key="alumnoEntrega.id">
              <input v-model="alumnoEntrega.nota.Nota" type="number" min="0" max="10" />
              <button @click="guardarNota(alumnoEntrega)" class="btn btn-success btn-sm">Guardar</button>
            </span>
          </td>
        </tr>
      </tbody>
    </table>

    <div v-else>No hay entregas asignadas para tus grados.</div>
  </div>
</template>
