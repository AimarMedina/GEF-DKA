<script setup>
    import Navbar from '@/components/Navbar.vue';
    import GradosList from '@/components/Grado/GradosList.vue'; 
    import GradoDatos from '@/components/Grado/GradoDatos.vue'; 
    import { ref } from 'vue';

    const gradoSeleccionado = ref(null);
    const vistaActual = ref(null);

    function seleccionarGrado(grado) {
         gradoSeleccionado.value = grado;
         vistaActual.value = null;
    }

    function mostrarAsignaturas(grado) {
        gradoSeleccionado.value = grado;
        vistaActual.value = 'asignaturas';
    }

    function mostrarCompetencias(grado) {
        gradoSeleccionado.value = grado;
        vistaActual.value = 'competencias';
    }
</script>

<template>
    <Navbar></Navbar>
    
    <div class="container-fluid mb-5">
        <div class="row">
            
            <GradosList 
                @seleccionarGrado="seleccionarGrado"
                @verAsignaturas="mostrarAsignaturas"
                @verCompetencias="mostrarCompetencias"
            />

            <GradoDatos 
                :grado="gradoSeleccionado" 
                :vista="vistaActual" 
            />
        </div>
    </div>
</template>