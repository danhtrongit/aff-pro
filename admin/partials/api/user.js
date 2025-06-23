import { affApi, jsonToFormData } from './index.js'

export function getUsers($parameters) {
  return affApi.post('', jsonToFormData(Object.assign({ action: 'aff_get_users'}, $parameters)))
}

export function getUserTree(parameters) {
  return affApi.post('', jsonToFormData(Object.assign({ action: 'aff_get_user_tree'}, parameters)))

}
export function getUserTree2(parameters) {
  return affApi.post('', jsonToFormData(Object.assign({ action: 'aff_get_user_tree_2'}, parameters)))

}
// export function getUser(id) {
//   return affApi.post('', jsonToFormData({ action: 'aff_get_user', id}))
// }

export function getUser(type = 'ID', value) {
  return affApi.post('', jsonToFormData({ action: 'aff_get_user', type, value}))
}

export function setRelationship(descendant_id, ancestor_id) {
  return affApi.post('', jsonToFormData({ action: 'aff_set_user_relationship', ancestor_id, descendant_id}))
}


export function updateUserStatus(data) {
  return affApi.post('', jsonToFormData({ action: 'aff_update_user_status', data}))
}

export function updateUser(data) {
  return affApi.post('', jsonToFormData({ action: 'aff_update_user', data}))
}

export function deleteUser(id) {
  return affApi.post('', jsonToFormData({ action: 'aff_delete_user', id}))
}


export function register(data) {
  return affApi.post('', jsonToFormData(Object.assign({ action: 'aff_user_register_2'}, data)))

}