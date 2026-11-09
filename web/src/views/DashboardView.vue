<script setup lang="ts">
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
</script>

<template>
  <main class="mx-auto max-w-6xl px-4 py-10 sm:px-6">
    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
      <p class="text-sm font-bold uppercase tracking-[0.16em] text-blue-600">Panel</p>
      <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-950">Witaj, {{ auth.user?.name }}</h1>
      <p class="mt-3 text-slate-600">Zalogowano jako {{ auth.user?.email }}.</p>
    </section>
  </main>
</template>
