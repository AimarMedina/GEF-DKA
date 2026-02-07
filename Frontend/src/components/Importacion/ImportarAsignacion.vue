<template>
  <div>
    <!-- Botón para abrir modal -->
    <button
      type="button"
      class="btn p-0"
      style="display: inline-block"
      data-bs-toggle="modal"
      data-bs-target="#importarAsignacionesModal"
    >
      <div
        class="card h-100 border-0 shadow hover-scale overflow-hidden"
        style="width: 350px; height: 350px"
      >
        <img
          src="../../../public/images/Asignaciones.png"
          class="card-img-top w-100 h-100 object-fit-cover"
          alt="Asignacion"
        />
      </div>
    </button>

    <!-- Modal Bootstrap -->
    <div
      class="modal fade"
      id="importarAsignacionesModal"
      tabindex="-1"
      aria-labelledby="importarAsignacionesModalLabel"
      aria-hidden="true"
      ref="modal"
    >
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <!-- Header -->
          <div class="modal-header">
            <h5 class="modal-title" id="importarAsignacionesModalLabel">
              <i class="fas fa-file-csv me-2"></i>
              Importar Asignaciones Profesor-Asignatura
            </h5>
            <button
              type="button"
              class="btn-close"
              data-bs-dismiss="modal"
              aria-label="Close"
            ></button>
          </div>

          <!-- Body -->
          <div class="modal-body">
            <!-- Zona de carga de archivo -->
            <div class="mb-4">
              <input
                type="file"
                ref="fileInput"
                accept=".csv"
                @change="handleFileSelect"
                class="form-control"
              />
              <small class="form-text text-muted">
                Formato: CSV con separador punto y coma (;), encoding Latin-1
              </small>
            </div>

            <!-- Archivo seleccionado -->
            <div
              v-if="archivo"
              class="alert alert-info d-flex align-items-center justify-content-between"
            >
              <div class="d-flex align-items-center">
                <i class="fas fa-file-csv fa-2x me-3 text-primary"></i>
                <div>
                  <strong>{{ archivo.name }}</strong>
                  <br />
                  <small>{{ formatearTamaño(archivo.size) }}</small>
                </div>
              </div>
              <button @click="eliminarArchivo" class="btn btn-sm btn-outline-danger">
                <i class="fas fa-trash"></i>
              </button>
            </div>

            <!-- Opciones -->
            <div v-if="archivo" class="mb-4">
              <h6 class="mb-3">Opciones de Importación</h6>

              <div class="mb-3">
                <label for="contraseñaDefecto" class="form-label">
                  Contraseña por defecto para tutores:
                </label>
                <input
                  type="text"
                  id="contraseñaDefecto"
                  v-model="opciones.contraseñaDefecto"
                  class="form-control"
                  placeholder="12345Abcde"
                />
              </div>
            </div>

            <!-- Barra de progreso -->
            <div v-if="procesando" class="mb-3">
              <div class="progress">
                <div
                  class="progress-bar progress-bar-striped progress-bar-animated"
                  :style="{ width: progreso + '%' }"
                >
                  {{ progreso }}%
                </div>
              </div>
              <small class="text-muted mt-2 d-block">
                {{ mensajeProgreso }}
              </small>
            </div>

            <!-- Resultados -->
            <div v-if="resultados">
              <div class="alert" :class="alertClass">
                <h6 class="alert-heading">
                  <i :class="alertIcon"></i>
                  Importación
                  {{ resultados.exito ? 'Completada' : 'Finalizada con Errores' }}
                </h6>

                <p class="mb-1">
                  Registros procesados:
                  <strong>{{ resultados.importados }}</strong>
                </p>

                <div v-if="resultados.estadisticas" class="mt-2">
                  <small>
                    <strong>Creados:</strong>
                    {{ resultados.estadisticas.tutores_creados }} tutores,
                    {{ resultados.estadisticas.grados_creados }} grados,
                    {{ resultados.estadisticas.asignaturas_creadas }} asignaturas,
                    {{ resultados.estadisticas.asignaciones_creadas }} asignaciones
                  </small>
                </div>

                <p v-if="resultados.errores.length > 0" class="mb-0 mt-2">
                  Errores:
                  <strong>{{ resultados.errores.length }}</strong>
                </p>
              </div>

              <!-- Lista de errores -->
              <div v-if="resultados.errores.length > 0" class="alert alert-warning">
                <h6>Detalles de Errores:</h6>
                <ul class="mb-0" style="max-height: 200px; overflow-y: auto">
                  <li v-for="(error, index) in resultados.errores" :key="index">
                    <strong v-if="error.fila"> Fila {{ error.fila }}: </strong>
                    {{ error.mensaje }}
                    <small v-if="error.profesor" class="text-muted"> ({{ error.profesor }}) </small>
                  </li>
                </ul>
              </div>
            </div>
          </div>

          <!-- Footer -->
          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-secondary"
              data-bs-dismiss="modal"
              :disabled="procesando"
            >
              Cerrar
            </button>

            <button
              v-if="archivo && !resultados"
              @click="procesarArchivo"
              :disabled="procesando"
              class="btn btn-success"
            >
              <span v-if="!procesando">
                <i class="fas fa-upload me-2"></i>
                Importar Asignaciones
              </span>
              <span v-else>
                <i class="fas fa-spinner fa-spin me-2"></i>
                Procesando...
              </span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import api from '@/services/api.js';

export default {
  name: 'ImportarAsignaciones',

  data() {
    return {
      archivo: null,
      procesando: false,
      progreso: 0,
      mensajeProgreso: '',
      resultados: null,
      opciones: {
        contraseñaDefecto: '12345Abcde',
      },
    };
  },

  computed: {
    alertClass() {
      return this.resultados?.exito ? 'alert-success' : 'alert-warning';
    },
    alertIcon() {
      return this.resultados?.exito
        ? 'fas fa-check-circle me-2'
        : 'fas fa-exclamation-triangle me-2';
    },
  },

  methods: {
    handleFileSelect(event) {
      const file = event.target.files[0];
      if (file) {
        this.archivo = file;
        this.resultados = null;
      }
    },

    eliminarArchivo() {
      this.archivo = null;
      this.resultados = null;
      this.$refs.fileInput.value = '';
    },

    async procesarArchivo() {
      this.procesando = true;
      this.progreso = 0;
      this.mensajeProgreso = 'Subiendo archivo...';

      const formData = new FormData();
      formData.append('archivo', this.archivo);
      formData.append('opciones', JSON.stringify(this.opciones));

      try {
        const response = await api.post('/api/asignaciones/importar', formData, {
          headers: { 'Content-Type': 'multipart/form-data' },
          onUploadProgress: (progressEvent) => {
            this.progreso = Math.round((progressEvent.loaded * 100) / progressEvent.total);
          },
        });

        this.mensajeProgreso = 'Procesando datos...';
        this.progreso = 100;
        this.resultados = response.data;

        if (this.resultados.exito) {
          this.$toast?.success(
            `${this.resultados.importados} asignaciones importadas correctamente`
          );
        } else {
          this.$toast?.warning('Importación completada con algunos errores');
        }
      } catch (error) {
        this.$toast?.error('Error durante la importación: ' + error.message);
        this.resultados = {
          exito: false,
          importados: 0,
          errores: [{ mensaje: error.message }],
        };
      } finally {
        this.procesando = false;
      }
    },

    formatearTamaño(bytes) {
      if (bytes < 1024) return bytes + ' B';
      if (bytes < 1048576) return (bytes / 1024).toFixed(2) + ' KB';
      return (bytes / 1048576).toFixed(2) + ' MB';
    },
  },
};
</script>
