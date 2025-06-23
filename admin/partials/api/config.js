import { affApi, jsonToFormData } from './index.js'

export function setConfigs(data) {
  return affApi.post('', jsonToFormData({ action: 'aff_set_configs', data }))
}


export function getConfigs(data) {
  return affApi.post('', jsonToFormData({ action: 'aff_get_configs', data }))
}


export function initUserRelationship(data) {
  return affApi.post('', jsonToFormData({ action: 'aff_init_user_relationship', data }))
}
