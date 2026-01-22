<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useUserStore } from '@/stores/userStore'

const userStore = useUserStore()
const tutorId = userStore.user?.id

const entregasPorGrado = ref([])
const mensaje = ref('')

async function fetchEntregas() {
  if (!tutorId) return

  try {
    const { data: grados } = await axios.get(
      `http://localhost:8000/api/tutor/${tutorId}/grados`
    )

    entregasPorGrado.value = []

    for (const grado of grados) {
      const { data: entregas } = await axios.get(
        `http://localhost:8000/api/grado/${grado.id}/entregas`,
        {
          params: {
            tutor_id: tutorId
          }
        }
      )



      entregasPorGrado.value.push({
        grado,
        esTutorPrincipal: grado.id_tutor === tutorId,
        entregas
      })
    }

    console.log(entregasPorGrado.value);

  } catch (err) {
    console.error(err)
    mensaje.value = 'Error cargando entregas'
  }
}

async function guardarObservacionYFeedback(alumnoEntrega) {
  try {
    await axios.post('http://localhost:8000/api/observacionesCuadernoAlumno', {
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

onMounted(fetchEntregas)
import { reactive } from 'vue'

const nuevaEntrega = reactive({
  Descripcion: '',
  Fecha_Limite: ''
})

async function crearNuevaEntrega(gradoId) {
  if (!nuevaEntrega.Descripcion || !nuevaEntrega.Fecha_Limite) {
    alert('Debes rellenar la descripción y la fecha límite')
    return
  }

  try {
    const { data } = await axios.post(
      `http://localhost:8000/api/grado/${gradoId}/entregas`,
      {
        Descripcion: nuevaEntrega.Descripcion,
        Fecha_Limite: nuevaEntrega.Fecha_Limite
      }
    )

    alert('Entrega creada correctamente')

    // Limpiar formulario
    nuevaEntrega.Descripcion = ''
    nuevaEntrega.Fecha_Limite = ''

    // Actualizar lista de entregas del grado
    const bloque = entregasPorGrado.value.find(b => b.grado.id === gradoId)
    if (bloque) {
      bloque.entregas.push(data)
    }

  } catch (err) {
    console.error(err)
    alert('Error al crear la entrega')
  }
}
async function eliminarEntrega(entregaId, gradoId) {
  if (!confirm('¿Seguro que quieres eliminar esta entrega? Esta acción no se puede deshacer.')) return

  try {
    await axios.delete(`http://localhost:8000/api/grado/${gradoId}/entregas/${entregaId}`)

    // Quitar entrega de la lista en Vue
    const bloque = entregasPorGrado.value.find(b => b.grado.id === gradoId)
    if (bloque) {
      bloque.entregas = bloque.entregas.filter(e => e.id !== entregaId)
    }

    alert('Entrega eliminada correctamente')
  } catch (err) {
    console.error(err)
    alert('Error al eliminar la entrega')
  }
}

</script>


<template>
  <div>
    <h3>Cuadernos de Alumnos</h3>

    <div v-if="mensaje" class="text-danger mb-3">
      {{ mensaje }}
    </div>

    <div v-for="bloque in entregasPorGrado" :key="bloque.grado.id" class="mb-5">
      <div class="d-flex justify-content-between align-items-center mb-3" v-if="bloque.entregas.length">
        <h4>
          {{ bloque.grado.nombre }} — {{ bloque.grado.curso }}
        </h4>
        <button v-if="bloque.esTutorPrincipal" class="btn btn-primary btn-sm"
          @click="bloque.showForm = !bloque.showForm">
          Crear nueva entrega
        </button>
      </div>

      <!-- ENTREGAS DEL GRADO -->
      <div v-if="bloque.entregas">
        <div v-for="entrega in bloque.entregas" :key="entrega.id" class="card mb-4 shadow-sm">
          <!-- HEADER -->
          <div class="card-header bg-indigo d-flex justify-content-between align-items-start">
            <div>
              <h5 class="text-white mb-1">{{ entrega.Descripcion }}</h5>
              <small class="text-white">
                Fecha límite: {{ entrega.Fecha_Limite }}
              </small>
            </div>

            <div class="d-flex align-items-start gap-2">
              <span class="badge" :class="entrega.alumno_entrega.length ? 'bg-success' : 'bg-danger'">
                {{ entrega.alumno_entrega.length ? `Entregas: ${entrega.alumno_entrega.length}` : 'Sin entregas' }}
              </span>

              <!-- Botón eliminar visible solo si eres tutor principal -->
              <button v-if="bloque.esTutorPrincipal" class="btn btn-danger btn-sm"
                @click="eliminarEntrega(entrega.id, bloque.grado.id)">
                Eliminar
              </button>
            </div>
          </div>


          <!-- BODY -->
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
                  <td>
                    {{ alumnoEntrega.alumno?.usuario?.nombre ?? '—' }}
                  </td>

                  <td class="text-center">
                    <a v-if="alumnoEntrega.URL_Cuaderno"
                      :href="`http://localhost:8000/api/alumno/entregas/descargar/${alumnoEntrega.id}`" target="_blank">
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
                  <td colspan="4" class="text-center text-muted">
                    Ningún alumno ha entregado este cuaderno
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <div v-if="bloque.esTutorPrincipal">

        <div v-if="bloque.showForm" class="card p-3 mb-3">
          <div class="mb-2">
            <label>Descripción</label>
            <input type="text" class="form-control" v-model="nuevaEntrega.Descripcion">
          </div>
          <div class="mb-2">
            <label>Fecha límite</label>
            <input type="date" class="form-control" v-model="nuevaEntrega.Fecha_Limite">
          </div>
          <button class="btn btn-success btn-sm" @click="crearNuevaEntrega(bloque.grado.id)">
            Guardar entrega
          </button>
        </div>
      </div>
      <p v-else class="text-muted">
        No hay entregas para este grado.
      </p>
    </div>
  </div>



</template>
