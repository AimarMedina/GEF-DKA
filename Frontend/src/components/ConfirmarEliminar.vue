<script setup>
import { defineProps, defineEmits, watch, ref } from 'vue'

const props = defineProps({
  mensaje: {
    type: String,
    required: true
  },
  show: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['confirm','close'])

// Control interno para mostrar el modal
const isVisible = ref(props.show)

// Sincronizar prop con estado interno
watch(() => props.show, (val) => {
  isVisible.value = val
})

// Función para cerrar modal
function closeModal() {
  isVisible.value = false
  emit('close', false)
}

// Funciones de confirmación
function aceptar() {
  emit('confirm', true)
  closeModal()
}

function cancelar() {
  emit('confirm', false)
  closeModal()
}
</script>

<template>
  <div class="modal fade show" tabindex="-1" style="display: block;" v-if="isVisible">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow-lg">
        <div class="modal-header bg-purple text-white">
          <h5 class="modal-title">Confirmación</h5>
          <button type="button" class="btn-close btn-close-white" @click="cancelar"></button>
        </div>
        <div class="modal-body">
          <p>{{ mensaje }}</p>
        </div>
        <div class="modal-footer">
          <button class="btn btn-purple text-white" @click="aceptar">Aceptar</button>
          <button class="btn btn-secondary" @click="cancelar">Cancelar</button>
        </div>
      </div>
    </div>
  </div>
  <div v-if="show" class="modal-backdrop fade show"></div>
</template>

<style scoped>
.bg-purple {
  background-color: #811C5E;
}

.btn-purple {
  background-color: #811C5E;
  border: none;
}

.btn-purple:hover {
  background-color: #A22674;
}
</style>
