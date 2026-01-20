<script setup>
import { ref, onMounted, computed, watch } from "vue";
import axios from "axios";
import UserTable from "@/components/UserTable.vue";
import Navbar from "../components/Navbar.vue";
import Buscador from "./Buscador.vue";
import { useRoute, useRouter } from "vue-router";
import NotasAlumnoModal from "@/components/Tutor/NotasAlumnoModal.vue";
const props = defineProps({
  endpoint: { type: String, required: true }, // URL completa
  title: { type: String, default: "Alumnos" },
  actions: {},
});

const route = useRoute();
const redireccionador= useRouter();
//Cogemos de la URL si los datos son de un tutor o de un instructor
const isTutorView = computed(() => route.name === "alumnosTutor");
const isInstructorView = computed(() => route.name === "alumnosInstructor");
const showNotasModal = ref(false);   // modal
const notasAlumno = ref(null);       // almacena las notas del alumno seleccionado
const alumnoSeleccionado = ref(null);



async function verNotas(alumno) {
  alumnoSeleccionado.value = alumno;
  const token = localStorage.getItem('token');

  try {
    const res = await axios.get(`http://localhost:8000/api/alumno/${alumno.ID_Usuario}/mis-notas`, {
      headers: { Authorization: `Bearer ${token}` },
    });
    notasAlumno.value = res.data;  // guarda las notas
    showNotasModal.value = true;   // abre el modal
  } catch (err) {
    console.error(err);
    alert("Error al cargar las notas del alumno");
  }
}


function verSeguimiento(alumno) {
  alumnoSeleccionado.value = alumno

  if (alumno.Tiene_estancia) { // solo comprobar booleano
    redireccionador.push(`/tutor/alumno/${alumno.estancia_id}/seguimiento`)
  } else {
    alert("Este alumno no tiene estancia asignada")
  }
}


const alumnos = ref([]); // por defecto array vacío de alumnos
const currentPage = ref(1); //por defecto se carga la primera pagina
const totalPages = ref(1); // por defecto una pagina en total
const perPage = ref(5); //por defecto 5 alumnos por página

const q = ref("");
let searchTimeout = null;
function onSearch(texto) {
  q.value = (texto || "").trim();
}
watch(q, () => {
  if (searchTimeout) {
    clearTimeout(searchTimeout);
  }

  searchTimeout = setTimeout(() => {
    fetchAlumnos(1); // se muestra la primera página
  }, 400);
});

async function fetchAlumnos(page = 1) {
  //si nos meten una página menor que uno redirigimos a la primera
  if (page < 1) page = 1;

  //actualizamos la página en la que estamos
  currentPage.value = page;

  const token = localStorage.getItem('token');
  try{
  //Cogemos la URL, la página en la que estamos y la cantidad de alumnos que va a haber en cada página. Guardamos en res
  const res = await axios.get(props.endpoint, {
    headers:  { Authorization: `Bearer ${token}` },
    params: { page: currentPage.value, per_page: perPage.value, q: q.value },
  });

  //guardamos el json que devuelve laravel en paginator
  const paginator = res.data.data;
  //metemos los datos en el array de alumnos y si no los hay guardamos un array vacio
  alumnos.value = paginator.data || [];
  //asignamos el total de páginas con el número de la última, mínimo 1
  totalPages.value = paginator.last_page || 1;
} catch(error){
  if(error.response && error.response.status === 403){
    redireccionador.push('/home');
    alert(error.response.data.message);
  }
}
}


onMounted(() => fetchAlumnos(1));
</script>

<template>
  <Navbar />

  <div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4 class="mb-0">{{ title }}</h4>
      <Buscador :tipo="'Buscar Alumnos'" @search="onSearch" />
    </div>

    <div class="table-responsive shadow-sm rounded">
      <table class="table table-hover table-striped align-middle">
        <thead class="table-indigo">
          <tr>
            <th>Nombre</th>
            <th>Apellidos</th>
            <th>Email</th>
            <th>Grado</th>
            <th class="text-center">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="a in alumnos" :key="a.ID_Usuario">
            <td>{{ a.Nombre }}</td>
            <td>{{ a.Apellidos }}</td>
            <td>{{ a.Email }}</td>
            <td>{{ a.Grado }}</td>
            <td class="text-center">
              <!-- ==== VISTA DE TUTORES ==== -->
              <span v-if="isTutorView">
                <button
                  v-if="a.estancia_id"
                  class="btn btn-sm btn-primary me-1"
                  @click="$router.push({ name: 'seguimiento', params: { estanciaId: a.estancia_id } })"
                  title="Ver Seguimiento"
                >
                  <i class="bi bi-bar-chart-line"></i> Ver seguimiento
                </button>

                <button
                  v-if="!a.Tiene_estancia"
                  class="btn btn-sm btn-warning me-1"
                  @click="verSeguimiento(a)"
                  title="Asignar Empresa"
                >
                  <i class="bi bi-building"></i> Asignar estancia
                </button>

                <button
                  class="btn btn-sm btn-success"
                  @click="verNotas(a)"
                  title="Ver Notas"
                >
                  <i class="bi bi-journal-text"></i> Ver notas
                </button>
              </span>

              <!-- ==== VISTA DE INSTRUCTORES ==== -->
              <span v-else-if="isInstructorView">
                <RouterLink
                  v-if="a.Tiene_estancia"
                  :to="`/instructor/alumnos/${a.ID_Usuario}/notas`"
                  class="btn btn-sm btn-primary me-1"
                  title="Ver Notas"
                >
                  <i class="bi bi-journal-text"></i>
                </RouterLink>

                <button v-else class="btn btn-sm btn-secondary me-1" disabled title="Sin Estancia">
                  <i class="bi bi-journal-text"></i>
                </button>
              </span>

              <span v-else>
                <span class="text-muted">-</span>
              </span>
            </td>
          </tr>

          <tr v-if="alumnos.length === 0">
            <td colspan="5" class="text-center py-3 text-muted">
              No hay alumnos
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Paginación -->
    <nav v-if="totalPages > 1" class="mt-3">
      <ul class="pagination justify-content-center">
        <li class="page-item" :class="{ disabled: currentPage === 1 }">
          <button class="page-link" @click="fetchAlumnos(currentPage - 1)">Anterior</button>
        </li>

        <li
          v-for="p in totalPages"
          :key="p"
          class="page-item"
          :class="{ active: currentPage === p }"
        >
          <button class="page-link" @click="fetchAlumnos(p)">{{ p }}</button>
        </li>

        <li class="page-item" :class="{ disabled: currentPage === totalPages }">
          <button class="page-link" @click="fetchAlumnos(currentPage + 1)">Siguiente</button>
        </li>
      </ul>
    </nav>

    <NotasAlumnoModal
      :alumno="alumnoSeleccionado"
      :notas="notasAlumno"
      :show="showNotasModal"
      @close="showNotasModal = false"
    />
  </div>
</template>
