<script setup>
import axios from "axios";
import { ref, watch, onMounted } from "vue";
import UserCreationButtons from "./UserCreationButtons.vue";
import api from "@/services/api.js";
import BuscadorSelect from "./BuscadorSelect.vue";
import Buscador from "./Buscador.vue";
const emit = defineEmits(["change"]);

const tipo = ref("NONE");
const grado = ref("NONE");
const busquedaTexto = ref("");
const grados = ref([]);
async function cargarGrados() {
  try {
    const response = await api.get("/api/grados");
    grados.value = response.data.data;
  } catch (e) {
    console.error(e);
  }
}

onMounted(() => {
  cargarGrados();
});

watch(tipo, () => {
  busquedaTexto.value = "";
  grado.value = "NONE";
});

watch([tipo, grado, busquedaTexto], () => {
  emit("change", {
    tipo: tipo.value,
    id_grado: grado.value,
    search: busquedaTexto.value,
  });
});

// Función para recibir el evento 'search' de tu componente Buscador.vue
function actualizarBusqueda(texto) {
  busquedaTexto.value = texto;
}
</script>

<template>
  <div class="card mb-4">
    <div class="card-body">
      <div class="mb-3">
        <label class="form-label">Tipo de usuario</label>
        <select v-model="tipo" class="form-select">
          <option value="NONE">Selecciona un tipo</option>
          <option value=".">Todos</option>
          <option value="alumno">Alumno</option>
          <option value="tutor">Tutor</option>
          <option value="instructor">Instructor</option>
          <option value="admin">Administrador</option>
        </select>
      </div>

      <div v-if="tipo === 'alumno'" class="mb-3">
        <label class="form-label">Grado</label>
        <BuscadorSelect
          v-model="grado"
          :options="grados"
          label-key="nombre"
          value-key="id"
          placeholder="Buscar o seleccionar grado..."
        />
      </div>
      <div
        v-if="tipo === 'tutor' || tipo === 'instructor'"
        class="col-md-4 mb-3"
      >
        <label class="form-label">Buscar por nombre</label>
        <Buscador
          :tipo="'Buscar ' + tipo + '...'"
          @search="actualizarBusqueda"
        />
      </div>
      <UserCreationButtons
        :tipo="tipo"
        :grado="grado == '.' ? false : grado"
      ></UserCreationButtons>
    </div>
  </div>
</template>
