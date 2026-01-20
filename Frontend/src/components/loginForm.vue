<template>
  <form class="d-flex flex-column justify-content-center align-items-center vh-100" @submit.prevent="login">
    <img src="../../public/GEF-DKA.png" alt="Imagen de GEF-DKA" class="logo-img">
    <div class="p-4 border rounded shadow">
      <div class="input-group mb-3">
        <span class="input-group-text" id="basic-addon1"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-at"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 12a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" /><path d="M16 12v1.5a2.5 2.5 0 0 0 5 0v-1.5a9 9 0 1 0 -5.5 8.28" /></svg></span>
        <input type="email" class="form-control" id="inputEmail3" v-model="email">
      </div>

      <div class="input-group mb-3">
        <span class="input-group-text" id="basic-addon1"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-key"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M16.555 3.843l3.602 3.602a2.877 2.877 0 0 1 0 4.069l-2.643 2.643a2.877 2.877 0 0 1 -4.069 0l-.301 -.301l-6.558 6.558a2 2 0 0 1 -1.239 .578l-.175 .008h-1.172a1 1 0 0 1 -.993 -.883l-.007 -.117v-1.172a2 2 0 0 1 .467 -1.284l.119 -.13l.414 -.414h2v-2h2v-2l2.144 -2.144l-.301 -.301a2.877 2.877 0 0 1 0 -4.069l2.643 -2.643a2.877 2.877 0 0 1 4.069 0" /><path d="M15 9h.01" /></svg></span>
        <input type="password" class="form-control" id="inputPassword3" v-model="password">
      </div>
        <div v-if="errorMessage" class="alert alert-danger mb-3" role="alert">
          {{ errorMessage }}
        </div>
      <input type="submit" class="btn btn-primary w-100" />
    </div>
  </form>
</template>


<script setup>
import { ref } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';

const email = ref('');
const password = ref('');
const errorMessage = ref('');
const router = useRouter();

const login = async () => {
  try {
    const response = await axios.post('http://127.0.0.1:8000/api/login', {
      email: email.value,
      password: password.value
    });

    if (response.data.status === 'success') {
      errorMessage.value = '';
      let token = response.data.token
      localStorage.setItem('token', token);
      localStorage.setItem('user',response.data.user.id)

      axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
      router.push('/home');
    } else {
      errorMessage.value = response.data.message;
    }
  } catch (error) {
    console.error(error);
    errorMessage.value = 'Ocurrió un error al iniciar sesión';
  }
};

</script>

<style scoped>
  .logo-img {

  height: 5rem; 
  width: auto;  
  margin-bottom: 1rem; 
  mix-blend-mode: multiply;
}
</style>
