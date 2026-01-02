<template>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
            <!-- Logo/Header -->
            <div class="mb-6 text-center">
                <h1 class="text-2xl font-bold text-gray-800">Turbine Maintenance System</h1>
                <p class="text-sm text-gray-600 mt-1">Sign in to your account</p>
            </div>

            <!-- Login Form -->
            <form @submit.prevent="handleSubmit">
                
                <!-- Email Input -->
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                        Email
                    </label>
                    <input id="email" v-model="form.email" type="email"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 border"
                        :class="{ 'border-red-500': errors.email }" required autofocus />
                    <p v-if="errors.email" class="mt-1 text-sm text-red-600">
                        {{ errors.email }}
                    </p>
                </div>

                <!-- Password Input -->
                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                        Password
                    </label>
                    <input id="password" v-model="form.password" type="password"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 border"
                        :class="{ 'border-red-500': errors.password }" required />
                    <p v-if="errors.password" class="mt-1 text-sm text-red-600">
                        {{ errors.password }}
                    </p>
                </div>

                <!-- Remember Me Checkbox -->
                <div class="mb-4 flex items-center">
                    <input id="remember" v-model="form.remember" type="checkbox"
                        class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                    <label for="remember" class="ml-2 block text-sm text-gray-700">
                        Remember me
                    </label>
                </div>

                <!-- Error Message -->
                <div v-if="errors.general" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-md">
                    <p class="text-sm text-red-600">{{ errors.general }}</p>
                </div>

                <!-- Submit Button -->
                <div class="flex items-center justify-between">
                    <router-link to="/forgot-password" class="text-sm text-indigo-600 hover:text-indigo-500">
                        Forgot password?
                    </router-link>
                    <button type="submit" :disabled="loading"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-50 disabled:cursor-not-allowed">
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
                        {{ loading ? 'Signing in...' : 'Sign in' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
export default {
    name: 'LoginPage',

    data() {
        return {
            form: {
                email: '',
                password: '',
                remember: false
            },
            errors: {},
            loading: false
        }
    },

    methods: {
        async handleSubmit() {
            // Clear previous errors
            this.errors = {};
            this.loading = true;

            try {
                await this.getCsrfToken();
                const csrfToken = this.getCsrfTokenFromCookie();              

                const response = await fetch('http://localhost:8000/user/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-XSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'include',
                    body: JSON.stringify({
                        email: this.form.email,
                        password: this.form.password,
                        remember: this.form.remember
                    })
                });


                const data = await response.json();

                if (response.ok) {
                    console.log('Login successful');
                    
                    if (data.user) {
                        localStorage.setItem('user', JSON.stringify(data.user));
                    }
                    
                    if (data.token) {
                        localStorage.setItem('token', data.token);
                    }
                    window.location.href = 'http://localhost:5173/dashboard';
                } else {
                    if (data.errors) {
                        this.errors = data.errors;
                    } else {
                        this.errors.general = data.message || 'These credentials do not match our records.';
                    }
                }
            } catch (error) {
                console.error('Login error:', error);
                this.errors.general = 'An error occurred. Please try again.';
            } finally {
                this.loading = false;
            }
        },

        async getCsrfToken() {
            await fetch('http://localhost:8000/sanctum/csrf-cookie', {
                credentials: 'include'
            });
        },

        getCsrfTokenFromCookie() {
            const name = 'XSRF-TOKEN';
            const value = `; ${document.cookie}`;
            const parts = value.split(`; ${name}=`);
            if (parts.length === 2) {
                return decodeURIComponent(parts.pop().split(';').shift());
            }
            return '';
        }
    }
}
</script>
