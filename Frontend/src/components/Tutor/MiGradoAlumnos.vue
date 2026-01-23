<script setup>
import { ref, onMounted } from "vue";
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
    // Llamada al backend (que ya realiza los cálculos internamente)
    const token = localStorage.getItem('token');
    const res = await api.get("/api/mi-grado/gestion",{
    headers:{ Authorization:`Bearer ${token}` }
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

/**
 * Función que VALIDA si se ha podido calcular la nota.
 * Como el backend devuelve '-' cuando faltan datos, buscamos esos guiones.
 */
const calcularNotaFinal = async (alumno) => {
    // 1. Opcional: Recargamos datos por si se acaban de meter notas en otra pestaña
    await fetchDatosGrado();
    
    // Buscamos al alumno actualizado en la lista recién traída
    const alumnoActualizado = alumnos.value.find(a => a.id === alumno.id);
    if (!alumnoActualizado) return;

    let errores = [];
    let asignaturasCompletas = 0;

    // Recorremos todas las asignaturas del grado
    asignaturas.value.forEach(asig => {
        const notasAsig = alumnoActualizado.notas_calculadas[asig.id];

        // Verificamos si la nota final es un guion '-' (indicador de fallo en backend)
        if (notasAsig.final === '-' || notasAsig.final === null) {
            let causas = [];
            
            // Analizamos por qué falta la final
            if (notasAsig.egibide === '-' || notasAsig.egibide === null) {
                causas.push("Falta Nota Egibide");
            }
            if (notasAsig.nota_empresa_calculada === '-' || notasAsig.nota_empresa_calculada === null) {
                // Si falta la nota empresa, suele ser porque faltan competencias evaluadas
                causas.push("Faltan datos Empresa (Competencias/Cuaderno)");
            }

            errores.push(`${asig.nombre}: ${causas.join(' y ')}`);
        } else {
            asignaturasCompletas++;
        }
    });

    // RESULTADO
    if (errores.length > 0) {
        // Mostramos error
        alert(`❌ NO SE PUEDE CALCULAR LA NOTA FINAL\n\nSe han encontrado los siguientes problemas:\n\n${errores.join('\n')}`);
    } else {
        // Todo OK
        alert(`✅ CÁLCULO EXITOSO\n\nTodas las asignaturas (${asignaturasCompletas}) tienen su nota final calculada correctamente.`);
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
                <td colspan="4" class="p-0">
                  <div class="p-4 border-bottom border-indigo-subtle animacion-entrada">
                    
                    <h6 class="text-indigo fw-bold mb-3">
                      <i class="bi bi-journal-text me-2"></i>
                      Resumen de Calificaciones
                    </h6>

                    <table class="table table-sm table-bordered bg-white shadow-sm mb-3 text-center align-middle">
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

                    <div class="d-flex justify-content-end mt-3">
                      <button class="btn btn-success d-flex align-items-center gap-2 shadow-sm" @click="calcularNotaFinal(alumno)">
                        <i class="bi bi-calculator"></i>
                        Verificar Cálculo
                      </button>
                    </div>

                  </div>
                </td>
              </tr>
            </template>
            
            <tr v-if="alumnos.length === 0">
                <td colspan="4" class="text-center py-4 text-muted">No hay alumnos matriculados en este grado.</td>
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
.bg-danger-subtle { background-color: #f8d7da !important; } /* Fondo rojo suave para errores */

.bg-light-subtle { background-color: #f8f9fa; }
.border-indigo-subtle { border-bottom: 2px solid #e0cffc !important; }

.animacion-entrada {
  animation: slideDown 0.3s ease-out;
}
@keyframes slideDown {
  from { opacity: 0; transform: translateY(-10px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>