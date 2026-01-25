<template>
  <div>
    <table class="table table-striped align-middle">
      <thead>
        <tr>
          <th>ID</th>
          <th>Nombre</th>
          <th>Email</th>
          <th>Tipo</th>
          <th v-if="filters?.tipo === 'instructor'">Empresa</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="user in users" :key="user.id">
          <td>{{ user.id }}</td>
          <td>{{ user.nombre }} {{ user.apellidos }}</td> <td>{{ user.email }}</td>
          <td>
            <span class="badge bg-secondary">{{ user.tipo }}</span>
          </td>
          
          <td v-if="filters?.tipo === 'instructor'">
             {{ user.instructor?.empresa?.Nombre || 'Sin empresa' }}
          </td>

          <td class="d-flex gap-1">
            <button class="btn btn-outline-indigo btn-sm" @click="abrirEditar(user)">Modificar</button>
            <button class="btn btn-danger btn-sm" @click="mostrarConfirmarModal = true; currentUser = user">Eliminar</button>
          </td>
        </tr>
      </tbody>
    </table>

    <nav v-if="totalPages > 1">
      <ul class="pagination">
        <li class="page-item" :class="{ disabled: currentPage === 1 }">
          <button class="page-link" @click="fetchUsers(currentPage - 1)">Anterior</button>
        </li>
        <li class="page-item" v-for="page in totalPages" :key="page" :class="{ active: currentPage === page }">
          <button class="page-link" @click="fetchUsers(page)">{{ page }}</button>
        </li>
        <li class="page-item" :class="{ disabled: currentPage === totalPages }">
          <button class="page-link" @click="fetchUsers(currentPage + 1)">Siguiente</button>
        </li>
      </ul>
    </nav>
    
    <ConfirmarEliminar 
      :show="mostrarConfirmarModal" 
      :mensaje="'¿Estás seguro...'"
      @confirm="handleConfirmDelete" 
      @close="mostrarConfirmarModal = false" 
    />
    <FormularioUsuario
      :show="mostrarModalUsuario"
      :tipo="tipoModal"
      :id_grado="idGradoModal"
      :usuario="usuarioEditar"
      @close="cerrarModalUsuario"
      @crear="guardarUsuario"
    />
  </div>
</template>

<script setup>
import { ref, watch } from "vue";
import api from '@/services/api.js'
import ConfirmarEliminar from './ConfirmarEliminar.vue'
import FormularioUsuario from './FormularioUsuario.vue'

const props = defineProps({
  filters: Object
});

const users = ref([]);
const currentPage = ref(1);
const totalPages = ref(1);
const perPage = ref(5);
// ... variables de modales ...
const mostrarConfirmarModal = ref(false)
const currentUser = ref(null)
const mostrarModalUsuario = ref(false)
const usuarioEditar = ref(null)
const tipoModal = ref('')
const idGradoModal = ref(false)


async function fetchUsers(page = 1) {
  currentPage.value = page;

  try {
    const response = await api.get("/api/users", {
      params: {
        page,
        per_page: perPage.value,
        tipo: props.filters?.tipo,
        id_grado: props.filters?.id_grado,
        search: props.filters?.search // <--- ENVIAMOS EL TEXTO A LA API
      }
    });

    users.value = response.data.data.data || [];
    totalPages.value = response.data.data.last_page;
  } catch (error) {
    console.error(error);
  }
}

watch(
  () => props.filters,
  () => {
    // Si es alumno y no hay grado, limpiamos (misma lógica que tenías)
    if(props.filters?.tipo === 'alumno' && (!props.filters?.id_grado || props.filters?.id_grado === "NONE")){
       // Nota: he afinado esta condición, en tu código original solo comprobabas id_grado === NONE
       // pero aquí dejo tu lógica original de limpieza:
       if (props.filters?.id_grado === "NONE") {
          users.value = []
          totalPages.value = 0
          return
       }
    }
    fetchUsers(1)
  },
  { deep: true, immediate: true } // deep: true para detectar cambios dentro del objeto filters
);

// ... Resto de funciones (abrirEditar, guardarUsuario, handleConfirmDelete) igual ...
function abrirEditar(user) {
  usuarioEditar.value = user
  tipoModal.value = user.tipo
  idGradoModal.value = user.tipo === 'alumno' ? user.id_grado : false
  mostrarModalUsuario.value = true
}

function cerrarModalUsuario() {
  mostrarModalUsuario.value = false
  usuarioEditar.value = null
}

async function guardarUsuario(data) {
    // ... tu lógica existente
     try {
    if(data.id){
      await api.put(`/api/users/${data.id}`, data)
      alert('Usuario actualizado correctamente')
    } else {
      await api.post(`/api/users`, data)
      alert('Usuario creado correctamente')
    }
    fetchUsers(currentPage.value)
    cerrarModalUsuario()
  } catch (e) {
    console.error(e)
    alert('Error al guardar usuario')
  }
}

async function handleConfirmDelete(confirm) {
    // ... tu lógica existente
      if (!confirm || !currentUser.value) return

  try {
    await api.delete(`/api/users/${currentUser.value.id}`)
    fetchUsers(currentPage.value)
    alert('Usuario eliminado correctamente')
  } catch (e) {
    console.error(e)
    alert('Error al eliminar usuario')
  } finally {
    mostrarConfirmarModal.value = false
    currentUser.value = null
  }
}
</script>