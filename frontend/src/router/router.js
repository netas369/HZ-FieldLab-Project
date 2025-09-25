import { createRouter, createWebHistory } from 'vue-router'
import Prototype from '@/views/Prototype.vue'

const routes = [
    // {
    //     path: '/',
    //     name: 'Home',
    //     component: () => import('@/views/Prototype.vue') // or whatever your home component is
    // },
    {
        path: '/prototype',
        name: 'Prototype',
        component: Prototype
    }
]

const router = createRouter({
    history: createWebHistory(),
    routes
})

export default router