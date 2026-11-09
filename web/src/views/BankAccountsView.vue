<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useBankAccountsStore } from '@/stores/bankAccounts';
import { ApiError } from '@/api/http';
import  BaseButton from '@/components/BaseButton.vue'

const store = useBankAccountsStore()
const newAccountName = ref('')
const errors = ref<string | null>('')
const editingError = ref<string | null>('')
const editingBankAccountId = ref<number | null>(null)
const editedBankAccountName = ref('')

function startEditing(id:number, currentName: string): void {
  editingBankAccountId.value = id
  editedBankAccountName.value = currentName
}

function cancelBankAccountEdit(): void {
  editingBankAccountId.value = null
  editingError.value = ''
}

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

async function saveBankAccountEdit(): Promise<void> {
  if (editingBankAccountId.value === null) {
    return
  }

  editingError.value = ''

  try {
    await store.update(editingBankAccountId.value, editedBankAccountName.value)
    editingBankAccountId.value = null
  } catch (caughtError) {
    if (caughtError instanceof ApiError && caughtError.status == 422 ) {
      editingError.value = caughtError.message
    }
  }
}

async function confirmDeleteBankAccount(id:number, name:string): Promise<void> {
  const accepted = window.confirm(
    `Usunac kategorie "${name}"? Tej operacji nie można cofnąć`
  )

  if (!accepted) {
    return
  }

  await store.remove(id)
}
</script>

<template>
  <div class="mx-auto w-full max-w-2xl space-y-8 px-4 py-10 sm:px-0">
  <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
  <form class="flex flex-col gap-4" @submit.prevent="submit">
    <label for="account-name" class="text-sm font-semibold text-slate-700">Nazwa konta</label>
    <input
      class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-slate-950 outline-none transition placeholder:text-slate-400 focus:border-blue-600 focus:ring-3 focus:ring-blue-100"
      placeholder="Dodaj nazwę rachunku"
      id="account-name"
      v-model ="newAccountName"
      type="text"
      required
      maxLength = "100"
    >
    <span class="text-sm text-red-600" v-if="errors != ''">{{ errors }}</span>
    <BaseButton
      type="submit"
      :disabled="store.isAdding"
    >
        {{ store.isAdding ? 'Dodawanie...' : 'Dodaj konto' }}
    </BaseButton>
  </form>
  </div>

  <div v-if="store.isLoading" class="rounded-xl border border-slate-200 bg-white p-5 text-sm text-slate-500 shadow-sm">
    <span>Ładowanie kont...</span>
  </div>
  <div v-else-if="store.error" class="rounded-xl border border-red-200 bg-red-50 p-5 text-sm text-red-700">
    <span>{{ store.error }}</span>
  </div>
  <div v-else-if="store.accounts.length === 0" class="rounded-xl border border-dashed border-slate-300 bg-white p-8 text-center text-slate-500">
    Nie masz jeszcze żadnych kont
  </div>
  <div v-else>
    <ul class="space-y-3">
      <li v-for="bankAccount in store.accounts"
          :key="bankAccount.id"
          class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-3 shadow-sm transition hover:border-slate-300 hover:shadow"
      >
        <template v-if="editingBankAccountId === bankAccount.id">
          <form @submit.prevent="saveBankAccountEdit" class="flex w-full flex-col gap-3 sm:flex-row sm:items-center">
            <input type="text" v-model="editedBankAccountName" class="w-full rounded-lg border-2 border-blue-500 px-3 py-2 text-slate-950 outline-none focus:ring-3 focus:ring-blue-100">
            <span v-if="editingError" class="text-sm text-red-600">{{ editingError }}</span>
            <div class="flex gap-2 sm:ml-auto">
              <BaseButton type="submit" :disabled="store.isUpdating">
                {{  store.isUpdating ? 'Zapisywanie' : 'Zapisz' }}
              </BaseButton>
              <BaseButton type="button" @click="cancelBankAccountEdit">
                Anuluj
              </BaseButton>
            </div>
          </form>
        </template>

        <template v-else>
          <span class="min-w-0 truncate font-medium text-slate-800">{{ bankAccount.name }}</span>
          <div class="ml-auto flex gap-2">
            <BaseButton type="button" @click="startEditing(bankAccount.id, bankAccount.name)">
              Edytuj
            </BaseButton>
            <BaseButton
              type="button"
              variant="danger"
              @click="confirmDeleteBankAccount(bankAccount.id, bankAccount.name)"
              :disabled="store.isDeleting"
            >
              {{ store.isDeleting ? 'Usuwanie...' : "Usuń" }}
            </BaseButton>
          </div>
        </template>
      </li>
    </ul>
  </div>
  </div>

</template>
