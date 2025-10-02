import { createRouter, createWebHistory } from 'vue-router'
import Prototype from '@/views/Prototype.vue'
import PrototypeB from '@/views/ControlRoom.vue'

const routes = [
    {
        path: '/',
        name: 'Dashboard',
        component: () => import('@/views/DashboardView.vue')
    },
    {
        path: '/map',
        name: 'Map',
        component: () => import('@/views/MapView.vue')
    },
    {
        path: '/predictive',
        name: 'Predictive',
        component: () => import('@/views/PredictiveView.vue')
    },
    {
        path: '/analytics',
        name: 'Analytics',
        component: () => import('@/views/AnalyticsView.vue')
    },
    {
        path: '/history',
        name: 'History',
        component: () => import('@/views/HistoryView.vue')
    },
    {
        path: '/scheduler',
        name: 'Scheduler',
        component: () => import('@/views/SchedulerView.vue')
    },
    {
        path: '/turbine/:id',
        name: 'TurbineDetail',
        component: () => import('@/views/DetailView.vue')
    },
    {
        path: '/prototype',
        name: 'Prototype',
        component: Prototype
    },
    {
        path: '/prototypeb',
        name: 'Prototype B',
        component: PrototypeB
    }
]

const router = createRouter({
    history: createWebHistory(),
    routes
})

export default router