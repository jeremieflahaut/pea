export type Transaction = {
    id: number
    isin: string
    quantity: number
    price: number
    type: 'buy' | 'sell'
    date: string
  }