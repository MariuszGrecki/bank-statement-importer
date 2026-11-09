<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'

import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const apiStatus = ref<'loading' | 'ok' | 'error'>('loading')

onMounted(async () => {
  try {
    const response = await fetch(`${import.meta.env.VITE_API_URL}/api/health`)
    apiStatus.value = response.ok ? 'ok' : 'error'
  } catch {
    apiStatus.value = 'error'
  }
})
</script>

<template>
  <main class="mx-auto flex max-w-6xl px-4 py-16 sm:px-6 sm:py-24">
    <section class="max-w-2xl">
      <p class="mb-4 text-sm font-bold uppercase tracking-[0.2em] text-blue-600">Twoje finanse</p>
      <h1 class="text-4xl font-bold tracking-tight text-slate-950 sm:text-6xl">Bank Statement Importer</h1>
      <p class="mt-6 text-lg leading-8 text-slate-600">Importer i kategoryzator wyciągów bankowych.</p>

      <p v-if="auth.isAuthenticated" class="mt-8">
        <RouterLink :to="{ name: 'dashboard' }" class="inline-flex rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-700">Przejdź do panelu</RouterLink>
      </p>
      <p v-else class="mt-8 flex flex-wrap items-center gap-4">
        <RouterLink :to="{ name: 'login' }" class="inline-flex rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-700">Zaloguj się</RouterLink>
        <RouterLink :to="{ name: 'register' }" class="text-sm font-semibold text-slate-700 transition hover:text-slate-950">Załóż konto <span aria-hidden="true">→</span></RouterLink>
      </p>
      <p v-if="apiStatus === 'loading'" class="mt-12 text-sm text-slate-500">API: sprawdzam...</p>
      <p v-else-if="apiStatus === 'ok'" class="mt-12 inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1.5 text-sm font-medium text-emerald-700">API działa</p>
      <p v-else class="mt-12 inline-flex items-center gap-2 rounded-full bg-red-50 px-3 py-1.5 text-sm font-medium text-red-700">API niedostępne</p>
    </section>
  </main>
</template>
