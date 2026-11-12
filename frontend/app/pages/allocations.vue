<script setup lang="ts">
import type { TableColumn } from '@nuxt/ui'
import type { Allocation } from '@/types'
import * as z from 'zod'
import { UButton } from '#components'
import { formatCurrency, formatDateTime } from '@/utils/formatters'

const { data: allocations, status, refresh } = await useAsyncData('allocations', () => useAllocations())

const isLoading = computed(() => status.value === 'pending')

// Les cours sont rafraîchis toutes les 6 h : au-delà de 48 h, le scraper ne tourne plus
// (ou le ticker ne répond plus) et les montants « À ajouter » ne sont plus fiables.
const STALE_PRICE_MS = 48 * 60 * 60 * 1000

const isPriceStale = (allocation: Allocation): boolean =>
    !allocation.price_updated_at
    || Date.now() - new Date(allocation.price_updated_at).getTime() > STALE_PRICE_MS

const stalePriceLabels = computed(() =>
    (allocations.value ?? []).filter(isPriceStale).map((allocation) => allocation.label)
)

const columns: TableColumn<Allocation>[] = [
    {
        accessorKey: 'label',
        header: 'Libellé',
    },
    {
        accessorKey: 'isin',
        header: 'ISIN',
    },
    {
        accessorKey: 'type',
        header: ({ column }) => {
            const isSorted = column.getIsSorted()

            return h(UButton, {
                color: 'neutral',
                variant: 'ghost',
                label: 'Type',
                icon: isSorted
                    ? isSorted === 'asc'
                        ? 'i-lucide-arrow-up-narrow-wide'
                        : 'i-lucide-arrow-down-wide-narrow'
                    : 'i-lucide-arrow-up-down',
                class: '-mx-2.5',
                onClick: () => column.toggleSorting(column.getIsSorted() === 'asc')
            })
        },
    },
    {
        accessorKey: 'target_percent',
        header: ({ column }) => {
            const isSorted = column.getIsSorted()

            return h(UButton, {
                color: 'neutral',
                variant: 'ghost',
                label: 'Cible %',
                icon: isSorted
                    ? isSorted === 'asc'
                        ? 'i-lucide-arrow-up-narrow-wide'
                        : 'i-lucide-arrow-down-wide-narrow'
                    : 'i-lucide-arrow-up-down',
                class: '-mx-2.5',
                onClick: () => column.toggleSorting(column.getIsSorted() === 'asc')
            })
        },
        cell: ({ row }) => `${row.original.target_percent}%`,
    },
    {
        accessorKey: 'current_amount',
        header: ({ column }) => {
            const isSorted = column.getIsSorted()

            return h(UButton, {
                color: 'neutral',
                variant: 'ghost',
                label: 'Actuel',
                icon: isSorted
                    ? isSorted === 'asc'
                        ? 'i-lucide-arrow-up-narrow-wide'
                        : 'i-lucide-arrow-down-wide-narrow'
                    : 'i-lucide-arrow-up-down',
                class: '-mx-2.5',
                onClick: () => column.toggleSorting(column.getIsSorted() === 'asc')
            })
        },
        cell: ({ row }) => formatCurrency(row.original.current_amount),
    },
    {
        accessorKey: 'price_updated_at',
        header: 'Cours au',
        cell: ({ row }) => {
            const updatedAt = row.original.price_updated_at
            const stale = isPriceStale(row.original)

            return h('span', {
                class: stale ? 'text-red-500 font-semibold' : 'text-gray-400',
                title: stale ? 'Cours de plus de 48 h : les montants à ajouter ne sont pas fiables' : undefined
            }, `${stale ? '⚠ ' : ''}${updatedAt ? formatDateTime(updatedAt) : 'jamais'}`)
        }
    },
    {
        accessorKey: 'current_percent',
        header: ({ column }) => {
            const isSorted = column.getIsSorted()

            return h(UButton, {
                color: 'neutral',
                variant: 'ghost',
                label: 'Actuelle %',
                icon: isSorted
                    ? isSorted === 'asc'
                        ? 'i-lucide-arrow-up-narrow-wide'
                        : 'i-lucide-arrow-down-wide-narrow'
                    : 'i-lucide-arrow-up-down',
                class: '-mx-2.5',
                onClick: () => column.toggleSorting(column.getIsSorted() === 'asc')
            })
        },
        cell: ({ row }) => {
            const color = row.original.current_percent > row.original.target_percent ? 'text-red-500 font-semibold' : 'text-gray-300'
            return h('span', { class: color }, `${row.original.current_percent}%`)
        }
    },
    {
        accessorKey: 'amount_to_add',
        header: ({ column }) => {
            const isSorted = column.getIsSorted()

            return h(UButton, {
                color: 'neutral',
                variant: 'ghost',
                label: 'À ajouter',
                icon: isSorted
                    ? isSorted === 'asc'
                        ? 'i-lucide-arrow-up-narrow-wide'
                        : 'i-lucide-arrow-down-wide-narrow'
                    : 'i-lucide-arrow-up-down',
                class: '-mx-2.5',
                onClick: () => column.toggleSorting(column.getIsSorted() === 'asc')
            })
        },
        cell: ({ row }) =>
            h('span', {
                class: row.original.amount_to_add > 0 ? 'text-green-500' : 'text-gray-500'
            }, formatCurrency(row.original.amount_to_add))
    },
    {
        id: 'actions',
        header: '',
        cell: ({ row }) =>
            h(UButton, {
                icon: 'i-lucide-pencil',
                color: 'neutral',
                variant: 'ghost',
                'aria-label': 'Modifier l\'objectif',
                onClick: () => openEdit(row.original)
            })
    }
]

