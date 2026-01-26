<script setup>
import { watch, ref } from 'vue'
import api from '@/services/api.js'

const props = defineProps({
  alumno: Object
})

const competencias = ref([])
const competenciasDisponibles = ref([])
const transversales = ref([])
const cargando = ref(false)
const cargandoTransversales = ref(false)
const competenciaSeleccionada = ref("")

watch(
  () => props.alumno,
  async (nuevo) => {
    if (!nuevo || !nuevo.estancia_actual?.id || !nuevo.grado?.id) {
      competencias.value = []
      competenciasDisponibles.value = []
      transversales.value = []
      return
    }

    cargando.value = true

    try {
      // Cargar competencias de la estancia
      const resEstancia = await api.get(
        `/api/estancias/${nuevo.estancia_actual.id}/competencias`,
        {
          params: {
            ID_Alumno: nuevo.ID_Usuario
          }
        }
      );

      competencias.value = resEstancia.data.competencias.map(c => ({
        ...c,
        nota: c.notas?.[0]?.Nota ?? null
      }));

      // Cargar competencias disponibles del grado
      const resGrados = await api.get(
        `/api/grados/${nuevo.grado.id}/competencias`
      )

      competenciasDisponibles.value = resGrados.data.filter(
        c => !competencias.value.some(asignada => asignada.id === c.id)
      )

      console.log('Competencias:', competencias.value);

    } catch (e) {
      console.error('Error cargando competencias', e)
      competencias.value = []
      competenciasDisponibles.value = []
    } finally {
      cargando.value = false
    }

    // Cargar transversales
    await cargarTransversales()
  },
  { immediate: true }
)

async function cargarTransversales() {
  if (!props.alumno?.ID_Usuario) return
  
  cargandoTransversales.value = true
  try {
    
    const res = await api.get(`/api/transversales/alumno/${props.alumno.ID_Usuario}`)
    
    transversales.value = res.data
    console.log(transversales.value)
  } catch (e) {
    console.error('Error cargando transversales', e)
    transversales.value = []
  } finally {
    cargandoTransversales.value = false
  }
}

const agregarCompetencia = async () => {
  if (!competenciaSeleccionada.value || !props.alumno?.estancia_actual?.id) {
    return
  }

  const competenciaId = competenciaSeleccionada.value
  
  try {
    const res = await api.post(
      `/api/estancias/${props.alumno.estancia_actual.id}/competencias`,
      { ID_Comp: competenciaId }
    )

    const competencia = competenciasDisponibles.value.find(
      c => c.id === competenciaId
    )

    if (competencia) {
      competencias.value.push({...competencia, nota: null})
      competenciasDisponibles.value = competenciasDisponibles.value.filter(
        c => c.id !== competenciaId
      )
    }

    competenciaSeleccionada.value = ""

  } catch (e) {
    console.error('Error añadiendo competencia', e)
  }
}

const actualizarNota = async (competencia) => {

  try {
    await api.put(
      `/api/alumnos/${props.alumno.ID_Usuario}/competencias/${competencia.id}/nota`,
      { nota: competencia.nota }
    )
  } catch (e) {
    console.error('Error guardando nota', e)
  }
}

const actualizarNotaTransversal = async (transversal) => {
  
  try {
    await api.put(
      `/api/alumnos/${props.alumno.ID_Usuario}/transversales/${transversal.id}/nota`,
      { nota: transversal.nota }
    )
    console.log('Nota transversal guardada:', transversal.nota)
  } catch (e) {
    console.error('Error guardando nota transversal', e)
    alert('Error al guardar la nota transversal')
  }
}

const eliminarCompetencia = async (competencia) => {
  if (!props.alumno?.estancia_actual?.id) return;

  try {
    await api.delete(
      `/api/estancias/${props.alumno.estancia_actual.id}/competencias/${competencia.id}`
    );

    competencias.value = competencias.value.filter(c => c.id !== competencia.id);
    competenciasDisponibles.value.push(competencia);

  } catch (e) {
    console.error('Error eliminando competencia', e);
  }
}
</script>

<template>
  <div v-if="alumno" class="card shadow-sm col-md-9 mt-3">
    <div class="card-body">
      <h4>{{ alumno.usuario.nombre }} {{ alumno.usuario.apellidos }}</h4>
      <p class="text-muted">{{ alumno.usuario.email }}</p>
      <p><strong>Grado:</strong> {{ alumno.grado.nombre }}</p>

      <hr />

      <!-- SECCIÓN COMPETENCIAS TÉCNICAS -->
      <h5>Competencias Técnicas</h5>

      <div v-if="cargando" class="spinner-border text-indigo"></div>

      <ul v-else-if="competencias.length" class="list-group mb-3">
        <li v-for="c in competencias" :key="c.id"
          class="list-group-item d-flex justify-content-between align-items-center">
          <span>{{ c.descripcion }}</span>

          <div class="d-flex align-items-center gap-2">
            <select class="form-select form-select-sm w-auto" v-model="c.nota" @change="actualizarNota(c)">
              <option :value="null" disabled>–</option>
              <option v-for="n in [1, 2, 3, 4]" :key="n" :value="n">{{ n }}</option>
            </select>

            <i class="bi bi-x-lg text-danger" style="cursor: pointer;" @click="eliminarCompetencia(c)"></i>
          </div>
        </li>
      </ul>

      <p v-else class="text-muted mb-3">
        No hay competencias técnicas asignadas
      </p>

      <!-- Añadir competencia técnica -->
      <div class="input-group mb-4">
        <select v-model="competenciaSeleccionada" class="form-select">
          <option value="" disabled>Selecciona una competencia</option>
          <option v-for="c in competenciasDisponibles" :key="c.id" :value="c.id">
            {{ c.descripcion }}
          </option>
        </select>
        <button class="btn btn-indigo text-white" @click="agregarCompetencia" :disabled="!competenciaSeleccionada">
          Añadir
        </button>
      </div>

      <hr />

      <!-- SECCIÓN COMPETENCIAS TRANSVERSALES -->
      <h5>Competencias Transversales</h5>

      <div v-if="cargandoTransversales" class="spinner-border text-indigo"></div>

      <ul v-else-if="transversales.length" class="list-group mb-3">
        <li v-for="t in transversales" :key="t.id"
          class="list-group-item d-flex justify-content-between align-items-center">
          <span>{{ t.descripcion }}</span>

          <select 
            class="form-select form-select-sm w-auto" 
            v-model="t.nota" 
            @change="actualizarNotaTransversal(t)"
          >
            <option :value="null">–</option>
            <option v-for="n in [1, 2, 3, 4]" :key="n" :value="n">{{ n }}</option>
          </select>
        </li>
      </ul>

      <p v-else class="text-muted">
        No hay competencias transversales disponibles
      </p>

    </div>
  </div>

  <p v-else class="text-muted text-center mt-4">
    Selecciona un alumno
  </p>
</template>