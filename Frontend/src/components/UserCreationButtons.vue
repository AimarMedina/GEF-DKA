<script setup>
import { ref, computed } from 'vue'
import FormularioUsuario from './FormularioUsuario.vue'
import api from '@/services/api.js'
import { useNotificacion } from '@/composables/useNotificacion'

const props = defineProps({
    tipo: {
        type: String,
        default: '.'
    },
    grado: {
        type: [Number, String],
        default: 'NONE'
    }
})

const showModal = ref(false)
const errorMessage = ref(null)
const tipoUsuario = ref(null)
const { info, success, error } = useNotificacion()

// si grado es NONE → false
const idGradoProp = computed(() => {
    if (props.tipo !== 'alumno') return false
    if (props.grado === 'NONE' || props.grado === '.') return false
    return props.grado
})


function abrirModal(tipoSeleccionado) {
    tipoUsuario.value = tipoSeleccionado
    showModal.value = true
}

async function crearUsuario(userData) {
  console.log(userData);

  info('Procesando', 'Un momento, por favor...')

  try {
    const response = await api.post('/api/user/create', {
      ...userData,
      tipo: tipoUsuario.value
    })
    console.log(response.data);
    
    success('Éxito', `Usuario ${response.data.nombre} creado correctamente`)
    showModal.value = false
    errorMessage.value = {}
  } catch (err) {
    if (err.response && err.response.status === 422) {
      errorMessage.value = err.response.data.errors || {}
      error('Error de validación', 'Revisa los campos marcados en rojo')
    } else {
      console.error(err)
      error('Error', err.response?.data?.message || 'No se pudo crear el usuario')
    }
  }
}
</script>

<template>
  <div class="d-flex gap-2 mb-3" v-if="tipo !== 'NONE'">

    <button
      v-if="tipo === '.' || tipo === 'alumno'"
      class="btn btn-outline-primary"
      @click="abrirModal('alumno')"
    >
      Crear alumno
    </button>

    <button
      v-if="tipo === '.' || tipo === 'tutor'"
      class="btn btn-outline-success"
      @click="abrirModal('tutor')"
    >
      Crear tutor
    </button>

    <button
      v-if="tipo === '.' || tipo === 'admin'"
      class="btn btn-outline-dark"
      @click="abrirModal('admin')"
    >
      Crear admin
    </button>

  </div>

  <FormularioUsuario
    :show="showModal"
    :errorMessage="errorMessage"
    :tipo="tipoUsuario"
    :id_grado="idGradoProp"
    @close="showModal = false; errorMessage = null"
    @crear="crearUsuario"
  />
</template>



<style scoped></style>
