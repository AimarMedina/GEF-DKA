<script setup>
import { ref, computed } from 'vue'
import FormularioUsuario from './FormularioUsuario.vue'
import axios from 'axios'

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
  
    try {
        const response = await axios.post('http://localhost:8000/api/user/create', {
            ...userData,
            tipo: tipoUsuario.value
        })
        console.log(response.data);
        showModal.value = false
        errorMessage.value = {}
    } catch (error) {
    if (error.response && error.response.status === 422) {
        errorMessage.value = error.response.data.errors || {};
    } else {
        console.error(error)
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
