<template>
  <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
    <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
      <!-- Logo/Header -->
      <div class="mb-6 text-center">
        <h1 class="text-2xl font-bold text-gray-800">
          Password Reset
        </h1>
        <p class="text-sm text-gray-600 mt-1">
          Enter your email to reset your password
        </p>
      </div>

      <!-- Success State -->
      <div
        v-if="resetToken"
        class="space-y-4"
      >
        <div class="p-4 bg-green-50 border border-green-200 rounded-md">
          <p class="text-sm text-green-800 font-medium mb-2">
            Password reset token generated!
          </p>
          <p class="text-sm text-green-700">
            Use this token to reset your password:
          </p>
        </div>

        <div class="bg-gray-50 border border-gray-300 rounded-md p-4">
          <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-semibold text-gray-700 uppercase">Reset Token</span>
            <button
              class="text-xs text-indigo-600 hover:text-indigo-700 font-medium"
              @click="copyToken"
            >
              {{ copied ? '✓ Copied' : 'Copy' }}
            </button>
          </div>
          <code class="text-sm text-gray-900 font-mono break-all">{{ resetToken }}</code>
        </div>

        <div class="p-3 bg-blue-50 border border-blue-200 rounded-md">
          <p class="text-xs text-blue-800">
            <strong>Note:</strong> This token expires in 30 minutes. Save it now.
          </p>
        </div>

        <div class="flex flex-col gap-2 pt-2">
          <button
            class="w-full px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition"
            @click="goToResetPassword"
          >
            Reset Password Now
          </button>
          <button
            class="w-full px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 transition"
            @click="goToLogin"
          >
            Back to Login
          </button>
        </div>
      </div>

      <!-- Request Form -->
      <form
        v-else
        @submit.prevent="handleSubmit"
      >
        <!-- Email Input -->
        <div class="mb-4">
          <label
            for="email"
            class="block text-sm font-medium text-gray-700 mb-1"
          >
            Email Address
          </label>
          <input
            id="email"
            v-model="form.email"
            type="email"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 border"
            :class="{ 'border-red-500': errors.email }"
            required
            autofocus
          >
          <p
            v-if="errors.email"
            class="mt-1 text-sm text-red-600"
          >
            {{ errors.email }}
          </p>
        </div>

        <!-- Error Message -->
        <div
          v-if="errors.general"
          class="mb-4 p-3 bg-red-50 border border-red-200 rounded-md"
        >
          <p class="text-sm text-red-600">
            {{ errors.general }}
          </p>
        </div>

        <!-- Submit Button -->
        <div class="flex flex-col gap-3">
          <button
            type="submit"
            :disabled="loading"
            class="w-full inline-flex justify-center items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span
              v-if="loading"
              class="mr-2"
            >
              <svg
                class="animate-spin h-4 w-4 text-white"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
              >
                <circle
                  class="opacity-25"
                  cx="12"
                  cy="12"
                  r="10"
                  stroke="currentColor"
                  stroke-width="4"
                />
                <path
                  class="opacity-75"
                  fill="currentColor"
                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                />
              </svg>
            </span>
            {{ loading ? 'Generating Token...' : 'Generate Reset Token' }}
          </button>

          <button
            type="button"
            class="w-full px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 transition"
            @click="goToLogin"
          >
            Back to Login
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

const form = reactive({
  email: '',
})

const errors = reactive({
  email: '',
  general: '',
})

const loading = ref(false)
const resetToken = ref(null)
const copied = ref(false)

const handleSubmit = async () => {
  // Clear previous errors
  errors.email = ''
  errors.general = ''
  loading.value = true

  try {
    const response = await fetch('http://localhost:8000/user/forgot-password', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        email: form.email,
      }),
    })

    const data = await response.json()
    if (!response.ok) {
      if (data.errors) {
        Object.assign(errors, data.errors)
      } else {
        errors.general = data.message || 'Failed to generate reset token'
      }
    } else {
      resetToken.value = data.token
    }
  } catch (error) {
    errors.general = 'An error occurred. Please try again.'
    console.error('Forgot password error:', error)
  } finally {
    loading.value = false
  }
}

const copyToken = () => {
  navigator.clipboard.writeText(resetToken.value)
  copied.value = true
  setTimeout(() => {
    copied.value = false
  }, 2000)
}

const goToLogin = () => {
  router.push('/login')
}

const goToResetPassword = () => {
  router.push({
    name: 'ResetPassword',
    query: { token: resetToken.value },
  })
}
</script>
