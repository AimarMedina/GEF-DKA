<script setup>
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
<AlumnoSeguimiento :estanciaId="estanciaIdNum" />
</template>
