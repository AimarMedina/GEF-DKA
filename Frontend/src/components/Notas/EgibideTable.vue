<script setup>
import { ref, watch } from 'vue'
import axios from 'axios'

const props = defineProps({
  egibide: Array,
  alumnoId: Number,
  puedeEditar: Boolean // true si es tutor/admin, false si es alumno
})

const notasEditable = ref([])

// Actualiza notasEditable cuando cambian las props
watch(
  () => props.egibide,
  (val) => {
    notasEditable.value = val.map(n => ({ ...n }))
  },
  { immediate: true }
)

async function guardarNota(nota) {
  try {
    const token = localStorage.getItem('token')
    await axios.post(
      `http://localhost:8000/api/alumnos/${props.alumnoId}/nota-egibide`,
      { id: nota.id, nota: nota.nota },
      { headers: { Authorization: `Bearer ${token}` } }
    )
    alert(`Nota de ${nota.asignatura?.nombre || 'Asignatura'} guardada correctamente`)
  } catch (err) {
    console.error(err)
    alert('Error al guardar la nota')
  }
}
</script>

<template>
<div class="mb-5">
  <h5>Asignaturas (Egibide)</h5>
  <div class="table-responsive">
    <table class="table table-striped table-bordered">
      <thead class="table-indigo text-white text-center text-md-start">
        <tr>
          <th>Asignatura</th>
          <th>Nota</th>
          <th v-if="puedeEditar">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(n, i) in notasEditable" :key="n.ID">
          <td class="text-center text-md-start">{{ n.asignatura?.nombre ?? 'Sin nombre' }}</td>
          
          <!-- Nota: badge si es alumno, input si puede editar -->
          <td class="text-center text-md-start">
            <template v-if="!puedeEditar">
              <span :class="{
                'badge bg-success': n.nota >= 5,
                'badge bg-danger text-white': n.nota < 5 && n.nota != null,
                'badge bg-warning text-dark': n.nota == null
              }">
                {{ n.nota ?? 'Pendiente' }}
              </span>
            </template>
            <template v-else>
              <input
                type="number"
                min="0"
                max="10"
                step="0.1"
                v-model.number="n.nota"
                class="form-control form-control-sm"
                :class="{
                  'border-success bg-success bg-opacity-10': n.nota >= 5,
                  'border-danger bg-danger bg-opacity-10': n.nota < 5 && n.nota !== null,
                  'border-warning bg-warning bg-opacity-10': n.nota === null
                }"
              />
            </template>
          </td>

          <td class="text-center text-md-start" v-if="puedeEditar">
            <button class="btn btn-sm btn-success" @click="guardarNota(n)">Guardar</button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>
</template>
