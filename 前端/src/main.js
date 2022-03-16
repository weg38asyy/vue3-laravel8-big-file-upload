import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import ElementPlus from 'element-plus'
import 'element-plus/dist/index.css'
import request from './utils/request'

const app = createApp(App)

// Global Setting
app.config.globalProperties.$request = request

// 主要用在Setup，提供父子組件傳遞(上面那個在Setup會讀取不到，建議用這個方式讀取較安全）
app.provide('$request', app.config.globalProperties.$request)

app.use(router).use(ElementPlus).mount('#app')
