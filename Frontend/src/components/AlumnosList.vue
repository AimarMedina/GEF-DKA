<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import Buscador from '@/components/Buscador.vue'
import { useUserStore } from '@/stores/userStore'
import AlumnoDatos from '@/components/Tutor/AlumnoDatos.vue'

const userStore = useUserStore()
const tutorId = userStore.user.id

const alumnosTutor = ref([])
const q = ref('')
const cargando = ref(false)
const alumnoSeleccionado = ref(null)

async function cargarAlumnos() {
  cargando.value = true
  try {
    const token = localStorage.getItem('token')
    const res = await axios.get(`http://localhost:8000/api/tutores/${tutorId}/alumnos`, {
      headers: { Authorization: `Bearer ${token}` }
    })
    alumnosTutor.value = res.data.data
  } catch (e) {
    console.error('Error cargando alumnos', e)
    alumnosTutor.value = []
  } finally {
    cargando.value = false
  }
}

function seleccionarAlumno(a) {
  alumnoSeleccionado.value = a
}

function onSearch(texto) {
  q.value = (texto || '').trim().toLowerCase()
}

const alumnosFiltrados = computed(() => {
  if (!q.value) return alumnosTutor.value
  return alumnosTutor.value.filter(a =>
    `${a.Nombre} ${a.Apellidos}`.toLowerCase().includes(q.value)
  )
})

onMounted(cargarAlumnos)
</script>

<template>
  <div class="row mt-3">
    <!-- Columna izquierda: lista de alumnos -->
    <div class="col-md-3">
      <Buscador tipo="Buscar Alumno" @search="onSearch" />

      <ul v-if="alumnosFiltrados.length" class="list-group mt-3 shadow-sm">
        <button
          class="list-group-item list-group-item-action bg-indigo text-white fw-semibold"
          @click="alumnoSeleccionado = null"
        >
          Alumnos
        </button>

        <li
          v-for="a in alumnosFiltrados"
          :key="a.ID_Usuario"
          class="list-group-item cursor-pointer"
          :class="{ ' bg-light text-dark': alumnoSeleccionado?.ID_Usuario === a.ID_Usuario }"
          @click="seleccionarAlumno(a)"
        >
          {{ a.Nombre }} {{ a.Apellidos }}
        </li>
      </ul>

      <div v-if="cargando" class="text-center mt-3">
        <div class="spinner-border text-indigo"></div>
      </div>

      <div v-if="!cargando && !alumnosFiltrados.length" class="text-muted text-center mt-3">
        No se encontraron alumnos
      </div>
    </div>

    <!-- Columna derecha: detalles y notas -->
    <div class="col-md-9">
      <AlumnoDatos v-if="alumnoSeleccionado" :alumno="alumnoSeleccionado" />
      <div v-else class="alert alert-info">
        Selecciona un alumno para ver los detalles
      </div>
    </div>
  </div>
</template>
