<script setup>
import { ref, watch } from 'vue'
const props = defineProps({
  visible: Boolean,
  editing: Object
})
const emit = defineEmits(['close','save'])

const form = ref({
  ID: null,
  Fecha: '',
  Hora: '',
  Accion_seguimiento: '',
  Seguimiento_actividad: ''
})

watch(() => props.editing, (val) => {
  if (val) form.value = { ...val } // copia los datos incluyendo ID
}, { immediate: true })

function guardar() {
  emit('save', { ...form.value }) // envía ID al padre
}
</script>

<template>
  <div v-if="visible" class="modal fade show d-block" style="background-color: rgba(0,0,0,0.5);">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Editar seguimiento</h5>
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
          <button class="btn btn-primary" @click="guardar">Guardar cambios</button>
        </div>
      </div>
    </div>
  </div>
</template>
