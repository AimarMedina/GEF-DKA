<script setup>
import { ref } from "vue";
import Navbar from "@/components/Navbar.vue";
import UserFilters from "@/components/UserFilters.vue";
import UserTable from "@/components/UserTable.vue";

const filters = ref(null);

function onFiltersChange(newFilters) {
  filters.value = null;

  if (newFilters.tipo === "NONE") {
    return;
  }

  if (newFilters.tipo === "alumno" && (!newFilters.id_grado || newFilters.id_grado === "NONE")) {
    return;
  }

  if (newFilters.tipo === "alumno" && newFilters.id_grado === ".") {
    filters.value = { tipo: "alumno", id_grado: null };
    return;
  }

  if (newFilters.tipo === ".") {
    filters.value = {};
    return;
  }

  if (newFilters.tipo === "alumno") {
    filters.value = {
      tipo: "alumno",
      id_grado: newFilters.id_grado
    };
    return;
  }

  filters.value = {
    tipo: newFilters.tipo,
    id_grado: null
  };

  console.log("Filters activos:", filters.value);
}




</script>

<template>
  <Navbar />

  <div class="container mt-4">
    <UserFilters @change="onFiltersChange" />

    <UserTable v-if="filters" :filters="filters"/>

    <div v-else class="alert alert-info text-center">
      Selecciona un tipo de usuario para mostrar la tabla
    </div>
  </div>
</template>
