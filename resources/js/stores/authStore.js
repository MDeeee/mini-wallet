import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import axios from 'axios';

export const useAuthStore = defineStore('auth', () => {
  const token = ref(localStorage.getItem('token') || null);
  const userStorage = localStorage.getItem('user');
  const user = ref(userStorage ? JSON.parse(userStorage) : null);

  const isAuthenticated = computed(() => !!token.value);

  function setAuth(newToken, newUser) {
    token.value = newToken;
    user.value = newUser;
    localStorage.setItem('token', newToken);
    localStorage.setItem('user', JSON.stringify(newUser));
    axios.defaults.headers.common['Authorization'] = `Bearer ${newToken}`;
  }

  function clearAuth() {
    token.value = null;
    user.value = null;
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    delete axios.defaults.headers.common['Authorization'];
  }

  async function register(name, email, password, passwordConfirmation) {
    try {
      const response = await axios.post('/api/register', {
        name,
        email,
        password,
        password_confirmation: passwordConfirmation,
      });

      // Set auth first
      setAuth(response.data.token, response.data.user);

      return { success: true };
    } catch (error) {
      return {
        success: false,
        errors: error.response?.data?.errors || { general: ['Registration failed'] },
      };
    }
  }

  async function login(email, password) {
    try {
      const response = await axios.post('/api/login', { email, password });
      setAuth(response.data.token, response.data.user);
      return { success: true };
    } catch (error) {
      return {
        success: false,
        errors: error.response?.data?.errors || { general: ['Invalid credentials'] },
      };
    }
  }

  async function logout() {
    try {
      await axios.post('/api/logout');
    } catch (error) {
      console.error('Logout error:', error);
    } finally {
      clearAuth();
      window.location.reload();
    }
  }

  async function fetchUser() {
    try {
      const response = await axios.get('/api/user');
      // Format balance for display if it's an object
      const userData = response.data;
      if (userData.balance && typeof userData.balance === 'object') {
        userData.balanceDisplay = userData.balance.amount;
        userData.currency = userData.balance.currency;
      }
      user.value = userData;
      localStorage.setItem('user', JSON.stringify(userData));
      return userData;
    } catch (error) {
      console.error('Fetch user error:', error);
      return null;
    }
  }

  // Initialize axios with token if exists
  if (token.value) {
    axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`;
  }

  return {
    token,
    user,
    isAuthenticated,
    register,
    login,
    logout,
    fetchUser,
  };
});
