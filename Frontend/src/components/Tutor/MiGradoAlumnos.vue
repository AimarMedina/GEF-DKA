<script setup>
import { ref, onMounted } from "vue";
import api from "@/services/api.js"
import { useNotasStore } from "@/stores/notas.js";

const notasStore = useNotasStore();
const notaInput = ref('');
const datosModal = ref({ idAlumno: null, titulo: '', callback: null });
const transversalSeleccionada = ref(''); // Para guardar el ID del select
const listaTransversales = ref([]); // Para las opciones del select
// Estados
const alumnos = ref([]);
const asignaturas = ref([]);
const gradoNombre = ref("");
const loading = ref(false);

// Estado para feedback visual de guardado (ID_Alumno-ID_Asignatura -> 'success' | 'error')
const inputStatus = ref({}); 

// Paginación
const currentPage = ref(1);
const totalPages = ref(1);
const perPage = ref(5);

// Control del acordeón
const alumnoDesplegado = ref(null);

const toggleNotas = (idAlumno) => {
  alumnoDesplegado.value = alumnoDesplegado.value === idAlumno ? null : idAlumno;
};

// Cargar datos del tutor
// param loadingGlobal: true para mostrar el spinner grande, false para recargas silenciosas
const fetchDatosGrado = async (page = 1, loadingGlobal = true) => {
  if (loadingGlobal) loading.value = true;
  currentPage.value = page;

  try {
    const res = await api.get("/api/mi-grado/gestion", {
      params: { page, per_page: perPage.value }
    });

    // Guardar en los refs locales
    alumnos.value = res.data.alumnos.data;
    asignaturas.value = res.data.asignaturas;
    gradoNombre.value = res.data.grado.nombre;
    totalPages.value = res.data.alumnos.last_page;

    // Guardar en Pinia
    notasStore.setDatosGrado({
      alumnosData: res.data.alumnos.data,
      asignaturasData: res.data.asignaturas,
      grado: res.data.grado
    });

    // IMPORTANTE: No cerramos el acordeón si estamos recargando tras editar nota
    // alumnoDesplegado.value = null; 

  } catch (error) {
    console.error("Error cargando datos:", error);
    alert("Error al cargar los datos del grado.");
  } finally {
    loading.value = false;
  }
};
const cargarTransversales = async () => {
    try {
        const response = await api.get('/api/competencias');
        listaTransversales.value = response.data;
    } catch (error) {
        console.error("Error cargando transversales:", error);
    }
};

// Llamamos a la carga al iniciar
onMounted(cargarTransversales);
// Función genérica para abrir el Pop-up
const mostrarModalNota = (idAlumno, titulo, funcionCallback) => {
  datosModal.value = { idAlumno, titulo, callback: funcionCallback };
  notaInput.value = ''; // Resetear el input
  
  // Esto abre el modal de Bootstrap sin importar librerías raras
  const modal = new bootstrap.Modal(document.getElementById('modalNotaBootstrap'));
  modal.show();
};
const confirmarGuardar = async () => {
  // Cerramos el modal buscando su instancia
  const modalElement = document.getElementById('modalNotaBootstrap');
  const modal = bootstrap.Modal.getInstance(modalElement);
  
  // Ejecutamos tu función (actualizarNotaTec o actualizarNotaTrans)
  await datosModal.value.callback(datosModal.value.idAlumno, notaInput.value);
  
  modal.hide();
};


