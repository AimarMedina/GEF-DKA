<script setup>
import { ref, computed, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import AsignarEstanciaModal from '@/components/Tutor/AsignarEstanciaModal.vue'
import NotasAlumno from '@/components/Tutor/NotasAlumno.vue'
import axios from 'axios'

const props = defineProps({
  alumno: Object
})

const router = useRouter()
const route = useRoute()
const isTutorView = computed(() => route.name === 'alumnosTutor')

const showEstanciaModal = ref(false)
const notasAlumno = ref({
  nota_cuaderno: [],
  notas_competencias: [],
  notas_transversales: [],
  notas_egibide: []
})

// Cargar notas automáticamente al cambiar el alumno
watch(
  () => props.alumno,
  async (alumno) => {
    if (!alumno) {
      notasAlumno.value = {
        nota_cuaderno: [],
        notas_competencias: [],
        notas_transversales: [],
        notas_egibide: []
      }
      return
    }
console.log(alumno)
    try {
      const token = localStorage.getItem('token')
      const res = await axios.get(
        `http://localhost:8000/api/alumno/${alumno.ID_Usuario}/mis-notas`,
        { headers: { Authorization: `Bearer ${token}` } }
      )

      const data = res.data
      console.log(data)
      notasAlumno.value = {
        nota_cuaderno: data?.nota_cuaderno || [],
        notas_competencias: data?.notas_competencias || [],
        notas_transversales: data?.notas_transversales || [],
        notas_egibide: data?.notas_egibide || []
      }

    } catch (e) {
      console.error('Error cargando notas', e)
      notasAlumno.value = {
        nota_cuaderno: [],
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
  <div v-if="alumno">
    <div class="card shadow-sm mt-3">
      <div class="card-header bg-indigo text-white">
        <h5 class="mb-0">{{ alumno?.Nombre }} {{ alumno?.Apellidos }}</h5>
      </div>

      <div class="card-body">
        <p><strong>Email:</strong> {{ alumno?.Email || 'N/A' }}</p>
        <p><strong>Grado:</strong> {{ alumno?.Grado || 'N/A' }}</p>

        <div class="d-flex gap-2 mt-3" v-if="isTutorView">
          <button
            v-if="alumno?.estancia_id"
            class="btn btn-primary"
            @click="router.push(`/tutor/seguimiento/${alumno.estancia_id}`)"
          >
            <i class="bi bi-bar-chart-line"></i> Seguimiento
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
        v-if="alumno && alumno?.estancia_id"
        :alumno="alumno"
        :notas="notasAlumno"
        />

      </div>

      <AsignarEstanciaModal
        :alumno="alumno"
        :show="showEstanciaModal"
        @close="showEstanciaModal = false"
      />
    </div>
  </div>
</template>
