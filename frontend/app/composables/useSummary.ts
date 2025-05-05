import type { Summary } from '@/types'

export const useSumary = async (): Promise<Summary> => {
  const { data } = await useApiFetch<Summary>('/api/summary')

  console.log(data.value)

  return data.value ?? {
    total_deposits: 0,
    current_value: 0,
    total_gain: 0,
    performance_percent: 0,
  }
}
