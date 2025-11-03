import { createRouter, createWebHistory } from 'vue-router'
import Prototype from '@/views/Prototype.vue'
import HomePage from "@/components/HomePage.vue";
import HistoryHomePage from "@/components/HistoryHomePage.vue";

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
    },
    {
        path: '/historical',
        name: 'HistoryPage',
        component: HistoryHomePage
    }
]

const router = createRouter({
    history: createWebHistory(),
    routes
})

export default router