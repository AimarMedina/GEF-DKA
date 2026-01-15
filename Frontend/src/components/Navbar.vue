<template>
  <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
    <div class="container-fluid">
      <!-- Logo -->
      <RouterLink to="/home">
        <img
          src="../../public/LOGO-EGIBIDE.png"
          alt="Logo Egibide"
          class="logo"
        />
      </RouterLink>
      <!-- Bienvenida (desktop, derecha) -->
      <span class="d-none d-lg-block ms-lg-auto me-3 fw-semibold">
        ¡Bienvenid@, {{ usuario.nombre }}!
      </span>

      <!-- Hamburguesa -->
      <button
        class="navbar-toggler"
        type="button"
        data-bs-toggle="offcanvas"
        data-bs-target="#offcanvasNavbar"
        aria-controls="offcanvasNavbar"
      >
        <span class="navbar-toggler-icon"></span>
      </button>

      <!-- Offcanvas -->
      <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasNavbar">
        <div class="offcanvas-header">
          <h5 class="offcanvas-title">¡Bienvenid@, {{ usuario.nombre }}!</h5>
          <button
            type="button"
            class="btn-close"
            data-bs-dismiss="offcanvas"
            aria-label="Close"
          ></button>
        </div>

        <div class="offcanvas-body">
          <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
            <li class="nav-item">
              <a class="nav-link">
                <RouterLink to="/home">Inicio</RouterLink>
              </a>
            </li>
            <li class="nav-item"></li>

            <li class="nav-item dropdown" v-if="usuario.tipo === 'admin'">
              <a
                class="nav-link dropdown-toggle"
                role="button"
                data-bs-toggle="dropdown"
                aria-expanded="false"
              >
                Gestión
              </a>
              <ul class="dropdown-menu">
                <li>
                  <a class="dropdown-item">
                    <RouterLink to="/users">Usuarios</RouterLink>
                  </a>
                  <a class="dropdown-item">
                    <RouterLink to="/users">Competencias</RouterLink>
                  </a>
                  <a class="dropdown-item">
                    <RouterLink to="/users">RAs</RouterLink>
                  </a>
                  <a class="dropdown-item">
                    <RouterLink to="/users">Grados y Asignaturas</RouterLink>
                  </a>
                  <a class="dropdown-item">
                    <RouterLink to="/empresa">Empresas</RouterLink>
                  </a>
                </li>
              </ul>
            </li>

            <li class="nav-item dropdown" v-if="usuario.tipo === 'tutor'">
              <a
                class="nav-link dropdown-toggle"
                role="button"
                data-bs-toggle="dropdown"
                aria-expanded="false"
              >
                Gestión
              </a>

              <ul class="dropdown-menu">
                <li>
                  <RouterLink
                    class="dropdown-item"
                    :to="`/tutores/${usuario.id}/alumnos`"
                  >
                    Alumnos
                  </RouterLink>
                </li>

                 <li>
                    <RouterLink class="dropdown-item" to="/cuadernos-tutor">
                      Cuadernos
                    </RouterLink>
                  </li>
              </ul>
            </li>

            <li class="nav-item" v-if="usuario.tipo === 'instructor'">
              <RouterLink
                class="nav-link"
                :to="`/instructores/${usuario.id}/alumnos`"
              >
                Alumnos
              </RouterLink>
            </li>
            <li class="nav-item" v-if="usuario.tipo ==='alumno'">
              <RouterLink
                class="nav-link"
                :to="`/alumno/${usuario.id}/estancia`"
              >
              Estancia
              </RouterLink>
            </li>
            
             <li class="nav-item" v-if="usuario.tipo === 'alumno'">
              <RouterLink class="nav-link" to="/cuadernos-alumno">Mis Cuadernos</RouterLink>
            </li>

            <li class="nav-item">
              <a class="nav-link text-danger" role="button" @click="logout">
                Cerrar sesión
              </a>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </nav>
</template>

<style scoped>
.logo {
  max-height: 45px;
  width: auto;
}

a {
  text-decoration: none;
  color: black;
}
</style>

<script setup>
import { useUserStore } from "@/stores/userStore";
import axios from "axios";
import { ref } from "vue";
import { useRouter } from "vue-router";
import { RouterLink } from "vue-router";
const router = useRouter();
const userStore = useUserStore()

let message = ref()
let usuario = userStore.user
async function logout() {
    const token = localStorage.getItem('token')
    try {
        const response = await axios.post('http://localhost:8000/api/logout', {}, {
            headers: {
                Authorization: `Bearer ${token}`
            }
        });
        if (response.data.status === 'success') {
            localStorage.removeItem('token');
            delete axios.defaults.headers.common['Authorization'];
            userStore.user.value = null
            router.push('/');
        }
    } catch (error) {
        console.error(error);
        message.value = 'Error cerrando sesión';
    }
  } 

</script>

<style lang="scss" scoped></style>
