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
    <div class="card shadow-sm border-0 w-75">
        <div class="card-header bg-indigo text-white fw-bold d-flex justify-content-between align-items-center">
            
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-book"></i>
                <span>Asignaturas de {{ grado.nombre }}</span>
            </div>

            <div>
                <button 
                    class="btn btn-sm btn-outline-light d-flex align-items-center gap-1" 
                    @click="asignaturaForm"
                >
                    <i class="bi bi-plus-lg"></i>
                    <span class="d-none d-sm-inline">Añadir</span>
                </button>
            </div>
        </div>

        <div class="card-body p-0">
            <div v-if="loading" class="p-4 text-center">Cargando asignaturas...</div>
            
            <table v-else class="table table-hover table-sm mb-0 align-middle" style="font-size: 0.9rem;">
                <tbody>
                    <tr v-for="(asig,i) in asignaturas" :key="asig.id">
                        <td class="text-center fw-bold text-secondary">{{ i + 1 }}</td>
                        <td class="py-2">{{ asig.nombre }}</td>
                        <td class="d-flex gap-2 justify-content-end"> 
                            <button class="btn btn-sm btn-outline-indigo">RAs </button>
                            <button class=" btn btn-sm btn-outline-danger ">Eliminar</button>
                        </td>
                    </tr>
                    <tr v-if="asignaturas.length === 0">
                        <td colspan="2" class="text-center py-3 text-muted">No hay asignaturas registradas.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<style scoped>
</style>