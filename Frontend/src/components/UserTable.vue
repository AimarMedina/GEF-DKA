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
const tipoModal = ref('')

// Vigilar cambios en filtros y recargar usuarios
watch(
  () => props.filters,
  (newFilters) => {
    usersStore.fetchUsers(1, newFilters)
  },
  { deep: true, immediate: true }
)

// Abrir modal para editar usuario
function abrirEditar(user) {
  usersStore.currentUser = { ...user }  // asignar currentUser en store
  tipoModal.value = user.tipo
  mostrarModalUsuario.value = true
}

// Abrir modal de confirmación para eliminar
function abrirEliminar(user) {
  usersStore.currentUser = { ...user }
  mostrarConfirmarModal.value = true
}

function cerrarModalUsuario() {
  mostrarModalUsuario.value = false
  usersStore.setCurrentUser(null) // limpiar currentUser
}

function cambiarPagina(page) {
  if (page < 1 || page > totalPages.value) return
  usersStore.fetchUsers(page, props.filters)
}

async function handleConfirmDelete(confirm) {
  if (!confirm || !currentUser.value) return
  await usersStore.handleConfirmDelete(confirm, props.filters)
  mostrarConfirmarModal.value = false
}
</script>

<template>
  <div>
    <!-- TABLA -->
    <table class="table table-striped align-middle">
      <thead>
        <tr>
          <th>Nombre</th>
          <th>Email</th>
          <th>Tipo</th>
          <th v-if="props.filters?.tipo === 'instructor'">Empresa</th>
          <th>Acciones</th>
        </tr>
      </thead>

      <tbody v-if="users.length">
        <tr v-for="user in users" :key="user.id">
          <td>{{ user.nombre }} {{ user.apellidos }}</td>
          <td>{{ user.email }}</td>
          <td><span class="badge bg-secondary">{{ user.tipo }}</span></td>
          <td v-if="props.filters?.tipo === 'instructor'">
            {{ user.instructor?.empresa?.Nombre ?? 'Sin empresa' }}
          </td>
          <td class="d-flex gap-1">
            <button class="btn btn-outline-indigo btn-sm" @click="abrirEditar(user)">
              Modificar
            </button>
            <button class="btn btn-danger btn-sm" @click="abrirEliminar(user)">
              Eliminar
            </button>
          </td>
        </tr>
      </tbody>
      <tbody v-else>
        <tr>
          <td colspan="5" class="text-center text-secondary py-3">
            No hay usuarios.
          </td>
        </tr>
      </tbody>
    </table>

    <!-- PAGINACIÓN -->
    <nav v-if="totalPages > 1">
      <ul class="pagination">
        <li class="page-item" :class="{ disabled: currentPage === 1 }">
          <button class="page-link" @click="cambiarPagina(currentPage - 1)">Anterior</button>
        </li>
        <li
          class="page-item"
          v-for="page in totalPages"
          :key="page"
          :class="{ active: currentPage === page }"
        >
          <button class="page-link" @click="cambiarPagina(page)">{{ page }}</button>
        </li>
        <li class="page-item" :class="{ disabled: currentPage === totalPages }">
          <button class="page-link" @click="cambiarPagina(currentPage + 1)">Siguiente</button>
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
      @close="cerrarModalUsuario"
      @crear="usersStore.guardarUsuario($event, props.filters)"
    />
  </div>
</template>
