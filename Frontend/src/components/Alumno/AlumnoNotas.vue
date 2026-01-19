<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useUserStore } from '@/stores/userStore'

import CuardernosTable from '../Notas/CuardernosTable.vue'
import CompetenciasTable from '../Notas/CompetenciasTable.vue'
import TransversalesTable from '../Notas/TransversalesTable.vue'
import EgibideTable from '@/components/notas/EgibideTable.vue'

const notas = ref(null)
const mensaje = ref('')
const userStore = useUserStore()
const id = userStore.user?.id

async function cargarNotas() {
  try {
    const token = localStorage.getItem('token')
    const res = await axios.get(`http://localhost:8000/api/alumno/${id}/mis-notas`, {
      headers: { Authorization: `Bearer ${token}` }
    })
    notas.value = res.data || null
    console.log(notas.value);

  } catch (err) {
    console.error(err)
    mensaje.value = 'Error al cargar las notas'
  }
}

onMounted(cargarNotas)
</script>

<template>
  <div class="container mt-4">
    <h3 class="mb-4 text-center text-md-start">Mis Notas</h3>

    <div v-if="mensaje" class="alert alert-danger">{{ mensaje }}</div>

    <div v-else-if="notas">
      <div class="mb-4 p-3 bg-light rounded shadow-sm text-center text-md-start">
        <h4>{{ notas.usuario.nombre }} {{ notas.usuario.apellidos }}</h4>
        <p class="mb-0"><strong>Grado:</strong> {{ notas.grado.nombre }}</p>
      </div>

      <CuardernosTable :cuadernos="notas.cuadernos" :alumno-id="id" />
      <CompetenciasTable :competencias="notas.notas_competencias" />
      <TransversalesTable :transversales="notas.notas_transversales" />
      <EgibideTable :egibide="notas.notas_egibide" />
    </div>

    <div v-else>
      <p>No se encontraron notas para este alumno.</p>
    </div>
  </div>
</template>
