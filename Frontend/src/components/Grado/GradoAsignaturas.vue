<script setup>
import { ref, watch } from 'vue';
import axios from 'axios';

const props = defineProps({ grado: Object });
const asignaturas = ref([]);
const loading = ref(false);

// Cada vez que cambie el grado, cargamos sus datos
watch(() => props.grado, async (nuevoGrado) => {
    if (!nuevoGrado) return;
    
    loading.value = true;
    try {
        const res = await axios.get(`http://127.0.0.1:8000/api/grados/${nuevoGrado.id}/asignaturas`);
        asignaturas.value = res.data;
    } catch (e) {
        console.error(e);
    } finally {
        loading.value = false;
    }
}, { immediate: true });
</script>

<template>
    <div class="card shadow-sm border-0">
        <div class="card-header bg-indigo text-white fw-bold">
            Asignaturas de {{ grado.nombre }}
        </div>
        <div class="card-body p-0">
            <div v-if="loading" class="p-4 text-center">Cargando asignaturas...</div>
            
            <table v-else class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Nombre de la Asignatura</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="asig in asignaturas" :key="asig.id">
                        <td>{{ asig.id }}</td>
                        <td>{{ asig.nombre }}</td>
                    </tr>
                    <tr v-if="asignaturas.length === 0">
                        <td colspan="2" class="text-center py-3">No hay asignaturas registradas.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<style scoped>
</style>