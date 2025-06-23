import { affApi, jsonToFormData } from './index.js'

export function getPayments(data) {
  return affApi.post('', jsonToFormData(Object.assign({ action: 'aff_get_payments', f: true}, data)))
}

export function createPaymentRequest(data) {
  return affApi.post('', jsonToFormData({ action: 'aff_create_payment_request', data}))
}
