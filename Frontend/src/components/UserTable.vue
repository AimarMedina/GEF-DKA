<script setup>
import { ref, watch, onMounted } from "vue";
import axios from "axios";

const props = defineProps({
  filters: Object
});

const users = ref([]);
const currentPage = ref(1);
const totalPages = ref(1);
const perPage = ref(5);

async function fetchUsers(page = 1) {
  currentPage.value = page;

  try {
    const response = await axios.get("http://127.0.0.1:8000/api/users", {
      params: {
        page,
        per_page: perPage.value,
        tipo: props.filters.tipo,
        grado: props.filters.grado
      }
    });

    users.value = response.data.data.data || [];
    totalPages.value = response.data.data.last_page;
  } catch (error) {
    console.error(error);
  }
}

watch(
  () => props.filters,
  () => fetchUsers(1),
  { immediate: true }
);
</script>
