<script setup>
import { ref, watch } from 'vue';
import axios from 'axios';

const props = defineProps({ grado: Object });
const competencias = ref([]);
const loading = ref(false);

watch(() => props.grado, async (nuevoGrado) => {
    if (!nuevoGrado) return;
    
    loading.value = true;
    try {
        const res = await axios.get(`http://127.0.0.1:8000/api/grados/${nuevoGrado.id}/competencias`);
        competencias.value = res.data;
    } catch (e) {
        console.error(e);
    } finally {
        loading.value = false;
    }
}, { immediate: true });
</script>

<template>
    <div class="card shadow-sm border-0">
        <div class="card-header bg-success text-white fw-bold"> Competencias Técnicas del Grado
        </div>
        <div class="card-body p-0">
            <div v-if="loading" class="p-4 text-center">Cargando competencias...</div>
            
            <table v-else class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">ID</th>
                        <th>Descripción</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="comp in competencias" :key="comp.id">
                        <td>{{ comp.id }}</td>
                        <td>{{ comp.descripcion }}</td>
                    </tr>
                     <tr v-if="competencias.length === 0">
                        <td colspan="2" class="text-center py-3">No hay competencias vinculadas.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>