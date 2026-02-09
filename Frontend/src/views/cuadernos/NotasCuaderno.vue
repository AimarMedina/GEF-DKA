<script setup>
import { onMounted } from 'vue'
import { useUserStore } from '@/stores/userStore'
import { useCuadernosStore } from '@/stores/cuadernos.store'

const emit = defineEmits(['notify'])

const userStore = useUserStore()
const tutorId = userStore.user?.id

const cuadernosStore = useCuadernosStore()
const alumnos = cuadernosStore.alumnos
const mensaje = cuadernosStore.mensaje
const savingId = cuadernosStore.savingId

async function fetchNotas() {
  try {
    await cuadernosStore.fetchNotasCuaderno(tutorId)
  } catch (err) {
    console.error(err)
    emit('notify', 'error', 'Error', 'Error cargando notas')
  }
}

async function guardarNota(alumno) {
  // Validaciones: nota debe existir y estar entre 0 y 10
  const nota = alumno?.nota_cuaderno?.Nota
  if (nota === null || nota === undefined || nota === '') {
    emit('notify', 'warning', 'Validación', 'Introduce una nota antes de guardar')
    return
  }
  const valor = Number(nota)
  if (Number.isNaN(valor) || valor < 0 || valor > 10) {
    emit('notify', 'warning', 'Validación', 'La nota debe ser un número entre 0 y 10')
    return
  }

  try {
    const resp = await cuadernosStore.guardarNotaCuaderno(alumno.ID_Usuario, valor)
    emit('notify', 'success', 'Éxito', 'Nota guardada correctamente')
    // store may merge response into alumnos
  } catch (err) {
    console.error('AxiosError', err)
    // Handle validation errors from backend (422)
    if (err.response && err.response.status === 422) {
      const data = err.response.data
      let detalle = ''
      if (data && data.errors) detalle = Object.values(data.errors).flat().join('; ')
      else if (data && data.message) detalle = data.message
      else detalle = 'Datos inválidos'
      emit('notify', 'error', 'Error de validación', detalle)
    } else if (err.response && err.response.data && err.response.data.message) {
      emit('notify', 'error', 'Error', err.response.data.message)
    } else {
      emit('notify', 'error', 'Error', 'Error al guardar la nota')
    }
  }
}

onMounted(fetchNotas)
</script>

<template>
  <div class="mt-5">
    <h3>Notas de Cuaderno</h3>

    <div v-if="mensaje" class="text-danger mb-3">
      {{ mensaje }}
    </div>

    <div v-if="alumnos.length" class="card shadow-sm">
      <div class="card-body">
        <table class="table table-striped align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Alumno</th>
              <th class="text-center" style="width: 140px">Nota</th>
              <th class="text-center" style="width: 120px">Acción</th>
            </tr>
          </thead>

          <tbody>
            <tr v-for="alumno in alumnos" :key="alumno.id || alumno.ID_Usuario">
              <td>
                {{ alumno.usuario?.nombre ?? '—' }}
              </td>

              <td class="text-center">
                <input
                  type="number"
                  min="0"
                  max="10"
                  step="0.25"
                  class="form-control text-center"
                  v-model="alumno.nota_cuaderno.Nota"
                />
              </td>

              <td class="text-center">
                <button
                  class="btn btn-outline-primary btn-sm"
                  :disabled="savingId === (alumno.ID_Usuario || alumno.id)"
                  @click="guardarNota(alumno)">
                  <span v-if="savingId === (alumno.ID_Usuario || alumno.id)" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                  Guardar
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div v-else class="text-muted">
      No hay alumnos asignados.
    </div>
  </div>
</template>
