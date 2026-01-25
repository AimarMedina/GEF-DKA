<template>
  <div class="buscador-select-wrapper" ref="wrapperRef">
    <div class="input-group">
      <input
        type="text"
        class="form-control"
        :placeholder="placeholder"
        v-model="busqueda"
        @focus="abrirMenu"
        @input="filtrar"
      />
      <button class="btn btn-outline-secondary" type="button" @click="toggleMenu">
        <i class="bi bi-chevron-down"></i>
      </button>
    </div>

    <ul v-if="abierto" class="dropdown-menu show w-100" style="max-height: 200px; overflow-y: auto;">
      <li v-if="opcionesFiltradas.length === 0">
        <span class="dropdown-item disabled">No hay coincidencias</span>
      </li>
      
      <li v-for="opcion in opcionesFiltradas" :key="opcion[valueKey]">
        <button 
          class="dropdown-item" 
          :class="{ active: modelValue === opcion[valueKey] }"
          @click="seleccionarOpcion(opcion)"
          type="button"
        >
          {{ opcion[labelKey] }}
        </button>
      </li>
    </ul>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';

const props = defineProps({
  modelValue: [String, Number], // El ID seleccionado
  options: { type: Array, default: () => [] }, // Array de objetos (grados)
  placeholder: { type: String, default: 'Selecciona una opción' },
  labelKey: { type: String, default: 'nombre' }, // Nombre de la propiedad a mostrar
  valueKey: { type: String, default: 'id' }      // Nombre de la propiedad del ID
});

const emit = defineEmits(['update:modelValue', 'change']);

const abierto = ref(false);
const busqueda = ref('');
const wrapperRef = ref(null);

// Filtrar las opciones basado en lo que escribe el usuario
const opcionesFiltradas = computed(() => {
  if (!busqueda.value) return props.options;
  
  // Si el texto en el input coincide exactamente con el seleccionado, mostramos todo
  // (Esto evita que al seleccionar se filtre la lista y solo salga 1 opción)
  const seleccionado = props.options.find(o => o[props.valueKey] === props.modelValue);
  if (seleccionado && seleccionado[props.labelKey] === busqueda.value) {
    return props.options;
  }

  return props.options.filter(opcion => 
    opcion[props.labelKey].toLowerCase().includes(busqueda.value.toLowerCase())
  );
});

// Sincronizar el texto del input cuando cambia el modelo externo (v-model)
watch(() => props.modelValue, (newVal) => {
  const seleccionado = props.options.find(o => o[props.valueKey] === newVal);
  if (seleccionado) {
    busqueda.value = seleccionado[props.labelKey];
  } else {
    busqueda.value = '';
  }
}, { immediate: true });

function abrirMenu() {
  abierto.value = true;
  // Si el input está vacío o tiene el nombre completo, limpiamos para buscar fácil? 
  // Opcional: busqueda.value = ''; 
}

function toggleMenu() {
  abierto.value = !abierto.value;
}

function filtrar() {
  if (!abierto.value) abierto.value = true;
}

function seleccionarOpcion(opcion) {
  busqueda.value = opcion[props.labelKey];
  emit('update:modelValue', opcion[props.valueKey]);
  emit('change', opcion[props.valueKey]); // Emitimos el evento change para cargar la matriz
  abierto.value = false;
}

// Lógica para cerrar el menú si haces click fuera
function handleClickOutside(event) {
  if (wrapperRef.value && !wrapperRef.value.contains(event.target)) {
    abierto.value = false;
    // Si cerramos y lo que hay escrito no es una opción válida, reseteamos al valor real
    const seleccionado = props.options.find(o => o[props.valueKey] === props.modelValue);
    busqueda.value = seleccionado ? seleccionado[props.labelKey] : '';
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>

<style scoped>
.buscador-select-wrapper {
  position: relative;
}

.dropdown-menu {
  position: absolute;
  z-index: 1050; 
}
</style>