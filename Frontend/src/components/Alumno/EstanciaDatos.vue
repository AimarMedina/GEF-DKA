<script setup>
const props = defineProps({
  estancia: Object
});
</script>

<template>
  <div class="col-12 col-lg-7">
    <h5 class="mt-4 fw-bold">Mi Empresa</h5>

    <table class="table table-striped mt-3">
      <thead>
        <tr>
          <th>Empresa</th>
          <th>Instructor</th>
          <th>Tutor</th>
          <th>Fecha inicio</th>
          <th>Fecha fin</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>{{ estancia.empresa?.Nombre }}</td>
          <td>
            {{ estancia.alumno?.instructor?.user?.nombre }}
            {{ estancia.alumno?.instructor?.user?.apellidos }}
          </td>
          <td>
            {{ estancia.alumno?.tutor?.user?.nombre }}
            {{ estancia.alumno?.tutor?.user?.apellidos }}
          </td>
          <td>{{ estancia.Fecha_inicio }}</td>
          <td>{{ estancia.Fecha_fin ?? '—' }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
