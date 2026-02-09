<script setup>
import { ref, computed } from 'vue'
import { useRoute } from 'vue-router'
import { useTutoresStore } from '@/stores/tutores.store'
import Navbar from '@/components/Navbar.vue'
import AlumnosList from '@/components/AlumnosList.vue'
import AlumnoDatos from '@/components/Tutor/AlumnoDatos.vue'

const route = useRoute()
const tutorId = route.params.id
const tutoresStore = useTutoresStore()
const alumnosListRef = ref(null)
const alumnoSeleccionado = ref(null)

// Estado para alternar entre 'tutor' y 'clase'
// Por defecto cargamos 'tutor' (puedes cambiarlo según prefieras)
const modoVista = ref('tutor') 

// El endpoint cambia dinámicamente según el modo seleccionado
const currentEndpoint = computed(() => {
  return modoVista.value === 'tutor' 
    ? `/api/tutores/${tutorId}/alumnos` 
    : `/api/tutores/${tutorId}/alumnos-clases`
})

const titulo = computed(() => {
  return modoVista.value === 'tutor'
    ? 'Alumnos de los grados los cuales tutorizas'
    : 'Alumnos de los grados en los que impartes clase'
})

function cambiarModo(nuevoModo) {
  modoVista.value = nuevoModo
  alumnoSeleccionado.value = null // Limpiamos la selección al cambiar de lista
  // El watch en AlumnosList detectará cambio de endpoint y recargará automáticamente
}

function estanciaCreada() {
  // Invalidar cache de ambos modos al crear una estancia
  tutoresStore.invalidateAllCache(tutorId)
  alumnosListRef.value?.recargarForzado()
}
</script>

<template>
  <Navbar />
  
  <h3 class="text-primary fw-semibold m-3 pb-2 bg-primary-subtle rounded-2 w-50">
    {{ titulo }}
  </h3>

  <div class="container-fluid">
    <div class="row">
      <AlumnosList 
        ref="alumnosListRef" 
        :key="currentEndpoint"
        @seleccionarAlumno="alumnoSeleccionado = $event"
        :endpoint="currentEndpoint" 
      />

      <AlumnoDatos 
        :alumno="alumnoSeleccionado" 
        @estanciaCreada="estanciaCreada" 
      />
    </div>

    <div class="d-flex gap-2 mt-4">
      <button 
        @click="cambiarModo('tutor')"
        class="btn" 
        :class="modoVista === 'tutor' ? 'btn-secondary disabled' : 'btn-primary'"
      >
        Alumnos de los grados en los que eres tutor
      </button>

      <button 
        @click="cambiarModo('clase')"
        class="btn" 
        :class="modoVista === 'clase' ? 'btn-secondary disabled' : 'btn-primary'"
      >
        Alumnos de los grados en los que das clase
      </button>
    </div>
  </div>
</template>