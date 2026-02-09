<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useUserStore } from '@/stores/userStore'
import api from '@/services/api.js'

const props = defineProps({
  endpoint: String
})

const emit = defineEmits(['seleccionarAlumno'])

const userStore = useUserStore()
const rol = userStore.user.tipo

const alumnos = ref([])
const cargando = ref(false)
const alumnoSeleccionado = ref(null)

// Función de carga
async function cargarAlumnos() {
  if (!props.endpoint) return // Evitar llamadas si el endpoint no existe
  
  cargando.value = true
  try {
    const res = await api.get(props.endpoint)
    alumnos.value = res.data
    // Limpiar selección al cargar nuevos datos
    alumnoSeleccionado.value = null 
  } catch (e) {
    console.error('Error cargando alumnos', e)
    alumnos.value = []
  } finally {
    cargando.value = false
  }
}

// IMPORTANTE: Vigilar cambios en el endpoint para recargar
watch(() => props.endpoint, () => {
  cargarAlumnos()
})

function seleccionarAlumno(a) {
  alumnoSeleccionado.value = a
  emit('seleccionarAlumno', a)
}

defineExpose({
  recargar: cargarAlumnos
})

onMounted(cargarAlumnos)

// --- Computeds (se mantienen igual) ---
const alumnosConEstancia = computed(() => {
  if (rol === 'tutor') return alumnos.value.filter(a => a.estancia_actual?.id)
  return alumnos.value
})

const alumnosSinEstancia = computed(() => {
  if (rol === 'tutor') return alumnos.value.filter(a => !a.estancia_actual?.id)
  return []
})
</script>

<template>
  <div class="col-md-3 mt-3">

    <!-- Solo para tutores -->
    <div v-if="rol === 'tutor'" class="mb-4">
      <div class="list-group shadow-sm">
        <div class="list-group-item bg-success text-white fw-semibold">Alumnos con estancia</div>
        <li v-for="a in alumnosConEstancia" :key="a.ID_Usuario"
            class="list-group-item cursor-pointer"
            :class="{ 'bg-light text-dark': alumnoSeleccionado?.ID_Usuario === a.ID_Usuario }"
            @click="seleccionarAlumno(a)">
          {{ a.usuario?.nombre }} {{ a.usuario?.apellidos }}
        </li>
        <div v-if="!alumnosConEstancia.length && !cargando" class="list-group-item text-muted text-center">
          Ninguno
        </div>
      </div>
    </div>

    <div v-if="rol === 'tutor'">
      <div class="list-group shadow-sm">
        <div class="list-group-item bg-warning fw-semibold">Alumnos sin estancia</div>
        <li v-for="a in alumnosSinEstancia" :key="a.ID_Usuario"
            class="list-group-item cursor-pointer"
            :class="{ 'bg-light text-dark': alumnoSeleccionado?.ID_Usuario === a.ID_Usuario }"
            @click="seleccionarAlumno(a)">
          {{ a.usuario?.nombre }} {{ a.usuario?.apellidos }}
        </li>
        <div v-if="!alumnosSinEstancia.length && !cargando" class="list-group-item text-muted text-center">
          Ninguno
        </div>
      </div>
    </div>

    <!-- Para instructor o roles que solo vean su lista -->
    <div v-else class="list-group shadow-sm">
      <div class="list-group-item bg-indigo text-white fw-semibold">Mis alumnos</div>
      <li v-for="a in alumnosConEstancia" :key="a.ID_Usuario"
          class="list-group-item cursor-pointer"
          :class="{ 'bg-light text-dark': alumnoSeleccionado?.ID_Usuario === a.ID_Usuario }"
          @click="seleccionarAlumno(a)">
        {{ a.usuario?.nombre }} {{ a.usuario?.apellidos }}
      </li>
    </div>

    <!-- ================= LOADING ================= -->
    <div v-if="cargando" class="text-center mt-3">
      <div class="spinner-border text-indigo"></div>
    </div>

  </div>
</template>
