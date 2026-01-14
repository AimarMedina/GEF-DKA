<template>
    <div class="col-md-3 mt-3">
        <ul v-if="empresas.length" class="list-group">
            <button type="button" class="list-group-item list-group-item-action active" aria-current="true"
                @click="emit('seleccionarEmpresa', null)">
                <h3>Empresas</h3>
            </button>
            <button v-for="empresa in empresas" :key="empresa.id" type="button"
                class="list-group-item list-group-item-action" @click="emit('seleccionarEmpresa', empresa)">
                {{ empresa.Nombre }}
            </button>
        </ul>

        <p v-else>
            Cargando empresas
        <ul class="carga">
            <li></li>
            <li></li>
            <li></li>
            <li></li>
            <li></li>
        </ul>
        </p>
    </div>
</template>

<script setup>
import axios from 'axios'
import { ref, onMounted } from 'vue'

const empresas = ref([])
const emit = defineEmits(['seleccionarEmpresa'])
const cargarEmpresas = async () => {

    try {
        const response = await axios.get('http://localhost:8000/api/empresas')
        empresas.value = await response.data
    } catch (error) {
        console.error('Error al cargar empresas:', error)
    }
}


onMounted(() => {
    cargarEmpresas()
})
</script>
