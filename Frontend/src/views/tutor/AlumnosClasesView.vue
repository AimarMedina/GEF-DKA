<script setup>
import { ref } from 'vue'
import Navbar from '@/components/Navbar.vue'
import AlumnosList from '@/components/AlumnosList.vue'
import AlumnoDatos from '@/components/Tutor/AlumnoDatos.vue'
import { useRoute } from 'vue-router'

const alumnoSeleccionado = ref(null)
const route = useRoute();

const tutorId = route.params.id;
const alumnosListRef = ref(null)

function recargarAlumnos() {
  alumnosListRef.value?.recargar()
}

</script>

<template>
  <Navbar />
  <h3 class="text-primary fw-semibold m-3 pb-2 bg-primary-subtle rounded-2 w-50">Alumnos de los grados en los que impartes clase</h3>

  <div class="container-fluid">
    <div class="row">
      <AlumnosList ref="alumnosListRef" @seleccionarAlumno="alumnoSeleccionado = $event"
        :endpoint="`/api/tutores/${tutorId}/alumnos`" />

      <AlumnoDatos :alumno="alumnoSeleccionado" @estanciaCreada="recargarAlumnos" />

    </div>
    <router-link :to="`/tutores/${tutorId}/alumnos`" class="btn btn-secondary mt-4">
        Alumnos de los grados en los que eres tutor
    </router-link>
  </div>


</template>
