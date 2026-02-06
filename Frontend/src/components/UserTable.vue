
<script setup>
import { ref, watch } from 'vue'
import { useUsersStore } from '@/stores/users.store'
import { storeToRefs } from 'pinia'
import ConfirmarEliminar from './ConfirmarEliminar.vue'
import FormularioUsuario from './FormularioUsuario.vue'

const props = defineProps({
  filters: Object
})

const usersStore = useUsersStore()

const { users, currentPage, totalPages, currentUser } = storeToRefs(usersStore)
const mostrarConfirmarModal = ref(false)
const mostrarModalUsuario = ref(false)
const usuarioEditar = ref(null)
const tipoModal = ref('')
const idGradoModal = ref(false)

// Contruye los filtros para mandarlo como consulta a backend
watch(
  () => props.filters,
  (newFilters) => {
    if (newFilters?.tipo === 'alumno' && newFilters?.id_grado === 'NONE') {
      users.value = []
      totalPages.value = 0
      return
    }

    usersStore.fetchUsers(1, newFilters)
  },
  { deep: true, immediate: true }
)

function abrirEditar(user) {
  usuarioEditar.value = user
  tipoModal.value = user.tipo
  idGradoModal.value = user.tipo === 'alumno' ? user.id_grado : false
  mostrarModalUsuario.value = true
}

function abrirEliminar(user) {
  currentUser.value = user
  mostrarConfirmarModal.value = true
}

function cerrarModalUsuario() {
  mostrarModalUsuario.value = false
  usuarioEditar.value = null
}

function cambiarPagina(page) {
  if (page < 1 || page > totalPages.value) return
  usersStore.fetchUsers(page, props.filters)
}


async function handleConfirmDelete(confirm) {
  if (!confirm || !currentUser.value) return

  await usersStore.handleConfirmDelete(currentUser.value, props.filters)
  mostrarConfirmarModal.value = false
  currentUser.value = null
}
</script>


<template>
  <div>
    <!-- TABLA -->
    <table class="table table-striped align-middle">
      <thead class="d-flexk justify-content-between">
        <tr>
          <th>Nombre</th>
          <th>Email</th>
          <th>Tipo</th>
          <th v-if="filters?.tipo === 'instructor'">Empresa</th>
          <th>Acciones</th>
        </tr>
      </thead>

      <tbody v-if="users.length > 0">
        <tr v-for="user in users" :key="user.id">
          <td>{{ user.nombre }} {{ user.apellidos }}</td>
          <td>{{ user.email }}</td>

          <td>
            <span class="badge bg-secondary">{{ user.tipo }}</span>
          </td>

          <td v-if="filters?.tipo === 'instructor'">
            {{ user.instructor?.empresa?.Nombre ?? 'Sin empresa' }}
          </td>

          <td class="d-flex gap-1">
            <button
              class="btn btn-outline-indigo btn-sm"
              @click="abrirEditar(user)"
            >
              Modificar
            </button>

            <button
              class="btn btn-danger btn-sm"
              @click="abrirEliminar(user)"
            >
              Eliminar
            </button>
          </td>
        </tr>
      </tbody>
    </table>

    <div v-if="users.length === 0" class="text-center text-secondary my-3">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
    </div>

    <!-- PAGINACIÓN -->
    <nav v-if="totalPages > 1">
      <ul class="pagination">
        <li class="page-item" :class="{ disabled: currentPage === 1 }">
          <button
            class="page-link"
            @click="cambiarPagina(currentPage - 1)"
            :disabled="currentPage === 1"
          >
            Anterior
          </button>
        </li>

        <li
          class="page-item"
          v-for="page in totalPages"
          :key="page"
          :class="{ active: currentPage === page }"
        >
          <button
            class="page-link"
            @click="cambiarPagina(page)"
          >
            {{ page }}
          </button>
        </li>

        <li class="page-item" :class="{ disabled: currentPage === totalPages }">
          <button
            class="page-link"
            @click="cambiarPagina(currentPage + 1)"
            :disabled="currentPage === totalPages"
          >
            Siguiente
          </button>
        </li>
      </ul>
    </nav>

    <!-- MODALES -->
    <ConfirmarEliminar
      :show="mostrarConfirmarModal"
      mensaje="¿Estás seguro de eliminar este usuario?"
      @confirm="handleConfirmDelete"
      @close="mostrarConfirmarModal = false"
    />

    <FormularioUsuario
      :show="mostrarModalUsuario"
      :tipo="tipoModal"
      :id_grado="idGradoModal"
      :usuario="usuarioEditar"
      @close="cerrarModalUsuario"
      @crear="usersStore.guardarUsuario($data, props.filters)"
    />
  </div>
</template>

