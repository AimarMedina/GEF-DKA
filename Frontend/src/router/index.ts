import { useUserStore } from '@/stores/userStore'
import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import LoginView from '../views/LoginView.vue'
import UsersView from '../views/UsersView.vue'
import AlumnosInstructorView from '@/views/instructor/AlumnosInstructorView.vue'
import AlumnosTutorView from '@/views/tutor/AlumnosTutorView.vue'
import SeguimientoView from '@/views/tutor/SeguimientoView.vue'
import AsignarEmpresaView from '@/views/tutor/AsignarEmpresaView.vue'
import EmpresaView from '../views/admin/EmpresaView.vue'
import EstanciaAlumnoView from '../views/alumno/EstanciaAlumnoView.vue'

import AlumnoCuadernosView from '@/views/cuadernos/AlumnoCuadernosView.vue';
import TutorCuadernosView from '@/views/cuadernos/TutorCuadernosView.vue';

import NotasAlumnoView from '@/views/alumno/NotasAlumnoView.vue'
import AlumnoNotasView from '@/views/alumno/AlumnoNotasView.vue'
import GradosTable from '@/components/GradosTable.vue'


const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/home',
      name: 'home',
      component: HomeView,
    },
    {
      path: '/',
      name: 'login',
      component: LoginView
    },
    {
      path: '/users',
      name: 'users',
      component: UsersView,
    },
    {
      path: '/tutores/:id/alumnos',
      name: 'alumnosTutor',
      component: AlumnosTutorView 
    },
    {
      path: '/instructores/:id/alumnos',
      name: 'alumnosInstructor',
      component:AlumnosInstructorView,
    },
    {
      path: '/alumnosTutor/seguimiento',
      name: 'seguimientoAlumno',
      component: SeguimientoView
    },
    
    {
      path: '/alumnosTutor/asignarEmpresa',
      name: 'asignarEmpresa',
      component: AsignarEmpresaView
    },
    
    {
      path: '/empresa',
      name: 'empresa',
      component: EmpresaView,
    },
    {
      path: '/alumno/:id/estancia',
      name: 'estanciaAlumno',
      component:EstanciaAlumnoView,
    },
    {
      path: '/cuadernos-alumno',
      name: 'alumno-cuadernos',
      component: AlumnoCuadernosView
    },
    {
      path: '/cuadernos-tutor',
      name: 'tutor-cuadernos',
      component: TutorCuadernosView
    },
    {
      path: "/instructor/alumnos/:idAlumno/notas",
      name: "instructor-alumno-notas",
      component: NotasAlumnoView ,
      meta: { requiresAuth: true }
    },
    {
      path: '/alumno/mis-notas',
      name: "alumno-notas",
      component: AlumnoNotasView ,
      meta: { requiresAuth: true }
    },
    {
      path: "/grados",
      name: "grados",
      component: GradosTable
    }
    


  ],
})

router.beforeEach(async (to, from, next) => {
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
