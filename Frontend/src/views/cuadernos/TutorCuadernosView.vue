<script setup>
import CuadernosObservaciones from '@/components/Cuaderno/CuadernosObservaciones.vue'
import NotasCuaderno from './NotasCuaderno.vue';
import Navbar from '../../components/Navbar.vue';
import PopupNotificacion from '@/components/PopupNotificacion.vue'
import { ref } from 'vue'

const popupRef = ref(null)

</script>

<template>
  <Navbar></Navbar>
  <div class="container mt-4">
    <CuadernosObservaciones @notify="(tipo,titulo,mensaje) => popupRef?.value?.mostrar(tipo,titulo,mensaje)" />
    <NotasCuaderno @notify="(tipo,titulo,mensaje) => popupRef?.value?.mostrar(tipo,titulo,mensaje)" />
    <PopupNotificacion ref="popupRef" />
  </div>
</template>
