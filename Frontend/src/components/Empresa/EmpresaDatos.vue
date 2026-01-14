<script setup>
import EmpresaInfo from './EmpresaInfo.vue'
import EmpresaInstructores from './EmpresaInstructores.vue'
import EmpresaAlumnos from './EmpresaAlumnos.vue';
const props = defineProps({
    empresa: Object
})



</script>

<template>
    <div class="col-8">
        <EmpresaInfo v-if="empresa" :empresa="empresa" />
        <EmpresaInstructores v-if="empresa" :empresa="empresa" />
        <EmpresaAlumnos v-if="empresa" :empresa="empresa"></EmpresaAlumnos>
        <div v-else class="alert alert-info mt-3">
            Selecciona una empresa para ver los detalles.
        </div>
    </div>
</template>
