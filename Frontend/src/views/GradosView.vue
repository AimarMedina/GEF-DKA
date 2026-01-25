<script setup>
import Navbar from '@/components/Navbar.vue';
import GradosList from '@/components/Grado/GradosList.vue';
import GradoDatos from '@/components/Grado/GradoDatos.vue';
import { ref } from 'vue';

const gradoSeleccionado = ref(null);
const vistaActual = ref(null);

function mostrarAsignaturas(grado) {
    gradoSeleccionado.value = grado;
    vistaActual.value = grado ? 'asignaturas' : null;
}

function mostrarCompetencias(grado) {
    gradoSeleccionado.value = grado;
    vistaActual.value = grado ? 'competencias' : null;
}
</script>

<template>
    <Navbar></Navbar>

    <div class="container-fluid mb-5">
        <div class="row">
            <GradosList @verAsignaturas="mostrarAsignaturas" @verCompetencias="mostrarCompetencias" />

            <GradoDatos v-if="gradoSeleccionado" :grado="gradoSeleccionado" :vista="vistaActual" />

        </div>
    </div>
</template>