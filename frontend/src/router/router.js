import { createRouter, createWebHistory } from 'vue-router'
import OverviewTab from '@/components/overview/OverviewTab.vue'
import Poc from '@/components/Poc.vue'

const routes = [
    {
        path: '/',
        name: 'Overview',
        component: Poc
    }
]

const router = createRouter({
    history: createWebHistory(),
    routes
})

export default router