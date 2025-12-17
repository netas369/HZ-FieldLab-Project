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

const routes = [
  {
    path: '/',
    component: AppLayout,
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
          icon: 'dashboard'
        }
      },
      {
        path: 'login',
        name: 'login',
        component: LoginPage,
        meta: {
          title: 'login',
          icon: 'dashboard'
        }
      },
      {
        path: 'alarms',
        name: 'Alarms',
        component: AlarmsPage,
        meta: { 
          title: 'Alarms',
          icon: 'alert'
        }
      },
      {
        path: 'import',
        name: 'DataImport',
        component: DataImportPage,
        meta: {
          title: 'Import',
          icon: 'import'
        }
      },
      {
        path: 'maintenance',
        name: 'Maintenance',
        component: MaintenancePage,
        meta: { 
          title: 'Maintenance',
          icon: 'wrench'
        }
      },
      {
        path: 'analytics',
        name: 'Analytics',
        component: AnalyticsPage,
        meta: { 
          title: 'Analytics',
          icon: 'chart'
        }
      },
      // {
      //   path: 'reports',
      //   name: 'Reports',
      //   component: ReportsPage,
      //   meta: {
      //     title: 'Reports',
      //     icon: 'file'
      //   }
      // },
      {
        path: 'settings',
        name: 'Settings',
        component: SettingsPage,
        meta: {
          title: 'Settings',
          icon: 'settings'
        }
      },
      {
        path: 'turbine/:id',
        name: 'TurbineDetail',
        component: TurbineDetailPage,
        meta: { 
          title: 'Turbine Details'
        },
        props: true // Pass route params as props
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

// Global navigation guard
router.beforeEach((to, from, next) => {
  // Update document title
  const baseTitle = 'WindFlow SCADA'
  document.title = to.meta.title 
    ? `${to.meta.title} - ${baseTitle}` 
    : baseTitle

  // You can add authentication checks here
  if (to.meta.requiresAuth && !isAuthenticated()) {
    return next('/login')
  }

  next()
})

// After navigation hook (for analytics, etc.)
router.afterEach((to, from) => {
  // Log route changes
  console.log(`Navigation: ${from.path} → ${to.path}`)
  
  // You can send analytics here
  // analytics.track('page_view', { path: to.path })
})

export default router