<script setup>

import Navbar from '@/components/Navbar.vue';
import AlumnoSeguimiento from '@/components/tutor/AlumnoSeguimiento.vue'

const props = defineProps({
  estanciaId: {
    type: Number,
    required: true
  }
})



</script>

<template>
  <Navbar></Navbar>
<AlumnoSeguimiento :estanciaId="estanciaId" />
</template>
