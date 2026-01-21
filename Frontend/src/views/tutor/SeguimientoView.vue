<script setup>
  
import Navbar from '@/components/Navbar.vue';
import AlumnoSeguimiento from '@/components/tutor/AlumnoSeguimiento.vue'

const props = defineProps({
  estanciaId: {
    type: Number,
    required: true
  }
})

const estanciaIdNum = Number(props.estanciaId)

</script>

<template>
  <Navbar></Navbar>
<AlumnoSeguimiento :estanciaId="estanciaIdNum" />
</template>
