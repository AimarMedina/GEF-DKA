<script setup>
import { ref } from 'vue';
import api from '@/services/api.js';

const props = defineProps({
  transversales: Array,
  alumnoId: Number,
});

const editando = ref(false);

const actualizarNota = async (transversal) => {
  if (transversal.Nota === null || transversal.Nota < 0 || transversal.Nota > 4) {
    alert('La nota debe estar entre 0 y 4');
    return;
  }

  try {
    await api.put(`/api/alumnos/${props.alumnoId}/transversales/${transversal.id}/nota`, {
      nota: transversal.Nota,
    });
  } catch (e) {
    console.error(e);
    alert('Error al guardar la nota');
  }
};
</script>

<template>
  <div class="mb-5">
    <h5>Transversales</h5>
    <div class="table-responsive">
      <table class="table table-striped table-bordered">
        <thead class="table-indigo text-white text-center text-md-start">
          <tr>
            <th>Transversal</th>
            <th>
              <div class="d-flex justify-content-between align-items-center">
                Nota
                <button class="btn btn-warning" @click="editando = !editando">
                  <i class="bi bi-pencil" v-if="!editando"></i>
                  <i class="bi bi-x-lg" v-if="editando"></i>
                </button>
              </div>
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="n in transversales" :key="n.id">
            <td class="text-center text-md-start">
              {{ n.transversal?.descripcion ?? 'Sin descripción' }}
            </td>
            <td class="text-center text-md-start">
              <span
                :class="{
                  'badge bg-success': n.Nota >= 3,
                  'badge bg-danger text-white': n.Nota < 3,
                  'badge bg-warning text-dark': n.Nota == null,
                }"
                class="mb-2"
                v-if="!editando"
                >{{ n.Nota ?? 'Sin nota' }}</span
              >
              <input
                type="number"
                min="0"
                max="10"
                step="0.1"
                class="form-control form-control-sm fw-bold"
                v-model.number="n.Nota"
                v-if="editando"
                @change="actualizarNota(n)"
                placeholder="—"
              />
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
