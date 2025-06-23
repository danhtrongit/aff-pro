import { affApi, jsonToFormData } from './index.js'

export function getProductById(id) {
  return affApi.post('', jsonToFormData({ action: 'aff_get_product_by_id', id }))
}


export function getProducts(data) {
  return affApi.post('', jsonToFormData({ action: 'aff_get_products', data }))
}

export function getProductCat(type) {
  return affApi.post('', jsonToFormData({ action: 'aff_get_product_category', type }))
}

export function getCommissionSetting(type) {
  return affApi.post('', jsonToFormData({ action: 'aff_get_commission_setting', type }))
}


export function saveCommissionSetting(data, type) {
  return affApi.post('', jsonToFormData({ action: 'aff_save_commission_setting', data, type }))
}