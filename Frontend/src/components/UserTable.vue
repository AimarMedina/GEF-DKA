<script setup>
import { ref, watch } from "vue";
import api from '@/services/api.js'
import ConfirmarEliminar from './ConfirmarEliminar.vue'
import FormularioUsuario from './FormularioUsuario.vue'
import { useUsersStore } from "@/stores/users.store.js";
import { storeToRefs } from "pinia";

const props = defineProps({
  filters: Object
});

const usersStore = useUsersStore();
const { users, currentPage, totalPages } = storeToRefs(usersStore);

// Variables de modales
const mostrarConfirmarModal = ref(false)
const currentUser = ref(null)
const mostrarModalUsuario = ref(false)
const usuarioEditar = ref(null)
const tipoModal = ref('')
const idGradoModal = ref(false)

// -----------------------
// WATCHERS
// -----------------------
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
);

// -----------------------
// FUNCIONES MODALES
// -----------------------
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

// -----------------------
// GUARDAR USUARIO
// -----------------------
async function guardarUsuario(data) {
  try {
    if(data.id){
      await api.put(`/api/users/${data.id}`, data)
      alert('Usuario actualizado correctamente')
    } else {
      await api.post(`/api/users`, data)
      alert('Usuario creado correctamente')
    }

    // Limpiar cache de la página actual
    const cacheKey = usersStore.getCacheKey(currentPage.value, props.filters)
    sessionStorage.removeItem(cacheKey)

    // Refrescar usuarios
    await usersStore.fetchUsers(currentPage.value, props.filters)

    cerrarModalUsuario()
  } catch (e) {
    console.error(e)
    alert('Error al guardar usuario')
  }
}

async function handleConfirmDelete(confirm) {
  if (!confirm || !currentUser.value) return

  try {
    await api.delete(`/api/users/${currentUser.value.id}`)

    const cacheKey = usersStore.getCacheKey(currentPage.value, props.filters)
    sessionStorage.removeItem(cacheKey)

    await usersStore.fetchUsers(currentPage.value, props.filters)

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
