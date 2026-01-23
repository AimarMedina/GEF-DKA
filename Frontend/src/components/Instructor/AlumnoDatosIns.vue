<script setup>
import { watch, ref } from 'vue'
import axios from 'axios'

const props = defineProps({
  alumno: Object
})

const competencias = ref([])
const competenciasDisponibles = ref([])
const cargando = ref(false)
const competenciaSeleccionada = ref("")

watch(
  () => props.alumno,
  async (nuevo) => {
    if (!nuevo || !nuevo.estancia_actual?.id || !nuevo.grado?.id) {
      competencias.value = []
      competenciasDisponibles.value = []
      return
    }

    cargando.value = true
    const token = localStorage.getItem('token')

    try {
      const resEstancia = await axios.get(
        `http://127.0.0.1:8000/api/estancias/${nuevo.estancia_actual.id}/competencias`,
        {
          params: {
            ID_Alumno: nuevo.ID_Usuario
          },
          headers: { Authorization: `Bearer ${token}` },
        }
      );

      competencias.value = resEstancia.data.competencias.map(c => ({
        ...c,
        nota: c.notas?.[0]?.Nota ?? null
      }));

      const resGrados = await axios.get(
        `http://127.0.0.1:8000/api/grados/${nuevo.grado.id}/competencias`,
        { headers: { Authorization: `Bearer ${token}` } }
      )


      competenciasDisponibles.value = resGrados.data.filter(
        c => !competencias.value.some(asignada => asignada.id === c.id)
      )

    console.log(competencias.value);

    } catch (e) {
      console.error('Error cargando competencias', e)
      competencias.value = []

      competenciasDisponibles.value = []
    } finally {
      cargando.value = false
    }
  },
  { immediate: true }
)
const agregarCompetencia = async () => {
  if (!competenciaSeleccionada.value || !props.alumno?.estancia_actual?.id) {
    return
  }

  const token = localStorage.getItem('token')


  const competenciaId = competenciaSeleccionada.value
  try {
    const res = await axios.post(
      `http://127.0.0.1:8000/api/estancias/${props.alumno.estancia_actual.id}/competencias`,
      { ID_Comp: competenciaId },
      { headers: { Authorization: `Bearer ${token}` } }
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
  const token = localStorage.getItem('token')
  try {
    await axios.put(
      `http://127.0.0.1:8000/api/alumnos/${props.alumno.ID_Usuario}/competencias/${competencia.id}/nota`,
      { nota: competencia.nota },
      { headers: { Authorization: `Bearer ${token}` } }
    )
  } catch (e) {
    console.error('Error guardando nota', e)
  }
}

const eliminarCompetencia = async (competencia) => {
  if (!props.alumno?.estancia_actual?.id) return;

  const token = localStorage.getItem('token');

  try {
    await axios.delete(
      `http://127.0.0.1:8000/api/estancias/${props.alumno.estancia_actual.id}/competencias/${competencia.id}`,
      { headers: { Authorization: `Bearer ${token}` } }
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

      <h5>Competencias de la estancia</h5>

      <div v-if="cargando" class="spinner-border text-indigo"></div>

      <ul v-else-if="competencias.length" class="list-group mb-3">
        <li v-for="c in competencias" :key="c.id"
          class="list-group-item d-flex justify-content-between align-items-center">
          <span>{{ c.descripcion }}</span>

          <div class="d-flex align-items-center gap-2">
            <select class="form-select form-select-sm w-auto" v-model="c.nota" @change="actualizarNota(c)">
              <option :value="null" disabled="">–</option>
              <option v-for="n in [1, 2, 3, 4]" :key="n" :value="n">{{ n }}</option>
            </select>

            <i class="bi bi-x-lg text-danger" @click="eliminarCompetencia(c)"></i>
          </div>
        </li>

      </ul>

      <p v-else class="text-muted mb-3">
        No hay competencias asignadas
      </p>

      <!-- Añadir competencia -->
      <div class="input-group mb-3">
        <select v-model="competenciaSeleccionada" class="form-select">
          <option value="" disabled="">Selecciona una competencia</option>
          <option v-for="c in competenciasDisponibles" :key="c.id" :value="c.id">
            {{ c.descripcion }}
          </option>
        </select>
        <button class="btn btn-indigo text-white" @click="agregarCompetencia" :disabled="!competenciaSeleccionada">
          Añadir
        </button>
      </div>

    </div>
  </div>

  <p v-else class="text-muted text-center mt-4">
    Selecciona un alumno
  </p>
</template>
