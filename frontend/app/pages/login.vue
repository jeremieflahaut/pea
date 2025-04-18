<script setup>
const { login } = useSanctumAuth();
const email = ref('')
const password = ref('')
const error = ref('')

const isLoading = ref(false)

const loginPost = async () => {
  if (!email.value || !password.value) return

  isLoading.value = true

  try {
    await login({
      email: email.value,
      password: password.value
    })

  } catch (err) {
    if (err?.response?.status === 422) {
      error.value = err?.response?._data?.errors?.email?.[0] || 'Identifiants invalides.'
    } else {
      error.value = 'Une erreur est survenue.'
    }
  } finally {
    isLoading.value = false
  }
}


const isFormValid = computed(() => {
  return email.value.trim() !== '' && password.value.trim() !== ''
})
</script>

<template>
  <div>Test 22</div>
  <div class="flex items-center justify-center min-h-screen">
    <UCard>
      <template #header>Login</template>
      <form @submit.prevent="loginPost" class="space-y-4">
        <UInput class="w-full" v-model="email" placeholder="Email" required />
        <UInput class="w-full" v-model="password" type="password" placeholder="Mot de passe" required />
        <UAlert v-if="error" color="error" variant="outline" icon="i-heroicons-exclamation-triangle" class="mb-2" :description="error" />
        <UButton class="w-full cursor-pointer" type="submit" :loading="isLoading" :disabled="!isFormValid">
          Se connecter
        </UButton>
      </form>
    </UCard>
  </div>
</template>
