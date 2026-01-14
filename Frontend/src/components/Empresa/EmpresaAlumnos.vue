<script setup>
import axios from 'axios'
import { ref, watch } from 'vue'
import ModalInstructor from '../Instructor/FormularioInstructor.vue'

const props = defineProps({ empresa: Object })

const alumnos = ref([])
const cache = ref({})
const loading = ref(false)
const showModal = ref(false)
const errorMessage = ref(null)

async function cargarAlumnos(cif) {
    if (cache.value[cif]) {
        alumnos.value = cache.value[cif]
        return
    }
    loading.value = true
    try {
        const response = await axios.get(`http://localhost:8000/api/empresa/${cif}/alumnos`)
        cache.value[cif] = response.data
        alumnos.value = response.data
        console.log(alumnos.value);
    } catch (e) {
        alumnos.value = []
    } finally {
        loading.value = false
    }
}

watch(
    () => props.empresa,
    (nuevaEmpresa) => {
        alumnos.value = []
        if (nuevaEmpresa) cargarAlumnos(nuevaEmpresa.CIF)
    },
    { immediate: true }
)


</script>

<template>
    <div class="card mt-3">
        <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
            Alumnos
        </div>

        <div class="card-body p-0">
            <table class="table mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th>Instructor</th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-if="loading">
                        <td colspan="3" class="text-center text-muted">
                            Cargando alumnos
                            <ul class="carga">
                                <li></li>
                                <li></li>
                                <li></li>
                                <li></li>
                                <li></li>
                            </ul>
                        </td>
                    </tr>

                       <tr v-for="estancia in alumnos" :key="estancia">
                            <td>{{ estancia.alumno.usuario.nombre }} {{ estancia.alumno.usuario.apellidos }}</td>
                            <td>{{ estancia.alumno.usuario.email }}</td>
                            <td>{{ estancia.alumno.usuario.n_tel }}</td>
                            <td>{{ estancia.alumno.instructor?.user?.nombre || '—' }}</td>
                        </tr>


                    <tr v-if="!loading && !alumnos.length">
                        <td colspan="3" class="text-center text-muted">No hay alumnos</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
