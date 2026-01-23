<script setup>
import { defineProps, computed } from "vue";
import CuardernosTable from '../Notas/CuardernosTable.vue';
import CompetenciasTable from '../Notas/CompetenciasTable.vue';
import TransversalesTable from '../Notas/TransversalesTable.vue';
import EgibideTable from '@/components/Notas/EgibideTable.vue';

import { useUserStore } from '@/stores/userStore'

const userStore = useUserStore()

const puedeEditar = computed(() => userStore.user.tipo !== 'alumno')

const props = defineProps({
  alumno: Object,       // Datos del alumno
  notas: Object         // Notas del alumno
});
</script>

<template>
  <div v-if="alumno" class="ceard mt-2 shadow-sm">
    <div class="card-header bg-indigo text-white">
      <h5 class="mb-0">Notas de {{ alumno.Nombre }} {{ alumno.Apellidos }}</h5>
    </div>
    <div class="card-body">
      <CuardernosTable v-if="notas?.nota_cuaderno" :notaCuaderno="[notas.nota_cuaderno]" :alumno-id="alumno.ID_Usuario" />
      <CompetenciasTable v-if="notas?.notas_competencias" :competencias="notas.notas_competencias" />
      <TransversalesTable v-if="notas?.notas_transversales" :transversales="notas.notas_transversales" />
      <EgibideTable :egibide="notas.notas_egibide" :alumno-id="alumno.ID_Usuario" :puede-editar="puedeEditar"/>
    </div>
  </div>
</template>
