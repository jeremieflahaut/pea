export const formatCurrency = (val: number): string =>
    new Intl.NumberFormat('fr-FR', {
      style: 'currency',
      currency: 'EUR',
      minimumFractionDigits: 3,
      maximumFractionDigits: 3,
    }).format(val)
  
  export const formatQuantity = (val: number): string =>
    new Intl.NumberFormat('fr-FR', {
      minimumFractionDigits: 0,
      maximumFractionDigits: 0,
    }).format(val)