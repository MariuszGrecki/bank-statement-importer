<script setup lang="ts">
import { RouterLink, RouterView, useRouter } from 'vue-router'

import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const router = useRouter()

async function logout() {
  await auth.logout()
  await router.push({ name: 'home' })
}
</script>

<template>
  <header class="border-b border-slate-200 bg-white/90 backdrop-blur">
    <nav class="mx-auto flex max-w-6xl items-center gap-1 px-4 py-3 sm:px-6">
      <RouterLink to="/" class="mr-3 text-sm font-bold tracking-tight text-slate-950">Bank Statement Importer</RouterLink>

      <template v-if="auth.isAuthenticated">
        <RouterLink :to="{ name: 'dashboard' }" class="app-nav-link">Panel</RouterLink>
        <RouterLink :to="{ name: 'bank-accounts'}" class="app-nav-link">Konta</RouterLink>
        <RouterLink :to="{ name: 'import-upload'}" class="app-nav-link">Import</RouterLink>
        <span class="ml-auto hidden text-sm font-medium text-slate-600 sm:inline">{{ auth.user?.name }}</span>
        <button type="button" class="ml-auto rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 hover:text-slate-950 sm:ml-2" @click="logout">Wyloguj</button>
      </template>

      <template v-else>
        <RouterLink :to="{ name: 'login' }" class="ml-auto app-nav-link">Zaloguj się</RouterLink>
        <RouterLink :to="{ name: 'register' }" class="app-nav-link">Zarejestruj się</RouterLink>
      </template>
    </nav>
  </header>

  <RouterView />
</template>