const actualizarNotaTec = async(idAlumno, nuevaNota)=>{
  if (nuevaNota === '' || nuevaNota < 0 || nuevaNota > 10) {
    alert("Introduce una nota entre 0 y 10");
    return;
  }
  // Convertimos la nota de base 10 a base 4
  // Ejemplo: un 5 se convierte en 2, un 10 en 4.
  const notaProporcional = (parseFloat(nuevaNota) * 4) / 10;
  try {
      await api.post(`/api/alumno/${idAlumno}/notaTec`, {
          nota: notaProporcional
        // Enviamos el valor ya convertido
      });
      await fetchDatosGrado(currentPage.value, false);
  } catch (error) {
      console.error("Error al guardar:", error.response?.data);
  }

}
const actualizarNotaTrans = async (idAlumno, nuevaNota) => {
  if (nuevaNota === '' || nuevaNota < 0 || nuevaNota > 10) {
    alert("Introduce una nota entre 0 y 10");
    return;
  }
  // Convertimos la nota de base 10 a base 4
  // Ejemplo: un 5 se convierte en 2, un 10 en 4.
  const notaProporcional = (parseFloat(nuevaNota) * 4) / 10;

  try {
      await api.post(`/api/alumno/${idAlumno}/notaTrans`, {
          nota: notaProporcional
        // Enviamos el valor ya convertido
      });
      await fetchDatosGrado(currentPage.value, false);
  } catch (error) {
      console.error("Error al guardar:", error.response?.data);
  }
}
const actualizarNotacuad = async (idAlumno, nuevaNota) => {
  // Evitamos llamadas innecesarias si la nota no es válida
  if (nuevaNota === '' || nuevaNota === '-' || nuevaNota < 0 || nuevaNota > 10) {
    return;
  }
  
  try {
      // Llamada a tu ruta exacta: alumno/{id}/notaCuad
      await api.post(`/api/alumno/${idAlumno}/notaCuad`, {
          nota: nuevaNota
      });
      
      // Recargamos para que se vean los cambios en los cálculos globales
      await fetchDatosGrado(currentPage.value, false);
  } catch (error) {
      console.error("Error al actualizar la nota de cuaderno única:", error.response?.data);
      // Si el error es 404, es que no existe la fila en la BD para ese alumno
  }
};
const actualizatrans = async ()=>{

}
const actualizatec = async ()=>{
  
}

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
      
      // Agregamos chequeos adicionales si es necesario
      if (notasAsig.cuaderno === 0 || notasAsig.cuaderno === '-') causas.push("Cuaderno");
      if (notasAsig.transversal === 0 || notasAsig.transversal === '-') causas.push("Transversal");
      if (notasAsig.tecnica === 0 || notasAsig.tecnica === '-') causas.push("Técnica");

      errores.push(`${asig.nombre}: ${causas.join(', ')}`);
    } else {
      asignaturasCompletas++;
    }
  });

  if (errores.length === 0) {
    return { 
      tipo: 'completo', 
      mensaje: `(${totalAsignaturas}/${totalAsignaturas})`, 
      icono: 'bi-check-circle-fill',
      errores: []
    };
  } else if (asignaturasCompletas > 0) {
    return { 
      tipo: 'parcial', 
      mensaje: `${asignaturasCompletas}/${totalAsignaturas}`, 
      icono: 'bi-exclamation-triangle-fill',
      errores: errores
    };
  } else {
    return { 
      tipo: 'incompleto', 
      mensaje: `0/${totalAsignaturas}`, 
      icono: 'bi-x-circle-fill',
      errores: errores
    };
  }
};

const getBadgeClass = (tipo) => {
  switch(tipo) {
    case 'completo': return 'bg-success';
    case 'parcial': return 'bg-warning text-dark';
    case 'incompleto': return 'bg-danger';
    default: return 'bg-secondary';
  }
};

onMounted(() => {
  fetchDatosGrado(1);
});
</script>

