<script setup>
    import axios from "axios";
    import { ref, watch, onMounted } from "vue";

    const emit = defineEmits(["change"]);

    const tipo = ref("");
    const grado = ref("");
    const grados = ref([]);

    async function cargarGrados() {
    try {
        const response = await axios.get("http://localhost:8000/api/grados");
        grados.value = response.data;
    } catch (e) {
        console.error(e);
    }
    }

    onMounted(() => {
    cargarGrados();
    });

    watch([tipo, grado], () => {
    emit("change", {
        tipo: tipo.value,
        grado: tipo.value === "alumno" ? grado.value : null
    });
    });
</script>


<template>
  <div class="card mb-4">
    <div class="card-body">
      <h5 class="fw-bold mb-3">Filtros</h5>

      <!-- Tipo de usuario -->
      <div class="mb-3">
        <label class="form-label">Tipo de usuario</label>
        <select v-model="tipo" class="form-select">
          <option value="">Selecciona un tipo</option>
          <option value="alumno">Alumno</option>
          <option value="tutor">Tutor</option>
          <option value="instructor">Instructor</option>
          <option value="admin">Administrador</option>
        </select>
      </div>

      <!-- Grado (solo alumnos) -->
      <div v-if="tipo === 'alumno'" class="mb-3">
        <label class="form-label">Grado</label>
        <select v-model="grado" class="form-select">
          <option value="">Selecciona un grado</option>
          <option v-for="g in grados" :key="g" :value="g">
            {{ g }}
          </option>
        </select>
      </div>
    </div>
  </div>
</template>
