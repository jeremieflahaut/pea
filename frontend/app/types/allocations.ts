export type Allocation = {
    label: string
    isin: string
    type: 'ETF' | 'Action' | 'Autre'
    target_percent: number
    current_amount: number
    current_percent: number
    amount_to_add: number
  }