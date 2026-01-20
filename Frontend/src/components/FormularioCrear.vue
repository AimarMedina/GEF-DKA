<script setup>
import { ref, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
    endpoint: { type: String, required: true }, // URL completa de la API
    tipo: { type: String, required: true },     // 'asig', 'comp', 'ra'
    idPadre: { type: Number, required: true }   // ID para la relación (foreign key)
});

const emit = defineEmits(['cancelar', 'creado']);

const texto = ref("");
const loading = ref(false);
const error = ref(null);

// Configuración visual y de datos según el tipo
const config = computed(() => {
    switch (props.tipo) {
        case 'asig':
            return {
                placeholder: 'Escribe el nombre de la asignatura...',
                btnClass: 'btn-indigo',
                payloadKey: 'nombre',      // La API espera 'nombre'
                fkKey: 'ID_Grado'          // La API espera 'ID_Grado'
            };
        case 'comp':
            return {
                placeholder: 'Escribe la descripción de la competencia...',
                btnClass: 'btn-success',
                payloadKey: 'descripcion', // La API espera 'descripcion'
                fkKey: 'ID_Grado'
            };
        case 'ra':
            return {
                placeholder: 'Describe el Resultado de Aprendizaje...',
                btnClass: 'btn-indigo',
                payloadKey: 'descripcion',
                fkKey: 'ID_Asignatura'
            };
        default:
            return {};
    }
});

const guardar = async () => {
    if (!texto.value.trim()) return;

    loading.value = true;
    error.value = null;

    try {
        // Construimos el objeto dinámicamente
        const payload = {
            [config.value.payloadKey]: texto.value, // nombre o descripcion
            [config.value.fkKey]: props.idPadre     // ID_Grado o ID_Asignatura
        };

        // Usamos el endpoint que nos pasa el padre
        await axios.post(props.endpoint, payload);
        
        texto.value = "";
        emit('creado'); // Avisamos éxito
    } catch (e) {
        console.error(e);
        error.value = "Error al guardar. Verifica los datos.";
    } finally {
        loading.value = false;
    }
};
</script>

<template>
    <div class="border-top p-3 bg-light animacion-entrada">
        <div class="d-flex flex-column gap-2">
            <label class="form-label fw-bold small text-muted mb-0">
                <i class="bi bi-plus-circle me-1"></i> Añadir nuevo registro
            </label>
            
            <div class="d-flex gap-2">
                <input 
                    type="text" 
                    class="form-control" 
                    :placeholder="config.placeholder"
                    v-model="texto"
                    @keyup.enter="guardar"
                    autofocus
                >
                
                <button 
                    class="btn text-white" 
                    :class="config.btnClass"
                    @click="guardar"
                    :disabled="loading || !texto"
                >
                    <span v-if="loading" class="spinner-border spinner-border-sm"></span>
                    <span v-else>Guardar</span>
                </button>

                <button class="btn btn-outline-secondary" @click="emit('cancelar')">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            
            <div v-if="error" class="text-danger small">{{ error }}</div>
        </div>
    </div>
</template>

<style scoped>
.btn-indigo { background-color: #6610f2; border-color: #6610f2; }
.btn-indigo:hover { background-color: #520dc2; }

/* Pequeña animación para que no aparezca de golpe */
.animacion-entrada {
    animation: slideDown 0.3s ease-out;
}
@keyframes slideDown {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>