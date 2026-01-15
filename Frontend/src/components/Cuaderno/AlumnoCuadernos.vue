<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useUserStore } from '@/stores/userStore';

const userStore = useUserStore();
const alumnoId = userStore.user?.ID;

const entregas = ref([]);
const archivo = ref(null);
const mensaje = ref('');

async function fetchEntregas() {
  if (!alumnoId) return;

  try {
    // Obtener el alumno para saber su grado
    const { data: alumno } = await axios.get(`http://localhost:8000/api/alumno/${alumnoId}`);
    const gradoId = alumno.ID_Grado;

    // Obtener las entregas del grado
    const { data } = await axios.get(`http://localhost:8000/api/grado/${gradoId}/entregas`);
    entregas.value = data;
  } catch (err) {
    console.error(err);
    mensaje.value = 'Error al cargar las entregas';
  }
}

async function subirPDF() {
  if (!archivo.value) return alert('Selecciona un archivo primero');

  const formData = new FormData();
  formData.append('cuaderno', archivo.value);

  try {
    await axios.post(`http://localhost:8000/api/alumno/${alumnoId}/subir-cuaderno`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });
    alert('Archivo subido correctamente');
    fetchEntregas(); // recargar la lista
    archivo.value = null;
  } catch (err) {
    console.error(err);
    alert('Error subiendo el PDF');
  }
}

onMounted(fetchEntregas);
</script>

<template>
  <div>
    <h3>Mis Cuadernos</h3>

    <div v-if="mensaje">{{ mensaje }}</div>

    <div v-for="entrega in entregas" :key="entrega.ID" class="mb-3 p-2 border rounded">
      <strong>Entrega ID: {{ entrega.ID }}</strong> | Fecha límite: {{ entrega.Fecha_Limite }}

      <div>
        <a v-if="entrega.alumnoEntrega[0]?.URL_Cuaderno"
           :href="entrega.alumnoEntrega[0].URL_Cuaderno"
           target="_blank">
          Ver PDF entregado
        </a>
        <span v-else>No entregado</span>
      </div>

      <div class="mt-2">
        <input type="file" accept="application/pdf" @change="e => archivo.value = e.target.files[0]" />
        <button @click="subirPDF" class="btn btn-primary btn-sm">Subir PDF</button>
      </div>
    </div>

    <div v-if="entregas.length === 0">No hay entregas asignadas para tu grado.</div>
  </div>
</template>
