<script setup>
import TutorCuadernos from '@/components/Cuaderno/TutorCuadernos.vue';
import NavBar from '../../components/Navbar.vue'
import Navbar from '../../components/Navbar.vue';

</script>

<template>
  <Navbar></Navbar>
  <div class="container mt-4">
    <h2>Cuadernos de mis alumnos</h2>
    <TutorCuadernos />
  </div>
</template>
