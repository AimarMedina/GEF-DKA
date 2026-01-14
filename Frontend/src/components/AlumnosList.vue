<script setup>
import { ref, onMounted, computed } from "vue";
import axios from "axios";
import UserTable from "@/components/UserTable.vue";
import Navbar from "../components/Navbar.vue";
import { useRoute } from "vue-router";
const props = defineProps({
  endpoint: { type: String, required: true }, // URL completa
  title: { type: String, default: "Alumnos" },
  actions: {},
});

const route = useRoute();
//Cogemos de la URL si los datos son de un tutor o de un instructor
const isTutorView = computed(() => route.name === "alumnosTutor");
const isInstructorView = computed(() => route.name === "alumnosInstructor");

function verNotas(alumno) {}
function verEntregas(alumno) {}
function verSeguimiento(alumno) {}
function crearSeguimiento(alumno) {}

const alumnos = ref([]); // por defecto array vacío de alumnos
const currentPage = ref(1); //por defecto se carga la primera pagina
const totalPages = ref(1); // por defecto una pagina en total
const perPage = ref(5); //por defecto 5 alumnos por página

//Asignamos la primera primera página al fetch
async function fetchAlumnos(page = 1) {
  //si nos meten una página menor que uno redirigimos a la primera
  if (page < 1) page = 1;

  //actualizamos la página en la que estamos
  currentPage.value = page;

  //Cogemos la URL, la página en la que estamos y la cantidad de alumnos que va a haber en cada página. Guardamos en res
  const res = await axios.get(props.endpoint, {
    params: { page: currentPage.value, per_page: perPage.value },
  });

  //guardamos el json que devuelve laravel en paginator
  const paginator = res.data.data;
  //metemos los datos en el array de alumnos y si no los hay guardamos un array vacio
  alumnos.value = paginator.data || [];
  //asignamos el total de páginas con el número de la última, mínimo 1
  totalPages.value = paginator.last_page || 1;
}

//Cuando el componente se cargue se va a la primera pagina
onMounted(() => fetchAlumnos(1));
</script>

<template>
  <Navbar></Navbar>

  <div>
    <h4 class="mb-3">{{ title }}</h4>

    <table class="table table-striped">
      <thead>
        <tr>
          <th>ID</th>
          <th>Nombre</th>
          <th>Apellidos</th>
          <th>Email</th>
          <th>Grado</th>
          <th>Acciones</th>
        </tr>
      </thead>

      <tbody>
        <tr v-for="a in alumnos" :key="a.ID_Usuario">
          <td>{{ a.ID_Usuario }}</td>
          <td>{{ a.Nombre }}</td>
          <td>{{ a.Apellidos }}</td>
          <td>{{ a.Email }}</td>
          <td>{{ a.Grado }}</td>
          <td>
            <span v-if="isTutorView">
              <!-- Hay que coger de la bd si un alumno tiene estancia
     SI TIENE ESTANCIA: Ver seguimiento/ borrar estancia
     SI NO TIENE ESTANCIA: Asignar Estancia-->
              <button v-if=" a.Tiene_estancia"
                class="btn btn-sm btn-primary me-2"
                @click="verSeguimiento(a)" 
              >
                Ver Segumiento
              </button>
               <button v-if="! a.Tiene_estancia"
                class="btn btn-sm btn-primary me-2"
                @click="verSeguimiento(a)" 
              >
                Asignar Empresa
              </button>

              <button class="btn btn-sm btn-secondary" @click="verEntregas(a)">
                Entregas
              </button>
            </span>

            <span v-else-if="isInstructorView">
              <button v-if=" a.Tiene_estancia"
                class="btn btn-sm btn-primary me-2"
                @click="verSeguimiento(a)" 
              >
                Ver Notas
              </button>
               <button v-if="! a.Tiene_estancia"
               disabled=""
                class="btn btn-sm btn-primary me-2"
                @click="verSeguimiento(a)" 
              >
                Ver Notas
              </button>

              
            </span>

            <span v-else>
              <span class="text-muted">-</span>
            </span>
          </td>
        </tr>

        <tr v-if="alumnos.length === 0">
          <td colspan="6" class="text-center py-3">No hay alumnos</td>
        </tr>
      </tbody>
    </table>

    <nav>
      <ul class="pagination justify-content-center">
        <li class="page-item" :class="{ disabled: currentPage === 1 }">
          <button
            class="page-link"
            @click="fetchAlumnos(currentPage - 1)"
            :disabled="currentPage === 1"
          >
            Anterior
          </button>
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
          <button
            class="page-link"
            @click="fetchAlumnos(currentPage + 1)"
            :disabled="currentPage === totalPages"
          >
            Siguiente
          </button>
        </li>
      </ul>
    </nav>
  </div>
</template>
