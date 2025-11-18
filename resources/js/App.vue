<template>
  <div id="mini-wallet" class="min-h-screen bg-gradient-to-br from-indigo-50 via-purple-50 to-pink-50">
    <!-- Navigation -->
    <nav v-if="authStore.isAuthenticated" class="bg-white/80 backdrop-blur-lg border-b border-purple-100 sticky top-0 z-50 shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
          <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-gradient-to-br from-purple-600 to-pink-600 rounded-xl flex items-center justify-center shadow-lg transform hover:scale-110 transition-transform">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
              </svg>
            </div>
            <div>
              <h1 class="text-xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">
                Mini Wallet
              </h1>
              <p class="text-xs text-gray-500">Digital Payment System</p>
            </div>
          </div>

          <div class="flex items-center space-x-4">
            <div class="text-right">
              <p class="text-sm text-gray-600">Welcome back,</p>
              <p class="font-semibold text-gray-900">{{ authStore.user?.name }}</p>
            </div>
            <button
              @click="authStore.logout"
              class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all duration-200"
            >
              Logout
            </button>
          </div>
        </div>
      </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Login/Register -->
      <div v-if="!authStore.isAuthenticated" class="min-h-[80vh] flex items-center justify-center">
        <AuthCard />
      </div>

      <!-- Dashboard -->
      <div v-else class="space-y-6">
        <!-- Balance Card with Animation -->
        <BalanceCard
          :balance="walletStore.balance || authStore.user?.balanceDisplay || '0.00'"
          :currency="walletStore.currency || authStore.user?.currency || 'USD'"
          :loading="walletStore.loading"
        />

        <!-- Quick Transfer Section -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <div class="lg:col-span-1">
            <TransferForm @transfer-success="handleTransferSuccess" />
          </div>

          <div class="lg:col-span-2">
            <TransactionHistory :transactions="walletStore.transactions" :loading="walletStore.loading" />
          </div>
        </div>

        <!-- Real-time Notification Toast -->
        <NotificationToast />
      </div>
    </main>

    <!-- Footer -->
    <footer class="mt-6 pb-6 text-center text-sm text-gray-500">
      <p>Mini Wallet • {{ new Date().getFullYear() }}</p>
    </footer>
  </div>
</template>

<script setup>
import { onMounted, watch } from 'vue';
import { useAuthStore } from './stores/authStore';
import { useWalletStore } from './stores/walletStore';
import { useNotificationStore } from './stores/notificationStore';
import AuthCard from './components/AuthCard.vue';
import BalanceCard from './components/BalanceCard.vue';
import TransferForm from './components/TransferForm.vue';
import TransactionHistory from './components/TransactionHistory.vue';
import NotificationToast from './components/NotificationToast.vue';

const authStore = useAuthStore();
const walletStore = useWalletStore();
const notificationStore = useNotificationStore();

async function loadUserData() {
  if (authStore.isAuthenticated) {
    // Fetch current user data (includes balance)
    await authStore.fetchUser();
    // Fetch transactions
    await walletStore.fetchTransactions();
    setupRealtimeListeners();
  }
}

onMounted(async () => {
  await loadUserData();
});

// Watch for authentication changes (login/register)
watch(() => authStore.isAuthenticated, async (newValue) => {
  if (newValue) {
    await loadUserData();
  }
}, { immediate: false });

// Also watch token directly for more reliable reactivity
watch(() => authStore.token, async (newToken) => {
  if (newToken) {
    await loadUserData();
  }
});

function setupRealtimeListeners() {
  const userId = authStore.user?.id;
  if (!userId) return;

  window.Echo.private(`user.${userId}`)
    .listen('.money.transferred', (event) => {
      console.log('Real-time event received:', event);
      walletStore.fetchTransactions();

      const isSender = event.sender_id === userId;
      notificationStore.show({
        type: isSender ? 'sent' : 'received',
        amount: event.amount,
        userName: isSender ? 'Recipient' : 'Sender',
        newBalance: isSender ? event.sender_new_balance : event.receiver_new_balance,
      });
    });
}

function handleTransferSuccess() {
  walletStore.fetchTransactions();
}
</script>

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

* {
  font-family: 'Inter', sans-serif;
}

#mini-wallet {
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}

/* Custom scrollbar */
::-webkit-scrollbar {
  width: 8px;
  height: 8px;
}

::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 10px;
}

::-webkit-scrollbar-thumb {
  background: linear-gradient(180deg, #9333ea 0%, #ec4899 100%);
  border-radius: 10px;
}

::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(180deg, #7e22ce 0%, #db2777 100%);
}

/* Animations */
@keyframes slideInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: .5;
  }
}

.animate-slide-in-up {
  animation: slideInUp 0.3s ease-out;
}

.animate-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>
