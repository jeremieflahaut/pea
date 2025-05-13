<script setup lang="ts">

const { data: summary, status, refresh } = await useAsyncData('summary', () => useSumary())

const formatEuro = (value: number) =>
  new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'EUR',
  }).format(value)

const gain = computed(() => summary.value?.total_gain ?? 0)
const isGainPositive = computed(() => gain.value > 0)
const isGainNegative = computed(() => gain.value < 0)

const performancePercent = computed(() => summary.value?.performance_percent ?? 0)
const isPerformancePercentPositive = computed(() => performancePercent.value > 0)
const isPerformancePercentNegative = computed(() => performancePercent.value < 0)

</script>

<template>
  <div class="p-6 grid gap-4 md:grid-cols-3">
    <!-- Card 1: Dépôts -->
    <UCard>
      <template #header>
        <span class="text-sm font-medium text-gray-500">Versements cumulés</span>
      </template>
      <div class="text-2xl font-semibold">
        {{ formatEuro(summary?.total_deposits ?? 0) }}
      </div>
    </UCard>

    <!-- Card 2: Valeur courante -->
    <UCard>
      <template #header>
        <span class="text-sm font-medium text-gray-500">Valeur courante</span>
      </template>
      <div class="text-2xl font-semibold">
        {{ formatEuro(summary?.current_value ?? 0) }}
      </div>
    </UCard>

    <!-- Card 3: Plus-values -->
    <UCard>
      <template #header>
        <span class="text-sm font-medium text-gray-500">Plus-values</span>
      </template>
      <div class="text-2xl font-semibold" :class="{
        'text-green-600': isGainPositive,
        'text-red-600': isGainNegative
      }">
        {{ formatEuro(summary?.total_gain ?? 0) }}
      </div>
    </UCard>

    <!-- Card 4: Rendement -->
    <UCard>
      <template #header>
        <span class="text-sm font-medium text-gray-500">Rendement</span>
      </template>
      <div class="text-2xl font-semibold" :class="{
        'text-green-600': isPerformancePercentPositive,
        'text-red-600': isPerformancePercentNegative
      }">
        {{ (summary?.performance_percent ?? 0).toFixed(2) }} %
      </div>
    </UCard>
  </div>
</template>
