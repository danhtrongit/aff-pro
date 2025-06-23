import { affApi, jsonToFormData } from './index.js'

export function getPayments(data) {
  return affApi.post('', jsonToFormData(Object.assign({ action: 'aff_get_payments'}, data)))
}



export function approvePayment(data) {
  return affApi.post('', jsonToFormData(Object.assign({ action: 'aff_approve_payments'}, data)))
}
