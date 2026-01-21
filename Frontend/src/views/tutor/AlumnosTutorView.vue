<script setup>
import { ref } from 'vue'
import Navbar from '@/components/Navbar.vue'
import AlumnosList from '@/components/AlumnosList.vue'
import AlumnoDatos from '@/components/Tutor/AlumnoDatos.vue'

const alumnoSeleccionado = ref(null)
</script>

<template>
  <Navbar />

  <div class="container-fluid">
    <div class="row">
      <AlumnosList @seleccionarAlumno="alumnoSeleccionado = $event" />
      <AlumnoDatos :alumno="alumnoSeleccionado" />
    </div>
  </div>
</template>
