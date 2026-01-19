<script setup>
import GradoAsignaturas from './GradoAsignaturas.vue';
import GradoCompetencias from './GradoCompetencias.vue';

defineProps({
    grado: Object,
    vista: String
});
</script>

<template>
    <div class="col-12 col-lg-8 mt-3"> 
        
        <div v-if="grado">
            <GradoAsignaturas v-if="vista === 'asignaturas'" :grado="grado" />
            <GradoCompetencias v-if="vista === 'competencias'" :grado="grado" />
        </div>

    </div>
</template>