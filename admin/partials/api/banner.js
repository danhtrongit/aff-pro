import { affApi, jsonToFormData } from './index.js'

export function addBanner(data) {
  return affApi.post('', jsonToFormData({ action: 'aff_add_banner', data }))
}


export function getBanners(data) {
  return affApi.post('', jsonToFormData({ action: 'aff_get_banners', data }))
}


export function removeBanner(id) {
  return affApi.post('', jsonToFormData({ action: 'aff_remove_banner', id }))
}
