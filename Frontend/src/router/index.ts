import { useUserStore } from '../stores/userStore'
import { createRouter, createWebHistory } from 'vue-router'
import LoginView from '../views/LoginView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
    routes: [
      {
        path: '/',
        name: 'login',
        component: LoginView
      },
      {
        path: '/home',
        name: 'home',
        component: () => import('../views/HomeView.vue')
      },
      {
        path: '/users',
        name: 'users',
        component: () => import('../views/UsersView.vue')
      },
      {
        path: '/tutores/:id/alumnos',
        name: 'alumnosTutor',
        component: () => import('../views/tutor/AlumnosTutorView.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: '/instructores/:id/alumnos',
        name: 'alumnosInstructor',
        component: () => import('../views/instructor/AlumnosInstructorView.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: '/tutor/alumno/:id/seguimiento',
        name: 'seguimientoAlumno',
        component: () => import('../views/tutor/SeguimientoView.vue')
      },
      {
        path: '/tutor/seguimiento/:estanciaId',
        name: 'seguimiento',
        component: () => import('../views/tutor/SeguimientoView.vue'),
        props: true
      },
      {
        path: '/empresa',
        name: 'empresa',
        component: () => import('../views/admin/EmpresaView.vue')
      },
      {
        path: '/alumno/:id/estancia',
        name: 'estanciaAlumno',
        component: () => import('../views/alumno/EstanciaAlumnoView.vue')
      },
      {
        path: '/cuadernos-alumno',
        name: 'alumno-cuadernos',
        component: () => import('../views/cuadernos/AlumnoCuadernosView.vue')
      },
      {
        path: '/cuadernos-tutor',
        name: 'tutor-cuadernos',
        component: () => import('../views/cuadernos/TutorCuadernosView.vue')
      },
      {
        path: '/alumno/mis-notas',
        name: 'alumno-notas',
        component: () => import('../views/alumno/AlumnoNotasView.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: '/grados',
        name: 'grados',
        component: () => import('../views/GradosView.vue')
      },
      {
        path: '/competenciasXra',
        name: 'compra',
        component: () => import('../views/CompRaView.vue')
      },
      {
        path: '/mi-grado',
        name: 'miGrado',
        component: () => import('../views/tutor/MiGradoView.vue')
      },
      {
        path: '/cambiar-contrasena',
        name: 'cambiar-contrasena',
        component: () => import('../views/CambiarContrasenaView.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: '/tutores/:id/alumnos-clases',
        name: 'alumnosClases',
        component: () => import('../views/tutor/AlumnosClasesView.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: '/:pathMatch(.*)*',
        redirect: '/home',
      }
    ]
});

router.beforeEach(async (to, _, next) => {
  const userStore = useUserStore()

  const userAuth = await userStore.getUser()
  const isAdmin = userStore.user?.tipo == 'admin';
  if (!userAuth && to.path !== '/') {
    return next('/')
  }

  if (userAuth && to.path === '/' || !isAdmin && to.path == '/users') {
    return next('/home')
  }

    console.log()
  next()
})



export default router
