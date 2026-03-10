<script setup>
import { ref, onMounted, watch } from 'vue'
import CrearSeguimientoModal from './CrearSeguimientoModal.vue'
import EditarSeguimientoModal from './EditarSeguimientoModal.vue'
import ConfirmarEliminar from '../ConfirmarEliminar.vue'
import api from '@/services/api.js'
import { useNotificacion } from '@/composables/useNotificacion'

const props = defineProps({
  estanciaId: {
    type: Number,
    required: true
  }
})

const { error } = useNotificacion()

// Datos y modales
const seguimientos = ref([])
const crearModalVisible = ref(false)
const editarModalVisible = ref(false)
const editing = ref(null)
const eliminarModalVisible = ref(false)
const seguimientoEliminar = ref(null)

// Cargar seguimientos
async function cargarSeguimientos() {

  if (!props.estanciaId) return

  try {
    const res = await api.get(
      `/api/estancia/${props.estanciaId}/seguimientos`
    )
    seguimientos.value = res.data
  } catch (err) {
    console.error(err)
  }
}

// Abrir modales
function abrirCrearModal() {
  crearModalVisible.value = true
}

function abrirEditarModal(s) {
  editing.value = { ...s } // copiar objeto completo
  editarModalVisible.value = true
}

function abrirEliminarModal(s) {
  seguimientoEliminar.value = s
  eliminarModalVisible.value = true
}


function confirmarEliminar(confirmado) {
  if (confirmado && seguimientoEliminar.value) {
    eliminarSeguimiento(seguimientoEliminar.value.id)
  }
  eliminarModalVisible.value = false
  seguimientoEliminar.value = null
}


// Guardar seguimiento (crear)
async function guardarNuevoSeguimiento(data) {

  try {
    const res = await api.post(
      `/api/seguimiento`,
      { ...data, ID_Estancia: props.estanciaId }
    )
    seguimientos.value.unshift(res.data)
    crearModalVisible.value = false
  } catch (err) {
    console.error(err)
    error('Error al guardar seguimiento', err.response?.data?.message || err.message)
  }
}

// Guardar seguimiento (editar)
async function guardarEdicionSeguimiento(data) {
  if (!data.id) {
    error('Error', 'No se puede editar: falta ID')
    return
  }

  try {
    const res = await api.put(
      `/api/seguimiento/${data.id}`,
      data
    )
    const i = seguimientos.value.findIndex(s => s.id === data.id)
    if (i !== -1) seguimientos.value[i] = res.data
    editarModalVisible.value = false
    editing.value = null
  } catch (err) {
    console.error(err)
    error('Error al actualizar', err.response?.data?.message || err.message)
  }
}

// Eliminar seguimiento
async function eliminarSeguimiento(id) {
  if (!id) {
    error('Error', 'No se puede eliminar: falta ID')
    return
  }


  try {
    const res = await api.delete(`/api/seguimiento/${id}`
    )
    seguimientos.value = seguimientos.value.filter(s => (s.id) !== id)
    console.log(res.data);

  } catch (err) {
    console.error(err)
    error('Error', 'Error al eliminar seguimiento')
  }
}

onMounted(cargarSeguimientos)
watch(() => props.estanciaId, cargarSeguimientos)
</script>

<template>
  <div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h3>Seguimientos de mis alumnos</h3>
      <button class="btn btn-secondary" @click="abrirCrearModal">
        <i class="bi bi-plus-lg"></i> Nuevo seguimiento
      </button>
    </div>

    <div v-if="seguimientos.length" class="table-responsive shadow-sm rounded">
      <table class="table table-striped table-hover align-middle">
        <thead class="table-indigo">
          <tr>
            <th>Fecha</th>
            <th>Hora</th>
            <th>Acción</th>
            <th>Actividad</th>
            <th class="text-center">Opciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="s in seguimientos" :key="s.id">
            <td>{{ s.Fecha }}</td>
            <td>{{ s.Hora }}</td>
            <td>{{ s.Accion_seguimiento }}</td>
            <td>{{ s.Seguimiento_actividad }}</td>
            <td class="d-flex gap-1 justify-content-center">
              <button class="btn btn-sm btn-warning" @click="abrirEditarModal(s)">Editar</button>
              <button class="btn btn-sm btn-danger" @click="abrirEliminarModal(s)">
                Eliminar
              </button>

            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <p v-else class="text-muted text-center py-3">
      No hay seguimientos
    </p>

    <!-- Modales -->
    <CrearSeguimientoModal :visible="crearModalVisible" @close="crearModalVisible = false"
      @save="guardarNuevoSeguimiento" />

    <EditarSeguimientoModal :visible="editarModalVisible" :editing="editing" @close="editarModalVisible = false"
      @save="guardarEdicionSeguimiento" />
    <ConfirmarEliminar :show="eliminarModalVisible" mensaje="¿Seguro que deseas eliminar este seguimiento?"
      @confirm="confirmarEliminar" @close="eliminarModalVisible = false" />

  </div>
</template>
