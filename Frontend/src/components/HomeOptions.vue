<script setup>
import { useUserStore } from "@/stores/userStore";
import { storeToRefs } from "pinia"; // Opcional, pero recomendado para reactividad
import { computed } from "vue";

const userStore = useUserStore();
const usuario = computed(() => userStore.user);

const isAdmin = computed(() => usuario.value?.tipo === 'admin');
const isTutor = computed(() => usuario.value?.tipo === 'tutor');
const isInstructor = computed(() => usuario.value?.tipo === 'instructor');
const isAlumno = computed(() => usuario.value?.tipo === 'alumno');

const claseRol = (tipo) => {
  return {
    admin: 'bg-primary',
    alumno: 'bg-success',
    tutor: 'bg-indigo',
    instructor: 'bg-warning'
  }[tipo] || 'bg-secondary'
}

</script>

<template>
  <div class="container mt-5">

    <div class="text-center mb-5" v-if="usuario">
      <h1 class="display-5 fw-bold text-primary">¡Hola, {{ usuario.nombre }}!</h1>
      <p class="lead text-muted">
        Bienvenid@ a tu panel de gestión como
        <span class="badge text-uppercase" :class="claseRol(usuario.tipo)">
          {{ usuario.tipo }}
        </span>
      </p>
    </div>

    <!-- ========== VISTA PRINCIPAL DE ADMINISTRADOR  =========  -->
    <!-- ====================================================== -->
    <!-- ====================================================== -->

    <div v-if="isAdmin" class="row g-4">
      <div class="col-12 col-md-6 col-lg-3 ">
        <RouterLink to="/users" class="img-link">
          <div class="card h-100 border-0 shadow hover-scale overflow-hidden">
            <img src="../../public/images/Usuarios.png" class="card-img-top w-100 h-100 object-fit-cover"
              alt="Gestión Usuarios">
          </div>
        </RouterLink>
      </div>

      <div class="col-12 col-md-6 col-lg-3 ">
        <RouterLink to="/grados" class="img-link">
          <div class="card h-100 border-0 shadow hover-scale overflow-hidden">
            <img src="../../public/images/GradosAsignaturas.png" class="card-img-top w-100 h-100 object-fit-cover"
              alt="Gestión Grados y Asingnaturas">
          </div>
        </RouterLink>
      </div>
      <div class="col-12 col-md-6 col-lg-3 ">
        <RouterLink to="/competenciasXra" class="img-link">
          <div class="card h-100 border-0 shadow hover-scale overflow-hidden">
            <img src="../../public/images/CompetenciasRas.png" class="card-img-top w-100 h-100 object-fit-cover"
              alt="Gestión Grados y Asingnaturas">
          </div>
        </RouterLink>
      </div>

      <div class="col-12 col-md-6 col-lg-3 ">
        <RouterLink to="/empresa" class="img-link">
          <div class="card h-100 border-0 shadow hover-scale overflow-hidden">
            <img src="../../public/images/Empresas.png" class="card-img-top w-100 h-100 object-fit-cover"
              alt="Gestión Empresas">
          </div>
        </RouterLink>
      </div>
    </div>

    <!-- =================================================================================================== -->
    <!-- =================================================================================================== -->


    <!-- ========== VISTA PRINCIPAL DE TUTOR  =========  -->

    <div v-else-if="isTutor" class="row g-4 justify-content-center">

      <div class="col-12 col-md-6 col-lg-4 ">
        <RouterLink :to="`/tutores/${usuario.id}/alumnos`">
          <div class="card h-100 border-0 shadow hover-scale overflow-hidden">
            <img src="../../public/images/Alumnos.png" class="card-img-top w-100 h-100 object-fit-cover"
              alt="Gestión de Alumnos del Tutor">
          </div>
        </RouterLink>
      </div>

      <div class="col-12 col-md-6 col-lg-4 ">
        <RouterLink :to="`/cuadernos-tutor`">
          <div class="card h-100 border-0 shadow hover-scale overflow-hidden">
            <img src="../../public/images/Cuadernos.png" class="card-img-top w-100 h-100 object-fit-cover"
              alt="Gestión de Cuadernos">
          </div>
        </RouterLink>
      </div>

    </div>

    <!-- ====================================================================================================== -->
    <!-- ====================================================================================================== -->


    <!-- ========== VISTA PRINCIPAL DE INSTRUCTOR  =========  -->

    <div v-else-if="isInstructor" class="row justify-content-center">

      <div class="col-12 col-md-6 col-lg-4 ">
        <RouterLink :to="`/instructores/${usuario.id}/alumnos`">
          <div class="card h-100 border-0 shadow hover-scale overflow-hidden">
            <img src="../../public/images/Alumnos.png" class="card-img-top w-100 h-100 object-fit-cover"
              alt="Gestión de Alumnos del Instructor">
          </div>
        </RouterLink>
      </div>

    </div>

    <!-- ====================================================================================================== -->
    <!-- ====================================================================================================== -->

    <!-- ========== VISTA PRINCIPAL DE ALUMNO  =========  -->

    <div v-else-if="isAlumno" class="row g-4">

      <div class="col-12 col-md-6 col-lg-4 ">
        <RouterLink :to="`/alumno/${usuario.id}/estancia`">
          <div class="card h-100 border-0 shadow hover-scale overflow-hidden">
            <img src="../../public/images/Estancia.png" class="card-img-top w-100 h-100 object-fit-cover"
              alt="Estancia de Alumno">
          </div>
        </RouterLink>
      </div>

      <div class="col-12 col-md-6 col-lg-4 ">
        <RouterLink to="/cuadernos-alumno">
          <div class="card h-100 border-0 shadow hover-scale overflow-hidden">
            <img src="../../public/images/Cuadernos.png" class="card-img-top w-100 h-100 object-fit-cover"
              alt="Cuadernos de Alumno">
          </div>
        </RouterLink>
      </div>

      <div class="col-12 col-md-6 col-lg-4 ">
        <RouterLink to="/alumno/mis-notas">
          <div class="card h-100 border-0 shadow hover-scale overflow-hidden">
            <img src="../../public/images/MisNotas.png" class="card-img-top w-100 h-100 object-fit-cover"
              alt="Notas de Alumno">
          </div>
        </RouterLink>
      </div>

    </div>

    <div v-else class="text-center mt-5">
      <div class="spinner-border text-secondary" role="status">
        <span class="visually-hidden">Cargando datos de usuario...</span>
      </div>
    </div>

  </div>
</template>

<style scoped>
.card {
  transition: transform 0.2s;
}

.card:hover {
  transform: translateY(-5px);
}
</style>
