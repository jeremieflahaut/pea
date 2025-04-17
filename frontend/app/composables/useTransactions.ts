import type { Transaction } from '@/types'

export const useTransactions = async (): Promise<Transaction[]> => {
  const { data } = await useApiFetch<Transaction[]>('/api/transactions')
  return data.value ?? []
}