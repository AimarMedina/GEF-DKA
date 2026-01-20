<script setup>
import { ref, watch } from "vue";
import axios from "axios";

const props = defineProps({ grado: Object });
const competencias = ref([]);
const loading = ref(false);

watch(
  () => props.grado,
  async (nuevoGrado) => {
    if (!nuevoGrado) return;

    loading.value = true;
    try {
      const res = await axios.get(
        `http://127.0.0.1:8000/api/grados/${nuevoGrado.id}/competencias`
      );
      competencias.value = res.data;
    } catch (e) {
      console.error(e);
    } finally {
      loading.value = false;
    }
  },
  { immediate: true }
);
</script>

<template>
  <div class="card shadow-sm border-0 w-75 m-l-5">
    <div class="card-header bg-success text-white fw-bold d-flex justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-gear "></i> 
            <div>Competencias Técnicas de {{ grado.nombre }} </div>
        </div>
        <div>
                <button 
                    class="btn btn-sm btn-outline-light d-flex align-items-center gap-1" 
                    @click="competenciaForm"
                >
                    <i class="bi bi-plus-lg"></i>
                    <span class="d-none d-sm-inline">Añadir</span>
                </button>
            </div>
    </div>
    <div class="card-body p-0">
      <div v-if="loading" class="p-4 text-center">Cargando competencias...</div>

      <table v-else class="table table-hover mb-0">
        <tbody>
          <tr v-for="(comp, i) in competencias" :key="comp.id">
            <td class="text-center fw-bold text-secondary"> {{ i+1 }}</td>
            <td>{{ comp.descripcion }}</td>
            <td class="d-flex justify-content-end">
                <button class=" btn btn-sm btn-outline-danger ">Eliminar</button>
            </td>
          </tr>
          <tr v-if="competencias.length === 0">
            <td colspan="2" class="text-center py-3">
              No hay competencias vinculadas.
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