<template>
  <div class="d-flex justify-content-center">
  <div class="card shadow-sm border-0 mt-5 col-lg-8 justify-content-center">
    <div class="card-header bg-indigo text-white py-3">
      <h5 class="mb-0">
        <i class="bi bi-mortarboard-fill me-2"></i>
        Gestión de Notas - {{ gradoNombre || 'Cargando...' }}
      </h5>
    </div>

    <div class="card-body p-0">
      <div v-if="loading" class="text-center p-5">
        <div class="spinner-border text-indigo" role="status"></div>
        <p class="mt-2 text-muted">Cargando alumnos...</p>
      </div>

      <div v-else class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
          <thead class="table-light">
            <tr>
              <th class="ps-4">Apellidos y Nombre</th>
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
                <td>
                  <span 
                    class="badge" 
                    :class="getBadgeClass(obtenerEstadoAlumno(alumno).tipo)"
                    :title="obtenerEstadoAlumno(alumno).errores.join('\n')"
                    style="cursor: help;"
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
                    
                    <div v-if="obtenerEstadoAlumno(alumno).tipo === 'incompleto'" class="alert alert-danger d-flex align-items-start mb-3">
                      <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                      <div>
                        <strong>Faltan datos para calcular la nota final</strong>
                        <ul class="mb-0 mt-2 small">
                          <li v-for="(error, idx) in obtenerEstadoAlumno(alumno).errores" :key="idx" class="text-start">
                            {{ error }}
                          </li>
                        </ul>
                      </div>
                    </div>

                    <div v-else-if="obtenerEstadoAlumno(alumno).tipo === 'parcial'" class="alert alert-warning d-flex align-items-start mb-3">
                      <i class="bi bi-exclamation-circle-fill me-2 fs-5"></i>
                      <div>
                        <strong>Cálculo parcial</strong> (Algunas asignaturas incompletas)
                        <ul class="mb-0 mt-2 small">
                          <li v-for="(error, idx) in obtenerEstadoAlumno(alumno).errores" :key="idx">
                            {{ error }}
                          </li>
                        </ul>
                      </div>
                    </div>

                    <div v-else-if="obtenerEstadoAlumno(alumno).tipo === 'completo'" class="alert alert-success d-flex align-items-center mb-3">
                      <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                      <strong>Todas las notas finales calculadas correctamente</strong>
                    </div>

                    <h6 class="text-indigo fw-bold mb-3">
                      <i class="bi bi-journal-text me-2"></i>
                      Calificaciones Detalladas
                    </h6>

                    <table class="table table-sm table-bordered bg-white shadow-sm mb-0 text-center align-middle">
                      <thead class="table-secondary small">
                        <tr>
                          <th rowspan="2" class="align-middle">Asignatura</th>
                          <th rowspan="2" class="align-middle bg-warning-subtle" style="width: 15%;">
                            Nota Egibide (80%)<br>
                            <span class="text-muted" style="font-size: 0.7em">(Editable)</span>
                          </th>
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
                          
                          <td class="bg-warning-subtle fw-bold text-dark p-1">
                            <input 
                              type="number" 
                              step="0.01"
                              min="0" 
                              max="10"
                              class="form-control form-control-sm text-center fw-bold"
                              :class="inputStatus[`${alumno.id}-${asig.id}`]"
                              :value="alumno.notas_calculadas?.[asig.id]?.egibide !== '-' ? alumno.notas_calculadas?.[asig.id]?.egibide : ''"
                              placeholder="-"
                              @change="actualizarNotaEgibide(alumno.id, asig.id, $event.target.value)"
                            >
                          </td>

                          <td class="text-center cursor-pointer" @click="mostrarModalNota(alumno.id, 'Nota Técnica', actualizarNotaTec)">
                            <span class="text-muted fst-italic">
                              {{ alumno.notas_calculadas?.[asig.id]?.tecnica ?? '-' }}
                            </span>
                            <i class="bi bi-pencil-square ms-1 small"></i> </td>

                          <td class="text-center cursor-pointer" @click="mostrarModalNota(alumno.id, 'Nota Transversal', actualizarNotaTrans)">
                            <span class="text-muted fst-italic">
                              {{ alumno.notas_calculadas?.[asig.id]?.transversal ?? '-' }}
                            </span>
                            <i class="bi bi-pencil-square ms-1 small"></i>
                          </td>

                          <td class="text-muted fst-italic">
                            <input 
                              type="number" 
                              step="0.1"
                              class="form-control form-control-sm border-0 bg-transparent text-muted fst-italic no-spinners" 
                              :value="alumno.notas_calculadas?.[asig.id]?.cuaderno ?? '-'"
                              @change="actualizarNotacuad(alumno.id, $event.target.value)"
                            >
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

      <div v-if="totalPages > 1" class="card-footer bg-white border-top">
        <nav>
          <ul class="pagination mb-0 justify-content-center">
            <li class="page-item" :class="{ disabled: currentPage === 1 }">
              <button class="page-link" @click="fetchDatosGrado(currentPage - 1)" :disabled="currentPage === 1">
                <i class="bi bi-chevron-left"></i> Anterior
              </button>
            </li>

            <li
              class="page-item"
              v-for="page in totalPages"
              :key="page"
              :class="{ active: currentPage === page }"
            >
              <button class="page-link" @click="fetchDatosGrado(page)">{{ page }}</button>
            </li>

            <li class="page-item" :class="{ disabled: currentPage === totalPages }">
              <button class="page-link" @click="fetchDatosGrado(currentPage + 1)" :disabled="currentPage === totalPages">
                Siguiente <i class="bi bi-chevron-right"></i>
              </button>
            </li>
          </ul>
        </nav>
      </div>

    </div>
  </div>
  </div>
  <div class="modal fade" id="modalNotaBootstrap" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
      <div class="modal-content shadow border-0">
        
        <div class="modal-header bg-indigo text-white border-0">
          <h5 class="modal-title fw-bold">{{ datosModal.titulo }}</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body py-3">
          <div class="mb-3">
            <label class="form-label fw-semibold small">Competencia:</label>
            <select class="form-select shadow-sm" v-model="transversalSeleccionada">
              <option value="" disabled>Selecciona una...</option>
              <option v-for="t in listaTransversales" :key="t.id" :value="t.id">
                {{ t.descripcion }}
              </option>
            </select>
          </div>

          <div class="mb-2 text-center">
            <label class="form-label fw-semibold small">Nota (0-10):</label>
            <input 
              type="text" 
              class="form-control form-control-lg text-center shadow-sm" 
              v-model="notaInput" 
              placeholder="0.0"
              @keyup.enter="confirmarGuardar"
            >
          </div>
        </div>

        <div class="modal-footer bg-light border-0 justify-content-center">
          <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-indigo btn-sm px-4" @click="confirmarGuardar">Guardar</button>
        </div>
        
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

/* Estilo para el input de nota */
input[type=number] {
    font-size: 1rem;
    color: #495057;
}
/* Eliminar flechas del input number en algunos navegadores */
input[type=number]::-webkit-inner-spin-button, 
input[type=number]::-webkit-outer-spin-button { 
  -webkit-appearance: none; 
  margin: 0; 
}

.pagination {
  margin: 0;
}

.page-item.disabled .page-link {
  color: #6c757d;
  pointer-events: none;
  background-color: #fff;
  border-color: #dee2e6;
}
</style>