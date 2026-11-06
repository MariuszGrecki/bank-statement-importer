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
          class="border rounded-xl p-2 flex items-center"
      >
        <template v-if="editingBankAccountId === bankAccount.id">
          <form @submit.prevent="saveBankAccountEdit" class="flex items-center gap-2">
            <input type="text" v-model="editedBankAccountName" class="border-2 border-blue-500 rounded px-3 py-2">
            <span v-if="editingError" class="text-red-600">{{ editingError }}</span>
            <div class="ml-auto flex gap-2">
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
          <span>{{ bankAccount.name }}</span>
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
