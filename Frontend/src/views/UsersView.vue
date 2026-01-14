<script setup>
import { ref } from "vue";
import Navbar from "@/components/Navbar.vue";
import UserFilters from "@/components/UserFilter.vue";
import UserTable from "@/components/UserTable.vue";

const filters = ref(null);

function onFiltersChange(newFilters) {
  filters.value = newFilters.tipo ? newFilters : null;
}
</script>

<template>
  <Navbar />

  <div class="container mt-4">
    <UserFilters @change="onFiltersChange" />

    <UserTable
      v-if="filters"
      :filters="filters"
    />

    <div v-else class="alert alert-info text-center">
      Selecciona un tipo de usuario para mostrar la tabla
    </div>
  </div>
</template>
