<template>
    <div class="col-md-3 mt-3">
        <button class="btn btn-secondary mb-2" @click="mostrarModal = true">
            <i class="bi bi-building-fill-add"></i> Añadir
        </button>
        <Buscador :tipo="'Buscar Empresa'" @search="onSearch"></Buscador>
        <EmpresaForm :show="mostrarModal" :errorMessage="errores" @close="mostrarModal = false" @crear="crearEmpresa" />

        <ul v-if="empresas.length" class="list-group">
            <button type="button" class="list-group-item list-group-item-action text-white bg-indigo"
                @click="emit('seleccionarEmpresa', null)">
                <h3>Empresas</h3>
            </button>

            <button v-for="empresa in empresas" :key="empresa.id" type="button"
                class="list-group-item list-group-item-action" @click="emit('seleccionarEmpresa', empresa)">
                {{ empresa.Nombre }}
            </button>
        </ul>

        <p v-if="cargando">
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
import { ref, onMounted, watch } from 'vue'
import EmpresaForm from './EmpresaForm.vue'
import Buscador from "../Buscador.vue";

const empresas = ref([])
const mostrarModal = ref(false)
const errores = ref(null)

const emit = defineEmits(['seleccionarEmpresa'])

async function cargarEmpresas() {
            cargando.value = true

    const response = await axios.get(
        'http://localhost:8000/api/empresas',
        {
            params: {
                q: q.value
            }
        }
    )

    empresas.value = response.data
    cargando.value = false
}



const crearEmpresa = async (empresa) => {
    try {
        const response = await axios.post('http://localhost:8000/api/empresa/create', {
            ...empresa
        })
        console.log(response.data);

        mostrarModal.value = false
        errores.value = null
        q.value = response.data.Nombre
    } catch (e) {
        if (e.response?.status === 422) {
            errores.value = e.response.data.errors
        }
    }
}
const cargando = ref(false)
const q = ref("");
let searchTimeout = null;
function onSearch(texto) {
    q.value = (texto || "").trim();
}
watch(q, () => {
    if (searchTimeout) {
        cargando.value = false
        clearTimeout(searchTimeout)
    }
    searchTimeout = setTimeout(() => {
        if (q.value.length < 2) {
            cargando.value =false
            empresas.value = []
            return
        }
        cargarEmpresas()
    }, 400)
})

</script>
