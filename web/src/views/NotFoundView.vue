<script setup lang="ts">
import { RouterLink } from 'vue-router'
</script>

<template>
  <main class="mx-auto mt-16 max-w-lg px-4 text-center sm:mt-24">
    <p class="text-sm font-bold uppercase tracking-[0.2em] text-blue-600">Błąd 404</p>
    <h1 class="mt-3 text-4xl font-bold tracking-tight text-slate-950">Nie ma takiej strony.</h1>
    <p class="mt-4 text-slate-600">Adres może być nieprawidłowy albo strona została przeniesiona.</p>
    <RouterLink to="/" class="mt-8 inline-flex rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-700">Wróć na stronę główną</RouterLink>
  </main>
</template>
