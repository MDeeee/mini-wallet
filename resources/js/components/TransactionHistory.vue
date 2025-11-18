<template>
  <div class="bg-white/90 backdrop-blur-xl rounded-3xl shadow-xl p-6 border border-purple-100">
    <div class="flex items-center justify-between mb-6">
      <div class="flex items-center space-x-3">
        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center">
          <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <div>
          <h3 class="text-xl font-bold text-gray-900">Transaction History</h3>
          <p class="text-sm text-gray-500">Recent activity</p>
        </div>
      </div>
      <button
        @click="refreshTransactions"
        :disabled="loading"
        class="p-2 text-gray-600 hover:text-purple-600 hover:bg-purple-50 rounded-lg transition-colors duration-200"
        title="Refresh"
      >
        <svg :class="['w-5 h-5', loading && 'animate-spin']" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
        </svg>
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="loading && !transactions.length" class="space-y-4">
      <div v-for="i in 3" :key="i" class="animate-pulse">
        <div class="flex items-center space-x-4 p-4 bg-gray-100 rounded-xl">
          <div class="w-12 h-12 bg-gray-300 rounded-full"></div>
          <div class="flex-1 space-y-2">
            <div class="h-4 bg-gray-300 rounded w-3/4"></div>
            <div class="h-3 bg-gray-300 rounded w-1/2"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else-if="!transactionsWithType.length" class="text-center py-12">
      <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
        </svg>
      </div>
      <h3 class="text-lg font-semibold text-gray-900 mb-2">No transactions yet</h3>
      <p class="text-gray-500">Your transaction history will appear here</p>
    </div>

    <!-- Transactions List -->
    <div v-else class="space-y-3 max-h-[600px] overflow-y-auto pr-2">
      <div
        v-for="transaction in transactionsWithType"
        :key="transaction.id"
        @click="toggleExpand(transaction.id)"
        class="rounded-xl border hover:shadow-md transition-all duration-200 cursor-pointer overflow-hidden"
        :class="[
          transaction.type === 'sent'
            ? 'bg-red-50 border-red-200 hover:bg-red-100'
            : 'bg-green-50 border-green-200 hover:bg-green-100',
          expandedTransactionId === transaction.id && 'ring-2 ring-purple-400'
        ]"
      >
        <!-- Main Transaction Info -->
        <div class="p-4">
          <div class="flex items-start justify-between">
            <div class="flex items-start space-x-3 flex-1">
              <div
                class="w-12 h-12 rounded-full flex items-center justify-center flex-shrink-0"
                :class="[
                  transaction.type === 'sent'
                    ? 'bg-red-200'
                    : 'bg-green-200'
                ]"
              >
                <svg
                  class="w-6 h-6"
                  :class="[
                    transaction.type === 'sent'
                      ? 'text-red-700'
                      : 'text-green-700'
                  ]"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    v-if="transaction.type === 'sent'"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M5 10l7-7m0 0l7 7m-7-7v18"
                  />
                  <path
                    v-else
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M19 14l-7 7m0 0l-7-7m7 7V3"
                  />
                </svg>
              </div>

              <div class="flex-1 min-w-0">
                <div class="flex items-center space-x-2 mb-1">
                  <span class="font-semibold text-gray-900">
                    {{ transaction.type === 'sent' ? 'Sent to' : 'Received from' }}
                  </span>
                  <span
                    class="px-2 py-0.5 text-xs font-medium rounded-full"
                    :class="[
                      transaction.type === 'sent'
                        ? 'bg-red-200 text-red-800'
                        : 'bg-green-200 text-green-800'
                    ]"
                  >
                    {{ transaction.type === 'sent' ? 'Sent' : 'Received' }}
                  </span>
                </div>

                <p class="text-sm text-gray-700 mb-1">
                  {{ transaction.type === 'sent' ? transaction.receiver.name : transaction.sender.name }}
                </p>

                <div class="flex items-center space-x-2 text-xs text-gray-500">
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <span>{{ formatDate(transaction.created_at) }}</span>
                </div>
              </div>
            </div>

            <div class="text-right ml-4 flex flex-col items-end">
              <div
                class="text-lg font-bold mb-1"
                :class="[
                  transaction.type === 'sent'
                    ? 'text-red-700'
                    : 'text-green-700'
                ]"
              >
                {{ transaction.type === 'sent' ? '-' : '+' }}{{ transaction.amount }}
              </div>
              <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-200 text-gray-700 mb-1">
                {{ transaction.status }}
              </span>
              <svg
                class="w-5 h-5 text-gray-500 transition-transform duration-200"
                :class="expandedTransactionId === transaction.id && 'rotate-180'"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </div>
          </div>
        </div>

        <!-- Expanded Details -->
        <transition
          enter-active-class="transition-all duration-300 ease-out"
          enter-from-class="max-h-0 opacity-0"
          enter-to-class="max-h-96 opacity-100"
          leave-active-class="transition-all duration-200 ease-in"
          leave-from-class="max-h-96 opacity-100"
          leave-to-class="max-h-0 opacity-0"
        >
          <div
            v-if="expandedTransactionId === transaction.id"
            class="border-t px-4 pb-4 pt-3 space-y-3"
            :class="transaction.type === 'sent' ? 'border-red-200 bg-red-100/50' : 'border-green-200 bg-green-100/50'"
          >
            <div class="grid grid-cols-2 gap-3 text-sm">
              <div>
                <p class="text-gray-600 text-xs mb-1">Transaction ID</p>
                <p class="font-mono font-semibold text-gray-900">#{{ transaction.id }}</p>
              </div>
              <div>
                <p class="text-gray-600 text-xs mb-1">Status</p>
                <p class="font-semibold text-gray-900 capitalize">{{ transaction.status }}</p>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-3 text-sm">
              <div>
                <p class="text-gray-600 text-xs mb-1">From</p>
                <p class="font-semibold text-gray-900">{{ transaction.sender.name }}</p>
                <p class="text-xs text-gray-500">ID: {{ transaction.sender.id }}</p>
              </div>
              <div>
                <p class="text-gray-600 text-xs mb-1">To</p>
                <p class="font-semibold text-gray-900">{{ transaction.receiver.name }}</p>
                <p class="text-xs text-gray-500">ID: {{ transaction.receiver.id }}</p>
              </div>
            </div>

            <div class="pt-2 border-t" :class="transaction.type === 'sent' ? 'border-red-200' : 'border-green-200'">
              <div class="flex justify-between text-sm mb-1">
                <span class="text-gray-600">Amount</span>
                <span class="font-semibold text-gray-900">{{ transaction.amount }}</span>
              </div>
              <div v-if="transaction.type === 'sent'" class="flex justify-between text-sm mb-1">
                <span class="text-gray-600">Commission Fee (1.5%)</span>
                <span class="font-semibold text-gray-900">{{ transaction.commission_fee }}</span>
              </div>
              <div class="flex justify-between text-sm pt-2 border-t" :class="transaction.type === 'sent' ? 'border-red-200' : 'border-green-200'">
                <span class="font-semibold text-gray-700">Total</span>
                <span class="font-bold" :class="transaction.type === 'sent' ? 'text-red-700' : 'text-green-700'">
                  {{ transaction.type === 'sent' ? '-' : '+' }}{{ transaction.amount }}
                </span>
              </div>
            </div>

            <div class="text-xs text-gray-500">
              <p>{{ new Date(transaction.created_at).toLocaleString('en-US', {
                dateStyle: 'long',
                timeStyle: 'short'
              }) }}</p>
            </div>
          </div>
        </transition>
      </div>

      <!-- Load More Button -->
      <div v-if="walletStore.hasMorePages && !loading" class="mt-4 text-center">
        <button
          @click="walletStore.loadMoreTransactions()"
          :disabled="walletStore.loadingMore"
          class="px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
        >
          <span v-if="walletStore.loadingMore" class="flex items-center justify-center space-x-2">
            <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Loading...</span>
          </span>
          <span v-else>Load More Transactions</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { useWalletStore } from '../stores/walletStore';
