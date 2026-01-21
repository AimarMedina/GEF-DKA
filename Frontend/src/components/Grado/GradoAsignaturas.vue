<script setup>
import { ref, watch } from 'vue';
import axios from 'axios';
import FormularioCrear from '@/components/FormularioCrear.vue';
import AsignaturaRas from './AsignaturaRas.vue';
import ConfirmarEliminar from '../ConfirmarEliminar.vue'; // <--- IMPORTAMOS

const props = defineProps({ grado: Object });
const asignaturas = ref([]);
const loading = ref(false);
const mostrarForm = ref(false);

// Variables para el modal de eliminar
const asigEliminar = ref(null);
const eliminarModalVisible = ref(false);

// Estado para controlar qué asignatura está abierta
const asignaturaExpandida = ref(null);

const fetchAsignaturas = async () => {
    if (!props.grado) return;
    loading.value = true;
    asignaturaExpandida.value = null;
    try {
        const res = await axios.get(`http://127.0.0.1:8000/api/grados/${props.grado.id}/asignaturas`);
        asignaturas.value = res.data;
    } catch (e) { console.error(e); } 
    finally { loading.value = false; }
};

watch(() => props.grado, fetchAsignaturas, { immediate: true });

function onCreado() {
    fetchAsignaturas();
    mostrarForm.value = false;
}

function toggleRas(idAsignatura) {
    if (asignaturaExpandida.value === idAsignatura) {
        asignaturaExpandida.value = null;
    } else {
        asignaturaExpandida.value = idAsignatura;
    }
}

// --- LÓGICA DE ELIMINAR ---
function abrirEliminarModal(asig) {
    asigEliminar.value = asig;
    eliminarModalVisible.value = true;
}

function confirmarEliminarAsig(confirmado) {
    if (confirmado && asigEliminar.value) {
        eliminarAsignatura(asigEliminar.value.id);
    }
    eliminarModalVisible.value = false;
    asigEliminar.value = null;
}

async function eliminarAsignatura(id){
    try{
        await axios.delete(`http://127.0.0.1:8000/api/asignaturas/${id}`);
        fetchAsignaturas();
    } catch (e){
        alert('Error al eliminar');
    }
}
</script>

<template>
    <div class="card shadow-sm border-0">
        <div class="card-header bg-indigo text-white fw-bold d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-book"></i>
                <span>Asignaturas de {{ grado.nombre }}</span>
            </div>
            <div>
                <button 
                    class="btn btn-sm btn-outline-light d-flex align-items-center gap-1" 
                    @click="mostrarForm = !mostrarForm"
                >
                    <i :class="mostrarForm ? 'bi bi-dash-lg' : 'bi bi-plus-lg'"></i>
                    <span class="d-none d-sm-inline">{{ mostrarForm ? 'Cerrar' : 'Añadir' }}</span>
                </button>
            </div>
        </div>

        <div class="card-body p-0">
            <div v-if="loading && !asignaturas.length" class="p-4 text-center">Cargando...</div>
            
            <table v-else class="table table-hover table-sm mb-0 align-middle" style="font-size: 0.9rem;">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width: 50px;">#</th>
                        <th>Nombre</th>
                        <th class="text-end pe-3">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <template v-for="(asig, index) in asignaturas" :key="asig.id">
                        
                        <tr :class="{'table-active': asignaturaExpandida === asig.id}">
                            <td class="text-center fw-bold text-secondary">{{ index + 1 }}</td>
                            <td class="py-2">{{ asig.nombre }}</td>
                            <td class="d-flex justify-content-end gap-2 pe-2"> 
                                <button 
                                    class="btn btn-sm " 
                                    :class="asignaturaExpandida === asig.id ? 'btn-indigo text-white' : 'btn-outline-indigo'"
                                    @click="toggleRas(asig.id)"
                                    title="Ver Resultados de Aprendizaje"
                                >
                                    RAs <i :class="asignaturaExpandida === asig.id ? 'bi bi-chevron-up' : 'bi bi-chevron-down'"></i>
                                </button>

                                <button @click="abrirEliminarModal(asig)" class="btn btn-sm btn-outline-danger">Eliminar</button>
                            </td>
                        </tr>

                        <tr v-if="asignaturaExpandida === asig.id">
                            <td colspan="3" class="p-0 border-0">
                                <div class="animacion-desplegar">
                                    <AsignaturaRas :asignatura="asig" />
                                </div>
                            </td>
                        </tr>

                    </template>
                    <tr v-if="asignaturas.length === 0">
                        <td colspan="3" class="text-center py-3 text-muted">No hay asignaturas.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <FormularioCrear 
            v-if="mostrarForm"
            endpoint="http://127.0.0.1:8000/api/asignaturas"
            tipo="asig"
            :idPadre="grado.id"
            @cancelar="mostrarForm = false"
            @creado="onCreado"
        />

        <ConfirmarEliminar 
            :show="eliminarModalVisible" 
            :mensaje="`¿Seguro que quieres eliminar la asignatura '${asigEliminar?.nombre}'? Esto borrará también sus RAs.`"
            @confirm="confirmarEliminarAsig" 
            @close="eliminarModalVisible = false"
        />
    </div>
</template>

<style scoped>
.animacion-desplegar {
    animation: fadeIn 0.3s ease-in-out;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-5px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>