import type { Import } from "@/types/import";
import { http } from '@/api/http';

interface ImportResponse {
  data: Import
}

export async function createImport(bankAccountId: number, file: File): Promise<Import> {
  const formData = new FormData()
    formData.append('bank_account_id', String(bankAccountId))
    formData.append('file', file)

  const response = await http.post<ImportResponse>('/imports', formData)

  return response.data
}
