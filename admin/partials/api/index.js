const { RV_CONFIGS } = window 
const axiosConfig = {
  baseURL: RV_CONFIGS.ajax_url,
  timeout: 30000,
};
const affApi = axios.create(axiosConfig)

const buildFormData = (formData, data, parentKey) => {
  if (data && typeof data === 'object' && !(data instanceof Date) && !(data instanceof File)) {
    Object.keys(data).forEach(key => {
      buildFormData(formData, data[key], parentKey ? `${parentKey}[${key}]` : key);
    });
  } else {
    const value = data == null ? '' : data;

    formData.append(parentKey, value);
  }
}
const jsonToFormData = (data) => {
  const formData = new FormData();

  buildFormData(formData, data);

  return formData;
}


export { affApi, jsonToFormData } 