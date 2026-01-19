<template>
  <div class="container-fluid py-4">
    <h2 class="mb-4 fw-bold" style="color:#811C5E">
      Matriz de Competencias vs RAs
    </h2>

    <div class="matriz-wrapper shadow-sm rounded">
      <table class="table matriz-table align-middle text-center mb-0">
        <thead>
          <tr>
            <!-- ASIGNATURA -->
            <th class="sticky-asignatura text-start">
              Asignatura
            </th>

            <!-- RA -->
            <th class="sticky-ra text-start">
              RA
            </th>

            <!-- COMPETENCIAS -->
            <th
  v-for="comp in competencias"
  :key="comp.id"
  class="competencia-head"
>
  <div class="comp-box">
    <div class="comp-id">C{{ comp.id }}</div>
    <div class="comp-text">
      {{ comp.descripcion }}
    </div>
  </div>
</th>

          </tr>
        </thead>

        <tbody>
          <template v-for="asignatura in asignaturas" :key="asignatura.id">
            <tr v-for="(ra, index) in asignatura.ras" :key="ra.id">

              <!-- ASIGNATURA -->
              <td
                v-if="index === 0"
                :rowspan="asignatura.ras.length"
                class="sticky-asignatura text-start fw-semibold"
              >
                {{ asignatura.nombre }}
              </td>

              <!-- RA -->
              <td class="sticky-ra text-start ra-cell">
                <strong>RA{{ index + 1 }}</strong>
                <div class="ra-text">
                  {{ ra.Descripcion }}
                </div>
              </td>

              <!-- COMPETENCIAS -->
              <td
                v-for="comp in competencias"
                :key="comp.id"
                class="comp-cell"
              >
                <span
                  v-if="tieneCompetencia(ra, comp.id)"
                  class="check"
                >
                  ✓
                </span>
                <span v-else class="empty">—</span>
              </td>

            </tr>
          </template>
        </tbody>
      </table>
    </div>
  </div>
</template>




<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const competencias = ref([]);
const asignaturas = ref([]);

const tieneCompetencia = (ra, competenciaId) => {
  if (!ra.comp_ras || ra.comp_ras.length === 0) return false;
  return ra.comp_ras.some(pivot => pivot.ID_Comp === competenciaId);
};

onMounted(async () => {
  try {
    const response = await axios.get('http://localhost:8000/api/matriz-competencias');
    competencias.value = response.data.competencias;
    asignaturas.value = response.data.asignaturas;
  } catch (error) {
    console.error("Error al cargar la matriz:", error);
  }
});
</script>
<style scoped>
/* CONTENEDOR */
.matriz-wrapper {
  max-height: 80vh;
  overflow: auto;
  background: white;
}

/* TABLA */
.matriz-table {
  border-collapse: separate;
  border-spacing: 0;
  min-width: 1100px;
  font-size: 0.9rem;
}

/* HEADER */
.matriz-table thead th {
  background: #811C5E;
  color: white;
  font-weight: 600;
  border-bottom: 2px solid #6d174f;
}

/* ASIGNATURA */
.sticky-asignatura {
  position: sticky;
  left: 0;
  z-index: 4;
  min-width: 160px;
  max-width: 180px;
  background: #f6eef3;
  border-right: 1px solid #e3c7d7;
}

/* RA */
.sticky-ra {
  position: sticky;
  left: 180px;
  z-index: 3;
  min-width: 260px;
  max-width: 300px;
  background: #faf4f8;
  border-right: 1px solid #e3c7d7;
}

/* TEXTO RA */
.ra-cell {
  font-size: 0.85rem;
}

.ra-text {
  color: #666;
  font-size: 0.8rem;
  line-height: 1.2;
}

/* COMPETENCIAS */
.competencia-head {
  width: 60px;
  font-size: 0.85rem;
}

.comp-cell {
  height: 42px;
}

/* CHECK */
.check {
  color: #811C5E;
  font-weight: bold;
  font-size: 1.1rem;
}

/* VACÍO */
.empty {
  color: #d8c2cf;
}

/* HOVER */
.matriz-table tbody tr:hover {
  background: #faf1f6;
}
</style>

