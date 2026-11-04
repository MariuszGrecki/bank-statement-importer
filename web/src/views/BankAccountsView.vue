<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useBankAccountsStore } from '@/stores/bankAccounts';
import { ApiError } from '@/api/http';
import  BaseButton from '@/components/BaseButton.vue'

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
  <div class="max-w-md mx-auto space-y-6">
  <div class="p-6 border rounded-xl shadow-sm max-w-md">
  <form class="flex flex-col gap-4" @submit.prevent="submit">
    <label for="account-name">Nazwa konta</label>
    <input
      class="border rounded px-3 p-2"
      placeholder="Dodaj nazwę rachunku"
      id="account-name"
      v-model ="newAccountName"
      type="text"
      required
      maxLength = "100"
    >
    <span class="text-red-600" v-if="errors != ''">{{ errors }}</span>
    <BaseButton
      type="submit"
      :disabled="store.isAdding"
    >
        {{ store.isAdding ? 'Dodawanie...' : 'Dodaj konto' }}
    </BaseButton>
  </form>
  </div>

  <div v-if="store.isLoading">
    <span>Ładowanie kont</span>
  </div>
  <div v-else-if="store.error" class="text-red">
    <span>{{ store.error }}</span>
  </div>
  <div v-else-if="store.accounts.length === 0">
    Nie masz jeszcze zadnych kont
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
  </div>

</template>
