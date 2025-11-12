import { createRouter, createWebHistory } from 'vue-router'
import Prototype from '@/views/Prototype.vue'
import HomePage from "@/components/HomePage.vue";
import HistoryHomePage from "@/components/history/HistoryHomePage.vue";
import MainPage from "@/components/singleTurbine/MainPage.vue";

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
    },
    {
        path: '/turbine/:id/scadaData',
        name: 'scadaData',
        component: MainPage
    }
]

const router = createRouter({
    history: createWebHistory(),
    routes
})

export default router