export interface Import {
  id: number,
  bank_account_id: number,
  status: 'queued' | 'processing' | 'completed' | 'failed' | 'rolled_back',
  original_filename: string
}
