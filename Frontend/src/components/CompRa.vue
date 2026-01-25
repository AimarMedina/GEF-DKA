<template>
  <div class="container py-4">

    <h2 class="mb-4 text-indigo fw-bold">
      Competencias vs RAs
    </h2>

    <div class="mb-4 col-12 col-md-4">
     <BuscadorSelect 
        v-model="gradoSeleccionado" 
        :options="grados" 
        label-key="nombre" 
        value-key="id"
        placeholder="Buscar grado..." 
        @change="cargarMatriz" 
      />
    </div>

    <div v-if="asignaturas.length" class="table-responsive">
      <table class="table table-bordered text-center align-middle mb-0">

        <thead class="table-indigo">
          <tr>
            <th rowspan="2">Asignatura</th>
            <th rowspan="2">Resultado de Aprendizaje (RA)</th>
            <th :colspan="competencias.length">Competencias</th>
          </tr>
          <tr>
            <th v-for="comp in competencias" :key="comp.id">
              {{ comp.descripcion }}
            </th>
          </tr>
        </thead>

        <tbody>
          <template v-for="asignatura in asignaturas" :key="asignatura.id">
            <tr v-for="(ra, i) in asignatura.ras" :key="ra.id">
              <td v-if="i === 0" :rowspan="asignatura.ras.length" class="fw-semibold text-start">
                {{ asignatura.nombre }}
              </td>

              <td class="text-start">
                <br>{{ ra.Descripcion }}
              </td>

              <td v-for="comp in competencias" :key="comp.id" class="cell-icon"
                @click="!ra.loading && toggleCompRa(asignatura, ra, comp)" @mouseenter="ra.hoverCompId = comp.id"
                @mouseleave="ra.hoverCompId = null">

                <!-- Loading -->
                <span v-if="ra.loading && ra.loadingCompId === comp.id"
                  class="spinner-border spinner-border-sm text-purple"></span>

                <!-- Hover dinámico -->
                <span v-else-if="ra.hoverCompId === comp.id">
                  {{ tieneCompetencia(ra, comp.id) ? '✕' : '✓' }}
                </span>

                <!-- Estado normal -->
                <span v-else-if="tieneCompetencia(ra, comp.id)">
                  ✓
                </span>
              </td>

            </tr>
          </template>
        </tbody>

      </table>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import api from '@/services/api.js'
import BuscadorSelect from '@/components/BuscadorSelect.vue'

const grados = ref([])
const gradoSeleccionado = ref('')
const competencias = ref([])
const asignaturas = ref([])



const tieneCompetencia = (ra, id) => {
    // 1. Si el RA no existe o no tiene la propiedad comp_ras, devolvemos false
    if (!ra || !ra.comp_ras) return false;

    // 2. Si comp_ras NO es un array (por si acaso viene null u otro dato), false
    if (!Array.isArray(ra.comp_ras)) return false;

    // 3. Ahora es seguro buscar
    return ra.comp_ras.some(c => c.ID_Comp === id);
}


const cargarMatriz = async () => {
  const { data } = await api.get(
    `/api/grado/${gradoSeleccionado.value}/matriz-competencias`
  )

  competencias.value = data.competencias
  asignaturas.value = data.asignaturas

  // después de cargar la matriz
  asignaturas.value.forEach(asig => {
    asig.ras.forEach(ra => {
      ra.hoverCompId = null;
      ra.loading = false;
      ra.loadingCompId = null;
    })
  })

}

async function toggleCompRa(asig, ra, comp) {
  if (!ra.comp_ras) ra.comp_ras = []

  const tiene = tieneCompetencia(ra, comp.id)

  // Optimistic update
  if (tiene) {
    ra.comp_ras = ra.comp_ras.filter(c => c.ID_Comp !== comp.id)
  } else {
    ra.comp_ras.push({ ID_Comp: comp.id, ID_Ra: ra.id, ID_Asignatura: asig.id })
  }

  // Marcar loading
  ra.loading = true
  ra.loadingCompId = comp.id

  try {
    await api.post('/api/compRa/create', {
      ID_Comp: comp.id,
      ID_Ra: ra.id,
      ID_Asignatura: asig.id
    })
  } catch (err) {
    console.error(err)
    // Revertir si falla
    if (tiene) {
      ra.comp_ras.push({ ID_Comp: comp.id, ID_Ra: ra.id, ID_Asignatura: asig.id })
    } else {
      ra.comp_ras = ra.comp_ras.filter(c => c.ID_Comp !== comp.id)
    }
  } finally {
    ra.loading = false
    ra.loadingCompId = null
  }
}

onMounted(async () => {
  const { data } = await api.get('/api/grados')
  grados.value = data.data
})



</script>

<style scoped>
td.cell-icon {
  width: 60px;
  height: 42px;
  cursor: pointer;
  vertical-align: middle;
  padding: 0;
}

.spinner-border.text-purple {
  border-color: #811C5E !important;
  border-top-color: transparent !important;
}
</style>
