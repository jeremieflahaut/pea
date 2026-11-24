import { useSanctumClient, useToast } from '#imports'
import type { Ref } from 'vue'

// Requête impérative via le client $fetch de nuxt-auth-sanctum : on l'appelle depuis des
// handlers useAsyncData et des submits. useSanctumFetch est un useFetch (état partagé par
// clé depuis Nuxt 4) : appelé ici, le refresh() des pages renverrait la donnée en cache.
export const useApiFetch = async <T>(
  url: string,
  options = {}
): Promise<{
  data: Ref<T | null>
  error: Ref<any>
}> => {
  const client = useSanctumClient()
  const toast = useToast()

  try {
    return { data: ref(await client<T>(url, options)) as Ref<T | null>, error: ref(null) }
  } catch (error: any) {
    toast.add({
      title: 'Erreur API',
      description: error?.message || 'Une erreur est survenue.',
      color: 'error',
      icon: 'i-heroicons-exclamation-triangle',
      duration: 4000,
    })

    return { data: ref(null), error: ref(error) }
  }
}
