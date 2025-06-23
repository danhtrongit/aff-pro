import { affApi, jsonToFormData } from './index.js'

export function getDashboardInfo(data) {
  return affApi.post('', jsonToFormData(Object.assign({ action: 'aff_get_dashboard_info', f: true}, data)))
}

export function getDashboardInfo2(data) {
  return affApi.post('', jsonToFormData(Object.assign({ action: 'aff_get_dashboard_info_2', f: true}, data)))
}

export function getListOrderComments(id) {
  return affApi.post('', jsonToFormData({ action: 'aff_list_order_comments', id}))
}
