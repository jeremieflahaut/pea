<script setup lang="ts">
import type { NavigationMenuItem } from '@nuxt/ui'

const { logout } = useSanctumAuth();


const items = ref<NavigationMenuItem[]>([
    {label: 'Dashboard', to: '/'},
    {label: 'Positions', to: '/positions'},
    {label: 'Allocations', to: '/allocations'},
    {label: 'Transactions', to: '/transactions'},
])
</script>

<template>
    <div class="flex items-center justify-between px-4 py-3">
        <!-- Menu centré -->
        <UNavigationMenu :items="items" class="w-full justify-center" />

        <!-- Bouton logout -->
        <UButton icon="i-heroicons-arrow-left-on-rectangle" variant="ghost" size="sm" @click="logout()" class="cursor-pointer" title="Se déconnecter" />
    </div>
</template>