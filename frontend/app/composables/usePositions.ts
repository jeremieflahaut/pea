import type { Position } from "@/types"

export const usePositions = async (): Promise<Position[]> => {
  const { data } = await useApiFetch<Position[]>('/api/positions')
  return data.value ?? []
}

  