<script setup lang="ts">
import type { TableColumn } from '@nuxt/ui'
import type { Position } from '@/types'

/* const positions = ref<Position[]>([])
const isLoading = ref(true) */

const { data: positions, status } = await useAsyncData('positions', () => usePositions())

const isLoading = computed(() => status.value === 'pending')

const columns: TableColumn<Position>[] = [
  {
    accessorKey: 'name',
    header: 'Titre'
  },
  {
    accessorKey: 'isin',
    header: 'ISIN'
  },
  {
    accessorKey: 'quantity',
    header: 'Qté'
  },
  {
    accessorKey: 'average_price',
    header: 'PRU',
    cell: ({ row }) =>
      formatCurrency(row.original.average_price)
  },
  {
    accessorKey: 'current_price',
    header: 'Cours',
    cell: ({ row }) =>
      formatCurrency(row.original.current_price)
  },
  {
    id: 'gain_loss',
    header: '+/-',
    cell: ({ row }) => {
      const p = row.original
      const gain = (p.current_price - p.average_price) * p.quantity
      return h(
        'span',
        {
          class: gain >= 0 ? 'text-green-500' : 'text-red-500'
        },
        formatCurrency(gain)
      )
    }
  }
]

const formatCurrency = (val: number) =>
    new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'EUR'
    }).format(val)


</script>

<template>
    
    <UCard>
        <template #header>Positions en portefeuille</template>

        <div v-if="isLoading" class="text-center p-6">
            Chargement...
        </div>

        <UTable v-else :data="positions ?? []" :columns="columns"></UTable>
    </UCard>
</template>
