<script setup>
import { ref } from 'vue'
import api from '@/services/api'

const props = defineProps({
  show: Boolean
})

const emit = defineEmits(['close', 'success'])

const file = ref(null)
const loading = ref(false)
const message = ref('')
const messageType = ref('')
const importResult = ref(null)

async function descargarPlantilla() {
  try {
    loading.value = true
    const response = await api.get('/api/teachers/import/template', {
      responseType: 'blob'
    })
    
    const blob = new Blob([response.data], { type: 'text/csv' })
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', `plantilla_profesorado_${new Date().toISOString().split('T')[0]}.csv`)
    document.body.appendChild(link)
    link.click()
    link.parentNode.removeChild(link)
    window.URL.revokeObjectURL(url)
  } catch (error) {
    message.value = 'Error al descargar la plantilla'
    messageType.value = 'error'
  } finally {
    loading.value = false
  }
}

function onFileSelected(event) {
  file.value = event.target.files[0]
}

async function importarProfesores() {
  if (!file.value) {
    message.value = 'Por favor selecciona un archivo CSV'
    messageType.value = 'error'
    return
  }

  try {
    loading.value = true
    message.value = ''
    messageType.value = ''

    const formData = new FormData()
    formData.append('file', file.value)

    const response = await api.post('/api/teachers/import', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })

    if (response.data.status === 'success') {
      importResult.value = response.data.data
      message.value = response.data.message
      messageType.value = 'success'
      file.value = null
      
      const fileInput = document.getElementById('csvFileInputTeachers')
      if (fileInput) fileInput.value = ''
      
      setTimeout(() => {
        emit('success')
      }, 1500)
    } else {
      message.value = response.data.message || 'Error en la importación'
      messageType.value = 'error'
    }
  } catch (error) {
    message.value = error.response?.data?.message || 'Error al procesar el archivo'
    messageType.value = 'error'
  } finally {
    loading.value = false
  }
}

function cerrarModal() {
  file.value = null
  message.value = ''
  messageType.value = ''
  importResult.value = null
  const fileInput = document.getElementById('csvFileInputTeachers')
  if (fileInput) fileInput.value = ''
  emit('close')
}
</script>

<template>
  <div v-if="show" class="modal d-block" style="background-color: rgba(0, 0, 0, 0.5)">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="bi bi-person-badge"></i> Importar Profesorado
          </h5>
          <button
            type="button"
            class="btn-close"
            @click="cerrarModal"
            :disabled="loading"
          ></button>
        </div>

        <div class="modal-body">
          <div
            v-if="message"
            :class="`alert alert-${messageType === 'success' ? 'success' : 'danger'} alert-dismissible fade show`"
            role="alert"
          >
            {{ message }}
            <button
              type="button"
              class="btn-close"
              @click="message = ''"
              v-if="messageType === 'success'"
            ></button>
          </div>

          <div v-if="importResult" class="mb-3">
            <div class="alert alert-info">
              <strong>Resumen de importación:</strong>
              <ul class="mb-0 mt-2">
                <li><strong>Total profesores procesados:</strong> {{ importResult.created + importResult.failed }}</li>
                <li><strong class="text-success">✓ Profesores creados:</strong> {{ importResult.created }}</li>
                <li v-if="importResult.failed > 0" class="text-danger">
                  <strong>✗ Profesores fallidos:</strong> {{ importResult.failed }}
                </li>
              </ul>
            </div>

            <div v-if="importResult.errors && importResult.errors.length > 0" class="alert alert-warning">
              <strong>Errores encontrados:</strong>
              <ul class="mb-0 mt-2 small">
                <li v-for="(error, index) in importResult.errors" :key="index">
                  {{ error }}
                </li>
              </ul>
            </div>
          </div>

          <div class="card mb-3 bg-light">
            <div class="card-body">
              <h6 class="card-title mb-2">
                <i class="bi bi-file-earmark-spreadsheet"></i> 1. Descargar Plantilla
              </h6>
              <p class="text-muted small">Si no tienes una plantilla, descarga la plantilla de ejemplo con el formato correcto.</p>
              <button
                class="btn btn-outline-primary btn-sm"
                @click="descargarPlantilla"
                :disabled="loading"
              >
                <i class="bi bi-download"></i> Descargar Plantilla CSV
              </button>
            </div>
          </div>

          <div class="card">
            <div class="card-body">
              <h6 class="card-title mb-2">
                <i class="bi bi-upload"></i> 2. Subir Archivo CSV
              </h6>
              <p class="text-muted small">
                <strong>Campos requeridos:</strong> nombre, apellidos, email, n_tel, password, tipo, cif_empresa
              </p>
              <p class="text-muted small">
                <strong>Tipo:</strong> tutor o instructor (cif_empresa solo para instructores)
              </p>

              <div class="mb-3">
                <input
                  id="csvFileInputTeachers"
                  type="file"
                  class="form-control"
                  accept=".csv,.txt"
                  @change="onFileSelected"
                  :disabled="loading"
                />
                <small class="text-muted">Máximo 5MB. Formato: CSV o TXT</small>
              </div>

              <div v-if="file" class="alert alert-info small mb-2">
                <i class="bi bi-check-circle"></i> Archivo seleccionado: <strong>{{ file.name }}</strong>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button
            type="button"
            class="btn btn-secondary"
            @click="cerrarModal"
            :disabled="loading"
          >
            Cerrar
          </button>
          <button
            type="button"
            class="btn btn-primary"
            @click="importarProfesores"
            :disabled="!file || loading"
          >
            <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
            {{ loading ? 'Importando...' : 'Importar Profesorado' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.modal.d-block {
  display: block !important;
}

.modal-header {
  display: flex;
  align-items: center;
}
.modal-header .modal-title {
  margin: 0;
}
.modal-header .btn-close {
  margin-left: auto;
}

.alert {
  position: relative;
  padding-right: 3rem;
}
.alert .btn-close {
  position: absolute;
  right: 0.75rem;
  top: 0.5rem;
}
</style>
