<script setup>
import { ref, onMounted, watch, defineProps, defineEmits } from 'vue'
import axios from 'axios'
import api from '@/services/api.js'

const props = defineProps({
  show: Boolean,
  alumno: Object
})

const emit = defineEmits(['close','crear'])

const diasSemana = ['Lunes','Martes','Miércoles','Jueves','Viernes']

const nuevaEstancia = ref({
  ID_Alumno: null,
  CIF_Empresa: '',
  ID_Instructor: null, // <-- ahora null por defecto
  Fecha_inicio: '',
  Fecha_fin: '',
  horarios: []
})

const empresas = ref([])
const instructores = ref([])

function resetHorarios(){
  nuevaEstancia.value.horarios = diasSemana.map(d => {
    // Viernes
    if (d === 'Viernes') {
      return {
        dia: d,
        activo: true,
        manana: { inicio: '09:00', fin: '15:00' },
        tarde:  { inicio: '', fin: '' }
      }
    }

    // Lunes a Jueves
    return {
      dia: d,
      activo: true,
      manana: { inicio: '08:00', fin: '14:00' },
      tarde:  { inicio: '15:00', fin: '17:00' }
    }
  })
}

onMounted(async () => {
  const token = localStorage.getItem('token')
  const res = await api.get('/api/empresas', {
    headers:{ Authorization:`Bearer ${token}` }
  })
  empresas.value = res.data.data || []
  
})

watch(() => props.show, val => {
  if(val && props.alumno){
    nuevaEstancia.value.ID_Alumno = props.alumno.ID_Usuario
    nuevaEstancia.value.CIF_Empresa = ''
    nuevaEstancia.value.ID_Instructor = null // <-- reiniciamos como null
    nuevaEstancia.value.Fecha_inicio = ''
    nuevaEstancia.value.Fecha_fin = ''
    resetHorarios()
    instructores.value = []
  }
})

watch(() => nuevaEstancia.value.CIF_Empresa, async cif => {
  if(!cif) return
  const token = localStorage.getItem('token')
  const res = await api.get(`/api/empresa/${cif}/instructores`, {
    headers:{ Authorization:`Bearer ${token}` }
  })
  instructores.value = res.data || []
  console.log(instructores.value);
})

async function crearEstancia(){
  const horariosActivos = nuevaEstancia.value.horarios.filter(h => h.activo)

  if(!horariosActivos.length){
    alert('Selecciona al menos un día con horario')
    return
  }
  if(!nuevaEstancia.value.Fecha_inicio || !nuevaEstancia.value.Fecha_fin){
    alert('Debes seleccionar fecha inicio y fin')
    return
  }
  const payload = {
    ID_Alumno: nuevaEstancia.value.ID_Alumno,
    CIF_Empresa: nuevaEstancia.value.CIF_Empresa,
    ID_Instructor: nuevaEstancia.value.ID_Instructor ? Number(nuevaEstancia.value.ID_Instructor) : null,
    Fecha_inicio: nuevaEstancia.value.Fecha_inicio,
    Fecha_fin: nuevaEstancia.value.Fecha_fin,
    horarios: horariosActivos.map(h => ({
      Dia: h.dia,
      Horario1: h.manana.inicio && h.manana.fin ? `${h.manana.inicio}-${h.manana.fin}` : null,
      Horario2: h.tarde.inicio && h.tarde.fin ? `${h.tarde.inicio}-${h.tarde.fin}` : null
    }))
  }

  try {
    const token = localStorage.getItem('token')
    const res = await api.post(
      '/api/asignarEstancia',
      payload,
      { headers:{ Authorization:`Bearer ${token}` } }
    )
    emit('crear', res.data)
    cerrarModal()
  } catch(err) {
    console.error(err)
    alert('Error al crear la estancia. Revisa la consola.')
  }
  }

  // Horario global para rellenar todos los días
  const horarioGlobal = ref({
    manana: { inicio: '', fin: '' },
    tarde:  { inicio: '', fin: '' }
  })

  // Función para aplicar global a todos los días
  function aplicarGlobal(turno, campo) {
    nuevaEstancia.value.horarios.forEach(h => {
      if(h.activo){
        h[turno][campo] = horarioGlobal.value[turno][campo]
      }
    })
  }


