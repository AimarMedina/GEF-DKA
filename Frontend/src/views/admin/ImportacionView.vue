<script setup>
import Navbar from '@/components/Navbar.vue';
import ImportarAlumnos from '@/components/Importacion/ImportarAlumnos.vue';
import ImportarAsignacion from '@/components/Importacion/ImportarAsignacion.vue';
</script>

<template>
  <Navbar />

  <div class="d-flex justify-content-center align-items-center gap-4 flex-wrap mt-4">
    <ImportarAlumnos />
    <ImportarAsignacion />
  </div>
</template>

<style scoped></style>
