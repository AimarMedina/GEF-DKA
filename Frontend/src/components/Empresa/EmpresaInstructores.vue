<script setup>
import axios from 'axios'
import { ref, watch } from 'vue'
import ModalInstructor from '../Instructor/FormularioInstructor.vue'

const props = defineProps({ empresa: Object })

const instructores = ref([])
const cache = ref({})
const loading = ref(false)
const showModal = ref(false)
const errorMessage = ref(null)

async function cargarInstructores(cif) {
    if (cache.value[cif]) {
        instructores.value = cache.value[cif]
        return
    }
    loading.value = true
    try {
        const response = await axios.get(`http://localhost:8000/api/empresa/${cif}/instructores`)
        cache.value[cif] = response.data
        instructores.value = response.data
    } catch (e) {
        instructores.value = []
    } finally {
        loading.value = false
    }
}

watch(
    () => props.empresa,
    (nuevaEmpresa) => {
        instructores.value = []
        if (nuevaEmpresa) cargarInstructores(nuevaEmpresa.CIF)
    },
    { immediate: true }
)

async function crearInstructor(instructorData) {
    try {
        const response = await axios.post('http://localhost:8000/api/empresa/instructor/create', {
            ...instructorData,
            CIF_Empresa: props.empresa.CIF
        })
        instructores.value.push(response.data.instructor)
        cache.value[props.empresa.CIF] = instructores.value
        showModal.value = false
        errorMessage.value = {}
    } catch (error) {
        errorMessage.value = error.response.data.errors
    }
}
</script>

<template>
    <div class="card mt-3">
        <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
            Instructores
            <button class="btn btn-outline-light btn-sm d-flex align-items-center gap-1" @click="showModal = true">
                <i class="bi bi-person-plus-fill"></i> Añadir
            </button>

        </div>

        <div class="card-body p-0">
            <table class="table mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-if="loading">
                        <td colspan="3" class="text-center text-muted">
                            Cargando instructores
                            <ul class="carga">
                                <li></li>
                                <li></li>
                                <li></li>
                                <li></li>
                                <li></li>
                            </ul>
                        </td>
                    </tr>

                    <tr v-for="instructor in instructores" :key="instructor.ID_Usuario">
                        <td>{{ instructor.user?.nombre }}</td>
                        <td>{{ instructor.user?.email }}</td>
                        <td>{{ instructor.user?.n_tel }}</td>
                    </tr>

                    <tr v-if="!loading && !instructores.length">
                        <td colspan="3" class="text-center text-muted">No hay instructores</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <ModalInstructor :show="showModal" :errorMessage="errorMessage" @close="showModal=false, errorMessage = null" @crear="crearInstructor" />
</template>
