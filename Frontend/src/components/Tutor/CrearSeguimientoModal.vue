<script setup>
import { ref, watch } from 'vue'
const props = defineProps({
  visible: Boolean
})
const emit = defineEmits(['close','save'])

const form = ref({
  Fecha: '',
  Hora: '',
  Accion_seguimiento: '',
  Seguimiento_actividad: ''
})

watch(() => props.visible, (v) => {
  if (v) {
    form.value = { Fecha:'', Hora:'', Accion_seguimiento:'', Seguimiento_actividad:'' }
  }
})

function guardar() {
  emit('save', { ...form.value })
}
</script>

<template>
  <div v-if="visible" class="modal fade show d-block" style="background-color: rgba(0,0,0,0.5);">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Crear seguimiento</h5>
          <button type="button" class="btn-close" @click="$emit('close')"></button>
        </div>
        <div class="modal-body">
          <input type="date" v-model="form.Fecha" class="form-control mb-2" />
          <input type="time" v-model="form.Hora" class="form-control mb-2" />
          <input type="text" v-model="form.Accion_seguimiento" placeholder="Acción" class="form-control mb-2" />
          <input type="text" v-model="form.Seguimiento_actividad" placeholder="Actividad" class="form-control mb-2" />
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" @click="$emit('close')">Cancelar</button>
          <button class="btn btn-primary" @click="guardar">Crear</button>
        </div>
      </div>
    </div>
  </div>
</template>
