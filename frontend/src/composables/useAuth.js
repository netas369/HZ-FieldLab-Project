import { computed } from 'vue'

export function useAuth() {
  const user = computed(() => {
    const storedUser = localStorage.getItem('user')
    return storedUser ? JSON.parse(storedUser) : null
  })

  const role = computed(() => user.value?.role)

  const isAdmin = computed(() => role.value === 'admin')
  const isDataAnalyst = computed(() => role.value === 'data_analyst')
  const isUser = computed(() => role.value === 'user')

  const hasRole = (roles) => {
    if (!role.value) return false
    return Array.isArray(roles) ? roles.includes(role.value) : roles === role.value
  }

  const canAccessAnalytics = computed(() => hasRole(['admin', 'data_analyst']))

  return {
    user,
    role,
    isAdmin,
    isDataAnalyst,
    isUser,
    hasRole,
    canAccessAnalytics,
  }
}
