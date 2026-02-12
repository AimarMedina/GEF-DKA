<script setup>
import { ref, watch } from 'vue'
import FormularioUsuario from '../FormularioUsuario.vue'
import api from '@/services/api.js'
import { useNotificacion } from '@/composables/useNotificacion'

const props = defineProps({ empresa: Object })

const instructores = ref([])
const cache = ref({})
const loading = ref(false)
const showModal = ref(false)
const errorMessage = ref(null)
const { info, success, error } = useNotificacion()

async function cargarInstructores(cif) {
    // COMENTA ESTA LÍNEA temporalmente para forzar la carga real
    // if (cache.value[cif]) { ... } 
    
    loading.value = true
    try {
        const response = await api.get(`/api/empresa/${cif}/instructores`)
        // Asegúrate de que estás guardando lo que viene en 'data'
        const lista = response.data.data || response.data
        instructores.value = lista
        console.log("Lo que llega al front:", lista[0].user) // Mira la consola aquí
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

async function crearUsuario(instructorData) {
    info('Procesando', 'Un momento, por favor...')
    
    try {
        const response = await api.post('/api/empresa/instructor/create', {
            ...instructorData,
            CIF_Empresa: props.empresa.CIF
        })
        instructores.value.push(response.data.instructor)
        cache.value[props.empresa.CIF] = instructores.value
        success('Éxito', `Instructor ${response.data.instructor.user.nombre} creado correctamente`)
        showModal.value = false
        errorMessage.value = {}
    } catch (err) {
        if (err.response?.status === 422) {
            errorMessage.value = err.response.data.errors
            error('Error de validación', 'Revisa los campos marcados en rojo')
        } else {
            error('Error', err.response?.data?.message || 'No se pudo crear el instructor')
        }
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
    <td>{{ instructor.user?.nombre }} {{ instructor.user?.apellidos }}</td>

    <td>{{ instructor.user?.email ?? '-' }}</td>
    
    <td>{{ instructor.user?.n_tel ?? '-' }}</td>
</tr>

                    <tr v-if="!loading && !instructores.length">
                        <td colspan="3" class="text-center text-muted">No hay instructores</td>
                    </tr>
                </tbody>

            </table>
        </div>
    </div>

    <FormularioUsuario :show="showModal" :errorMessage="errorMessage" :tipo="'Instructor'" @close="showModal=false, errorMessage = null" @crear="crearUsuario" />
</template>