import { useAuthStore } from '../stores/authStore';

const props = defineProps({
  transactions: {
    type: Array,
    default: () => []
  },
  loading: {
    type: Boolean,
    default: false
  }
});

const walletStore = useWalletStore();
const authStore = useAuthStore();
const expandedTransactionId = ref(null);

// Add type to each transaction based on current user and format amounts
const transactionsWithType = computed(() => {
  const currentUserId = authStore.user?.id;
  return props.transactions.map(transaction => {
    const type = transaction.sender.id === currentUserId ? 'sent' : 'received';

    // Format amounts from object to display string
    const amount = transaction.amount?.amount
      ? `$${transaction.amount.amount}`
      : transaction.amount;

    const commission_fee = transaction.commission_fee?.amount
      ? `$${transaction.commission_fee.amount}`
      : transaction.commission_fee;

    return {
      ...transaction,
      type,
      amount,
      commission_fee
    };
  });
});

function toggleExpand(transactionId) {
  expandedTransactionId.value = expandedTransactionId.value === transactionId ? null : transactionId;
}

function formatDate(dateString) {
  const date = new Date(dateString);
  const now = new Date();
  const diffInSeconds = Math.floor((now - date) / 1000);

  if (diffInSeconds < 60) return 'Just now';
  if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)}m ago`;
  if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)}h ago`;
  if (diffInSeconds < 604800) return `${Math.floor(diffInSeconds / 86400)}d ago`;

  return date.toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: date.getFullYear() !== now.getFullYear() ? 'numeric' : undefined
  });
}

function refreshTransactions() {
  walletStore.fetchTransactions();
}
</script>
