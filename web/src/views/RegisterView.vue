<script setup lang="ts">
import { reactive, ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'

import { ApiError, type ValidationErrors } from '@/api/http'
import { useAuthStore, type RegisterPayload } from '@/stores/auth'

const auth = useAuthStore()
const router = useRouter()

const form = reactive<RegisterPayload>({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})
const errors = ref<ValidationErrors>({})
const generalError = ref<string | null>(null)
const submitting = ref(false)

async function submit() {
  submitting.value = true
  errors.value = {}
  generalError.value = null

  try {
    await auth.register(form)
    await router.push({ name: 'dashboard' })
  } catch (error) {
    if (error instanceof ApiError && error.status === 422) {
      errors.value = error.errors
    } else if (error instanceof ApiError && error.status === 429) {
      generalError.value = 'Za dużo prób. Spróbuj ponownie za minutę.'
    } else {
      generalError.value = 'Nie udało się zarejestrować. Spróbuj ponownie.'
    }
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <main class="mx-auto mt-10 max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:mt-16 sm:p-8">
    <p class="text-sm font-bold uppercase tracking-[0.16em] text-blue-600">Nowe konto</p>
    <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-950">Rejestracja</h1>

    <form class="auth-form" @submit.prevent="submit">
      <p v-if="generalError" class="form-error" role="alert">{{ generalError }}</p>

      <label>
        Imię
        <input v-model="form.name" type="text" autocomplete="name" required />
      </label>
      <p v-if="errors.name" class="field-error">{{ errors.name[0] }}</p>

      <label>
        E-mail
        <input v-model="form.email" type="email" autocomplete="email" required />
      </label>
      <p v-if="errors.email" class="field-error">{{ errors.email[0] }}</p>

      <label>
        Hasło (min. 8 znaków)
        <input v-model="form.password" type="password" autocomplete="new-password" required />
      </label>
      <p v-if="errors.password" class="field-error">{{ errors.password[0] }}</p>

      <label>
        Powtórz hasło
        <input
          v-model="form.password_confirmation"
          type="password"
          autocomplete="new-password"
          required
        />
      </label>

      <button type="submit" :disabled="submitting">
        {{ submitting ? 'Rejestracja...' : 'Załóż konto' }}
      </button>
    </form>

    <p class="mt-6 text-sm text-slate-600">Masz już konto? <RouterLink :to="{ name: 'login' }" class="font-semibold text-blue-700 hover:text-blue-800">Zaloguj się</RouterLink></p>
  </main>
</template>
