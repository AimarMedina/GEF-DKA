<script setup>
import { ref, onMounted, defineEmits } from "vue";
import axios from "axios";
import Buscador from "../Buscador.vue";
import CrearGradoModal from "./CrearGradoModal.vue";
import TransversalModal from "@/components/Grado/TranversalModal.vue";
import ConfirmarEliminar from "../ConfirmarEliminar.vue";
import api from '@/services/api.js'

// AÑADIMOS LOS NUEVOS EVENTOS AL EMIT
const emit = defineEmits(['verAsignaturas', 'verCompetencias']);

// Estado
const grados = ref([]);
const currentPage = ref(1);
const totalPages = ref(1);
const loading = ref(false);
const mostrarModal = ref(false);
const mostrarTransversalModal = ref(false);
const eliminarModal = ref(false);
const gradoEliminar = ref(null);

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
    const response = await api.get(`/api/grados`, {
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

const crearGrado = async (nuevoGrado) => {
    console.log("Grado creado:", nuevoGrado);
    mostrarModal.value = false;
    fetchGrados(currentPage.value); // Recargar la página actual
}

function abrirEliminarModal(grado) {
  gradoEliminar.value = grado;
  eliminarModal.value = true;
}

async function confirmarEliminar(confirmado) {
  if (confirmado && gradoEliminar.value) {
    await eliminarGrado(gradoEliminar.value.id);
  }
  eliminarModal.value = false;
  gradoEliminar.value = null;
}

async function eliminarGrado(id) {
  try {
    const token = localStorage.getItem('token');
    await api.delete(`/api/grados/${id}`, {
      headers: { Authorization: `Bearer ${token}` }
    });
    
    console.log('Grado eliminado correctamente');
    
    // Limpiar la selección si el grado eliminado era el seleccionado
    emit('verAsignaturas', null);
    emit('verCompetencias', null);
    
    fetchGrados(currentPage.value); // Recargar la página actual
  } catch (err) {
    console.error('Error al eliminar grado:', err);
    alert(err.response?.data?.message || 'Error al eliminar el grado');
  }
}

onMounted(() => {
  fetchGrados(1);
});
</script>

<template>
  <div class="col-12 col-lg-4 mt-3">

    <Buscador tipo="Buscar grado..." @search="onSearch" />

    <div class="d-flex gap-2 mb-2">
      <button class="btn btn-secondary flex-fill" @click="mostrarModal = true">
        <i class="bi bi-building-fill-add"></i> Añadir Grado
      </button>
      <button class="btn btn-secondary flex-fill" @click="mostrarTransversalModal = true">
        <i class="bi "></i> Transversales
      </button>
    </div>

    <div v-if="!loading" class="list-group shadow-sm">
        <div class="list-group-item text-white bg-indigo cursor-pointer"
            @click="emit('seleccionarGrado', null)">
            <h3 class="mb-0 fs-4">Grados</h3>
        </div>

        <div v-for="grado in grados" :key="grado.id"
            class="list-group-item list-group-item-action flex-column align-items-start p-3"
            style="cursor: pointer;">

            <div class="d-flex w-100 justify-content-between mb-2">
                <h5 class="mb-1 text-break">{{ grado.nombre }}</h5>
                <div class="d-flex gap-2 align-items-start">
                    <small class="badge bg-secondary">{{ grado.curso }}</small>
                    <button 
                        class="btn btn-sm btn-danger p-1" 
                        style="line-height: 1;"
                        @click.stop="abrirEliminarModal(grado)"
                        title="Eliminar grado"
                    >
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>

            <div class="d-flex gap-2 w-100 mt-2">
                <button class="btn btn-sm btn-outline-indigo flex-fill"
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

    <!-- Modales -->
    <CrearGradoModal
      :show="mostrarModal"
      @close="mostrarModal = false"
      @created="crearGrado"
    />

    <TransversalModal
      :show="mostrarTransversalModal"
      @close="mostrarTransversalModal = false"
    />

    <ConfirmarEliminar
      :show="eliminarModal"
      mensaje="¿Seguro que deseas eliminar este grado? Esta acción también eliminará todas las asignaturas, alumnos y datos relacionados."
      @confirm="confirmarEliminar"
      @close="eliminarModal = false"
    />

  </div>
</template>