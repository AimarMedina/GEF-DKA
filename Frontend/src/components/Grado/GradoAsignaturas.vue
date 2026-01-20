<script setup>
import { ref, watch } from 'vue';
import axios from 'axios';
import FormularioCrear from '@/components/FormularioCrear.vue';
import AsignaturaRas from './AsignaturaRas.vue'; // <--- IMPORTAR NUEVO COMPONENTE

const props = defineProps({ grado: Object });
const asignaturas = ref([]);
const loading = ref(false);
const mostrarForm = ref(false);

// Estado para controlar qué asignatura está abierta
const asignaturaExpandida = ref(null);

const fetchAsignaturas = async () => {
    if (!props.grado) return;
    loading.value = true;
    asignaturaExpandida.value = null; // Cerramos acordeón al cambiar de grado
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

// Función para abrir/cerrar el acordeón de RAs
function toggleRas(idAsignatura) {
    if (asignaturaExpandida.value === idAsignatura) {
        asignaturaExpandida.value = null; // Si ya está abierto, lo cerramos
    } else {
        asignaturaExpandida.value = idAsignatura; // Abrimos el nuevo
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
                                    class="btn btn-sm" 
                                    :class="asignaturaExpandida === asig.id ? 'btn-indigo text-white' : 'btn-outline-indigo'"
                                    @click="toggleRas(asig.id)"
                                    title="Ver Resultados de Aprendizaje"
                                >
                                    RAs <i :class="asignaturaExpandida === asig.id ? 'bi bi-chevron-up' : 'bi bi-chevron-down'"></i>
                                </button>

                                <button class="btn btn-sm btn-outline-danger">Eliminar</button>
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
    </div>
</template>

<style scoped>
.bg-indigo { background-color: #6610f2; }
.btn-indigo { background-color: #6610f2; color: white; }
.btn-indigo:hover { background-color: #520dc2; }

.btn-outline-indigo { color: #6610f2; border-color: #6610f2; }
.btn-outline-indigo:hover { background-color: #6610f2; color: white; }

/* Animación simple para el despliegue */
.animacion-desplegar {
    animation: fadeIn 0.3s ease-in-out;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-5px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>