<script setup>
import { ref, onMounted, computed } from "vue";
import api from "@/services/api.js"

// Estados
const alumnos = ref([]);
const asignaturas = ref([]);
const gradoNombre = ref("");
const loading = ref(false);

// Control del acordeón
const alumnoDesplegado = ref(null);

const toggleNotas = (idAlumno) => {
  alumnoDesplegado.value = alumnoDesplegado.value === idAlumno ? null : idAlumno;
};

// Cargar datos del tutor
const fetchDatosGrado = async () => {
  loading.value = true;
  try {
    const token = localStorage.getItem('token');
    const res = await api.get("/api/mi-grado/gestion", {
      headers: { Authorization: `Bearer ${token}` }
    });
    
    alumnos.value = res.data.alumnos;
    asignaturas.value = res.data.asignaturas;
    gradoNombre.value = res.data.grado.nombre;

  } catch (error) {
    console.error("Error cargando datos:", error);
    alert("Error al cargar los datos del grado.");
  } finally {
    loading.value = false;
  }
};

// Función para obtener el estado de un alumno
const obtenerEstadoAlumno = (alumno) => {
  if (!alumno.notas_calculadas) {
    return { tipo: 'sin-datos', mensaje: 'Sin datos de notas', icono: 'bi-question-circle' };
  }

  let errores = [];
  let asignaturasCompletas = 0;
  let totalAsignaturas = asignaturas.value.length;

  asignaturas.value.forEach(asig => {
    const notasAsig = alumno.notas_calculadas[asig.id];
    
    if (!notasAsig) {
      errores.push(`${asig.nombre}: Sin datos`);
      return;
    }

    if (notasAsig.final === '-' || notasAsig.final === null) {
      let causas = [];
      
      if (notasAsig.egibide === '-' || notasAsig.egibide === null) {
        causas.push("Falta Nota Egibide");
      }
      if (notasAsig.nota_empresa_calculada === '-' || notasAsig.nota_empresa_calculada === null) {
        causas.push("Faltan datos Empresa");
      }
      if (notasAsig.cuaderno === 0 || notasAsig.cuaderno === '-') {
        causas.push("Sin nota de cuaderno");
      }
      if (notasAsig.transversal === 0 || notasAsig.transversal === '-') {
        causas.push("Sin competencias transversales");
      }
      if (notasAsig.tecnica === 0 || notasAsig.tecnica === '-') {
        causas.push("Sin competencias técnicas");
      }

      errores.push(`${asig.nombre}: ${causas.join(', ')}`);
    } else {
      asignaturasCompletas++;
    }
  });

  if (errores.length === 0) {
    return { 
      tipo: 'completo', 
      mensaje: `✓ Todas las asignaturas calculadas (${totalAsignaturas}/${totalAsignaturas})`, 
      icono: 'bi-check-circle-fill',
      errores: []
    };
  } else if (asignaturasCompletas > 0) {
    return { 
      tipo: 'parcial', 
      mensaje: `⚠ Calculadas: ${asignaturasCompletas}/${totalAsignaturas}`, 
      icono: 'bi-exclamation-triangle-fill',
      errores: errores
    };
  } else {
    return { 
      tipo: 'incompleto', 
      mensaje: `✗ Ninguna asignatura calculada`, 
      icono: 'bi-x-circle-fill',
      errores: errores
    };
  }
};

// Computed para obtener clase de badge según estado
const getBadgeClass = (tipo) => {
  switch(tipo) {
    case 'completo': return 'bg-success';
    case 'parcial': return 'bg-warning text-dark';
    case 'incompleto': return 'bg-danger';
    default: return 'bg-secondary';
  }
};

onMounted(() => {
  fetchDatosGrado();
});
</script>

