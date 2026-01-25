<script setup>
import { ref, onMounted } from 'vue'
import { useUserStore } from '@/stores/userStore'
import api from '@/services/api.js'

const notas = ref(null)
const mensaje = ref('')
const loading = ref(true)

const userStore = useUserStore()
const id = userStore.user?.id

const cargarNotas = async () => {
  loading.value = true
  try {
    const token = localStorage.getItem('token')
    const res = await api.get(`/api/alumno/${id}/mis-notasAlumno`, {
      headers: { Authorization: `Bearer ${token}` }
    })
    notas.value = res.data || null
  } catch (err) {
    console.error(err)
    mensaje.value = 'Error al cargar las notas'
  } finally {
    loading.value = false
  }
}

const getNotaFinalClass = (nota) => {
  if (nota === '-' || nota === null) return 'text-danger bg-danger-subtle'
  if (nota >= 5) return 'text-success bg-success-subtle'
  return 'text-dark bg-warning-subtle'
}

onMounted(cargarNotas)
</script>

<template>
  <div class="container mt-4">
    <h3 class="mb-4 text-center text-md-start text-indigo fw-bold">Mis Notas</h3>

    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-indigo" role="status"></div>
      <p class="mt-2 text-muted">Cargando notas...</p>
    </div>

    <div v-else-if="mensaje" class="alert alert-danger">{{ mensaje }}</div>

    <div v-else-if="notas">
      <div class="mb-4 p-4 bg-indigo-light rounded shadow-sm text-center text-md-start">
        <h4 class="text-indigo fw-bold">{{ notas.usuario.nombre }} {{ notas.usuario.apellidos }}</h4>
        <p class="mb-1"><strong>Grado:</strong> {{ notas.grado.nombre }}</p>
        <p class="mb-0"><strong>Nota Transversal:</strong> {{ notas.notas_transversales ?? '-' }} | <strong>Cuaderno:</strong> {{ notas.nota_cuaderno ?? '-' }}</p>
      </div>

      <div class="row g-4">
        <div v-for="asig in notas.asignaturas" :key="asig.id" class="col-12 col-md-6">
          <div class="card shadow-sm border border-indigo">
            <div class="card-body">
              <h5 class="card-title text-indigo fw-bold">{{ asig.nombre }}</h5>

              <ul class="list-group list-group-flush">
                <li class="list-group-item d-flex justify-content-between align-items-center">
                  Nota Egibide
                  <span class="fw-semibold">{{ notas.notas_calculadas[asig.id]?.egibide ?? '-' }}</span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                  Parte Técnica
                  <span class="fw-semibold">{{ notas.notas_calculadas[asig.id]?.tecnica ?? '-' }}</span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                  Nota Final
                  <span
                    class="fw-semibold px-2 py-1 rounded"
                    :class="getNotaFinalClass(notas.notas_calculadas[asig.id]?.final)"
                  >
                    {{ notas.notas_calculadas[asig.id]?.final ?? '-' }}
                  </span>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Sin notas -->
    <div v-else>
      <p class="text-center text-muted">No se encontraron notas para este alumno.</p>
    </div>
  </div>
</template>

<style scoped>


.bg-indigo-light {
  background-color: #eef2ff !important;
}

</style>

