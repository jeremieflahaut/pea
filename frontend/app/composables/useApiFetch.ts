import { useSanctumFetch, useToast } from '#imports'
import type { Ref } from 'vue'

export const useApiFetch = async <T>(
  url: string,
  options = {}
): Promise<{
  data: Ref<T | null>
  error: Ref<any>
  status: Ref<'idle' | 'pending' | 'success' | 'error'>
  refresh: () => Promise<void>
}> => {
  const toast = useToast()

  const result = await useSanctumFetch<T>(url, options)

  if (result.error.value) {
    toast.add({
      title: 'Erreur API',
      description: result.error.value.message || 'Une erreur est survenue.',
      color: 'error',
      icon: 'i-heroicons-exclamation-triangle',
      duration: 4000,
    })
  }

  // ✅ on cast ici uniquement
  return {
    data: result.data as Ref<T | null>,
    error: result.error,
    status: result.status,
    refresh: result.refresh
  }
}
