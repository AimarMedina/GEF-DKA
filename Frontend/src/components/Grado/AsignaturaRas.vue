<script setup>
import { ref, watch } from 'vue';
import axios from 'axios';
import FormularioCrear from '@/components/FormularioCrear.vue';

const props = defineProps({
    asignatura: Object // Recibimos la asignatura completa (id y nombre)
});

const ras = ref([]);
const loading = ref(false);
const mostrarForm = ref(false);

// Cargar RAs
const fetchRas = async () => {
    loading.value = true;
    try {
        const res = await axios.get(`http://127.0.0.1:8000/api/asignaturas/${props.asignatura.id}/ras`);
        ras.value = res.data;
    } catch (e) {
        console.error(e);
    } finally {
        loading.value = false;
    }
};

// Eliminar RA
const eliminarRa = async (id) => {
    if(!confirm('¿Seguro que quieres eliminar este RA?')) return;
    try {
        await axios.delete(`http://127.0.0.1:8000/api/ras/${id}`);
        fetchRas(); // Recargar lista
    } catch (e) {
        alert('Error al eliminar');
    }
};

// Al añadir uno nuevo
const onCreado = () => {
    fetchRas();
    mostrarForm.value = false;
};

// Cargar al montar o cambiar asignatura
watch(() => props.asignatura, fetchRas, { immediate: true });
</script>

<template>
    <div class="bg-light p-3 rounded shadow-inner mb-2 border border-top-0">
        
        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
            <h6 class="m-0 text-indigo fw-bold">
                <i class="bi bi-list-check me-1"></i> 
                RAs de {{ asignatura.nombre }}
            </h6>
            <button 
                class="btn btn-sm btn-outline-indigo d-flex align-items-center gap-1"
                @click="mostrarForm = !mostrarForm"
            >
                <i :class="mostrarForm ? 'bi bi-dash' : 'bi bi-plus'"></i>
                {{ mostrarForm ? 'Cerrar' : 'Añadir RA' }}
            </button>
        </div>

        <div v-if="loading && !ras.length" class="text-center py-2 text-muted">Cargando RAs...</div>

        <ul v-else class="list-group list-group-flush bg-white rounded border">
            <li v-for="(ra, index) in ras" :key="ra.id" 
                class="list-group-item d-flex justify-content-between align-items-center">
                
                <div class="d-flex gap-2">
                    <span class="badge bg-secondary rounded-pill h-100">{{ index + 1 }}</span>
                    <span>{{ ra.descripcion || ra.Descripcion }}</span>
                </div>

                <button class="btn btn-sm btn-outline-danger" @click="eliminarRa(ra.id)">
                    <i class="bi bi-trash"></i>
                </button>
            </li>
            
            <li v-if="ras.length === 0" class="list-group-item text-center text-muted fst-italic">
                No hay resultados de aprendizaje definidos.
            </li>
        </ul>

        <div v-if="mostrarForm" class="mt-3">
            <FormularioCrear 
                endpoint="http://127.0.0.1:8000/api/ras"
                tipo="ra"
                :idPadre="asignatura.id"
                @cancelar="mostrarForm = false"
                @creado="onCreado"
            />
        </div>
    </div>
</template>

<style scoped>
.text-indigo { color: #6610f2; }
.btn-outline-indigo { color: #6610f2; border-color: #6610f2; }
.btn-outline-indigo:hover { background-color: #6610f2; color: white; }
.shadow-inner { box-shadow: inset 0 2px 4px 0 rgba(0,0,0,.06); }
</style>