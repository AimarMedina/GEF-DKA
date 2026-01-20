<script setup>
import { ref, defineProps, defineEmits } from "vue";
import CuardernosTable from '../Notas/CuardernosTable.vue';
import CompetenciasTable from '../Notas/CompetenciasTable.vue';
import TransversalesTable from '../Notas/TransversalesTable.vue';
import EgibideTable from '@/components/notas/EgibideTable.vue';

const props = defineProps({
  alumno: Object,       // Datos del alumno
  notas: Object,        // Notas del alumno
  show: Boolean         // Para mostrar/ocultar modal
});

const emit = defineEmits(['close']);
</script>

<template>
  <div v-if="show" class="modal d-block fade show" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            Notas de {{ alumno?.Nombre }} {{ alumno?.Apellidos }}
          </h5>
          <button type="button" class="btn-close" @click="$emit('close')"></button>
        </div>
        <div class="modal-body">
          <CuardernosTable v-if="notas?.nota_cuaderno" :notaCuaderno="notas.nota_cuaderno" :alumno-id="alumno.ID_Usuario" />
          <CompetenciasTable v-if="notas?.notas_competencias" :competencias="notas.notas_competencias" />
          <TransversalesTable v-if="notas?.notas_transversales" :transversales="notas.notas_transversales" />
          <EgibideTable v-if="notas?.notas_egibide" :egibide="notas.notas_egibide" />
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="$emit('close')">Cerrar</button>
        </div>
      </div>
    </div>
  </div>
  <div v-if="show" class="modal-backdrop fade show"></div>
</template>
