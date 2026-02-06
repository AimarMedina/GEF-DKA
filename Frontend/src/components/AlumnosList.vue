<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import Buscador from '@/components/Buscador.vue'
import { useUserStore } from '@/stores/userStore'
import api from '@/services/api.js'

const prop = defineProps({
  endpoint: String
})
defineExpose({
  recargar: cargarAlumnos
})

const userStore = useUserStore()
const tutorId = userStore.user.id
const rol = userStore.user.tipo

const alumnos = ref([])
const cargando = ref(false)
const alumnoSeleccionado = ref(null)
const emit = defineEmits(['seleccionarAlumno'])

/* ===================== NUEVO: SIN ASIGNAR + PAGINACIÓN ===================== */
const alumnosSinAsignar = ref([])
const cargandoSinAsignar = ref(false)
const pageSinAsignar = ref(1)
const lastPageSinAsignar = ref(1)
/* ========================================================================== */

const alumnosConEstancia = computed(() => {
  if (rol === 'tutor') return alumnos.value.filter(a => a.estancia_actual?.id)
  if (rol === 'instructor') return alumnos.value // Instructor ve solo los suyos
  return alumnos.value
})

const alumnosSinEstancia = computed(() => {
  if (rol === 'tutor') return alumnos.value.filter(a => !a.estancia_actual?.id)
  return [] // Instructor no necesita esta lista
})

async function cargarAlumnos() {
  cargando.value = true
  try {
    const res = await api.get(prop.endpoint)
    alumnos.value = res.data
  } catch (e) {
    console.error('Error cargando alumnos', e)
    alumnos.value = []
  } finally {
    cargando.value = false
  }
}

/* ===================== NUEVO: CARGAR SIN ASIGNAR ===================== */
async function cargarAlumnosSinAsignar(page = 1) {
  if (rol !== 'tutor') return

  cargandoSinAsignar.value = true
  try {
    const res = await api.get('/api/tutor/alumnos-sin-asignar', {
      params: { page, per_page: 5 }
    })

    alumnosSinAsignar.value = res.data.data
    pageSinAsignar.value = res.data.current_page
    lastPageSinAsignar.value = res.data.last_page
  } catch (e) {
    console.error('Error cargando alumnos sin asignar', e)
    alumnosSinAsignar.value = []
    pageSinAsignar.value = 1
    lastPageSinAsignar.value = 1
  } finally {
    cargandoSinAsignar.value = false
  }
}
/* ==================================================================== */

/* ===================== NUEVO: ASIGNAR ALUMNO AL TUTOR ===================== */
async function asignarmeAlumno(a) {
  try {
    const res = await api.put(`/api/alumnos/${a.ID_Usuario}/asignar-tutor`)
    const alumnoActualizado = res.data

    alumnosSinAsignar.value = alumnosSinAsignar.value.filter(x => x.ID_Usuario !== a.ID_Usuario)
    alumnos.value.push(alumnoActualizado)
    seleccionarAlumno(alumnoActualizado)
  } catch (e) {
    console.error('Error asignando alumno al tutor', e)
    alert('No se ha podido asignar el alumno')
  }
}
/* ======================================================================== */

/* ===================== NUEVO: DESASIGNAR ALUMNO DEL TUTOR ===================== */
async function desasignarAlumno(a) {
  try {
    // Endpoint esperado: debe dejar ID_Tutor a null (y devolver el alumno actualizado)
    const res = await api.put(`/api/alumnos/${a.ID_Usuario}/desasignar-tutor`)
    const alumnoActualizado = res.data

    // 1) quitar de la lista principal (ya no es "mi alumno")
    alumnos.value = alumnos.value.filter(x => x.ID_Usuario !== a.ID_Usuario)

    // 2) recargar "sin asignar" para que aparezca (paginado)
    await cargarAlumnosSinAsignar(pageSinAsignar.value)

    // 3) si estaba seleccionado, lo limpiamos
    if (alumnoSeleccionado.value?.ID_Usuario === a.ID_Usuario) {
      alumnoSeleccionado.value = null
    }
  } catch (e) {
    console.error('Error desasignando alumno del tutor', e)
    alert('No se ha podido desasignar el alumno')
  }
}
/* =========================================================================== */

function seleccionarAlumno(a) {
  alumnoSeleccionado.value = a
  emit('seleccionarAlumno', a)
}

onMounted(async () => {
  await cargarAlumnos()
  await cargarAlumnosSinAsignar(1)
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

        <!-- CAMBIO: ahora cada alumno tiene botón "Desasignar" -->
        <li v-for="a in alumnosSinEstancia" :key="a.ID_Usuario"
            class="list-group-item d-flex justify-content-between align-items-center"
            :class="{ 'bg-light text-dark': alumnoSeleccionado?.ID_Usuario === a.ID_Usuario }">
          <div class="cursor-pointer" @click="seleccionarAlumno(a)">
            {{ a.usuario?.nombre }} {{ a.usuario?.apellidos }}
          </div>

          <button class="btn btn-sm btn-outline-warning"
                  :disabled="cargando"
                  @click.stop="desasignarAlumno(a)">
            Desasignar
          </button>
        </li>

        <div v-if="!alumnosSinEstancia.length && !cargando" class="list-group-item text-muted text-center">
          Ninguno
        </div>
      </div>
    </div>

    <!-- ===================== NUEVO: ALUMNOS SIN ASIGNAR ===================== -->
    <div v-if="rol === 'tutor'" class="mt-4">
      <div class="list-group shadow-sm">
        <div class="list-group-item bg-secondary text-white fw-semibold">
          Alumnos sin asignar
        </div>

        <li v-for="a in alumnosSinAsignar" :key="a.ID_Usuario"
            class="list-group-item d-flex justify-content-between align-items-center">
          <div class="cursor-pointer"
               :class="{ 'fw-semibold': alumnoSeleccionado?.ID_Usuario === a.ID_Usuario }"
               @click="seleccionarAlumno(a)">
            {{ a.usuario?.nombre }} {{ a.usuario?.apellidos }}
          </div>

          <button class="btn btn-sm btn-outline-dark"
                  :disabled="cargandoSinAsignar"
                  @click.stop="asignarmeAlumno(a)">
            Asignarme
          </button>
        </li>

        <div v-if="cargandoSinAsignar" class="list-group-item text-center">
          <div class="spinner-border spinner-border-sm"></div>
        </div>

        <div v-if="!alumnosSinAsignar.length && !cargandoSinAsignar" class="list-group-item text-muted text-center">
          Ninguno
        </div>

        <!-- Paginación -->
        <div class="list-group-item d-flex justify-content-between align-items-center">
          <button class="btn btn-sm btn-outline-dark"
                  :disabled="pageSinAsignar <= 1 || cargandoSinAsignar"
                  @click="cargarAlumnosSinAsignar(pageSinAsignar - 1)">
            Anterior
          </button>

          <span class="small text-muted">
            Página {{ pageSinAsignar }} / {{ lastPageSinAsignar }}
          </span>

          <button class="btn btn-sm btn-outline-dark"
                  :disabled="pageSinAsignar >= lastPageSinAsignar || cargandoSinAsignar"
                  @click="cargarAlumnosSinAsignar(pageSinAsignar + 1)">
            Siguiente
          </button>
        </div>
      </div>
    </div>
    <!-- ==================================================================== -->

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