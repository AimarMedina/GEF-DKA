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

  <div class="container-fluid">
    <div class="row">
      <AlumnosList ref="alumnosListRef" @seleccionarAlumno="alumnoSeleccionado = $event"
        :endpoint="`/api/tutores/${tutorId}/alumnos`" />

      <AlumnoDatos :alumno="alumnoSeleccionado" @estanciaCreada="recargarAlumnos" />

    </div>
  </div>
</template>
