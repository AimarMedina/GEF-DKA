<script setup>
import { ref, onMounted, reactive } from 'vue'
import { useUserStore } from '@/stores/userStore'
import FormEntregaModal from './FormEntregaModal.vue'
import api from '@/services/api.js'

const userStore = useUserStore()
const tutorId = userStore.user?.id

const entregasPorGrado = ref([])
const mensaje = ref('')

// Modal
const crearModalVisible = ref(false)
const gradoSeleccionado = ref(null)

function abrirCrearEntregaModal(grado) {
  console.log('Abriendo modal para grado:', grado)
  gradoSeleccionado.value = grado
  crearModalVisible.value = true
}
// Manejar cuando se guarda una nueva entrega
function onEntregaGuardada(nuevaEntrega) {
  console.log('Nueva entrega guardada:', nuevaEntrega)
  
  // Encontrar el bloque del grado y agregar la entrega
  const bloque = entregasPorGrado.value.find(b => b.grado.id === nuevaEntrega.id_grado)
  if (bloque) {
    // Inicializar alumno_entrega si no existe
    const entregaCompleta = {
      ...nuevaEntrega,
      alumno_entrega: nuevaEntrega.alumno_entrega || []
    }
    bloque.entregas.push(entregaCompleta)
    console.log('Entrega agregada al bloque:', bloque)
  } else {
    console.error('No se encontró el bloque para el grado:', nuevaEntrega.id_grado)
  }
  
  // Cerrar modal
  crearModalVisible.value = false
}
// Cargar entregas
async function fetchEntregas() {
  if (!tutorId) return
  try {
    const { data: grados } = await api.get(`/api/tutor/${tutorId}/grados`)
    entregasPorGrado.value = []

    for (const grado of grados) {
      const { data: entregas } = await api.get(`/api/grado/${grado.id}/entregas`, {
        params: { tutor_id: tutorId }
      })

      entregasPorGrado.value.push({
        grado,
        esTutorPrincipal: grado.id_tutor === tutorId,
        entregas
      })
    }
    console.log('Entregas cargadas:', entregasPorGrado.value)
  } catch (err) {
    console.error(err)
    mensaje.value = 'Error cargando entregas'
  }
}

// Guardar observaciones y feedback
async function guardarObservacionYFeedback(alumnoEntrega) {
  try {
    await api.post('/api/observacionesCuadernoAlumno', {
      ID_Cuaderno: alumnoEntrega.id,
      Observaciones: alumnoEntrega.Observaciones ?? '',
      Feedback: alumnoEntrega.Feedback ?? null,
    })
    alert('Observaciones y Feedback guardados correctamente')
  } catch (err) {
    console.error(err)
    alert('Error al guardar observaciones y feedback')
  }
}

// Eliminar entrega
async function eliminarEntrega(entregaId, gradoId) {
  if (!confirm('¿Seguro que quieres eliminar esta entrega?')) return

  try {
    await api.delete(`/api/grado/${gradoId}/entregas/${entregaId}`)
    const bloque = entregasPorGrado.value.find(b => b.grado.id === gradoId)
    if (bloque) bloque.entregas = bloque.entregas.filter(e => e.id !== entregaId)
    alert('Entrega eliminada correctamente')
  } catch (err) {
    console.error(err)
    alert('Error al eliminar la entrega')
  }
}

onMounted(fetchEntregas)
</script>

<template>
  <div>
    <h3>Cuadernos de Alumnos</h3>

    <div v-if="mensaje" class="text-danger mb-3">{{ mensaje }}</div>

    <div v-for="bloque in entregasPorGrado" :key="bloque.grado.id" class="mb-5">
      <div class="d-flex justify-content-between align-items-center mb-3" v-if="bloque.entregas.length">
        <h4>{{ bloque.grado.nombre }} — {{ bloque.grado.curso }}</h4>
        <button v-if="bloque.esTutorPrincipal" class="btn btn-primary btn-sm"
          @click="abrirCrearEntregaModal(bloque.grado)">
          Crear nueva entrega
        </button>
      </div>

      <div v-if="bloque.entregas">
        <div v-for="entrega in bloque.entregas" :key="entrega.id" class="card mb-4 shadow-sm">
          <div class="card-header bg-indigo d-flex justify-content-between align-items-start">
            <div>
              <h5 class="text-white mb-1">{{ entrega.Descripcion }}</h5>
              <small class="text-white">Fecha límite: {{ entrega.Fecha_Limite }}</small>
            </div>

            <div class="d-flex align-items-start gap-2">
              <span class="badge" :class="entrega.alumno_entrega.length ? 'bg-success' : 'bg-danger'">
                {{ entrega.alumno_entrega.length ? `Entregas: ${entrega.alumno_entrega.length}` : 'Sin entregas' }}
              </span>

              <button v-if="bloque.esTutorPrincipal" class="btn btn-danger btn-sm"
                @click="eliminarEntrega(entrega.id, bloque.grado.id)">
                Eliminar
              </button>
            </div>
          </div>

          <div class="card-body">
            <table class="table table-striped align-middle">
              <thead>
                <tr>
                  <th>Alumno</th>
                  <th class="text-center">PDF</th>
                  <th class="text-center">Feedback</th>
                  <th class="text-center">Observaciones</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="alumnoEntrega in entrega.alumno_entrega" :key="alumnoEntrega.id">
                  <td>{{ alumnoEntrega.alumno?.usuario?.nombre ?? '—' }}</td>
                  <td class="text-center">
                    <a v-if="alumnoEntrega.URL_Cuaderno"
                      :href="`/api/alumno/entregas/descargar/${alumnoEntrega.id}`" target="_blank">
                      Descargar PDF
                    </a>
                  </td>
                  <td>
                    <select class="form-select" v-model="alumnoEntrega.Feedback">
                      <option disabled value="">-- Selecciona --</option>
                      <option>Bien</option>
                      <option>Regular</option>
                      <option>Debe mejorar</option>
                    </select>
                  </td>
                  <td class="d-flex gap-2">
                    <textarea class="form-control" rows="2" v-model="alumnoEntrega.Observaciones"></textarea>
                    <button class="btn btn-outline-secondary btn-sm"
                      @click="guardarObservacionYFeedback(alumnoEntrega)">
                      Guardar
                    </button>
                  </td>
                </tr>
                <tr v-if="entrega.alumno_entrega.length === 0">
                  <td colspan="4" class="text-center text-muted">Ningún alumno ha entregado este cuaderno</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <p v-else class="text-muted">No hay entregas para este grado.</p>
    </div>

    <!-- Modal crear entrega -->
    <FormEntregaModal
    :visible="crearModalVisible"
    :grado="gradoSeleccionado"
    @close="crearModalVisible = false"
    @saved="onEntregaGuardada"
  />
  </div>
</template>
