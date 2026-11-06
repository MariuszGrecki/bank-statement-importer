import { ref } from 'vue'
import { defineStore } from 'pinia'

import { getBankAccounts, createBankAccount, updateBankAccount, deleteBankAccount } from '@/api/bankAccounts';
import type { BankAccount } from '@/types/bankAccounts';
import BankAccountsView from '@/views/BankAccountsView.vue';

export const useBankAccountsStore = defineStore('bankAccounts', () => {
  const accounts = ref<BankAccount[]>([])
  const isLoading = ref<boolean>(false)
  const error = ref<string | null>(null)
  const isAdding = ref<boolean>(false)
  const isUpdating = ref<boolean>(false)
  const isDeleting = ref<boolean>(false)

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

  async function update(id:number, name:string) : Promise<void> {
    isUpdating.value = true

    try {
      const changedBankAccount = await updateBankAccount(id, name)

      console.log(changedBankAccount)

      const index = accounts.value.findIndex((account) => account.id === changedBankAccount.id)

      if (index !== -1) {
        accounts.value[index] = changedBankAccount
      }
    } finally {
      isUpdating.value = false
    }
  }

  async function remove(id:number) : Promise<void> {
    isDeleting.value = true

    try {
      await deleteBankAccount(id)
      accounts.value = accounts.value.filter((account) => account.id !== id)
    } finally {
      isDeleting.value = false
    }
  }


  return {
    accounts,
    isLoading,
    isAdding,
    isUpdating,
    isDeleting,
    error,
    load,
    add,
    update,
    remove
  }
})
