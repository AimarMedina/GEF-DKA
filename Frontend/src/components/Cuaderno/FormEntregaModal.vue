<script setup>
import { reactive, watch } from 'vue'
import api from '@/services/api.js'


const props = defineProps({
  visible: Boolean,
  grado: Object
})

const emit = defineEmits(['close', 'saved'])

const nuevaEntrega = reactive({
  Descripcion: '',
  Fecha_Limite: ''
})

// Limpiar formulario al abrir
watch(() => props.visible, (val) => {
  if (val) {
    nuevaEntrega.Descripcion = ''
    nuevaEntrega.Fecha_Limite = ''
  }
})

async function guardarEntrega() {
  if (!nuevaEntrega.Descripcion || !nuevaEntrega.Fecha_Limite) {
    alert('Debes rellenar la descripción y la fecha límite')
    return
  }

  if (!props.grado?.id) {
    alert('Error: No se ha seleccionado un grado válido')
    return
  }

  try {
    const { data } = await api.post(
      `/api/grado/${props.grado.id}/entregas`,
      {
        Descripcion: nuevaEntrega.Descripcion,
        Fecha_Limite: nuevaEntrega.Fecha_Limite
      }
    )

    alert('Entrega creada correctamente')

    // Emitir la nueva entrega con el id del grado
    emit('saved', { ...data, id_grado: props.grado.id })
    emit('close')

  } catch (err) {
    console.error('Error completo:', err)
    console.error('Response data:', err.response?.data)
    alert(`Error al crear la entrega: ${err.response?.data?.message || err.message}`)
  }
}
</script>

<template>
  <div v-if="visible" class="modal fade show d-block" style="background-color: rgba(0,0,0,0.5);">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Crear nueva entrega — {{ grado?.nombre }}</h5>
          <button type="button" class="btn-close" @click="emit('close')"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Descripción</label>
            <input 
              type="text" 
              class="form-control" 
              v-model="nuevaEntrega.Descripcion"
              placeholder="Describe la entrega..."
            >
          </div>
          <div class="mb-3">
            <label class="form-label">Fecha límite</label>
            <input 
              type="date" 
              class="form-control" 
              v-model="nuevaEntrega.Fecha_Limite"
            >
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" @click="emit('close')">Cancelar</button>
          <button class="btn btn-success" @click="guardarEntrega">Guardar entrega</button>
        </div>
      </div>
    </div>
  </div>
</template>