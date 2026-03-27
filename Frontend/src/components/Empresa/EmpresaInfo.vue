<script setup>
defineProps({
    empresa: Object
})
</script>

<template>
    <div class="card shadow-sm mt-3">
        <div class="card-header bg-indigo text-white">
            Información de {{ empresa.Nombre }}
        </div>
        <div class="card-body">
            <p><strong>CIF:</strong> {{ empresa.CIF }}</p>
            <p><strong>Dirección:</strong> {{ empresa.Direccion }}</p>
            <p><strong>Email:</strong> {{ empresa.Email }}</p>
            <p><strong>Telefono:</strong> {{ empresa.N_Tel }}</p>
        </div>
    </div>
</template>
