import { affApi, jsonToFormData } from './index.js'



export function getConfigs(data) {
  return affApi.post('', jsonToFormData({ action: 'aff_get_configs', data }))
}