<template>
  <div class="card shadow-sm border-0">
    <div class="card-header bg-indigo text-white py-3">
      <h5 class="mb-0">
        <i class="bi bi-mortarboard-fill me-2"></i>
        Gestión de Notas - {{ gradoNombre || 'Cargando...' }}
      </h5>
    </div>

    <div class="card-body p-0">
      <div v-if="loading" class="text-center p-5">
        <div class="spinner-border text-indigo" role="status"></div>
        <p class="mt-2 text-muted">Calculando notas...</p>
      </div>

      <div v-else class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
          <thead class="table-light">
            <tr>
              <th class="ps-4">Apellidos y Nombre</th>
              <th>Email</th>
              <th>Teléfono</th>
              <th>Estado Notas</th>
              <th class="text-end pe-4">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <template v-for="alumno in alumnos" :key="alumno.id">
              <tr :class="{'table-active': alumnoDesplegado === alumno.id}">
                <td class="ps-4 fw-bold text-secondary">
                  {{ alumno.apellidos }}, {{ alumno.nombre }}
                </td>
                <td>{{ alumno.email }}</td>
                <td>{{ alumno.n_tel || '-' }}</td>
                <td>
                  <span 
                    class="badge" 
                    :class="getBadgeClass(obtenerEstadoAlumno(alumno).tipo)"
                    :title="obtenerEstadoAlumno(alumno).errores.join('\n')"
                  >
                    <i class="bi" :class="obtenerEstadoAlumno(alumno).icono"></i>
                    {{ obtenerEstadoAlumno(alumno).mensaje }}
                  </span>
                </td>
                <td class="text-end pe-4">
                  <button 
                    class="btn btn-sm" 
                    :class="alumnoDesplegado === alumno.id ? 'btn-indigo text-white' : 'btn-outline-indigo'"
                    @click="toggleNotas(alumno.id)"
                  >
                    <i class="bi" :class="alumnoDesplegado === alumno.id ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                    Notas
                  </button>
                </td>
              </tr>

              <tr v-if="alumnoDesplegado === alumno.id" class="bg-light-subtle">
                <td colspan="5" class="p-0">
                  <div class="p-4 border-bottom border-indigo-subtle animacion-entrada">
                    
                    <!-- Alertas de estado -->
                    <div v-if="obtenerEstadoAlumno(alumno).tipo === 'incompleto'" class="alert alert-danger d-flex align-items-start mb-3">
                      <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                      <div>
                        <strong>No se pueden calcular las notas finales</strong>
                        <ul class="mb-0 mt-2 small">
                          <li v-for="(error, idx) in obtenerEstadoAlumno(alumno).errores" :key="idx">
                            {{ error }}
                          </li>
                        </ul>
                      </div>
                    </div>

                    <div v-else-if="obtenerEstadoAlumno(alumno).tipo === 'parcial'" class="alert alert-warning d-flex align-items-start mb-3">
                      <i class="bi bi-exclamation-circle-fill me-2 fs-5"></i>
                      <div>
                        <strong>Cálculo parcial de notas</strong>
                        <ul class="mb-0 mt-2 small">
                          <li v-for="(error, idx) in obtenerEstadoAlumno(alumno).errores" :key="idx">
                            {{ error }}
                          </li>
                        </ul>
                      </div>
                    </div>

                    <div v-else-if="obtenerEstadoAlumno(alumno).tipo === 'completo'" class="alert alert-success d-flex align-items-center mb-3">
                      <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                      <strong>Todas las notas finales han sido calculadas correctamente</strong>
                    </div>

                    <h6 class="text-indigo fw-bold mb-3">
                      <i class="bi bi-journal-text me-2"></i>
                      Resumen de Calificaciones
                    </h6>

                    <table class="table table-sm table-bordered bg-white shadow-sm mb-0 text-center align-middle">
                      <thead class="table-secondary small">
                        <tr>
                          <th rowspan="2" class="align-middle">Asignatura</th>
                          <th rowspan="2" class="align-middle bg-warning-subtle" style="width: 15%;">Nota Egibide (80%)</th>
                          <th colspan="3" class="border-bottom-0">Parte Empresa (20%)</th>
                          <th rowspan="2" class="align-middle bg-success-subtle" style="width: 10%;">NOTA FINAL</th>
                        </tr>
                        <tr>
                          <th class="fw-normal text-muted" style="font-size: 0.8rem; width: 12%;">Técnica (60%)</th>
                          <th class="fw-normal text-muted" style="font-size: 0.8rem; width: 12%;">Transv. (20%)</th>
                          <th class="fw-normal text-muted" style="font-size: 0.8rem; width: 12%;">Cuaderno (20%)</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="asig in asignaturas" :key="asig.id">
                          <td class="text-start px-3 fw-bold text-secondary">{{ asig.nombre }}</td>
                          
                          <td class="bg-warning-subtle fw-bold text-dark">
                            {{ alumno.notas_calculadas?.[asig.id]?.egibide ?? '-' }}
                          </td>

                          <td class="text-muted fst-italic">
                            {{ alumno.notas_calculadas?.[asig.id]?.tecnica ?? '-' }}
                          </td>
                          <td class="text-muted fst-italic">
                            {{ alumno.notas_calculadas?.[asig.id]?.transversal ?? '-' }}
                          </td>
                          <td class="text-muted fst-italic">
                            {{ alumno.notas_calculadas?.[asig.id]?.cuaderno ?? '-' }}
                          </td>
                          
                          <td class="fw-bold fs-6" 
                              :class="(alumno.notas_calculadas?.[asig.id]?.final === '-' || !alumno.notas_calculadas?.[asig.id]?.final) ? 'text-danger bg-danger-subtle' : 'text-dark bg-success-subtle'">
                            {{ alumno.notas_calculadas?.[asig.id]?.final ?? '-' }}
                          </td>
                        </tr>
                      </tbody>
                    </table>

                  </div>
                </td>
              </tr>
            </template>
            
            <tr v-if="alumnos.length === 0">
              <td colspan="5" class="text-center py-4 text-muted">No hay alumnos matriculados en este grado.</td>
            </tr>

          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<style scoped>
.bg-warning-subtle { background-color: #fff3cd !important; }
.bg-success-subtle { background-color: #d1e7dd !important; }
.bg-danger-subtle { background-color: #f8d7da !important; }

.bg-light-subtle { background-color: #f8f9fa; }
.border-indigo-subtle { border-bottom: 2px solid #e0cffc !important; }

.animacion-entrada {
  animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
  from { opacity: 0; transform: translateY(-10px); }
  to { opacity: 1; transform: translateY(0); }
}

.badge {
  padding: 0.5rem 0.75rem;
  font-size: 0.75rem;
  font-weight: 500;
}
</style>