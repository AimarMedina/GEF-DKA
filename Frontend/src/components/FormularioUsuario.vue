<script setup>
import { ref, watch } from 'vue'
import BuscadorSelect from './BuscadorSelect.vue'
import { useUsersStore } from '@/stores/users.store'
import api from '@/services/api.js'

const props = defineProps({
  show: Boolean,
  tipo: String
})

const emit = defineEmits(['close', 'crear'])
const usersStore = useUsersStore()

const grados = ref([])
const gradoSeleccionado = ref('')
const nuevoUsuario = ref({
  nombre: '',
  apellidos: '',
  email: '',
  n_tel: '',
  password: ''
})
const errorMessage = ref(null)

// Llenar formulario cuando se abre modal
watch(
  () => props.show,
  async (val) => {
    if (!val) {
      resetFormulario()
      return
    }

    // Inicializar currentUser si es nuevo
    if (!usersStore.currentUser) usersStore.initCurrentUser()

    const user = usersStore.currentUser
    nuevoUsuario.value = {
      nombre: user.nombre || '',
      apellidos: user.apellidos || '',
      email: user.email || '',
      n_tel: user.n_tel || '',
      password: ''
    }

    if (props.tipo === 'alumno') {
      gradoSeleccionado.value = user.alumno?.ID_Grado || ''
      if (!grados.value.length) await cargarGrados()
    }
  },
  { immediate: true }
)

async function cargarGrados() {
  try {
    const response = await api.get('/api/grados')
    grados.value = response.data.data
  } catch (e) {
    console.error(e)
  }
}

function guardar() {
  const id_grado_final = props.tipo === 'alumno' ? gradoSeleccionado.value || null : null
  if (props.tipo === 'alumno' && !id_grado_final) {
    alert('Debes seleccionar un grado')
    return
  }

  emit('crear', {
    id: usersStore.currentUser?.id || null,
    ...nuevoUsuario.value,
    tipo: props.tipo,
    ...(props.tipo === 'alumno' ? { id_grado: id_grado_final } : {})
  })
}

function resetFormulario() {
  nuevoUsuario.value = { nombre: '', apellidos: '', email: '', n_tel: '', password: '' }
  gradoSeleccionado.value = ''
  errorMessage.value = null
}
</script>

<template>
  <div v-if="show" class="modal d-block">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            {{ usersStore.currentUser?.id ? 'Editar' : 'Nuevo' }} {{ tipo }}
          </h5>
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

          <div v-if="tipo === 'alumno'" class="mb-2">
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
              <span v-for="msg in messages" :key="msg">{{ msg }}</span><br />
            </span>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" @click="$emit('close')">Cancelar</button>
          <button class="btn btn-primary" @click="guardar">
            {{ usersStore.currentUser?.id ? 'Guardar cambios' : 'Crear' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
