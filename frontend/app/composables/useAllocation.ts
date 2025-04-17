import type { Allocation } from '@/types'

export const useAllocations = async (): Promise<Allocation[]> => {
  const { data } = await useApiFetch<Allocation[]>('/api/allocations')
  return data.value ?? []
}
