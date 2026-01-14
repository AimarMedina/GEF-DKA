<script setup>
    import { useRoute } from "vue-router";
    import AlumnosList from "../../components/AlumnosList.vue";

const route = useRoute();
const tutorId = route.params.id;    
</script>
<template>
     <AlumnosList
    :endpoint="`http://127.0.0.1:8000/api/tutores/${tutorId}/alumnos`"
    title="Tus Alumnos"
  />
     <!-- <template #actions="{ alumno }">
    <button class="btn btn-sm btn-primary me-2" @click="verDetalle(alumno)">
      Ver
    </button>
  </template>
  </AlumnosList> -->
</template>