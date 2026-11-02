<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useBankAccountsStore } from '@/stores/bankAccounts';
import { ApiError } from '@/api/http';

const store = useBankAccountsStore()
const newAccountName = ref('')
const errors = ref<string | null>('')

onMounted(() => {
  store.load()
})

async function submit() {
  errors.value = ''

  try {
    await store.add(newAccountName.value)
    newAccountName.value = ''
  } catch (caughtError) {
    if (caughtError instanceof ApiError && caughtError.status == 422 ) {
      errors.value = caughtError.message
    }
  }
}
</script>

<template>
  <div class="p-6 border rounded-xl shadow-sm max-w-md">
  <form class="flex flex-col gap-4" @submit.prevent="submit">
    <label for="account-name">Nazwa konta</label>
    <input
      class="border rounded px-3 p-2"
      placeholder="Szukaj..."
      id="account-name"
      v-model ="newAccountName"
      type="text"
      required
      maxLength = "100"
    >
    <span v-if="errors != ''">{{ errors }}</span>
    <button
      type="submit"
      class="border p-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700 cursor-pointer">
        Dodaj konto
    </button>
  </form>
  </div>

  <div v-if="store.isLoading">
    <span>Ładowanie kont</span>
  </div>
  <div v-else-if="store.error" class="text-red">
    <span>{{ store.error }}</span>
  </div>
  <div v-else-if="store.accounts.length === 0">
    NIe masz jeszcze zadnych kont
  </div>
  <div v-else>
    <ul class="mt-6 space-y-2">
      <li v-for="bankAccount in store.accounts"
          :key="bankAccount.id"
          class="border rounded-xl p-2"
      >
        <span>{{ bankAccount.name }}</span>
      </li>
    </ul>
  </div>

</template>
