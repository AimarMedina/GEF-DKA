<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useUserStore } from '@/stores/userStore'

const userStore = useUserStore()
const tutorId = userStore.user?.id

const entregas = ref([])
const mensaje = ref('')

async function fetchEntregas() {
  if (!tutorId) return

  try {
    const gradosResponse = await axios.get(
      `http://localhost:8000/api/tutor/${tutorId}/grados`
    )
    console.log(gradosResponse.data);
    entregas.value = []

    for (const grado of gradosResponse.data) {
      const resResponse = await axios.get(
        `http://localhost:8000/api/grado/${grado.id}/entregas`
      )

      entregas.value.push(...resResponse.data)
    }
    console.log(entregas.value);
  } catch (err) {
    console.error(err)
    mensaje.value = 'Error cargando entregas'
  }
}

async function guardarObservacionYFeedback(alumnoEntrega) {
  console.log(alumnoEntrega);
  try {
    await axios.post('http://localhost:8000/api/observacionesCuadernoAlumno', {
      ID_Cuaderno: alumnoEntrega.id,
      Observaciones: alumnoEntrega.Observaciones ?? '',
      Feedback: alumnoEntrega.Feedback ?? null,
    });

    alert('Observaciones y Feedback guardados correctamente');
  } catch (err) {
    console.error(err);
    alert('Error al guardar observaciones y feedback');
  }
}



onMounted(fetchEntregas)
</script>

<template>
  <div>
    <h3>Cuadernos de Alumnos</h3>

    <div v-if="mensaje" class="text-danger mb-3">
      {{ mensaje }}
    </div>

    <div v-if="entregas.length">
      <div v-for="entrega in entregas" :key="entrega.id" class="card mb-4 shadow-sm">
        <!-- HEADER -->
        <div class="card-header bg-indigo d-flex justify-content-between align-items-center">
          <div>
            <h5 class="text-white mb-1">
              {{ entrega.Descripcion }}
            </h5>
            <small class="text-white">
              Fecha límite: {{ entrega.Fecha_Limite }}
            </small>
          </div>

          <span class="badge" :class="entrega.alumno_entrega.length ? 'bg-success' : 'bg-danger'">
            {{ entrega.alumno_entrega.length
              ? `Entregas: ${entrega.alumno_entrega.length}`
              : 'Sin entregas' }}
          </span>
        </div>

        <!-- BODY -->
        <div class="card-body pb-2">
          <div class="table-responsive">
            <table class="table table-striped mb-0 align-middle">
              <thead class="table-light">
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
                      :href="`http://localhost:8000/api/alumno/entregas/descargar/${alumnoEntrega.id}`" target="_blank"
                      class="link-primary">
                      Descargar PDF
                    </a>

                  </td>

                  <td class="text-center">
                    <select class="form-select" v-model="alumnoEntrega.Feedback">
                      <option disabled value="">-- Selecciona --</option>
                      <option>Bien</option>
                      <option>Regular</option>
                      <option>Debe mejorar</option>
                    </select>
                  </td>

                  <td class="text-center d-flex gap-2">
                    <textarea class="form-control" rows="2" v-model="alumnoEntrega.Observaciones"
                      placeholder="Escribe tus observaciones...">
                  </textarea>
                    <button class="btn btn-outline-secondary btn-sm"
                      @click="guardarObservacionYFeedback(alumnoEntrega)">
                      Guardar
                    </button>
                  </td>


                </tr>

                <tr v-if="entrega.alumno_entrega.length === 0">
                  <td colspan="4" class="text-center text-muted py-3">
                    Ningún alumno ha entregado este cuaderno
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div v-else class="text-center text-muted mt-3">
      No hay entregas asignadas para tus grados.
    </div>

  </div>
</template>
