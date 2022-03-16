import { createRouter, createWebHashHistory } from 'vue-router'
// Page load
import home from '@/components/home.vue'
import index from '@/views/index.vue'

const routes = [
    {
        name: 'home',
        path: '/',
        meta: {
            title: '首頁',
        },
        component: home,
        redirect: '/index',
        children: [
            {
                name: 'index',
                path: '/index',
                meta: {
                    title: '歡迎頁',
                },
                component: index,
            },
        ],
    },
]

// 建立Router Object
const router = createRouter({
    // 路由模式為Hash模式
    history: createWebHashHistory(),
    routes,
})

export default router
