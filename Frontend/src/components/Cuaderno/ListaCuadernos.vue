<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
  idGrado: Number
});

const entregas = ref([]);

async function fetchEntregas() {
  try {
    const res = await axios.get(`http://localhost:8000/api/grado/${props.idGrado}/entregas`);
    entregas.value = res.data;
  } catch (err) {
    console.error(err);
  }
}

onMounted(() => {
  fetchEntregas();
});
</script>

<template>
  <table class="table table-striped">
    <thead>
      <tr>
        <th>Alumno</th>
        <th>PDF</th>
        <th>Nota</th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="e in entregas" :key="e.ID">
        <td>{{ e.alumnoEntrega[0]?.alumno?.usuario?.nombre ?? '—' }}</td>
        <td>
          <a v-if="e.alumnoEntrega[0]?.URL_Cuaderno" :href="e.alumnoEntrega[0].URL_Cuaderno" target="_blank">
            Ver PDF
          </a>
          <span v-else>No entregado</span>
        </td>
        <td>
          {{ e.alumnoEntrega[0]?.nota?.Nota ?? '—' }}
        </td>
      </tr>
    </tbody>
  </table>
</template>
