import { createRouter, createWebHistory } from 'vue-router'
import Prototype from '@/views/Prototype.vue'
import HomePage from "@/components/HomePage.vue";

const routes = [
    {
        path: '/',
        name: 'Home',
        component: HomePage
    },
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