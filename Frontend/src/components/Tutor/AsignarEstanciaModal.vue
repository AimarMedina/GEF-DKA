<script setup>
import { ref, onMounted, watch, defineProps, defineEmits } from 'vue'
import axios from 'axios'

const props = defineProps({
  show: Boolean,
  alumno: Object
})

const emit = defineEmits(['close','crear'])

// Datos de la estancia a crear
const nuevaEstancia = ref({
  ID_Alumno: null,
  CIF_Empresa: '',
  Fecha_inicio: '',
  Fecha_fin: ''
})

// Lista de empresas
const empresas = ref([])

// Cargar empresas al montar el modal
onMounted(async () => {
  try {
    const token = localStorage.getItem('token')
    const res = await axios.get('http://localhost:8000/api/empresas', {
      headers: { Authorization: `Bearer ${token}` }
    })
    empresas.value = res.data || []
  } catch (err) {
    console.error(err)
    alert('Error al cargar las empresas')
  }
})

// Asignar ID del alumno cuando se abre el modal
watch(() => props.show, (val) => {
  if(val && props.alumno) {
    nuevaEstancia.value.ID_Alumno = props.alumno.ID_Usuario
    nuevaEstancia.value.CIF_Empresa = ''
    nuevaEstancia.value.Fecha_inicio = ''
    nuevaEstancia.value.Fecha_fin = ''
  }
})

// Crear estancia
async function crearEstancia() {
  try {
    const token = localStorage.getItem('token')
    const res = await axios.post('http://localhost:8000/api/asignarEstancia', nuevaEstancia.value, {
      headers: { Authorization: `Bearer ${token}` }
    })
    alert('Estancia creada correctamente')
    emit('crear', res.data)
    cerrarModal()
  } catch(err) {
    console.error(err)
    alert(err.response?.data?.message || 'Error al crear estancia')
  }
}

function cerrarModal() {
  emit('close')
}
</script>

<template>
  <div v-if="show" class="modal-backdrop-custom d-flex justify-content-center align-items-center">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content shadow-lg rounded-4 border-0">
        <!-- Header -->
        <div class="modal-header text-white" style="background-color: #4f46e5;">
          <h5 class="modal-title fw-bold">
            <i class="bi bi-building-fill me-2"></i>
            Asignar Estancia a {{ alumno?.Nombre }} {{ alumno?.Apellidos }}
          </h5>
          <button type="button" class="btn-close btn-close-white" @click="cerrarModal"></button>
        </div>

        <!-- Body -->
        <div class="modal-body py-4">
          <form @submit.prevent="crearEstancia" class="row g-4">
            <!-- Empresa -->
            <div class="col-12">
              <label for="empresa" class="form-label fw-semibold">Empresa</label>
              <select v-model="nuevaEstancia.CIF_Empresa" id="empresa" class="form-select form-select-lg" required>
                <option value="" disabled>Selecciona una empresa</option>
                <option v-for="e in empresas" :key="e.CIF" :value="e.CIF">
                  {{ e.Nombre }}
                </option>
              </select>
            </div>

            <!-- Fecha Inicio -->
            <div class="col-md-6">
              <label for="fechaInicio" class="form-label fw-semibold">Fecha Inicio</label>
              <input type="date" id="fechaInicio" v-model="nuevaEstancia.Fecha_inicio" class="form-control form-control-lg" required />
            </div>

            <!-- Fecha Fin -->
            <div class="col-md-6">
              <label for="fechaFin" class="form-label fw-semibold">Fecha Fin</label>
              <input type="date" id="fechaFin" v-model="nuevaEstancia.Fecha_fin" class="form-control form-control-lg" required />
            </div>
          </form>
        </div>

        <!-- Footer -->
        <div class="modal-footer py-3">
          <button type="button" class="btn btn-outline-secondary btn-lg" @click="cerrarModal">
            <i class="bi bi-x-circle me-1"></i> Cancelar
          </button>
          <button type="button" class="btn btn-indigo btn-lg text-white" @click="crearEstancia">
            <i class="bi bi-check-circle me-1"></i> Crear Estancia
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style>

.btn-indigo {
  background-color: #4f46e5;
  border-color: #4f46e5;
}

.btn-indigo:hover {
  background-color: #4338ca;
  border-color: #4338ca;
  color: white;
}

.modal-content {
  transition: transform 0.2s ease-in-out;
}
.modal-content:hover {
  transform: scale(1.01);
}
</style>
