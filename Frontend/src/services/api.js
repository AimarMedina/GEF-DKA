import axios from 'axios';

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL,
  withCredentials: true,
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json'
  }
});

api.interceptors.request.use(config => {
  const token = localStorage.getItem('token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
}, error => {
  return Promise.reject(error);
});

// (Opcional) INTERCEPTOR DE RESPUESTAS
// Si el servidor nos devuelve un 401 (Token inválido/expirado), cerramos sesión automáticamente
api.interceptors.response.use(response => response, error => {
  if (error.response && error.response.status === 401) {
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    window.location.href = '/'; 
    // Opcional: Redirigir al login usando window.location o router si lo importas
    // window.location.href = '/'; 
  }
  return Promise.reject(error);
});

export default api;