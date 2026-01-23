<script setup>
import { ref, watch } from 'vue'
import api from '@/services/api.js'

const props = defineProps({
  egibide: Array,
  alumnoId: Number,
  puedeEditar: Boolean
})

const notasEditable = ref([])

watch(
  () => props.egibide,
  (val) => {
    notasEditable.value = (val ?? []).map(n => ({ ...n }))
  },
  { immediate: true }
)

async function guardarNota(nota) {
  console.log(nota);
  
  try {
    const token = localStorage.getItem('token')

    await api.post(
      `/api/alumnos/${props.alumnoId}/nota-egibide`,
      {
        id_asignatura: nota.id_asignatura,
        nota: nota.nota
      },
      {
        headers: { Authorization: `Bearer ${token}` }
      }
    )

    alert(`Nota de ${nota.asignatura} guardada correctamente`)
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
      <thead class="table-indigo text-white">
        <tr>
          <th>Asignatura</th>
          <th>Nota</th>
          <th v-if="puedeEditar">Acciones</th>
        </tr>
      </thead>

      <tbody>
        <tr v-for="n in notasEditable" :key="n.id_asignatura">
          <td>{{ n.asignatura }}</td>

          <td>
            <template v-if="!puedeEditar">
              <span :class="{
                'badge bg-success': n.nota >= 5,
                'badge bg-danger': n.nota < 5 && n.nota !== null,
                'badge bg-warning text-dark': n.nota === null
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

          <td v-if="puedeEditar">
            <button class="btn btn-sm btn-success" @click="guardarNota(n)">
              Guardar
            </button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>
</template>

