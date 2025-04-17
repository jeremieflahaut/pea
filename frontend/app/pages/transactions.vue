<script setup lang="ts">
import { z } from 'zod'

const { data: transactions, status, refresh } = await useAsyncData('transactions', () => useTransactions())
const isLoading = computed(() => status.value === 'pending')

const formatCurrency = (val: number) =>
    new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(val)

const formatDate = (iso: string) =>
    new Date(iso).toLocaleDateString('fr-FR')



const open = ref(false)
const isSubmitting = ref(false)

const types = ref([
    { label: 'Achat', value: 'buy' },
    { label: 'Vente', value: 'sell' }
])


const transactionSchema = z.object({
    isin: z.string().length(12, 'ISIN invalide'),
    type: z.enum(['buy', 'sell']),
    quantity: z.number({ invalid_type_error: 'Nombre requis' }).min(0.0001),
    price: z.number({ invalid_type_error: 'Nombre requis' }).min(0),
    date: z.string().min(1, 'Date requise'),
})

const newTransaction = reactive({
    isin: '',
    type: 'buy',
    quantity: 0,
    price: 0,
    date: new Date().toISOString().split('T')[0],
})

const onSubmit = async () => {
    isSubmitting.value = true
    try {
        await useApiFetch('/api/transactions', {
            method: 'POST',
            body: newTransaction
        })

        open.value = false
        Object.assign(newTransaction, {
            isin: '',
            type: 'buy',
            quantity: 0,
            price: 0,
            date: new Date().toISOString().split('T')[0],
        })

        await refresh()
    } finally {
        isSubmitting.value = false
    }
}


</script>

<template>


    <UModal v-model:open="open" title="Nouvelle allocation" description="Remplissez les champs pour ajouter une nouvelle allocation cible">
        <UButton label="Ajouter une allocation" color="neutral" variant="subtle" class="mb-5" />

        <template #body>
            <UForm :schema="transactionSchema" :state="newTransaction" @submit="onSubmit" class="space-y-4">

                <UFormField label="Type" required name="type">
                    <USelect v-model="newTransaction.type" :items="types" class="w-full" />
                </UFormField>

                <UFormField label="Date" name="date" required>
                    <UInput type="date" v-model="newTransaction.date" class="w-full" />
                </UFormField>

                <UFormField label="ISIN" required name="isin">
                    <UInput v-model="newTransaction.isin" class="w-full" />
                </UFormField>

                <UFormField label="Quantité" name="quantity" required>
                    <UInput type="number" v-model.number="newTransaction.quantity" />
                </UFormField>

                <UFormField label="Prix unitaire" name="price" required>
                    <UInput type="number" v-model.number="newTransaction.price" class="w-full" />
                </UFormField>

               

                <div class="mt-4">
                    <UButton type="submit" block :loading="isSubmitting" class="w-full">Créer</UButton>
                </div>
            </UForm>
        </template>
    </UModal>


    <UCard>
        <template #header>Mes transactions</template>

        <div v-if="isLoading" class="p-6 text-center">Chargement...</div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            <UCard v-for="tx in transactions ?? []" :key="tx.id" class="border">
                <template #header>
                    <div class="flex justify-between items-center">
                        <span class="font-semibold">{{ tx.type === 'buy' ? 'Achat' : 'Vente' }}</span>
                        <span class="text-sm text-gray-400">{{ formatDate(tx.date) }}</span>
                    </div>
                </template>

                <div class="space-y-1">
                    <div><strong>ISIN :</strong> {{ tx.isin }}</div>
                    <div><strong>Quantité :</strong> {{ tx.quantity }}</div>
                    <div><strong>Prix unitaire :</strong> {{ formatCurrency(tx.price) }}</div>
                    <div><strong>Total :</strong> {{ formatCurrency(tx.price * tx.quantity) }}</div>
                </div>
            </UCard>
        </div>
    </UCard>
</template>
