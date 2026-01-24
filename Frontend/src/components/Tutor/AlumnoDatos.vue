<script setup>
import { ref, computed, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import AsignarEstanciaModal from '@/components/Tutor/AsignarEstanciaModal.vue'
import NotasAlumno from '@/components/Tutor/NotasAlumno.vue'
import AsignarInstructorModal from '@/components/Tutor/AsignarInstructorModal.vue'
import ConfirmarEliminar from '@/components/ConfirmarEliminar.vue'
import api from '@/services/api.js'

const props = defineProps({
  alumno: Object
})
const emit = defineEmits(['estanciaCreada', 'estanciaEliminada', 'instructorAsignado'])

const router = useRouter()
const route = useRoute()
const isTutorView = computed(() => route.name === 'alumnosTutor')

const showEstanciaModal = ref(false)
const showInstructorModal = ref(false)
const eliminarEstanciaModal = ref(false)

const notasAlumno = ref({
  nota_cuaderno: [],
  notas_competencias: [],
  notas_transversales: [],
  notas_egibide: []
})

function estanciaCreada() {
  showEstanciaModal.value = false
  emit('estanciaCreada')
}

async function instructorAsignado() {
  showInstructorModal.value = false
  
  // Recargar los datos del alumno para obtener el instructor actualizado
  try {
    const token = localStorage.getItem('token')
    const res = await api.get(
      `/api/alumno/${props.alumno.ID_Usuario}/mis-notas`,
      { headers: { Authorization: `Bearer ${token}` } }
    )
    
    // Actualizar el instructor en el objeto alumno
    if (res.data.instructor) {
      props.alumno.instructor = res.data.instructor
    }
  } catch (e) {
    console.error('Error recargando datos del instructor:', e)
  }
  
  emit('instructorAsignado')
}

function abrirEliminarEstanciaModal() {
  eliminarEstanciaModal.value = true
}

async function confirmarEliminarEstancia(confirmado) {
  if (!confirmado || !props.alumno?.estancia_actual?.id) {
    eliminarEstanciaModal.value = false
    return
  }
  
  try {
    await eliminarEstancia(props.alumno.estancia_actual.id)
    eliminarEstanciaModal.value = false
  } catch (err) {
    // El error ya se maneja en eliminarEstancia
    eliminarEstanciaModal.value = false
  }
}

async function eliminarEstancia(estanciaId) {
  try {
    const token = localStorage.getItem('token')
    await api.delete(`/api/estancia/${estanciaId}`, {
      headers: { Authorization: `Bearer ${token}` }
    })
    console.log('Estancia eliminada correctamente')
    
    // Limpiar la estancia del alumno para que baje a la tabla de sin estancia
    if (props.alumno) {
      props.alumno.estancia_actual = null
      props.alumno.instructor = null
    }
    
    emit('estanciaEliminada')
  } catch (err) {
    console.error('Error al eliminar estancia:', err)
    alert('Error al eliminar la estancia')
    throw err // Re-lanzar el error para que confirmarEliminarEstancia lo capture
  }
}

// Cargar notas automáticamente al cambiar el alumno
watch(
  () => props.alumno,
  async (alumno) => {
    if (!alumno) {
      notasAlumno.value = {
        nota_cuaderno: null,
        notas_competencias: [],
        notas_transversales: [],
        notas_egibide: []
      }
      return
    }

    try {
      const token = localStorage.getItem('token')
      const res = await api.get(
        `/api/alumno/${alumno.ID_Usuario}/mis-notas`,
        { headers: { Authorization: `Bearer ${token}` } }
      )

      const data = res.data
      console.log('Datos del alumno:', data)
      
      // Solo actualizar si vienen datos válidos (para alumnos CON estancia)
      if (data.instructor) {
        alumno.instructor = data.instructor
      }
      if (data.estancia_actual) {
        alumno.estancia_actual = data.estancia_actual
      }
      
      notasAlumno.value = {
        nota_cuaderno: data.nota_cuaderno ?? null,
        notas_competencias: data.notas_competencias ?? [],
        notas_transversales: data.notas_transversales ?? [],
        notas_egibide: data.grado?.asignaturas?.map(a => ({
          id_asignatura: a.id,
          asignatura: a.nombre,
          nota: a.nota_egibide ? Number(a.nota_egibide.nota) : null
        })) ?? []
      }

    } catch (e) {
      console.error('Error cargando notas', e)
      notasAlumno.value = {
        nota_cuaderno: null,
        notas_competencias: [],
        notas_transversales: [],
        notas_egibide: []
      }
    }
  },
  { immediate: true }
)
</script>

<template>
  <div v-if="alumno" class="col-md-9 mt-3">
    <div class="card shadow-sm mt-3">
      <div class="card-header bg-indigo text-white">
        <h5 class="mb-0">{{ alumno?.usuario?.nombre }} {{ alumno?.usuario?.apellidos }}</h5>
      </div>

      <div class="card-body">
        <p><strong>Email:</strong> {{ alumno?.usuario?.email || 'N/A' }}</p>
        <p><strong>Grado:</strong> {{ alumno?.grado?.nombre || 'N/A' }}</p>
        <p><strong>Empresa:</strong> {{ alumno?.estancia_actual?.empresa?.nombre || 'N/A' }}</p>

        <!-- Mostrar instructor o botón para asignar -->
        <div class="d-flex align-items-center gap-2 mb-3">
          <strong>Instructor:</strong>
          <span v-if="alumno?.instructor?.user">
            {{ alumno.instructor.user.nombre }} {{ alumno.instructor.user.apellidos }}
          </span>
          <span v-else-if="!alumno?.estancia_actual?.id">
            —
          </span>
          <button 
            v-else-if="!alumno?.instructor" 
            class="btn btn-sm btn-warning"
            @click="showInstructorModal = true"
          >
            <i class="bi bi-person-plus"></i> Asignar instructor
          </button>
        </div>

        <div class="d-flex gap-2 mt-3" v-if="isTutorView">
          <button 
            v-if="alumno?.estancia_actual?.id" 
            class="btn btn-primary"
            @click="router.push(`/tutor/seguimiento/${alumno.estancia_actual.id}`)"
          >
            <i class="bi bi-bar-chart-line"></i> Seguimiento
          </button>

          <!-- Botón para eliminar estancia -->
          <button 
            v-if="alumno?.estancia_actual?.id" 
            class="btn btn-danger"
            @click="abrirEliminarEstanciaModal"
          >
            <i class="bi bi-trash"></i> Eliminar estancia
          </button>

          <button 
            v-else 
            class="btn btn-warning" 
            @click="showEstanciaModal = true"
          >
            <i class="bi bi-building"></i> Asignar estancia
          </button>
        </div>

        <!-- Notas -->
        <NotasAlumno 
          v-if="alumno && alumno?.estancia_actual?.id" 
          :alumno="alumno" 
          :notas="notasAlumno" 
        />
      </div>

      <!-- Modales -->
      <AsignarEstanciaModal 
        :alumno="alumno" 
        :show="showEstanciaModal" 
        @close="showEstanciaModal = false"
        @crear="estanciaCreada" 
      />

      <AsignarInstructorModal
        :alumno="alumno"
        :show="showInstructorModal"
        @close="showInstructorModal = false"
        @asignado="instructorAsignado"
      />

      <ConfirmarEliminar
        :show="eliminarEstanciaModal"
        mensaje="¿Seguro que deseas eliminar la estancia de este alumno? Esta acción también eliminará todos los seguimientos asociados."
        @confirm="confirmarEliminarEstancia"
        @close="eliminarEstanciaModal = false"
      />
    </div>
  </div>
</template>