<template>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
            <!-- Logo/Header -->
            <div class="mb-6 text-center">
                <h1 class="text-2xl font-bold text-gray-800">Set New Password</h1>
                <p class="text-sm text-gray-600 mt-1">Enter your reset token and new password</p>
            </div>

            <!-- Success State -->
            <div v-if="success" class="space-y-4">
                <div class="p-4 bg-green-50 border border-green-200 rounded-md">
                    <p class="text-sm text-green-800 font-medium mb-2">✓ Password reset successful!</p>
                    <p class="text-sm text-green-700">You can now log in with your new password.</p>
                </div>

                <button @click="goToLogin"
                    class="w-full px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition">
                    Go to Login
                </button>
            </div>

            <!-- Reset Form -->
            <form v-else @submit.prevent="handleSubmit">
                <!-- Token Input -->
                <div class="mb-4">
                    <label for="token" class="block text-sm font-medium text-gray-700 mb-1">
                        Reset Token
                    </label>
                    <input id="token" v-model="form.token" type="text"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 border font-mono text-sm"
                        :class="{ 'border-red-500': errors.token }" placeholder="Enter your reset token" required
                        autofocus />
                    <p v-if="errors.token" class="mt-1 text-sm text-red-600">
                        {{ errors.token }}
                    </p>
                </div>

                <!-- Email Input -->
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                        Email
                    </label>
                    <input id="email" v-model="form.email" type="email"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 border"
                        :class="{ 'border-red-500': errors.email }" required />
                    <p v-if="errors.email" class="mt-1 text-sm text-red-600">
                        {{ errors.email }}
                    </p>
                </div>

                <!-- New Password Input -->
                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                        New Password
                    </label>
                    <input id="password" v-model="form.password" type="password"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 border"
                        :class="{ 'border-red-500': errors.password }" required />
                    <p v-if="errors.password" class="mt-1 text-sm text-red-600">
                        {{ errors.password }}
                    </p>
                    <p class="mt-1 text-xs text-gray-500">
                        Must be at least 8 characters long
                    </p>
                </div>

                <!-- Confirm Password Input -->
                <div class="mb-4">
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                        Confirm Password
                    </label>
                    <input id="password_confirmation" v-model="form.password_confirmation" type="password"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 border"
                        :class="{ 'border-red-500': errors.password_confirmation }" required />
                    <p v-if="errors.password_confirmation" class="mt-1 text-sm text-red-600">
                        {{ errors.password_confirmation }}
                    </p>
                </div>

                <!-- Error Message -->
                <div v-if="errors.general" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-md">
                    <p class="text-sm text-red-600">{{ errors.general }}</p>
                </div>

                <!-- Submit Button -->
                <div class="flex flex-col gap-3">
                    <button type="submit" :disabled="loading"
                        class="w-full inline-flex justify-center items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span v-if="loading" class="mr-2">
                            <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                        </span>
                        {{ loading ? 'Resetting Password...' : 'Reset Password' }}
                    </button>

                    <button type="button" @click="goToLogin"
                        class="w-full px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 transition">
                        Back to Login
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'

const router = useRouter()
const route = useRoute()

const form = reactive({
    token: '',
    email: '',
    password: '',
    password_confirmation: ''
})

const errors = reactive({
    token: '',
    email: '',
    password: '',
    password_confirmation: '',
    general: ''
})

const loading = ref(false)
const success = ref(false)

onMounted(() => {
    // Pre-fill token if passed via query params
    if (route.query.token) {
        form.token = route.query.token
    }
})

const handleSubmit = async () => {
    // Clear previous errors
    Object.keys(errors).forEach(key => errors[key] = '')
    loading.value = true

    // Client-side validation
    if (form.password.length < 8) {
        errors.password = 'Password must be at least 8 characters'
        loading.value = false
        return
    }

    if (form.password !== form.password_confirmation) {
        errors.password_confirmation = 'Passwords do not match'
        loading.value = false
        return
    }

    try {
        const response = await fetch(`${import.meta.env.VITE_API_BASE_URL?.replace('/api', '') || 'http://localhost:8000'}/user/reset-password`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                token: form.token,
                email: form.email,
                password: form.password,
                password_confirmation: form.password_confirmation
            })
        })

        const data = await response.json()

        if (!response.ok) {
            if (data.errors) {
                Object.assign(errors, data.errors)
            } else {
                errors.general = data.message || 'Failed to reset password'
            }
        } else {
            success.value = true
        }
    } catch (error) {
        errors.general = 'An error occurred. Please try again.'
        console.error('Reset password error:', error)
    } finally {
        loading.value = false
    }
}

const goToLogin = () => {
    router.push('/login')
}
</script>