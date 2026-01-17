<script setup>
import { useUserStore } from "@/stores/userStore";
import { storeToRefs } from "pinia"; // Opcional, pero recomendado para reactividad
import { computed } from "vue";

// 1. Inicializamos el store (ESTO ES LO QUE TE FALTABA)
const userStore = useUserStore();

// 2. Extraemos el usuario. Usamos computed para que sea reactivo si cambia.
// Nota: userStore.user ya es reactivo en Pinia, pero asignarlo a una const requiere cuidado.
const usuario = computed(() => userStore.user);

// Helpers para limpiar el template
const isAdmin = computed(() => usuario.value?.tipo === 'admin');
const isTutor = computed(() => usuario.value?.tipo === 'tutor');
const isInstructor = computed(() => usuario.value?.tipo === 'instructor');
const isAlumno = computed(() => usuario.value?.tipo === 'alumno');

</script>

<template>
  <div class="container mt-5">
    
    <div class="text-center mb-5" v-if="usuario">
      <h1 class="display-5 fw-bold text-primary">¡Hola, {{ usuario.nombre }}!</h1>
      <p class="lead text-muted">
        Bienvenido a tu panel de gestión como 
        <span class="badge bg-secondary text-uppercase">{{ usuario.tipo }}</span>
      </p>
    </div>

    <div v-if="isAdmin" class="row g-4">
      <div class="col-md-4">
        <div class="card h-100 shadow-sm border-primary">
          <div class="card-body text-center">
            <h5 class="card-title">Usuarios</h5>
            <p class="card-text">Gestiona alumnos, tutores e instructores.</p>
            <RouterLink to="/users" class="btn btn-outline-primary">Ir a Usuarios</RouterLink>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card h-100 shadow-sm border-success">
          <div class="card-body text-center">
            <h5 class="card-title">Grados y Asignaturas</h5>
            <p class="card-text">Configura la oferta académica.</p>
            <RouterLink to="/grados" class="btn btn-outline-success">Gestionar Grados</RouterLink>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card h-100 shadow-sm border-info">
          <div class="card-body text-center">
            <h5 class="card-title">Empresas</h5>
            <p class="card-text">Administra las empresas colaboradoras.</p>
            <RouterLink to="/empresa" class="btn btn-outline-info">Ver Empresas</RouterLink>
          </div>
        </div>
      </div>
    </div>

    <div v-else-if="isTutor" class="row g-4 justify-content-center">
      <div class="col-md-5">
        <div class="card h-100 shadow-sm border-primary">
          <div class="card-body text-center">
            <h3 class="mb-3">👨‍🎓</h3>
            <h5 class="card-title">Mis Alumnos</h5>
            <p class="card-text">Seguimiento, asignación de empresas y notas.</p>
            <RouterLink :to="`/tutores/${usuario.id}/alumnos`" class="btn btn-primary w-100">
              Ver Alumnos
            </RouterLink>
          </div>
        </div>
      </div>
      <div class="col-md-5">
        <div class="card h-100 shadow-sm border-warning">
          <div class="card-body text-center">
            <h3 class="mb-3">📖</h3>
            <h5 class="card-title">Cuadernos</h5>
            <p class="card-text">Revisar entregas de cuadernos de seguimiento.</p>
            <RouterLink to="/cuadernos-tutor" class="btn btn-warning text-white w-100">
              Revisar Cuadernos
            </RouterLink>
          </div>
        </div>
      </div>
    </div>

    <div v-else-if="isInstructor" class="row justify-content-center">
      <div class="col-md-6">
        <div class="card shadow-lg border-0">
          <div class="card-body text-center p-5">
            <h2 class="mb-4">🏭</h2>
            <h4 class="card-title">Alumnos en Prácticas</h4>
            <p class="card-text mb-4">Evalúa las competencias técnicas y transversales de tus alumnos asignados.</p>
            <RouterLink :to="`/instructores/${usuario.id}/alumnos`" class="btn btn-primary btn-lg">
              Acceder al Listado
            </RouterLink>
          </div>
        </div>
      </div>
    </div>

    <div v-else-if="isAlumno" class="row g-4">
      <div class="col-md-4">
        <div class="card h-100 shadow-sm">
          <div class="card-body text-center">
            <h5 class="card-title">Mi Estancia</h5>
            <p class="card-text">Datos de tu empresa y fechas.</p>
            <RouterLink :to="`/alumno/${usuario.id}/estancia`" class="btn btn-outline-dark">
              Ver Estancia
            </RouterLink>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card h-100 shadow-sm">
          <div class="card-body text-center">
            <h5 class="card-title">Cuaderno Digital</h5>
            <p class="card-text">Sube tus entregas y rellena el seguimiento.</p>
            <RouterLink to="/cuadernos-alumno" class="btn btn-outline-primary">
              Mis Entregas
            </RouterLink>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card h-100 shadow-sm">
          <div class="card-body text-center">
            <h5 class="card-title">Calificaciones</h5>
            <p class="card-text">Consulta tus evaluaciones.</p>
            <RouterLink to="/alumno/mis-notas" class="btn btn-outline-success">
              Ver Notas
            </RouterLink>
          </div>
        </div>
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