<template>
  <div class="w-full max-w-md mx-auto animate-slide-in-up">
    <div class="bg-white/90 backdrop-blur-xl rounded-3xl shadow-2xl p-8 border border-purple-100">
      <!-- Logo & Title -->
      <div class="text-center mb-8">
        <div class="w-20 h-20 bg-gradient-to-br from-purple-600 to-pink-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
          <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
          </svg>
        </div>
        <h2 class="text-3xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent mb-2">
          Welcome to Mini Wallet
        </h2>
        <p class="text-gray-600">Secure digital payment system</p>
      </div>

      <!-- Toggle Buttons -->
      <div class="flex space-x-2 bg-gray-100 rounded-xl p-1 mb-6">
        <button
          @click="isLogin = true"
          :class="[
            'flex-1 py-2 px-4 rounded-lg font-medium transition-all duration-200',
            isLogin
              ? 'bg-white text-purple-600 shadow-md'
              : 'text-gray-600 hover:text-gray-900'
          ]"
        >
          Login
        </button>
        <button
          @click="isLogin = false"
          :class="[
            'flex-1 py-2 px-4 rounded-lg font-medium transition-all duration-200',
            !isLogin
              ? 'bg-white text-purple-600 shadow-md'
              : 'text-gray-600 hover:text-gray-900'
          ]"
        >
          Register
        </button>
      </div>

      <!-- Error Messages -->
      <div v-if="errorMessage" class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm">
        {{ errorMessage }}
      </div>

      <!-- Forms Container -->
      <div
        class="relative overflow-hidden transition-all duration-500 ease-in-out"
        :style="{ height: containerHeight }"
      >
        <div
          class="flex gap-8 transition-transform duration-500 ease-in-out"
          :style="{ transform: isLogin ? 'translateX(0)' : 'translateX(calc(-100% - 2rem))' }"
        >
          <!-- Login Form -->
          <div class="w-full flex-shrink-0">
            <form @submit.prevent="handleLogin" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
          <input
            v-model="form.email"
            type="email"
            required
            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
            placeholder="your@email.com"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
          <input
            v-model="form.password"
            type="password"
            required
            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
            placeholder="••••••••"
          />
        </div>

        <button
          type="submit"
          :disabled="loading"
          class="w-full py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white font-semibold rounded-xl hover:from-purple-700 hover:to-pink-700 focus:ring-4 focus:ring-purple-300 transition-all duration-200 shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <span v-if="!loading">Sign In</span>
          <span v-else class="flex items-center justify-center">
            <svg class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Signing in...
          </span>
        </button>
            </form>
          </div>

          <!-- Register Form -->
          <div class="w-full flex-shrink-0">
            <form @submit.prevent="handleRegister" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
          <input
            v-model="form.name"
            type="text"
            required
            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
            placeholder="John Doe"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
          <input
            v-model="form.email"
            type="email"
            required
            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
            placeholder="your@email.com"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
          <input
            v-model="form.password"
            type="password"
            required
            minlength="8"
            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
            placeholder="••••••••"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Confirm Password</label>
          <input
            v-model="form.passwordConfirmation"
            type="password"
            required
            minlength="8"
            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
            placeholder="••••••••"
          />
        </div>

        <button
          type="submit"
          :disabled="loading"
          class="w-full py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white font-semibold rounded-xl hover:from-purple-700 hover:to-pink-700 focus:ring-4 focus:ring-purple-300 transition-all duration-200 shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <span v-if="!loading">Create Account</span>
          <span v-else class="flex items-center justify-center">
            <svg class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Creating account...
          </span>
        </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import { useAuthStore } from '../stores/authStore';

const authStore = useAuthStore();

const isLogin = ref(true);
const loading = ref(false);
const errorMessage = ref('');

const form = reactive({
  name: '',
  email: '',
  password: '',
  passwordConfirmation: '',
});

const containerHeight = computed(() => {
  return isLogin.value ? '240px' : '420px';
});

async function handleLogin() {
  loading.value = true;
  errorMessage.value = '';

  const result = await authStore.login(form.email, form.password);

  if (!result.success) {
    errorMessage.value = Object.values(result.errors).flat().join(', ');
  }

  loading.value = false;
}

async function handleRegister() {
  loading.value = true;
  errorMessage.value = '';

  const result = await authStore.register(
    form.name,
    form.email,
    form.password,
    form.passwordConfirmation
  );

  if (!result.success) {
    errorMessage.value = Object.values(result.errors).flat().join(', ');
  }

  loading.value = false;
}
</script>

