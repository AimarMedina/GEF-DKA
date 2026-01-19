<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  visible: Boolean,
  editing: Object
})
const emit = defineEmits(['close','save'])

// Form reactivo incluye ID
const form = ref({
  ID: null,
  Fecha: '',
  Hora: '',
  Accion_seguimiento: '',
  Seguimiento_actividad: ''
})

// Cada vez que editing cambia, copiamos valores a form
watch(
  () => props.editing,
  (val) => {
    if (val) {
      form.value = { ...val } // ✅ incluye ID
    } else {
      form.value = {
        ID: null,
        Fecha: '',
        Hora: '',
        Accion_seguimiento: '',
        Seguimiento_actividad: ''
      }
    }
  },
  { immediate: true }
)

// Guardar: envía todos los campos incluyendo ID
function guardar() {
  emit('save', { ...form.value })
}
</script>

<template>
  <div v-if="visible" class="modal fade show d-block" style="background-color: rgba(0,0,0,0.5);">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">{{ form.ID ? 'Modificar seguimiento' : 'Crear nuevo seguimiento' }}</h5>
          <button type="button" class="btn-close" @click="$emit('close')"></button>
        </div>
        <div class="modal-body">
          <div class="mb-2">
            <label>Fecha</label>
            <input type="date" v-model="form.Fecha" class="form-control" />
          </div>
          <div class="mb-2">
            <label>Hora</label>
            <input type="time" v-model="form.Hora" class="form-control" />
          </div>
          <div class="mb-2">
            <label>Acción</label>
            <input type="text" v-model="form.Accion_seguimiento" class="form-control" />
          </div>
          <div class="mb-2">
            <label>Actividad</label>
            <input type="text" v-model="form.Seguimiento_actividad" class="form-control" />
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" @click="$emit('close')">Cancelar</button>
          <button class="btn btn-primary" @click="guardar">{{ form.ID ? 'Guardar cambios' : 'Crear' }}</button>
        </div>
      </div>
    </div>
  </div>
</template>
