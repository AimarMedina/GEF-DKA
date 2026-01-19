<script setup>
import { ref, onMounted, defineEmits } from "vue";
import axios from "axios";
import Buscador from "../Buscador.vue"; 
// import GradoForm from './GradoForm.vue'; 

// AÑADIMOS LOS NUEVOS EVENTOS AL EMIT
const emit = defineEmits(['verAsignaturas', 'verCompetencias']);

// Estado
const grados = ref([]);
const currentPage = ref(1);
const totalPages = ref(1);
const loading = ref(false);
const mostrarModal = ref(false); 

const searchQuery = ref("");
let searchTimeout = null;

function onSearch(texto) {
  searchQuery.value = texto;
  if (searchTimeout) clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    fetchGrados(1); 
  }, 400);
}

const fetchGrados = async (page = 1) => {
  loading.value = true;
  try {
    const response = await axios.get(`http://127.0.0.1:8000/api/grados`, {
        params: {
            page: page,
            q: searchQuery.value
        }
    });
    
    const data = response.data;
    grados.value = data.data || []; 
    currentPage.value = data.current_page || 1;
    totalPages.value = data.last_page || 1;

  } catch (error) {
    console.error("Error al cargar los grados:", error);
  } finally {
    loading.value = false;
  }
};

const crearGrado = async (grado) => {
    console.log("Crear grado", grado);
    mostrarModal.value = false;
    fetchGrados(1);
}

onMounted(() => {
  fetchGrados(1);
});
</script>

<template>
  <div class="col-12 col-lg-4 mt-3"> 
   
    <Buscador tipo="Buscar grado..." @search="onSearch" />

     <button class="btn btn-secondary mb-2" @click="mostrarModal = true">
        <i class="bi bi-building-fill-add"></i> Añadir
    </button>

    <div v-if="!loading" class="list-group shadow-sm">
        <div class="list-group-item text-white bg-indigo cursor-pointer"
            @click="emit('seleccionarGrado', null)">
            <h3 class="mb-0 fs-4">Grados</h3>
        </div>

        <div v-for="grado in grados" :key="grado.id" 
            class="list-group-item list-group-item-action flex-column align-items-start p-3"
            style="cursor: pointer;"
           >
            
            <div class="d-flex w-100 justify-content-between mb-2">
                <h5 class="mb-1 text-break">{{ grado.nombre }}</h5>
                <small class="badge bg-secondary align-self-start">{{ grado.curso }}</small>
            </div>

            <div class="d-flex gap-2 w-100 mt-2">
                <button class="btn btn-sm btn-outline-primary flex-fill" 
                        @click.stop="emit('verAsignaturas', grado)">
                    <i class="bi bi-book"></i> Asignaturas
                </button>
                
                <button class="btn btn-sm btn-outline-success flex-fill" 
                        @click.stop="emit('verCompetencias', grado)">
                    <i class="bi bi-gear"></i> Competencias
                </button>
            </div>
        </div>

        <div v-if="grados.length === 0" class="list-group-item text-center text-muted">
            No se encontraron grados.
        </div>
    </div>

    <div v-if="loading" class="mt-3">
        <p class="text-muted">Cargando grados...</p>
        <ul class="carga">
            <li></li><li></li><li></li><li></li><li></li>
        </ul>
    </div>

    <nav v-if="totalPages > 1 && !loading" class="mt-3">
      <ul class="pagination pagination-sm justify-content-center">
        <li class="page-item" :class="{ disabled: currentPage === 1 }">
          <button class="page-link" @click.prevent="fetchGrados(currentPage - 1)">
            &laquo;
          </button>
        </li>
        <li class="page-item disabled">
            <span class="page-link">{{ currentPage }} / {{ totalPages }}</span>
        </li>
        <li class="page-item" :class="{ disabled: currentPage === totalPages }">
          <button class="page-link" @click.prevent="fetchGrados(currentPage + 1)">
            &raquo;
          </button>
        </li>
      </ul>
    </nav>

  </div>
</template>