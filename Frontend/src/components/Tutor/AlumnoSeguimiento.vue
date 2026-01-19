<script setup>
import { ref, onMounted, watch } from 'vue'
import axios from 'axios'
import CrearSeguimientoModal from './CrearSeguimientoModal.vue'
import EditarSeguimientoModal from './EditarSeguimientoModal.vue'

const props = defineProps({
  estanciaId: {
    type: Number,
    required: true
  }
})

// Lista de seguimientos
const seguimientos = ref([])

// Modales
const crearModalVisible = ref(false)
const editarModalVisible = ref(false)
const editing = ref(null)

// Cargar seguimientos
async function cargarSeguimientos() {
  const token = localStorage.getItem('token')
  if (!props.estanciaId) return

  try {
    const res = await axios.get(
      `http://localhost:8000/api/estancia/${props.estanciaId}/seguimientos`,
      { headers: { Authorization: `Bearer ${token}` } }
    )
    console.log('seguimientos cargados:', res.data) // <-- revisa aquí
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
  // Mapear ID correctamente
  editing.value = {
    ID: s.ID ?? s.id,
    Fecha: s.Fecha,
    Hora: s.Hora,
    Accion_seguimiento: s.Accion_seguimiento,
    Seguimiento_actividad: s.Seguimiento_actividad
  }
  editarModalVisible.value = true
}

// Guardar seguimiento (crear)
async function guardarNuevoSeguimiento(data) {
  const token = localStorage.getItem('token')
  try {
    const res = await axios.post(
      `http://localhost:8000/api/seguimiento`,
      { ...data, ID_Estancia: props.estanciaId },
      { headers: { Authorization: `Bearer ${token}` } }
    )
    seguimientos.value.unshift(res.data)
    crearModalVisible.value = false
  } catch (err) {
    console.error(err)
    alert(err.response?.data?.message || err.message)
  }
}

// Guardar seguimiento (editar)
async function guardarEdicionSeguimiento(data) {
  const token = localStorage.getItem('token')
  if (!data.ID) {
    alert('No se puede editar: falta ID')
    return
  }
  try {
    const res = await axios.put(
      `http://localhost:8000/api/seguimiento/${data.ID}`,
      data,
      { headers: { Authorization: `Bearer ${token}` } }
    )
    const i = seguimientos.value.findIndex(s => s.ID === data.ID)
    if (i !== -1) seguimientos.value[i] = res.data
    editarModalVisible.value = false
    editing.value = null
  } catch (err) {
    console.error(err)
    alert(err.response?.data?.message || err.message)
  }
}

// Eliminar seguimiento
async function eliminarSeguimiento(id) {
  if (!id) {
    alert('No se puede eliminar: falta ID')
    return
  }

  const token = localStorage.getItem('token')
  try {
    await axios.delete(`http://localhost:8000/api/seguimiento/${Number(id)}`, 
      { headers: { Authorization: `Bearer ${token}` } }
    )
    seguimientos.value = seguimientos.value.filter(s => (s.ID ?? s.id) !== id)
  } catch (err) {
    console.error(err)
    alert('Error al eliminar seguimiento')
  }
}


onMounted(cargarSeguimientos)
watch(() => props.estanciaId, cargarSeguimientos)
</script>

<template>
<div class="container mt-4">
  <h3>Seguimiento</h3>

  <button class="btn btn-primary mb-3" @click="abrirCrearModal">
    Crear nuevo seguimiento
  </button>

  <div v-if="seguimientos.length">
    <table class="table table-bordered">
      <thead>
        <tr>
          <th>Fecha</th>
          <th>Hora</th>
          <th>Acción</th>
          <th>Actividad</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="s in seguimientos" :key="s.ID ?? s.id">
          <td>{{ s.Fecha }}</td>
          <td>{{ s.Hora }}</td>
          <td>{{ s.Accion_seguimiento }}</td>
          <td>{{ s.Seguimiento_actividad }}</td>
          <td>
            <button class="btn btn-sm btn-warning" @click="abrirEditarModal(s)">Editar</button>
            <button class="btn btn-sm btn-danger" 
                    @click="eliminarSeguimiento(s.ID ?? s.id)">Eliminar</button>
          </td>
        </tr>

      </tbody>
    </table>
  </div>

  <p v-else class="text-muted">No hay seguimientos</p>

  <!-- Modales -->
  <CrearSeguimientoModal
    :visible="crearModalVisible"
    @close="crearModalVisible=false"
    @save="guardarNuevoSeguimiento"
  />

  <EditarSeguimientoModal
    :visible="editarModalVisible"
    :editing="editing"
    @close="editarModalVisible=false"
    @save="guardarEdicionSeguimiento"
  />
</div>
</template>
