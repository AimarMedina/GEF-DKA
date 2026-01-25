<script setup>
import { ref } from "vue";
import Navbar from "@/components/Navbar.vue";
import UserFilters from "@/components/UserFilters.vue";
import UserTable from "@/components/UserTable.vue";

const filters = ref(null);

function onFiltersChange(newFilters) {
  // Inicializamos objeto base
  let filtrosActivos = {};

  if (newFilters.tipo === "NONE") {
    filters.value = null;
    return;
  }

  // Lógica base
  filtrosActivos.tipo = newFilters.tipo;
  filtrosActivos.search = newFilters.search || null; // <--- AGREGAMOS SEARCH

  // Lógica específica para alumnos
  if (newFilters.tipo === "alumno") {
    if (!newFilters.id_grado || newFilters.id_grado === "NONE") {
        filters.value = null; // Si es alumno y no hay grado, no mostramos nada (según tu lógica anterior)
        return;
    }
    if (newFilters.id_grado !== ".") {
        filtrosActivos.id_grado = newFilters.id_grado;
    }
  }

  // Si seleccionan "Todos" (el punto) en tipo
  if (newFilters.tipo === ".") {
     filters.value = { search: newFilters.search }; // Solo filtramos por texto si hay
     return;
  }

  filters.value = filtrosActivos;
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