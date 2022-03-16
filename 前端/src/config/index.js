/**
 * [環境配置封裝]
 */
const EnvConfig = {
    // 開發環境
    dev: {
        baseApi: 'http://localhost:8001/api',
    },
    // 正式環境
    prod: {
        baseApi: '/api',
    },
}
const env = import.meta.env.MODE || 'prod'

export default {
    env,
    namespace: 'vue3',
    ...EnvConfig[env],
}
