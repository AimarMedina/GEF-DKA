<script setup>
import { ref, watch, defineProps, defineEmits } from 'vue'
import axios from 'axios'

const props = defineProps({
  show: Boolean,
  errorMessage: Object,
  tipo: String,
  id_grado: {
    type: [Number, Boolean],
    default: false
  }
})

const emit = defineEmits(['close', 'crear'])
const grados = ref([])
const nuevoUsuario = ref({
  nombre: '',
  apellidos: '',
  email: '',
  n_tel: '',
  password: '',
})

const gradoSeleccionado = ref('') // solo si no se pasa id_grado

watch(() => props.show, async (val) => {
  if (!val) {
    resetFormulario()
  } else if (props.tipo === 'alumno' && props.id_grado === false) {
    await cargarGrados()
  }
})

async function cargarGrados() {
  try {
    const response = await axios.get("http://localhost:8000/api/grados")
    grados.value = response.data.data
    console.log(grados.value);
    
  } catch (e) {
    console.error(e)
  }
}

function crear() {
  let id_grado_final = props.tipo === 'alumno'
    ? (props.id_grado !== false ? props.id_grado : gradoSeleccionado.value)
    : null

  if (props.tipo === 'alumno' && !id_grado_final) {
    alert('Debes seleccionar un grado')
    return
  }

  emit('crear', {
    ...nuevoUsuario.value,
    tipo: props.tipo,
    ...(props.tipo === 'alumno' ? { id_grado: id_grado_final } : {})
  })
}

function resetFormulario() {
  nuevoUsuario.value = { nombre: '', apellidos: '', email: '', n_tel: '', password: '' }
  gradoSeleccionado.value = ''
}
</script>

<template>
<div v-if="show" class="modal d-block">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Nuevo {{ props.tipo }}</h5>
        <button type="button" class="btn-close" @click="$emit('close')"></button>
      </div>
      <div class="modal-body">
        <div class="mb-2">
          <label>Nombre</label>
          <input v-model="nuevoUsuario.nombre" type="text" class="form-control" />
        </div>
        <div class="mb-2">
          <label>Apellidos</label>
          <input v-model="nuevoUsuario.apellidos" type="text" class="form-control" />
        </div>
        <div class="mb-2">
          <label>Email</label>
          <input v-model="nuevoUsuario.email" type="email" class="form-control" />
        </div>
        <div class="mb-2">
          <label>Teléfono</label>
          <input v-model="nuevoUsuario.n_tel" type="text" class="form-control" />
        </div>
        <div class="mb-2">
          <label>Password</label>
          <input v-model="nuevoUsuario.password" type="password" class="form-control" />
        </div>

        <div v-if="props.tipo === 'alumno' && props.id_grado === false" class="mb-2">
          <label>Grado</label>
          <select v-model="gradoSeleccionado" class="form-select">
            <option value="">Selecciona un grado</option>
            <option v-for="g in grados" :key="g.id" :value="g.id">{{ g.nombre }}</option>
          </select>
        </div>

        <div v-if="errorMessage" class="alert alert-danger text-start">
          <span v-for="messages in errorMessage">
            <span v-for="msg in messages" :key="msg">{{ msg }}</span><br>
          </span>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" @click="$emit('close')">Cancelar</button>
        <button class="btn btn-primary" @click="crear">Crear</button>
      </div>
    </div>
  </div>
</div>
</template>
