import { inject } from 'vue'

export function useNotificacion() {
  const mostrarNotificacion = inject<(tipo: string, titulo: string, mensaje: string) => void>('mostrarNotificacion')

  const success = (titulo: string, mensaje: string) => {
    mostrarNotificacion?.('success', titulo, mensaje)
  }

  const error = (titulo: string, mensaje: string) => {
    mostrarNotificacion?.('error', titulo, mensaje)
  }

  const info = (titulo: string, mensaje: string) => {
    mostrarNotificacion?.('info', titulo, mensaje)
  }

  const warning = (titulo: string, mensaje: string) => {
    mostrarNotificacion?.('warning', titulo, mensaje)
  }

  return {
    mostrarNotificacion,
    success,
    error,
    info,
    warning
  }
}
