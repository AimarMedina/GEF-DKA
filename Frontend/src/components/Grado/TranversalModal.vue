<script setup>
import { ref, watch } from 'vue'
import api from '@/services/api.js'
import ConfirmarEliminar from '../ConfirmarEliminar.vue'

const props = defineProps({
  show: Boolean
})

const emit = defineEmits(['close'])

const transversales = ref([])
const cargando = ref(false)
const nuevaTransversal = ref('')
const editando = ref(null)
const transversalEditada = ref('')
const eliminarModal = ref(false)
const transversalEliminar = ref(null)

watch(() => props.show, async (val) => {
  if (val) {
    await cargarTransversales()
    nuevaTransversal.value = ''
    editando.value = null
  }
})

async function cargarTransversales() {
  cargando.value = true
  try {
    const res = await api.get('/api/transversales')
    transversales.value = res.data
  } catch (e) {
    console.error('Error cargando transversales:', e)
    alert('Error al cargar las transversales')
  } finally {
    cargando.value = false
  }
}

async function crearTransversal() {
  if (!nuevaTransversal.value.trim()) {
    alert('La descripción es obligatoria')
    return
  }

  try {
    await api.post('/api/transversales', 
      { descripcion: nuevaTransversal.value }
    )
    
    nuevaTransversal.value = ''
    await cargarTransversales()
  } catch (e) {
    console.error('Error creando transversal:', e)
    alert('Error al crear la transversal')
  }
}

function iniciarEdicion(transversal) {
  editando.value = transversal.id
  transversalEditada.value = transversal.descripcion
}

function cancelarEdicion() {
  editando.value = null
  transversalEditada.value = ''
}

async function guardarEdicion(id) {
  if (!transversalEditada.value.trim()) {
    alert('La descripción es obligatoria')
    return
  }

  try {
    await api.put(`/api/transversales/${id}`,
      { descripcion: transversalEditada.value }
    )
    
    editando.value = null
    transversalEditada.value = ''
    await cargarTransversales()
  } catch (e) {
    console.error('Error actualizando transversal:', e)
    alert('Error al actualizar la transversal')
  }
}

function abrirEliminarModal(transversal) {
  transversalEliminar.value = transversal
  eliminarModal.value = true
}

async function confirmarEliminar(confirmado) {
  if (confirmado && transversalEliminar.value) {
    await eliminarTransversal(transversalEliminar.value.id)
  }
  eliminarModal.value = false
  transversalEliminar.value = null
}

async function eliminarTransversal(id) {
  try {
    await api.delete(`/api/transversales/${id}`)
    
    await cargarTransversales()
  } catch (e) {
    console.error('Error eliminando transversal:', e)
    alert(e.response?.data?.message || 'Error al eliminar la transversal')
  }
}
</script>

<template>
  <div v-if="show" class="modal fade show d-block" style="background: rgba(0,0,0,.5)" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header bg-indigo text-white">
          <h5 class="modal-title">
            <i class="bi"></i> Gestión de Competencias Transversales
          </h5>
          <button class="btn-close btn-close-white" @click="emit('close')"></button>
        </div>

        <div class="modal-body">
          <!-- Formulario para crear nueva transversal -->
          <div class="card mb-3">
            <div class="card-body">
              <h6 class="card-title">Nueva Competencia Transversal</h6>
              <div class="input-group">
                <input 
                  type="text" 
                  class="form-control" 
                  v-model="nuevaTransversal"
                  placeholder="Descripción de la competencia transversal"
                  @keyup.enter="crearTransversal"
                />
                <button 
                  class="btn btn-success" 
                  @click="crearTransversal"
                  :disabled="!nuevaTransversal.trim()"
                >
                  <i class="bi bi-plus-lg"></i> Añadir
                </button>
              </div>
            </div>
          </div>

          <!-- Lista de transversales -->
          <div v-if="cargando" class="text-center py-4">
            <div class="spinner-border text-indigo"></div>
            <p class="mt-2">Cargando transversales...</p>
          </div>

          <div v-else-if="transversales.length" class="table-responsive">
            <table class="table table-hover">
              <thead class="table-light">
                <tr>
                  <th style="width: 80px;">ID</th>
                  <th>Descripción</th>
                  <th style="width: 120px;" class="text-center">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="t in transversales" :key="t.id">
                  <td class="align-middle">{{ t.id }}</td>
                  <td class="align-middle">
                    <!-- Modo edición -->
                    <div v-if="editando === t.id" class="input-group input-group-sm">
                      <input 
                        type="text" 
                        class="form-control" 
                        v-model="transversalEditada"
                        @keyup.enter="guardarEdicion(t.id)"
                        @keyup.esc="cancelarEdicion"
                      />
                      <button class="btn btn-success btn-sm" @click="guardarEdicion(t.id)">
                        <i class="bi bi-check-lg"></i>
                      </button>
                      <button class="btn btn-secondary btn-sm" @click="cancelarEdicion">
                        <i class="bi bi-x-lg"></i>
                      </button>
                    </div>
                    <!-- Modo vista -->
                    <span v-else>{{ t.descripcion }}</span>
                  </td>
                  <td class="align-middle text-center">
                    <div class="btn-group btn-group-sm">
                      <button 
                        v-if="editando !== t.id"
                        class="btn btn-outline-primary" 
                        @click="iniciarEdicion(t)"
                        title="Editar"
                      >
                        <i class="bi bi-pencil"></i>
                      </button>
                      <button 
                        v-if="editando !== t.id"
                        class="btn btn-outline-danger" 
                        @click="abrirEliminarModal(t)"
                        title="Eliminar"
                      >
                        <i class="bi bi-trash"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div v-else class="alert alert-info text-center">
            <i class="bi bi-info-circle"></i> No hay competencias transversales registradas
          </div>
        </div>

        <div class="modal-footer">
          <button class="btn btn-secondary" @click="emit('close')">
            Cerrar
          </button>
        </div>
      </div>
    </div>

    <!-- Modal de confirmación de eliminación -->
    <ConfirmarEliminar
      :show="eliminarModal"
      :mensaje="`¿Seguro que deseas eliminar la competencia transversal '${transversalEliminar?.descripcion}'? Esta acción también eliminará todas las notas asociadas.`"
      @confirm="confirmarEliminar"
      @close="eliminarModal = false"
    />
  </div>
</template>

<style scoped>
.modal.show {
  display: block;
}
</style>