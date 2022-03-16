/**
 * Axios封裝
 */
import axios from 'axios'
import config from '@/config'
import { ElMessage } from 'element-plus'
const NETWORK_ERROR = '網路請求異常，請稍後重新嘗試！'

const service = axios.create({
    // Dev or Prod api request url
    baseURL: config.baseApi,
    // Timeout of over 2 minutes
    timeout: 120000,
})

// 回傳攔截
service.interceptors.response.use(res => {
    const { code, result, message } = res.data
    if (res.status === 200) {
        return res.data
    } else {
        ElMessage.error(message || NETWORK_ERROR)
        return Promise.reject(message || NETWORK_ERROR)
    }
})

/**
 * 請求回傳
 * @param   {*}  options 　請求配置
 * @return  {obj}          Axios物件
 */
function request(options) {
    options.method = options.method || 'GET'

    if (options.method.toUpperCase() === 'get') {
        options.params = options.data
    }

    return service(options)
}

;['get', 'post', 'put', 'delete', 'patch'].forEach(item => {
    request[item] = (url, data, options) => {
        return request({
            url,
            data,
            method: item,
            ...options,
        })
    }
})

export default request
