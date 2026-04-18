<script setup>
import { useRoute } from 'vue-router'
const route = useRoute()

const hideNavOn = ['/login', '/register']
const showNav = computed(() => !hideNavOn.includes(route.path))
</script>

<template>
    <UApp>
        <div class="flex flex-col min-h-screen">
            <header v-if="showNav">
                <navBar />
            </header>
            <main class="flex-1">
                <div class="w-full px-4">
                    <NuxtPage />
                </div>
            </main>
            <footer class="text-center py-4">
                © {{ new Date().getFullYear() }} — Mon App PEA
            </footer>
        </div>
    </UApp>
</template>
