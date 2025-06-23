import { affApi, jsonToFormData } from './index.js'

export function getBalanceHistory(data) {
  return affApi.post('', jsonToFormData(Object.assign({ action: 'aff_get_balance_history'}, data)))
}
