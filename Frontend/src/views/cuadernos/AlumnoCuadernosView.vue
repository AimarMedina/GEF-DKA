<script setup>
import AlumnoCuadernos from '@/components/Cuaderno/AlumnoCuadernos.vue';
import NavBar from '../../components/Navbar.vue'

</script>

<template>
  <NavBar></NavBar>
  <div class="container mt-4">
    <h2>Mis Cuadernos</h2>
    <AlumnoCuadernos />
  </div>
</template>
