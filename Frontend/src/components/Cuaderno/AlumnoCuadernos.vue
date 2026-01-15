<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useUserStore } from '@/stores/userStore';

const userStore = useUserStore();
const alumnoId = userStore.user?.id;

const entregas = ref([]);
const archivo = ref(null);
const mensaje = ref('');

async function fetchEntregas() {
  try {
    const res = await axios.get('http://localhost:8000/api/alumno/entregas', {
      headers: { Authorization: `Bearer ${userStore.token}` }
    });

    const data = res.data;

    // Asegurarse de que alumnoEntrega sea array
    data.forEach(entrega => {
      entrega.alumnoEntrega = entrega.alumnoEntrega || [];
    });

    entregas.value = data;

  } catch (err) {
    console.error(err);
    mensaje.value = 'Error al cargar las entregas';
  }
}

async function subirPDF(entregaId) {
  if (!archivo.value) return alert('Selecciona un archivo');

  const formData = new FormData();
  formData.append('cuaderno', archivo.value);
  formData.append('ID_Entrega', entregaId);
  formData.append('ID_Alumno', alumnoId);

  try {
    await axios.post('http://localhost:8000/api/alumno/entrega', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
        Authorization: `Bearer ${userStore.token}`
      }
    });
    alert('Archivo subido correctamente');
    archivo.value = null;
    fetchEntregas();
  } catch (err) {
    console.error(err);
    alert('Error al subir el archivo');
  }
}

function cambioArchivo(e) {
  archivo.value = e.target.files[0];
}

onMounted(fetchEntregas);
</script>

<template>
  <div>
    <h3>Mis Cuadernos</h3>
    <div v-if="mensaje">{{ mensaje }}</div>

    <div v-for="entrega in entregas" :key="entrega.id" class="mb-3 p-2 border rounded">
      <strong>Entrega ID: {{ entrega.id }}</strong> | Fecha límite: {{ entrega.Fecha_Limite }}

      <div class="mt-2">
        <template v-if="entrega.alumnoEntrega.length">
          <a v-if="entrega.alumnoEntrega[0].URL_Cuaderno"
             :href="entrega.alumnoEntrega[0].URL_Cuaderno"
             target="_blank">
            Ver PDF entregado
          </a>
          <span v-else>No entregado</span>
        </template>
        <span v-else>No entregado</span>
      </div>

      <div class="mt-2">
        <input type="file" accept="application/pdf" @change="cambioArchivo" />
        <button @click="subirPDF(entrega.id)" class="btn btn-primary btn-sm">Subir PDF</button>
      </div>
    </div>

    <div v-if="entregas.length === 0" class="mt-3">
      No hay entregas asignadas para tu grado.
    </div>
  </div>
</template>
