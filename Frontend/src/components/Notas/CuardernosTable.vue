<script setup>
const props = defineProps({
  cuadernos: Array
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
          <tr v-for="c in cuadernos" :key="c.id">
            <td class="text-center text-md-start">
              Nota final
            </td>
            <td class="text-center text-md-start">
              <span :class="{
                'badge bg-success': c.nota?.Nota >= 5,
                'badge bg-danger text-white': c.nota?.Nota < 5 && c.nota?.Nota != null,
                'badge bg-warning text-dark': !c.nota?.Nota
              }">
                {{ c.nota?.Nota ?? 'Pendiente' }}
              </span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
