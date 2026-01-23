<script setup>

import Navbar from '@/components/Navbar.vue';
import AlumnoSeguimiento from '@/components/Tutor/AlumnoSeguimiento.vue'

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
