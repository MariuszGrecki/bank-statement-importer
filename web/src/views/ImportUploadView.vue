<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { createImport, getImportPreview } from '@/api/import'
import { useBankAccountsStore } from '@/stores/bankAccounts'
import { ApiError } from '@/api/http'

import BaseButton from '@/components/BaseButton.vue'
import type { ImportPreview } from '@/types/import'

const store = useBankAccountsStore()
const selectedBankAccountId = ref<number | null>(null)
const selectedFile = ref<File | null>(null)
const isSubmitting = ref<boolean>(false)
const bankAccountIdError = ref<string | null>(null)
const fileError = ref<string | null>(null)
const preview = ref<ImportPreview | null>(null)

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
    bankAccountIdError.value = 'Należy podać rachunek bankowy'
  }

  if (!selectedFile.value) {
    fileError.value = 'Należy wrzucić plik'
  }

  if (!selectedBankAccountId.value || !selectedFile.value) {
    return
  }

  isSubmitting.value = true

  try {
    const createdImport = await createImport(selectedBankAccountId.value, selectedFile.value)

    console.log(createdImport)

    preview.value = await getImportPreview(createdImport.id)
  } catch (caughtError) {
    if (caughtError instanceof ApiError && caughtError.status == 422) {
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
    class="mx-auto mt-8 max-w-xl space-y-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:mt-10 sm:p-8"
    @submit.prevent="submit"
  >
    <div class="space-y-2">
      <label for="bank-account" class="block text-sm font-semibold text-slate-800"
        >Wybierz konto</label
      >
      <select
        id="bank-account"
        v-model="selectedBankAccountId"
        class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-slate-900 outline-none transition focus:border-slate-900 focus:ring-2 focus:ring-slate-200"
      >
        <option :value="null" disabled>Wybierz sposób dostawy</option>
        <option v-for="bankAccount in store.accounts" :key="bankAccount.id" :value="bankAccount.id">
          {{ bankAccount.name }}
        </option>
      </select>
      <span v-if="bankAccountIdError" class="block text-sm font-medium text-red-600">{{
        bankAccountIdError
      }}</span>
    </div>
    <p class="text-sm text-slate-600">
      Wybrano konto:
      <span class="font-medium text-slate-900">{{ selectedBankAccountId ?? 'brak' }}</span>
    </p>
    <div class="space-y-2">
      <label class="block text-sm font-semibold text-slate-800" for="import-file">Plik CSV</label>
      <input
        id="import-file"
        class="block w-full cursor-pointer rounded-lg border border-dashed border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-700 file:mr-4 file:rounded-md file:border-0 file:bg-slate-900 file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-white hover:file:bg-slate-700"
        type="file"
        accept=".csv,text/csv"
        @change="selectFile"
      />
      <span v-if="fileError" class="block text-sm font-medium text-red-600">{{ fileError }}</span>
      <p v-if="selectedFile" class="text-sm text-slate-600">
        Wybrany plik: <span class="font-medium text-slate-900">{{ selectedFile.name }}</span>
      </p>
    </div>
    <BaseButton type="submit" :disabled="isSubmitting" class="w-full sm:w-auto">
      {{ isSubmitting ? 'Wysyłanie…' : 'Wyślij import' }}
    </BaseButton>
  </form>
  <div
    v-if="preview"
    class="mx-auto mt-6 max-w-5xl overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
  >
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-slate-200 text-left text-sm text-slate-700">
        <thead>
          <tr>
            <th
              v-for="header in preview.headers"
              :key="header"
              class="whitespace-nowrap bg-slate-900 px-4 py-3 text-xs font-semibold uppercase tracking-wide text-white"
            >
              {{ header }}
            </th>
          </tr>
        </thead>

        <tbody>
          <tr v-for="(row, rowIndex) in preview.rows" :key="rowIndex" class="hover:bg-slate-50">
            <td
              v-for="(element, elementIndex) in row"
              :key="elementIndex"
              class="whitespace-nowrap px-4 py-3 text-slate-700"
            >
              {{ element }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
