<script setup>
import { ref, watch, computed } from 'vue'
import { useUsersStore } from '@/stores/users.store'
import { storeToRefs } from 'pinia'
import ConfirmarEliminar from './ConfirmarEliminar.vue'
import FormularioUsuario from './FormularioUsuario.vue'
import { useNotificacion } from '@/composables/useNotificacion'

const props = defineProps({
  filters: Object
})

const usersStore = useUsersStore()
const { users, currentPage, totalPages, currentUser } = storeToRefs(usersStore)
const { info, success, error } = useNotificacion()

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
  usersStore.initCurrentUser({ ...user })  // asignar currentUser en store
  tipoModal.value = user.tipo
  mostrarModalUsuario.value = true
}

// Abrir modal de confirmación para eliminar
function abrirEliminar(user) {
  usersStore.initCurrentUser({ ...user })
  mostrarConfirmarModal.value = true
}

function cerrarModalUsuario() {
  mostrarModalUsuario.value = false
  usersStore.initCurrentUser(null) // limpiar currentUser
}

function cambiarPagina(page) {
  if (page < 1 || page > totalPages.value) return
  usersStore.fetchUsers(page, props.filters)
}

const paginationChunkSize = 5

const chunkStart = computed(() => {
  const cp = currentPage.value || 1
  return Math.floor((cp - 1) / paginationChunkSize) * paginationChunkSize + 1
})

const visiblePages = computed(() => {
  const start = chunkStart.value
  const end = Math.min(start + paginationChunkSize - 1, totalPages.value)
  const pages = []
  for (let p = start; p <= end; p++) pages.push(p)
  return pages
})

const hasPrevEllipsis = computed(() => chunkStart.value > 1)
const hasNextEllipsis = computed(() => chunkStart.value + paginationChunkSize <= totalPages.value)

const prevEllipsisTarget = computed(() => Math.max(1, chunkStart.value - paginationChunkSize))
const nextEllipsisTarget = computed(() => Math.min(totalPages.value, chunkStart.value + paginationChunkSize))

async function handleConfirmDelete(confirm) {
  if (!confirm || !currentUser.value) return
  
  info('Procesando', 'Un momento, por favor...')
  try {
    await usersStore.handleConfirmDelete(confirm, props.filters)
    success('Éxito', 'Usuario eliminado correctamente')
  } catch (err) {
    error('Error', err.message || 'No se pudo eliminar el usuario')
  }
  mostrarConfirmarModal.value = false
}

async function handleGuardarUsuario(userData) {
  const esEdicion = userData.id
  info('Procesando', 'Un momento, por favor...')
  
  try {
    await usersStore.guardarUsuario(userData, props.filters)
    success('Éxito', esEdicion ? 'Usuario actualizado correctamente' : 'Usuario creado correctamente')
    cerrarModalUsuario()
  } catch (err) {
    error('Error', err.message || 'No se pudo guardar el usuario')
  }
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

          <li v-if="hasPrevEllipsis" class="page-item">
            <button class="page-link" @click="cambiarPagina(prevEllipsisTarget)">...</button>
          </li>

          <li
            class="page-item"
            v-for="page in visiblePages"
            :key="page"
            :class="{ active: currentPage === page }"
          >
            <button class="page-link" @click="cambiarPagina(page)">{{ page }}</button>
          </li>

          <li v-if="hasNextEllipsis" class="page-item">
            <button class="page-link" @click="cambiarPagina(nextEllipsisTarget)">...</button>
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
      @crear="handleGuardarUsuario($event)"
    />
  </div>
</template>
