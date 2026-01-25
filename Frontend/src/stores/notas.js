import { defineStore } from "pinia";
import { ref } from "vue";

export const useNotasStore = defineStore("notas", () => {
  const alumnos = ref([]);       // array de alumnos
  const asignaturas = ref([]);
  const gradoNombre = ref("");

  const setDatosGrado = ({ alumnosData, asignaturasData, grado }) => {
    alumnos.value = alumnosData;          // ¡Esto debe ser el array de alumnos!
    asignaturas.value = asignaturasData;
    gradoNombre.value = grado.nombre;
  };

  const getNotasAlumno = (idAlumno) => {
    return alumnos.value.find(a => a.id === idAlumno)?.notas_calculadas ?? null;
  };

  const setNotasAlumno = (idAlumno, nuevasNotas) => {
    const alumno = alumnos.value.find(a => a.id === idAlumno);
    if (alumno) alumno.notas_calculadas = nuevasNotas;
  };

  return {
    alumnos,
    asignaturas,
    gradoNombre,
    setDatosGrado,
    getNotasAlumno,
    setNotasAlumno,
  };
});
