<script setup>
import { ref } from 'vue'

const mostrarPopup = ref(false)
const tipo = ref('info')
const titulo = ref('Notificación')
const mensaje = ref('')

const mostrar = (nuevoTipo, nuevoTitulo, nuevoMensaje) => {
  tipo.value = nuevoTipo || 'info'
  titulo.value = nuevoTitulo || 'Notificación'
  mensaje.value = nuevoMensaje || ''
  mostrarPopup.value = true
}

const cerrar = () => {
  mostrarPopup.value = false
}

defineExpose({
  mostrar,
  cerrar,
  mostrarPopup
})
</script>

<template>
  <teleport to="body" v-if="mostrarPopup">
    <!-- Sirva para poner el fondo mas oscuro -->
    <div 
      class="popup-overlay" 
      @click="cerrar"
      style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.5); display: flex; align-items: center; justify-content: center; z-index: 9999; animation: fadeIn 0.3s ease-in;"
    >
      <!-- Contenedor -->
      <div 
        class="popup-container"
        @click.stop
        style="background-color: white; border-radius: 8px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3); max-width: 90%; width: 100%; max-height: 80vh; overflow-y: auto; animation: slideIn 0.3s ease-out;"
      >
        <div 
          class="popup-header p-4 text-white d-flex align-items-center justify-content-between bg-primary"
        >
          <div class="d-flex align-items-center gap-3">
            <i class="bi bi-info-circle-fill" style="font-size: 1.5rem;"></i>
            <h5 class="mb-0">{{ titulo }}</h5>
          </div>
          <button 
            type="button" 
            class="btn-close btn-close-white" 
            @click="cerrar"
            aria-label="Cerrar"
          ></button>
        </div>

        <!-- Aqui sale el err msj, lo de arriba es el 'header' -->
        <div class="popup-body p-4">
          <p class="mb-0" v-if="typeof mensaje === 'string'">{{ mensaje }}</p>
          <div v-else v-html="mensaje"></div>
        </div>

        <!-- Footer con botones -->
        <div class="popup-footer p-4 border-top d-flex justify-content-end gap-2">
          <button 
            type="button" 
            class="btn btn-primary"
            @click="cerrar"
          >
            <i class="bi bi-check-circle me-2"></i>Cerrar
          </button>
        </div>
      </div>
    </div>
  </teleport>
</template>


<style scoped>
@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

@keyframes slideIn {
  from {
    transform: translateY(-50px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

.popup-container {
  min-width: 300px;
  animation: slideIn 0.3s ease-out;
}

.popup-header {
  border-radius: 8px 8px 0 0;
}

.popup-body {
  color: #333;
  font-size: 0.95rem;
  line-height: 1.6;
}

.popup-footer {
  border-top: 1px solid #e9ecef;
}

.btn-close-white {
  filter: brightness(0) invert(1);
}
</style>
