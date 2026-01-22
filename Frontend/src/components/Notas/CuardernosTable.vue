<script setup>
const props = defineProps({
  notaCuaderno: Array
})
</script>

<template>
  <div class="mb-5">
    <h5>Cuadernos</h5>
    <div class="table-responsive">
      <table class="table table-striped table-bordered">
        <thead class="table-indigo text-white text-center text-md-start">
          <tr>
            <th>Cuaderno</th>
            <th>Nota</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td class="text-center text-md-start">
              Nota final
            </td>
            <td class="text-center text-md-start">
              <span :class="{
                'badge bg-success': notaCuaderno?.Nota >= 5,
                'badge bg-danger text-white': notaCuaderno?.Nota < 5 && notaCuaderno?.Nota != null,
                'badge bg-warning text-dark': !notaCuaderno?.Nota
              }">
                {{ notaCuaderno?.Nota ?? 'Pendiente' }}
              </span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
