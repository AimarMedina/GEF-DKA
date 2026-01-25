<template>
  <div v-if="show" class="modal d-block">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">{{ usuario ? 'Editar' : 'Nuevo' }} {{ tipo }}</h5>
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

         <div v-if="tipo === 'alumno' && id_grado === false" class="mb-2">
            <label>Grado</label>
            
            <BuscadorSelect 
              v-model="gradoSeleccionado"
              :options="grados"
              label-key="nombre"
              value-key="id"
              placeholder="Buscar o seleccionar grado..."
            />

          </div>

          <div v-if="errorMessage" class="alert alert-danger text-start">
            <span v-for="messages in errorMessage" :key="messages">
              <span v-for="msg in messages" :key="msg">{{ msg }}</span><br>
            </span>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" @click="$emit('close')">Cancelar</button>
          <button class="btn btn-primary" @click="guardar">{{ usuario ? 'Guardar cambios' : 'Crear' }}</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import api from '@/services/api.js'
import BuscadorSelect from './BuscadorSelect.vue'

const props = defineProps({
  show: Boolean,
  errorMessage: Object,
  tipo: String,
  id_grado: {
    type: [Number, Boolean],
    default: false
  },
  usuario: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close', 'crear'])

const grados = ref([])
const gradoSeleccionado = ref('')
const nuevoUsuario = ref({
  nombre: '',
  apellidos: '',
  email: '',
  n_tel: '',
  password: '',
})

watch(() => props.show, async (val) => {
  console.log(props.usuario);

  if (!val) {
    resetFormulario()
  } else {
    if (props.tipo === 'alumno' && props.id_grado === false) {
      await cargarGrados()
    }
    console.log(props);

    if (props.usuario) {
      nuevoUsuario.value = {
        nombre: props.usuario.nombre || '',
        apellidos: props.usuario.apellidos || '',
        email: props.usuario.email || '',
        n_tel: props.usuario.n_tel || '',
        password: ''
      }

      if (props.tipo === 'alumno') {
        gradoSeleccionado.value = props.usuario.alumno.ID_Grado || ''
      }
    }
  }
})

async function cargarGrados() {
  try {
    const response = await api.get("/api/grados")
    grados.value = response.data.data
  } catch (e) {
    console.error(e)
  }
}

function guardar() {
  let id_grado_final = props.tipo === 'alumno'
    ? (props.id_grado !== false ? props.id_grado : gradoSeleccionado.value)
    : null

  if (props.tipo === 'alumno' && !id_grado_final) {
    alert('Debes seleccionar un grado')
    return
  }

  emit('crear', {
    id: props.usuario?.id || null,
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
