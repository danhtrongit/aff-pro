import { affApi, jsonToFormData } from './index.js'

export function getBanners(data) {
  return affApi.post('', jsonToFormData({ action: 'aff_get_banners', data }))
}

