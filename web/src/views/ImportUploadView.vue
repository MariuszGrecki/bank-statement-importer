<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { createImport } from '@/api/import';
import { useBankAccountsStore } from '@/stores/bankAccounts';
import { ApiError } from '@/api/http';

import BaseButton from '@/components/BaseButton.vue';

const store = useBankAccountsStore()
const selectedBankAccountId = ref<number | null>(null)
const selectedFile = ref<File | null>(null)
const isSubmitting = ref<boolean>(false)
const bankAccountIdError = ref<string | null>(null)
const fileError = ref<string | null>(null)

onMounted(() => {
  store.load()
})

function selectFile(event: Event): void {
  const input = event.target as HTMLInputElement
  selectedFile.value = input.files?.[0] ?? null
}

async function submit() {
  bankAccountIdError.value = null
  fileError.value = null

  if (!selectedBankAccountId.value) {
    bankAccountIdError.value = "Należy podać rachunek bankowy"
  }

  if (!selectedFile.value) {
    fileError.value = "Należy wrzucić plik"
  }

  if (!selectedBankAccountId.value || !selectedFile.value) {
    return;
  }

  isSubmitting.value = true;

  try {
    await createImport(selectedBankAccountId.value, selectedFile.value)

  } catch (caughtError) {
    if (caughtError instanceof ApiError && caughtError.status == 422 ) {
      bankAccountIdError.value = caughtError.errors['bank_account_id']?.[0] ?? null
      fileError.value = caughtError.errors['file']?.[0] ?? null
    }

  } finally {
    isSubmitting.value = false
  }
}

</script>

<template>
  <form
    class="mx-auto max-w-xl space-y-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8"
    @submit.prevent="submit"
  >
  <div class="space-y-2">
  <label for="bank-account" class="block text-sm font-semibold text-slate-800">Wybierz konto</label>
  <select
    id="bank-account"
    v-model="selectedBankAccountId"
    class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-slate-900 outline-none transition focus:border-slate-900 focus:ring-2 focus:ring-slate-200"
  >
    <option
      :value="null"
      disabled
    >
      Wybierz sposób dostawy
    </option>
    <option
      v-for="bankAccount in store.accounts"
      :key="bankAccount.id"
      :value="bankAccount.id"
    >
      {{ bankAccount.name  }}
    </option>

  </select>
  <span v-if="bankAccountIdError">{{ bankAccountIdError }}</span>
  </div>
  <p class="text-sm text-slate-600">
    Wybrano konto: <span class="font-medium text-slate-900">{{ selectedBankAccountId ?? 'brak' }}</span>
  </p>
  <div class="space-y-2">
  <label class="block text-sm font-semibold text-slate-800" for="import-file">Plik CSV</label>
  <input
    id="import-file"
    class="block w-full cursor-pointer rounded-lg border border-dashed border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-700 file:mr-4 file:rounded-md file:border-0 file:bg-slate-900 file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-white hover:file:bg-slate-700"
    type="file"
    accept=".csv,text/csv"
    @change="selectFile"
  >
  <span v-if="fileError">{{ fileError }}</span>
  <p v-if="selectedFile" class="text-sm text-slate-600">
    Wybrany plik: <span class="font-medium text-slate-900">{{ selectedFile.name }}</span>
  </p>
  </div>
  <BaseButton
    type="submit"
    :disabled="isSubmitting"
    class="w-full sm:w-auto"
  >
    {{ isSubmitting ? 'Wysyłanie…' : 'Wyślij import' }}
  </BaseButton>
  </form>
</template>
