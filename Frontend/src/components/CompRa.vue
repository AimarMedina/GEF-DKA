<template>
  <div class="container py-4">

    <h2 class="mb-4 text-indigo fw-bold">
      Competencias vs RAs
    </h2>

    <div class="mb-4 col-12 col-md-4">
      <select v-model="gradoSeleccionado" class="form-select" @change="cargarMatriz">
        <option disabled value="">Selecciona un grado</option>
        <option v-for="g in grados" :key="g.id" :value="g.id">
          {{ g.nombre }}
        </option>
      </select>
    </div>

    <div v-if="asignaturas.length" class="table-responsive">
      <table class="table table-bordered table-hover text-center align-middle mb-0">

        <thead class="table-indigo">
          <tr>
            <th rowspan="2">Asignatura</th>
            <th rowspan="2">Resultado de Aprendizaje (RA)</th>
            <th :colspan="competencias.length">Competencias</th>
          </tr>
          <tr>
            <th v-for="comp in competencias" :key="comp.id">
              C{{ comp.id }}<br>{{ comp.descripcion }}
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

              <td v-for="comp in competencias" :key="comp.id">
                {{ tieneCompetencia(ra, comp.id) ? '✓' : '—' }}
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

  const grados = ref([])
  const gradoSeleccionado = ref('')
  const competencias = ref([])
  const asignaturas = ref([])

  const tieneCompetencia = (ra, id) =>
    ra.comp_ras?.some(c => c.ID_Comp === id)

  const cargarMatriz = async () => {
    const { data } = await axios.get(
      `http://localhost:8000/api/grado/${gradoSeleccionado.value}/matriz-competencias`
    )

    competencias.value = data.competencias
    asignaturas.value = data.asignaturas
    
  }

  onMounted(async () => {
    const { data } = await axios.get('http://localhost:8000/api/grados')
    grados.value = data.data
  })
</script>
