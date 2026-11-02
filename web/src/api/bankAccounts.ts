import type { BankAccount } from "@/types/bankAccounts";
import { http } from '@/api/http';

interface BankAccountsResponse {
  data: BankAccount[]
}

interface BankAccountResponse {
  data: BankAccount
}

export async function getBankAccounts(): Promise<BankAccount[]> {
  const response = await http.get<BankAccountsResponse>('/bank-accounts')

  return response.data
}

export async function createBankAccount(name: string): Promise<BankAccount> {
  const response = await http.post<BankAccountResponse>('/bank-accounts', {
    name: name
  })

  return response.data
}
