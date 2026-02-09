import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'

export const useCuadernosStore = defineStore('cuadernos', () => {
  const alumnos = ref([])
  const mensaje = ref('')
  const savingId = ref(null)

  async function fetchNotasCuaderno(tutorId) {
    if (!tutorId) return []
    try {
      const res = await api.get(`/api/tutor/${tutorId}/notas-cuaderno`)
      alumnos.value = res.data.map(alumno => ({
        ...alumno,
        nota_cuaderno: alumno.nota_cuaderno ?? { Nota: null }
      }))
      return alumnos.value
    } catch (err) {
      mensaje.value = 'Error cargando notas'
      throw err
    }
  }

  async function guardarNotaCuaderno(ID_Alumno, Nota) {
    savingId.value = ID_Alumno
    try {
      const resp = await api.post('/api/nota-cuaderno', { ID_Alumno, Nota })
      // merge response if returns updated nota
      if (resp && resp.data) {
        const updated = resp.data
        const idx = alumnos.value.findIndex(a => a.ID_Usuario === ID_Alumno || a.id === ID_Alumno)
        if (idx !== -1) {
          alumnos.value[idx].nota_cuaderno = updated.nota_cuaderno ?? alumnos.value[idx].nota_cuaderno
        }
      }
      savingId.value = null
      return resp
    } catch (err) {
      savingId.value = null
      throw err
    }
  }

  return {
    alumnos,
    mensaje,
    savingId,
    fetchNotasCuaderno,
    guardarNotaCuaderno
  }
})
