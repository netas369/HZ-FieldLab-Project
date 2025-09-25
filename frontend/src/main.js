import './assets/main.css'
import router from './router/router.js'  // Import your router

import { createApp } from 'vue'
import App from './App.vue'

createApp(App)
    .use(router)
    .mount('#app')
