import { affApi, jsonToFormData } from './index.js'

export function getUserTree(parameters) {
  return affApi.post('', jsonToFormData(Object.assign({ action: 'aff_get_user_tree'}, parameters)))

}

export function getUserTree2(parameters) {
  return affApi.post('', jsonToFormData(Object.assign({ action: 'aff_get_user_tree_2'}, parameters)))

}

export function getUserProfile(id = '') {
  return affApi.post('', jsonToFormData(Object.assign({ action: 'aff_get_user_profile'}, id)))

}


export function updateUserProfile(data) {
  return affApi.post('', jsonToFormData(Object.assign({ action: 'aff_update_user_profile'}, data)))

}
export function getUserBankInfo() {
  return affApi.post('', jsonToFormData(Object.assign({ action: 'aff_get_user_bank_info'})))

}

export function login(data) {
  return affApi.post('', jsonToFormData(Object.assign({ action: 'aff_user_login'}, data)))

}

export function changePassword(data) {
  return affApi.post('', jsonToFormData(Object.assign({ action: 'aff_user_change_password'}, data)))

}



export function lost_password(data) {
  return affApi.post('', jsonToFormData(Object.assign({ action: 'aff_lost_password'}, data)))

}

export function register(data) {
  return affApi.post('', jsonToFormData(Object.assign({ action: 'aff_user_register_2'}, data)))

}
export function logout(data) {
  return affApi.post('', jsonToFormData({ action: 'aff_user_logout'}))

}