import { affApi, jsonToFormData } from './index.js'

export function getBalanceHistory(data) {
  return affApi.post('', jsonToFormData(Object.assign({ action: 'aff_get_balance_history'}, data)))
}


export function getUserOrderHistory(data) {
  return affApi.post('', jsonToFormData(Object.assign({ action: 'aff_get_user_order_history'}, data)))
}

export function getUserOrderIncome(data) {
  return affApi.post('', jsonToFormData(Object.assign({ action: 'aff_get_user_order_income'}, data)))
}
