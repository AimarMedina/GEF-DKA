<script setup lang="ts">
import { onBeforeMount, ref, provide } from 'vue'
import { RouterView } from 'vue-router'
import { useUserStore } from './stores/userStore'
import PopupNotificacion from './components/PopupNotificacion.vue'

const userStore = useUserStore()
const popupRef = ref()

const mostrarNotificacion = (tipo: string, titulo: string, mensaje: string) => {
  if (popupRef.value) {
    popupRef.value.mostrar(tipo, titulo, mensaje)
  }
}

provide('mostrarNotificacion', mostrarNotificacion)

onBeforeMount(async () => {
  await userStore.getUser()
})
</script>

<template>
  <PopupNotificacion ref="popupRef" />
  
  <div
    v-if="!userStore.initialized"
    class="d-flex justify-content-center align-items-center mt-5 gap-2"
  >
    <span>Cargando</span>

    <ul class="carga">
      <li></li>
      <li></li>
      <li></li>
      <li></li>
      <li></li>
    </ul>
  </div>

  <RouterView v-else />
</template>


<style>
@keyframes loading {
  0% {
    transform: translateY(0);
    opacity: 0.4;
  }

  25% {
    transform: translateY(-1.5px);
    opacity: 0.7;
  }

  50% {
    transform: translateY(-3px);
    opacity: 1;
  }

  75% {
    transform: translateY(-1.5px);
    opacity: 0.7;
  }

  100% {
    transform: translateY(0);
    opacity: 0.4;
  }
}




.carga {
  display: inline-flex;
  margin: 0;
  padding: 0;
  list-style: none;
  gap: 6px;
}

.carga li {
  background-color: rgb(32, 80, 144);
  width: 6px;
  height: 6px;
  border-radius: 50%;
  animation: loading 0.6s linear infinite;
}

.carga li:nth-child(1) { animation-delay: 0s; }
.carga li:nth-child(2) { animation-delay: 0.1s; }
.carga li:nth-child(3) { animation-delay: 0.2s; }
.carga li:nth-child(4) { animation-delay: 0.3s; }
.carga li:nth-child(5) { animation-delay: 0.4s; }

li{
  cursor: pointer;
}
</style>
