<script setup>
import { ref, onMounted } from "vue";
import api from "@/services/api.js"
import { useNotasStore } from "@/stores/notas.js";

const notasStore = useNotasStore();
const notaInput = ref('');
// datosModal ahora incluye el tipo: 'tec' | 'trans' | null
const datosModal = ref({ idAlumno: null, titulo: '', callback: null, tipo: null });
const transversalSeleccionada = ref(''); // Para guardar el ID del select (solo para transversales)
const listaTransversales = ref([]); // Para las opciones del select
// Estado para competencias técnicas y modal específico
const datosModalTecnica = ref({ idAlumno: null, titulo: '' });
const tecnicaSeleccionada = ref('');
const listaTecnicas = ref([]);
const notaInputTecnica = ref('');
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
        const response = await api.get('/api/transversales');
        listaTransversales.value = response.data;
    } catch (error) {
        console.error("Error cargando transversales:", error);
    }
};
const cargarTecnicas = async () => {
    try {
        const response = await api.get('/api/competencias');
        listaTecnicas.value = response.data;
    } catch (error) {
        console.error("Error cargando técnicas:", error);
    }
};

// Llamamos a la carga al iniciar
onMounted(() => {
  cargarTransversales();
  cargarTecnicas();
});
// Función genérica para abrir el Pop-up
// tipo: 'tec' | 'trans'
const mostrarModalNota = (idAlumno, titulo, funcionCallback, tipo = null) => {
  datosModal.value = { idAlumno, titulo, callback: funcionCallback, tipo };
  notaInput.value = ''; // Resetear el input

  // Si abrimos para técnica, limpiamos selección de transversal
  if (tipo !== 'trans') transversalSeleccionada.value = '';

  // Esto abre el modal de Bootstrap sin importar librerías raras
  const modal = new bootstrap.Modal(document.getElementById('modalNotaBootstrap'));
  modal.show();
};
const mostrarModalTecnica = (idAlumno, titulo) => {
  datosModalTecnica.value = { idAlumno, titulo };
  notaInputTecnica.value = ''; // Resetear el input
  tecnicaSeleccionada.value = ''; // Resetear selección de técnica

  const modal = new bootstrap.Modal(document.getElementById('modalTecnicaBootstrap'));
  modal.show();
};