const open = ref(false)
const isSubmitting = ref(false)

const types = ref(['ETF', 'Action'])

const allocationSchema = z.object({
    name: z.string().min(2, 'Nom requis'),
    ticker: z.string().min(2, 'Ticker requis'),
    isin: z.string().length(12, 'ISIN invalide'),
    type: z.enum(['ETF', 'Action']),
    target_percent: z
        .number({ invalid_type_error: 'Entrez un nombre' })
        .min(0, 'Min: 0%')
        .max(100, 'Max: 100%')
})

const newAllocation = reactive({
    name: '',
    ticker: '',
    isin: '',
    type: 'ETF',
    target_percent: 0
})

const totalTargetPercent = computed(() =>
    (allocations.value ?? []).reduce((sum, item) => sum + Number(item.target_percent), 0)
)

const onSubmit = async () => {
    isSubmitting.value = true
    try {
        await useApiFetch('/api/allocations', {
            method: 'POST',
            body: newAllocation
        })
        open.value = false
        await refresh()

        Object.assign(newAllocation, {
            name: '',
            ticker: '',
            isin: '',
            type: 'ETF',
            target_percent: 0
        })
    } finally {
        isSubmitting.value = false
    }
}

const editOpen = ref(false)
const isUpdating = ref(false)

const editSchema = z.object({
    target_percent: z
        .number({ invalid_type_error: 'Entrez un nombre' })
        .min(0, 'Min: 0%')
        .max(100, 'Max: 100%')
})

const editedAllocation = reactive({
    id: 0,
    name: '',
    type: 'ETF' as Allocation['type'],
    target_percent: 0
})

const openEdit = (allocation: Allocation) => {
    Object.assign(editedAllocation, {
        id: allocation.id,
        name: allocation.label,
        type: allocation.type,
        target_percent: Number(allocation.target_percent)
    })
    editOpen.value = true
}

const onEditSubmit = async () => {
    isUpdating.value = true
    try {
        // L'API exige name + type en plus de l'objectif : on renvoie les valeurs actuelles.
        const { error } = await useApiFetch(`/api/allocations/${editedAllocation.id}`, {
            method: 'PUT',
            body: {
                name: editedAllocation.name,
                type: editedAllocation.type,
                target_percent: editedAllocation.target_percent
            }
        })
        if (error.value) return

        editOpen.value = false
        await refresh()
    } finally {
        isUpdating.value = false
    }
}

</script>

<template>

    <UModal v-model:open="open" title="Nouvelle allocation" description="Remplissez les champs pour ajouter une nouvelle allocation cible">
        <UButton label="Ajouter une allocation" color="neutral" variant="subtle" class="mb-5" />

        <template #body>
            <UForm :schema="allocationSchema" :state="newAllocation" @submit="onSubmit" class="space-y-4">
                <UFormField label="Nom" required name="name">
                    <UInput v-model="newAllocation.name" class="w-full" />
                </UFormField>

                <UFormField label="ISIN" required name="isin">
                    <UInput v-model="newAllocation.isin" class="w-full" />
                </UFormField>

                <UFormField label="Ticker" required name="ticker">
                    <UInput v-model="newAllocation.ticker" class="w-full" />
                </UFormField>

                <UFormField label="Type" required name="type">
                    <USelect v-model="newAllocation.type" :items="types" class="w-full" />
                </UFormField>

                <UFormField label="Objectif (%)" required name="target_percent">
                    <UInput v-model.number="newAllocation.target_percent" type="number" class="w-full" />
                </UFormField>

                <div class="mt-4">
                    <UButton type="submit" block :loading="isSubmitting" class="w-full">Créer</UButton>
                </div>
            </UForm>
        </template>
    </UModal>

    <UModal v-model:open="editOpen" :title="`Modifier ${editedAllocation.name}`" description="Nouvel objectif de répartition">
        <template #body>
            <UForm :schema="editSchema" :state="editedAllocation" @submit="onEditSubmit" class="space-y-4">
                <UFormField label="Objectif (%)" required name="target_percent">
                    <UInput v-model.number="editedAllocation.target_percent" type="number" step="0.01" class="w-full" />
                </UFormField>

                <div class="mt-4">
                    <UButton type="submit" block :loading="isUpdating" class="w-full">Enregistrer</UButton>
                </div>
            </UForm>
        </template>
    </UModal>

    <UCard>
        <template #header>
            <div class="flex justify-between items-center">
                <span class="font-semibold">Répartition cible vs actuelle</span>
                <span class="text-sm text-gray-400">
                    Total alloué :
                    <span :class="totalTargetPercent > 100 ? 'text-red-500 font-semibold' : 'text-white/70'">
                        {{ totalTargetPercent }}%
                    </span>
                    &nbsp;/
                    <span class="text-white/30">100%</span>
                </span>
            </div>
        </template>

        <UAlert
            v-if="!isLoading && stalePriceLabels.length"
            color="error"
            variant="subtle"
            icon="i-lucide-triangle-alert"
            title="Cours périmés"
            :description="`Cours de plus de 48 h pour : ${stalePriceLabels.join(', ')}. Les montants « À ajouter » ne sont pas fiables.`"
            class="mb-4"
        />

        <div v-if="isLoading" class="p-6 text-center">Chargement...</div>

        <UTable v-else :columns="columns" :data="allocations ?? []" />
    </UCard>
</template>
