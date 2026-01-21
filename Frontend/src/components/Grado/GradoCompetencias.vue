<script setup>
import { ref, watch } from "vue";
import axios from "axios";
import FormularioCrear from "@/components/FormularioCrear.vue";
import ConfirmarEliminar from '../ConfirmarEliminar.vue'; // <--- IMPORTAMOS

const props = defineProps({ grado: Object });
const competencias = ref([]);
const loading = ref(false);
const mostrarForm = ref(false);

// Variables para el modal de eliminar
const compEliminar = ref(null);
const eliminarModalVisible = ref(false);

const fetchCompetencias = async () => {
  if (!props.grado) return;
  loading.value = true;
  try {
    const res = await axios.get(
      `http://127.0.0.1:8000/api/grados/${props.grado.id}/competencias`,
    );
    competencias.value = res.data;
  } catch (e) {
    console.error(e); 
  } finally {
    loading.value = false;
  }
};

watch(() => props.grado, fetchCompetencias, { immediate: true });

function onCreado() {
  fetchCompetencias();
  mostrarForm.value = false;
}

// --- LÓGICA DE ELIMINAR ---
function abrirEliminarModal(comp) {
    compEliminar.value = comp;
    eliminarModalVisible.value = true;
}

function confirmarEliminarComp(confirmado) {
    if (confirmado && compEliminar.value) {
        eliminarCompetencia(compEliminar.value.id);
    }
    eliminarModalVisible.value = false;
    compEliminar.value = null;
}

async function eliminarCompetencia(id) {
  try {
    await axios.delete(
      `http://127.0.0.1:8000/api/competencias/${id}`,
    );
    fetchCompetencias();
  } catch (e) {
    alert("Error al eliminar");
  }
}
</script>

<template>
  <div class="card shadow-sm border-0 w-75 m-l-5">
    <div
      class="card-header bg-success text-white fw-bold d-flex justify-content-between"
    >
      <div class="d-flex align-items-center gap-2">
        <i class="bi bi-gear"></i>
        <div>Competencias Técnicas de {{ grado.nombre }}</div>
      </div>
      <div>
        <button
          class="btn btn-sm btn-outline-light d-flex align-items-center gap-1"
          @click="mostrarForm = !mostrarForm"
        >
          <i :class="mostrarForm ? 'bi bi-dash-lg' : 'bi bi-plus-lg'"></i>
          <span class="d-none d-sm-inline">{{
            mostrarForm ? "Cerrar" : "Añadir"
          }}</span>
        </button>
      </div>
    </div>
    <div class="card-body p-0">
      <div v-if="loading" class="p-4 text-center">Cargando competencias...</div>

      <table v-else class="table table-hover mb-0">
        <tbody>
          <tr v-for="(comp, i) in competencias" :key="comp.id">
            <td class="text-center fw-bold text-secondary">{{ i + 1 }}</td>
            <td>{{ comp.descripcion }}</td>
            <td class="d-flex justify-content-end">
              <button
                @click="abrirEliminarModal(comp)"
                class="btn btn-sm btn-outline-danger"
              >
                Eliminar
              </button>
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
    <FormularioCrear
      v-if="mostrarForm"
      endpoint="http://127.0.0.1:8000/api/competencias"
      tipo="comp"
      :idPadre="grado.id"
      @cancelar="mostrarForm = false"
      @creado="onCreado"
    />

    <ConfirmarEliminar 
        :show="eliminarModalVisible" 
        mensaje="¿Seguro que quieres eliminar esta competencia?"
        @confirm="confirmarEliminarComp" 
        @close="eliminarModalVisible = false"
    />
  </div>
</template>