<template>
    <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top shadow-sm">
        <div class="container-fluid">

            <!-- Logo -->
            <a class="navbar-brand d-flex align-items-center" href="#">
                <img src="../../public/LOGO-EGIBIDE.png" alt="Logo Egibide" class="logo">
            </a>

            <!-- Bienvenida (desktop, derecha) -->
            <span class="d-none d-lg-block ms-lg-auto me-3 fw-semibold">
                ¡Bienvenid@, {{ usuario }}!
            </span>

            <!-- Hamburguesa -->
            <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar"
                aria-controls="offcanvasNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Offcanvas -->
            <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasNavbar">
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title">
                        ¡Bienvenido, {{ usuario }}!
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>

                <div class="offcanvas-body">
                    <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link" href="#">Servicios</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">Sobre nosotros</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">Contacto</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-danger" role="button" @click="logout">
                                Cerrar sesión
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

        </div>
    </nav>
</template>


<style scoped>
.logo {
    max-height: 45px;
    width: auto;
}
</style>


<script setup>
    import { useUserStore } from '@/stores/userStore';
    import axios from 'axios';
    import { ref } from 'vue';
    import { useRouter } from 'vue-router';

    const router = useRouter();
    const userStore = useUserStore()

    let message = ref()
    let usuario = userStore.user.nombre + " " + userStore.user.apellidos

    async function logout() {
        const token = localStorage.getItem('token')
        try {
            const response = await axios.post('http://localhost:8000/api/logout', {}, {
                headers: {
                    Authorization: `Bearer ${token}`
                }
            });
            if (response.data.status === 'success') {
                localStorage.removeItem('token');
                delete axios.defaults.headers.common['Authorization'];
                userStore.user.value = null
                router.push('/');
            }
        } catch (error) {
            console.error(error);
            message.value = 'Error cerrando sesión';
        }
    }
</script>

<style lang="scss" scoped></style>
