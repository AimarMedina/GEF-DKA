<script setup>
import { defineProps, reactive } from "vue";

const props = defineProps({
  tipo: { type: String, required: true },
  competencias: { type: Array, default: () => [] },
});

const notas = reactive({});

import { useRoute, useRouter } from "vue-router";
import { onMounted, ref } from "vue";
import axios from "axios";
const route = useRoute();
const router = useRouter();
const idAlumno = route.params.idAlumno;
const loading = ref(true);
const autorizado = ref(false);
onMounted(async () => {
  try {
    const token = localStorage.getItem("token");
    console.log("TOKEN: ", token);
    await axios.get(`http://127.0.0.1:8000/api/alumnos/${idAlumno}/notas`, {
      headers: {
        Authorization: `Bearer ${token}`,
      },
    });
    autorizado.value = true;
  } catch (err) {
    if (err.response?.status === 403 || err.response?.status === 401) {
      // No autorizado: fuera
      router.push("/home");
    } else {
      console.error(err);
    }
  } finally {
    loading.value = false;
  }
});
</script>

<template>
  <div v-if="loading">
    Cargando
    <ul class="carga">
      <li></li>
      <li></li>
      <li></li>
      <li></li>
      <li></li>
    </ul>
  </div>

  <div v-else-if="autorizado">
    <table class="table table-bordered">
      <thead>
        <tr>
          <th style="width: 50px">#</th>
          <th>{{ tipo }}</th>
          <th style="width: 120px">Nota</th>
        </tr>
      </thead>

      <tbody>
        <tr v-for="(c, index) in competencias" :key="c.id">
          <td class="text-center">{{ index + 1 }}</td>

          <td>
            {{ c.descripcion }}
          </td>

          <td>
            <select
              class="form-select form-select-sm"
              v-model="notas[c.id]"
              required
            >
              <option v-for="n in 4" :key="n" :value="n">
                {{ n }}
              </option>
            </select>
          </td>
        </tr>

        <tr v-if="competencias.length === 0">
          <td colspan="3" class="text-center">No hay competencias</td>
        </tr>
      </tbody>
    </table>
  </div>
  <div v-else>
    <h1> DONDE VAS PRINGAO</h1>
  </div>
</template>
