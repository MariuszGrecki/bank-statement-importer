<script setup lang="ts">
import { reactive, ref } from 'vue'
import { RouterLink, useRoute, useRouter, type RouteLocationRaw } from 'vue-router'

import { ApiError, type ValidationErrors } from '@/api/http'
import { useAuthStore, type LoginPayload } from '@/stores/auth'

const auth = useAuthStore()
const route = useRoute()
const router = useRouter()

const form = reactive<LoginPayload>({ email: '', password: '' })
const errors = ref<ValidationErrors>({})
const generalError = ref<string | null>(null)
const submitting = ref(false)

function redirectTarget(): RouteLocationRaw {
  const redirect = route.query.redirect
  return typeof redirect === 'string' && redirect.startsWith('/') && !redirect.startsWith('//')
    ? redirect
    : { name: 'dashboard' }
}

async function submit() {
  submitting.value = true
  errors.value = {}
  generalError.value = null

  try {
    await auth.login(form)
    await router.push(redirectTarget())
  } catch (error) {
    if (error instanceof ApiError && error.status === 422) {
      errors.value = error.errors
    } else if (error instanceof ApiError && error.status === 429) {
      generalError.value = 'Za dużo prób logowania. Spróbuj ponownie za minutę.'
    } else {
      generalError.value = 'Nie udało się zalogować. Spróbuj ponownie.'
    }
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <main class="mx-auto mt-10 max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:mt-16 sm:p-8">
    <p class="text-sm font-bold uppercase tracking-[0.16em] text-blue-600">Witaj ponownie</p>
    <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-950">Logowanie</h1>

    <form class="auth-form" @submit.prevent="submit">
      <p v-if="generalError" class="form-error" role="alert">{{ generalError }}</p>

      <label>
        E-mail
        <input v-model="form.email" type="email" autocomplete="email" required />
      </label>
      <p v-if="errors.email" class="field-error">{{ errors.email[0] }}</p>

      <label>
        Hasło
        <input v-model="form.password" type="password" autocomplete="current-password" required />
      </label>
      <p v-if="errors.password" class="field-error">{{ errors.password[0] }}</p>

      <button type="submit" :disabled="submitting">
        {{ submitting ? 'Logowanie...' : 'Zaloguj się' }}
      </button>
    </form>

    <p class="mt-6 text-sm text-slate-600">Nie masz konta? <RouterLink :to="{ name: 'register' }" class="font-semibold text-blue-700 hover:text-blue-800">Zarejestruj się</RouterLink></p>
  </main>
</template>
