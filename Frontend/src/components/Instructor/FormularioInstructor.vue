<script setup>
import { ref, defineProps, defineEmits, watch } from 'vue'

const props = defineProps({
    show: Boolean,
    errorMessage: Object
})

const emit = defineEmits(['close', 'crear'])

const nuevoInstructor = ref({
    nombre: '',
    apellidos: '',
    email: '',
    n_tel: '',
    password: ''
})

watch(() => props.show, (val) => {
    if (!val) {
        nuevoInstructor.value = { nombre: '', apellidos: '', email: '', n_tel: '', password: '' }
    }
})

function crear() {
    emit('crear', { ...nuevoInstructor.value })
}
</script>

<template>
    <div v-if="show" class="modal d-block">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nuevo Instructor</h5>
                    <button type="button" class="btn-close" @click="$emit('close')"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label>Nombre</label>
                        <input v-model="nuevoInstructor.nombre" type="text" class="form-control" />
                    </div>
                    <div class="mb-2">
                        <label>Apellidos</label>
                        <input v-model="nuevoInstructor.apellidos" type="text" class="form-control" />
                    </div>
                    <div class="mb-2">
                        <label>Email</label>
                        <input v-model="nuevoInstructor.email" type="email" class="form-control" />
                    </div>
                    <div class="mb-2">
                        <label>Teléfono</label>
                        <input v-model="nuevoInstructor.n_tel" type="text" class="form-control" />
                    </div>
                    <div class="mb-2">
                        <label>Password</label>
                        <input v-model="nuevoInstructor.password" type="password" class="form-control" />
                    </div>
                    <div v-if="errorMessage" class="alert alert-danger text-start">
                            <span v-for="messages in errorMessage">
                                <span v-for="msg in messages" :key="msg">{{ msg }}</span><br>
                            </span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" @click="$emit('close')">Cancelar</button>
                    <button class="btn btn-primary" @click="crear">Crear</button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.modal {
    background-color: rgba(0, 0, 0, 0.5);
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1050;
}
.modal-dialog {
    max-width: 500px;
}
</style>
