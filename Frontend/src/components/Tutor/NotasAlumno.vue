<script setup>
import { defineProps, computed } from 'vue';
import CuardernosTable from '../Notas/CuardernosTable.vue';
import CompetenciasTable from '../Notas/CompetenciasTable.vue';
import TransversalesTable from '../Notas/TransversalesTable.vue';
import EgibideTable from '@/components/Notas/EgibideTable.vue';

import { useUserStore } from '@/stores/userStore';

const userStore = useUserStore();

const puedeEditar = computed(() => userStore.user.tipo !== 'alumno');

const props = defineProps({
  alumno: Object, // Datos del alumno
  notas: Object, // Notas del alumno
});
</script>

<template>
  <div class="ceard mt-2 shadow-sm row">
    <div class="card-header bg-indigo text-white">
      <h5 class="mb-0">Notas</h5>
    </div>
    <div class="card-body">
      <div class="row">
        <div class="col-12 col-md-6">
          <CuardernosTable
            v-if="notas?.nota_cuaderno"
            :notaCuaderno="[notas.nota_cuaderno]"
            :alumno-id="alumno.ID_Usuario"
          />
        </div>
        <div class="col-12 col-md-6">
          <CompetenciasTable
            v-if="notas?.notas_competencias"
            :competencias="notas.notas_competencias"
            :alumno-id="alumno.ID_Usuario"
          />
        </div>
      </div>
      <div class="row">
        <div class="col-12 col-md-6">
          <TransversalesTable
            v-if="notas?.notas_transversales"
            :transversales="notas.notas_transversales"
            :alumno-id="alumno.ID_Usuario"
          />
        </div>
        <div class="col-12 col-md-6">
          <EgibideTable
            :egibide="notas.notas_egibide"
            :alumno-id="alumno.ID_Usuario"
            :puede-editar="puedeEditar"
          />
        </div>
      </div>
    </div>
  </div>
</template>
