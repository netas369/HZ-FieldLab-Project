import { createRouter, createWebHistory } from 'vue-router'

// Layout
import AppLayout from '@/components/core/AppLayout.vue'

// Pages
import OverviewPage from '@/views/OverviewPage.vue'
import AlarmsPage from '@/views/AlarmsPage.vue'
import MaintenancePage from '@/views/MaintenancePage.vue'
import AnalyticsPage from '@/views/AnalyticsPage.vue'
import ReportsPage from '@/views/ReportsPage.vue'
import SettingsPage from '@/views/SettingsPage.vue'
import TurbineDetailPage from '@/views/TurbineDetailPage.vue'
import LoginPage from '@/views/LoginPage.vue'
import DataImportPage from "@/views/DataImportPage.vue";
import ForgotPasswordPage from "@/components/login/ForgotPassword.vue";
import ResetPasswordPage from "@/components/login/ResetPassword.vue";

const routes = [
  {
    path: '/login',
    name: 'login',
    component: LoginPage,
    meta: {
      title: 'login',
      icon: 'dashboard',
      requiresAuth: false
    }
  },
  {
    path: '/forgot-password',
    name: 'ForgotPassword',
    component: ForgotPasswordPage,
    meta: {
      title: 'Forgot Password',
      requiresAuth: false
    }
  },
  {
    path: '/reset-password',
    name: 'ResetPassword',
    component: ResetPasswordPage,
    meta: {
      title: 'Reset Password',
      requiresAuth: false
    }
  },
  {
    path: '/',
    component: AppLayout,
    meta: {
      requiresAuth: true 
    },
    children: [
      {
        path: '',
        redirect: '/overview'
      },
      {
        path: 'overview',
        name: 'Overview',
        component: OverviewPage,
        meta: { 
          title: 'Overview',
          icon: 'dashboard',
      requiresAuth: true
        }
      },
      {
        path: 'alarms',
        name: 'Alarms',
        component: AlarmsPage,
        meta: { 
          title: 'Alarms',
          icon: 'alert',
          requiresAuth: true 
        }
      },
      {
        path: 'import',
        name: 'DataImport',
        component: DataImportPage,
        meta: {
          title: 'Import',
          icon: 'import',
          requiresAuth: true,
          roles: ['admin', 'data_analyst']
        }
      },
      {
        path: 'maintenance',
        name: 'Maintenance',
        component: MaintenancePage,
        meta: { 
          title: 'Maintenance',
          icon: 'wrench',
          requiresAuth: true 
        }
      },
      {
        path: 'analytics',
        name: 'Analytics',
        component: AnalyticsPage,
        meta: { 
          title: 'Analytics',
          icon: 'chart',
          requiresAuth: true 
        }
      },
      // {
      //   path: 'reports',
      //   name: 'Reports',
      //   component: ReportsPage,
      //   meta: {
      //     title: 'Reports',
      //     icon: 'file',
      // requiresAuth: true
      //   }
      // },
      {
        path: 'settings',
        name: 'Settings',
        component: SettingsPage,
        meta: {
          title: 'Settings',
          icon: 'settings',
          requiresAuth: true,
          roles: ['admin']
        }
      },
      {
        path: 'turbine/:id',
        name: 'TurbineDetail',
        component: TurbineDetailPage,
        meta: { 
          title: 'Turbine Details',
          requiresAuth: true
        },
        props: true,
      }
    ]
  },
  // 404 redirect
  {
    path: '/:pathMatch(.*)*',
    redirect: '/overview'
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    // Scroll to top on route change, or restore position
    if (savedPosition) {
      return savedPosition
    }
    return { top: 0, behavior: 'smooth' }
  }
})

router.beforeEach((to, from, next) => {
  const baseTitle = 'WindFlow SCADA'
  document.title = to.meta.title
    ? `${to.meta.title} - ${baseTitle}`
    : baseTitle

  const isAuthenticated = !!JSON.parse(localStorage.getItem('user'))

  if (to.meta.requiresAuth && !isAuthenticated) {
    next('/login')
  }
  else if (!to.path === '/login'  && isAuthenticated) {
   next('/') 
  } else {
    next()
  }

})

// After navigation hook (for analytics, etc.)
router.afterEach((to, from) => {
  // Log route changes
  console.log(`Navigation: ${from.path} → ${to.path}`)
  
  // You can send analytics here
  // analytics.track('page_view', { path: to.path })
})

export default router