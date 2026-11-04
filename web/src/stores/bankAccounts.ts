import { ref } from 'vue'
import { defineStore } from 'pinia'

import { getBankAccounts, createBankAccount } from '@/api/bankAccounts';
import type { BankAccount } from '@/types/bankAccounts';

export const useBankAccountsStore = defineStore('bankAccounts', () => {
  const accounts = ref<BankAccount[]>([])
  const isLoading = ref<boolean>(false)
  const error = ref<string | null>(null)
  const isAdding = ref<boolean>(false)

  async function load() : Promise<void> {
    isLoading.value = true;
    error.value = null;

    try {
      accounts.value = await getBankAccounts()
    } catch (caughtError) {
      console.error(caughtError);
      error.value = "Nie udało się pobrać kont bankowych"
    } finally {
      isLoading.value = false
    }
  }

  async function add(name:string) : Promise<void> {
    isAdding.value = true

    try {
      const account = await createBankAccount(name)
      accounts.value.push(account)
    } finally {
      isAdding.value = false
    }
  }

  return {
    accounts,
    isLoading,
    isAdding,
    error,
    load,
    add
  }
})