const confirmarGuardar = async () => {
  // Cerramos el modal buscando su instancia
  const modalElement = document.getElementById('modalNotaBootstrap');
  const modal = bootstrap.Modal.getInstance(modalElement);

  if (datosModal.value.tipo === 'trans') {
    // Validación: asegurarnos de que se haya seleccionado una competencia
    if (!transversalSeleccionada.value) {
      alert('Selecciona una competencia antes de guardar.');
      return;
    }

    // Guardar nota transversal con la competencia seleccionada
    await actualizatrans(datosModal.value.idAlumno, notaInput.value, transversalSeleccionada.value);

    // Reset select
    transversalSeleccionada.value = '';
  } else {
    // Por defecto guardamos como técnica
    await actualizarNotaTec(datosModal.value.idAlumno, notaInput.value);
  }

  // Resetear campo de nota y cerrar modal
  notaInput.value = '';
  modal.hide();
};
const confirmarGuardarTecnica = async () => {
  const modalElement = document.getElementById('modalTecnicaBootstrap');
  const modal = bootstrap.Modal.getInstance(modalElement);

  // Validación: asegurarnos de que se haya seleccionado una competencia técnica
  if (!tecnicaSeleccionada.value) {
    alert('Selecciona una competencia técnica antes de guardar.');
    return;
  }

  await actualizatec(datosModalTecnica.value.idAlumno, notaInputTecnica.value, tecnicaSeleccionada.value);

  // Resetear campo de nota y cerrar modal
  notaInputTecnica.value = '';
  tecnicaSeleccionada.value = '';
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
const actualizatrans = async (idAlumno, nuevaNota, idTransversal) => {
  if (nuevaNota === '' || nuevaNota < 0 || nuevaNota > 10) {
    alert("Introduce una nota entre 0 y 10");
    return;
  }

  // Convertimos la nota de base 10 a base 4 y la redondeamos a entero (1-4)
  const notaProporcionalFloat = (parseFloat(nuevaNota) * 4) / 10;
  // Aseguramos entero entre 1 y 4 (si el usuario introduce 0 lo convertimos a 1 mínimamente)
  let notaEntera = Math.round(notaProporcionalFloat);
  if (notaEntera < 1) notaEntera = 1;
  if (notaEntera > 4) notaEntera = 4;

  try {
      // El endpoint real espera PUT sobre /api/alumnos/{idAlumno}/transversales/{transversalId}/nota
      await api.put(`/api/alumnos/${idAlumno}/transversales/${idTransversal}/nota`, {
          nota: notaEntera
      });

      await fetchDatosGrado(currentPage.value, false);
  } catch (error) {
      console.error("Error al guardar:", error.response?.data || error);
  }

}
const actualizatec = async (idAlumno, nuevaNota, idTecnica) => {
  if (nuevaNota === '' || nuevaNota < 0 || nuevaNota > 10) {
    alert("Introduce una nota entre 0 y 10");
    return;
  }

  const notaProporcional = (parseFloat(nuevaNota) * 4) / 10;
  
  try {
      await api.post(`/api/alumno/${idAlumno}/notaTec/${idTecnica}`, {
          nota: notaProporcional
      });

      // --- SOLUCIÓN AL ERROR DE ACCESIBILIDAD ---
      // Buscamos el modal por su ID y lo cerramos correctamente
      const modalEl = document.getElementById('modalTecnicaBootstrap');
      if (modalEl) {
          const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
          modalInstance.hide();
          
          // Quitamos manualmente el "backdrop" (la capa oscura) si se queda pegada
          const backdrop = document.querySelector('.modal-backdrop');
          if (backdrop) backdrop.remove();
          document.body.style.overflow = 'auto'; // Habilitar scroll de nuevo
      }

      await fetchDatosGrado(currentPage.value, false);

  } catch (error) {
      console.error("Error al guardar:", error.response?.data || error);
      alert("Error al guardar la nota en la base de datos");
  }
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

                          <td class="text-center cursor-pointer" @click="mostrarModalTecnica(alumno.id, 'Nota Técnica')">
                            <span class="text-muted fst-italic">
                              {{ alumno.notas_calculadas?.[asig.id]?.tecnica ?? '-' }}
                            </span>
                            <i class="bi bi-pencil-square ms-1 small"></i> </td>

                          <td class="text-center cursor-pointer" @click="mostrarModalNota(alumno.id, 'Nota Transversal', actualizarNotaTrans, 'trans')">
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
    <div class="modal-content shadow-lg border-0 rounded-4">
      <div class="modal-header bg-indigo text-white border-0 py-3">
        <h5 class="modal-title fw-bold">
          <i class="bi bi-pencil-square me-2"></i>{{ datosModal.titulo }}
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body py-4 px-4">
        
        <div class="form-floating mb-4" v-if="datosModal.tipo === 'trans'">
          <select class="form-select border-2 border-indigo-subtle rounded-3 shadow-sm" 
                  id="selectTrans" 
                  v-model="transversalSeleccionada"
                  style="height: 60px; pt: 1.5rem;">
            <option value="" disabled selected>Selecciona una competencia</option>
            <option v-for="t in listaTransversales" :key="t.id" :value="t.id" class="py-2">
              {{ t.descripcion }}
            </option>
          </select>
          <label for="selectTrans" class="text-indigo fw-bold">Competencia Transversal</label>
        </div>

        <div class="text-center">
          <p class="small text-muted mb-2 fw-bold text-uppercase">Calificación</p>
          <input type="number" 
                 class="form-control form-control-lg text-center fw-bold border-2 border-indigo rounded-3" 
                 v-model="notaInput" 
                 @keyup.enter="confirmarGuardar" 
                 min="0" max="10" 
                 placeholder="0.0"
                 style="font-size: 1.8rem; color: #6610f2;">
        </div>
      </div>
      <div class="modal-footer border-0 justify-content-center pt-0 pb-4">
        <button type="button" class="btn btn-light px-3 fw-semibold text-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-indigo px-4 py-2 rounded-3 shadow" @click="confirmarGuardar">
          Guardar Nota
        </button>
      </div>
    </div>
  </div>
</div>
  <div class="modal fade" id="modalTecnicaBootstrap" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content shadow-lg border-0 rounded-4">
      <div class="modal-header bg-indigo text-white border-0 py-3">
        <h5 class="modal-title fw-bold">
          <i class="bi bi-gear-wide-connected me-2"></i>{{ datosModalTecnica.titulo }}
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body py-4 px-4">
        
        <div class="form-floating mb-4">
          <select class="form-select border-2 border-indigo-subtle rounded-3 shadow-sm" 
                  id="selectTec" 
                  v-model="tecnicaSeleccionada"
                  style="height: 60px;">
            <option value="" disabled selected>Selecciona técnica</option>
            <option v-for="t in listaTecnicas" :key="t.id" :value="t.id">
              {{ t.descripcion }}
            </option>
          </select>
          <label for="selectTec" class="text-indigo fw-bold">Competencia Técnica</label>
        </div>

        <div class="text-center">
          <p class="small text-muted mb-2 fw-bold text-uppercase">Nota Final</p>
          <input type="number" 
                 class="form-control form-control-lg text-center fw-bold border-2 border-indigo rounded-3" 
                 v-model="notaInputTecnica" 
                 @keyup.enter="confirmarGuardarTecnica" 
                 min="0" max="10" 
                 placeholder="0.0"
                 style="font-size: 1.8rem; color: #6610f2;">
        </div>
      </div>
      <div class="modal-footer border-0 justify-content-center pt-0 pb-4">
        <button type="button" class="btn btn-light px-3 fw-semibold text-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-indigo px-4 py-2 rounded-3 shadow" @click="confirmarGuardarTecnica">
          Guardar Nota
        </button>
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