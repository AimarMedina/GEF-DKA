<script setup>
import NotasAlumno from "@/components/Alumno/NotasAlumno.vue";
import Navbar from "@/components/Navbar.vue";

</script>

<template>
  <Navbar />

 
    <NotasAlumno
      tipo="Competencias transversales"
      :competencias="[
        { id: 1, descripcion: 'Programa y prueba aplicaciones' },
        { id: 2, descripcion: 'Desarrolla el frontend...' },
        { id: 3, descripcion: 'Desarrolla el backend...' },
      ]"
    />

    <NotasAlumno
      tipo="Competencias técnicas"
      :competencias="[
        { id: 1, descripcion: 'Programa y prueba aplicaciones' },
        { id: 2, descripcion: 'Desarrolla el frontend...' },
        { id: 3, descripcion: 'Desarrolla el backend...' },
      ]"
    />
  <!-- </div> -->
</template>
