<script setup>
import { ref, watch, defineEmits, defineProps } from "vue";

const props = defineProps({
  tipo: { type: String, default: "Buscador" },
});

const emit = defineEmits(["search"]);
const texto = ref("");


watch(texto, (nuevoValor) => {
  emit("search", nuevoValor);
});
</script>

<template>
  <nav class="navbar bg-body-tertiary mb-3">
    <div class="">
      <input
        class="form-control"
        type="search"
        v-model="texto"
        :placeholder="props.tipo"
        aria-label="Búsqueda"
      />
    </div>
  </nav>
</template>
