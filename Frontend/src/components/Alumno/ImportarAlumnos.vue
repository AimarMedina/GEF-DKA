<script>
import * as XLSX from 'xlsx';
import axios from 'axios';
import api from '@/services/api.js';

export default {
  name: 'ImportarAlumnos',

  data() {
    return {
      archivo: null,
      vistaPrevia: [],
      procesando: false,
      progreso: 0,
      mensajeProgreso: '',
      resultados: null,
      opciones: {
        crearGrados: true,
        contraseñaDefecto: 'Egibide2025',
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
        this.cargarArchivo(file);
      }
    },

    async cargarArchivo(file) {
      this.archivo = file;
      this.resultados = null;

      // Leer archivo y generar vista previa
      const reader = new FileReader();
      reader.onload = (e) => {
        const data = new Uint8Array(e.target.result);
        const workbook = XLSX.read(data, { type: 'array' });
        const firstSheet = workbook.Sheets[workbook.SheetNames[0]];
        const jsonData = XLSX.utils.sheet_to_json(firstSheet);

        // Generar vista previa (primeras 5 filas)
        this.vistaPrevia = jsonData.slice(0, 5).map((row) => ({
          nombre: row['NOMBRE ALUMNO'],
          apellidos: `${row['APELLIDO1 ALUMNO']} ${row['APELLIDO2 ALUMNO']}`,
          email: row['EMAIL ALUMNO'],
          clase: row['CLASE'],
          dni: row['DNI ALUMNO'],
          matricula: row['MATRICULA ALUMNO'],
          id: row['ID PERSONA'],
        }));
      };
      reader.readAsArrayBuffer(file);
    },

    eliminarArchivo() {
      this.archivo = null;
      this.vistaPrevia = [];
      this.resultados = null;
      this.$refs.fileInput.value = '';
    },

    async procesarArchivo() {
      this.procesando = true;
      this.progreso = 0;
      this.mensajeProgreso = 'Preparando importación...';

      const formData = new FormData();
      formData.append('archivo', this.archivo);
      formData.append('opciones', JSON.stringify(this.opciones));

      try {
        const response = await api.post('/api/alumnos/importar', formData, {
          headers: { 'Content-Type': 'multipart/form-data' },
          onUploadProgress: (progressEvent) => {
            this.progreso = Math.round((progressEvent.loaded * 100) / progressEvent.total);
          },
        });

        this.mensajeProgreso = 'Procesando datos...';
        this.progreso = 100;

        this.resultados = response.data;

        if (this.resultados.exito) {
          this.$toast?.success(`${this.resultados.importados} alumnos importados correctamente`);
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

<template>
  <div>
    <!-- Botón para abrir modal -->
    <button
      type="button"
      class="btn btn-primary"
      data-bs-toggle="modal"
      data-bs-target="#importarAlumnosModal"
    >
      <i class="fas fa-file-import me-2"></i>
      Importar Alumnos
    </button>

    <!-- Modal Bootstrap -->
    <div
      class="modal fade"
      id="importarAlumnosModal"
      tabindex="-1"
      aria-labelledby="importarAlumnosModalLabel"
      aria-hidden="true"
      ref="modal"
    >
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <!-- Header -->
          <div class="modal-header">
            <h5 class="modal-title" id="importarAlumnosModalLabel">
              <i class="fas fa-upload me-2"></i>
              Importar Alumnos desde Excel
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
                accept=".xlsx,.xls"
                @change="handleFileSelect"
                class="form-control"
              />
              <small class="form-text text-muted">
                Formatos aceptados: .xlsx, .xls (máx. 10MB)
              </small>
            </div>

            <!-- Archivo seleccionado -->
            <div
              v-if="archivo"
              class="alert alert-info d-flex align-items-center justify-content-between"
            >
              <div class="d-flex align-items-center">
                <i class="fas fa-file-excel fa-2x me-3 text-success"></i>
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

            <!-- Vista previa -->
            <div v-if="vistaPrevia.length > 0" class="mb-4">
              <h6 class="mb-3">Vista Previa (primeras {{ vistaPrevia.length }} filas)</h6>
              <div class="table-responsive">
                <table class="table table-sm table-bordered">
                  <thead class="table-light">
                    <tr>
                      <th>Nombre</th>
                      <th>Apellidos</th>
                      <th>Email</th>
                      <th>Clase</th>
                      <th>DNI</th>
                      <th>Matrícula</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(alumno, index) in vistaPrevia" :key="index">
                      <td>{{ alumno.nombre }}</td>
                      <td>{{ alumno.apellidos }}</td>
                      <td>
                        <small>{{ alumno.email }}</small>
                      </td>
                      <td>{{ alumno.clase }}</td>
                      <td>{{ alumno.dni }}</td>
                      <td>{{ alumno.matricula }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- Opciones -->
            <div v-if="archivo" class="mb-4">
              <h6 class="mb-3">Opciones de Importación</h6>

              <div class="form-check mb-2">
                <input
                  type="checkbox"
                  id="crearGrados"
                  v-model="opciones.crearGrados"
                  class="form-check-input"
                />
                <label for="crearGrados" class="form-check-label">
                  Crear grados automáticamente si no existen
                </label>
              </div>

              <div class="mb-3">
                <label for="contraseñaDefecto" class="form-label">Contraseña por defecto:</label>
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
              <small class="text-muted mt-2 d-block">{{ mensajeProgreso }}</small>
            </div>

            <!-- Resultados -->
            <div v-if="resultados">
              <div class="alert" :class="alertClass">
                <h6 class="alert-heading">
                  <i :class="alertIcon"></i>
                  Importación {{ resultados.exito ? 'Completada' : 'Finalizada con Errores' }}
                </h6>
                <p class="mb-0">
                  Alumnos importados: <strong>{{ resultados.importados }}</strong>
                </p>
                <p v-if="resultados.errores.length > 0" class="mb-0">
                  Errores: <strong>{{ resultados.errores.length }}</strong>
                </p>
              </div>

              <!-- Lista de errores -->
              <div v-if="resultados.errores.length > 0" class="alert alert-warning">
                <h6>Detalles de Errores:</h6>
                <ul class="mb-0" style="max-height: 200px; overflow-y: auto">
                  <li v-for="(error, index) in resultados.errores" :key="index">
                    <strong>Fila {{ error.fila }}:</strong> {{ error.mensaje }}
                    <small v-if="error.email" class="text-muted">({{ error.email }})</small>
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
                Importar Alumnos
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

<style scoped>
.table-responsive {
  max-height: 300px;
  overflow-y: auto;
}

.table th {
  position: sticky;
  top: 0;
  background: #f8f9fa;
  z-index: 1;
}

.table-sm td,
.table-sm th {
  font-size: 0.875rem;
}
</style>