function cerrarModal(){
  emit('close')
}
</script>

<template>
  <div v-if="show" class="card shadow-sm">

    <!-- HEADER -->
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0">
        Asignar estancia a {{ alumno?.Nombre }} {{ alumno?.Apellidos }}
      </h5>
      <button type="button" class="btn-close" @click="cerrarModal"></button>
    </div>

    <!-- BODY -->
    <div class="card-body">

      <!-- Empresa -->
      <div class="mb-2">
        <label>Empresa</label>
        <select v-model="nuevaEstancia.CIF_Empresa" class="form-control" required>
          <option disabled value="">Selecciona una empresa</option>
          <option v-for="e in empresas" :key="e.CIF" :value="e.CIF">
            {{ e.Nombre }}
          </option>
        </select>
      </div>

      <!-- Instructor Opcional -->
<div class="mb-2">
  <label>Instructor (opcional)</label>
  <select v-model.number="nuevaEstancia.ID_Instructor" class="form-control">
    <option :value="null">No asignar</option>
    <option v-for="i in instructores" :key="i.user.id" :value="i.user.id">
      {{ i.user.nombre }} {{ i.user.apellidos }}
    </option>
  </select>
</div>


      <!-- Fechas -->
      <div class="row">
        <div class="col-md-6 mb-2">
          <label>Fecha inicio</label>
          <input type="date" v-model="nuevaEstancia.Fecha_inicio" class="form-control">
        </div>

        <div class="col-md-6 mb-2">
          <label>Fecha fin</label>
          <input type="date" v-model="nuevaEstancia.Fecha_fin" class="form-control">
        </div>
      </div>

      <!-- HORARIOS -->
<hr>
<div class="d-flex justify-content-between align-items-center mb-2">
  <h6 class="fw-bold mb-0">Horarios semanales</h6>
  <div class="d-flex gap-2">
    <input type="time" v-model="horarioGlobal.manana.inicio" class="form-control form-control-sm" @change="aplicarGlobal('manana', 'inicio')">
    <input type="time" v-model="horarioGlobal.manana.fin" class="form-control form-control-sm" @change="aplicarGlobal('manana', 'fin')">
    <input type="time" v-model="horarioGlobal.tarde.inicio" class="form-control form-control-sm" @change="aplicarGlobal('tarde', 'inicio')">
    <input type="time" v-model="horarioGlobal.tarde.fin" class="form-control form-control-sm" @change="aplicarGlobal('tarde', 'fin')">
  </div>
</div>

<div v-for="(h,i) in nuevaEstancia.horarios" :key="i" class="border rounded p-2 mb-2">
  <div class="form-check mb-2">
    <input class="form-check-input" type="checkbox" v-model="h.activo" :id="'dia-' + i">
    <label class="form-check-label fw-semibold" :for="'dia-' + i">{{ h.dia }}</label>
  </div>

  <div v-if="h.activo" class="row">
    <div class="col-md-3 mb-2">
      <label>Mañana inicio</label>
      <input type="time" v-model="h.manana.inicio" class="form-control">
    </div>
    <div class="col-md-3 mb-2">
      <label>Mañana fin</label>
      <input type="time" v-model="h.manana.fin" class="form-control">
    </div>
    <div class="col-md-3 mb-2">
      <label>Tarde inicio</label>
      <input type="time" v-model="h.tarde.inicio" class="form-control">
    </div>
    <div class="col-md-3 mb-2">
      <label>Tarde fin</label>
      <input type="time" v-model="h.tarde.fin" class="form-control">
    </div>
  </div>
</div>


    </div>

    <!-- FOOTER -->
    <div class="card-footer d-flex justify-content-end gap-2">
      <button class="btn btn-secondary" @click="cerrarModal">Cancelar</button>
      <button class="btn btn-indigo" @click="crearEstancia">Guardar</button>
    </div>

  </div>
</template>

