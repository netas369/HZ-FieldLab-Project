import { createRouter, createWebHistory } from 'vue-router'
import Prototype from '@/views/Prototype.vue'
import Poc from '@/views/Poc.vue'

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
    },
    {
        path: '/poc',
        name: 'Poc',
        component: Poc
    }
]

const router = createRouter({
    history: createWebHistory(),
    routes
})

export default router