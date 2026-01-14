<script setup>
import { computed } from "vue";

const props = defineProps({
  horario: Array
});

const diasCompletos = {
  L: "Lunes",
  M: "Martes",
  X: "Miércoles",
  J: "Jueves",
  V: "Viernes",
  S: "Sábado",
  D: "Domingo"
};

const horariosPorDia = computed(() => {
  const mapDias = {};
  if (!props.horario) return mapDias;

  props.horario.forEach(h => {
    h.Dias.split('-').forEach(dia => {
      if (!mapDias[dia]) mapDias[dia] = [];
      mapDias[dia].push(h.Horario1);
      if (h.Horario2) mapDias[dia].push(h.Horario2);
    });
  });

  const ordenDias = ['L','M','X','J','V','S','D'];
  const sortedMap = {};
  ordenDias.forEach(d => {
    if (mapDias[d]) sortedMap[d] = mapDias[d];
  });

  return sortedMap;
});
</script>

<template>
  <div v-if="Object.keys(horariosPorDia).length" class="mt-4 col-12 col-lg-5">
    <h5 class="mb-4 fw-bold">Mi Horario</h5>
    <div class="d-flex flex-wrap gap-3">
      <div
        v-for="(horas, dia) in horariosPorDia"
        :key="dia"
        class="card text-center flex-grow-0 shadow-sm"
        style="min-width: 150px;"
        @mouseover="hover = true"
        @mouseleave="hover = false"
      >
        <!-- Header -->
        <div class="card-header bg-indigo text-white fw-bold">
          {{ diasCompletos[dia] }}
        </div>

        <!-- Body -->
        <div class="card-body d-flex flex-column gap-2 py-3">
          <div
            v-for="(h, i) in horas"
            :key="i"
            class="rounded px-2 py-1"
            style="background-color: #d1e7dd; color: #0f5132; font-weight: 500;"
          >
            {{ h }}
          </div>
        </div>
      </div>
    </div>
  </div>

  <div v-else>
    <p class="text-muted">No hay horarios registrados.</p>
  </div>
</template>

<style scoped>

</style>
