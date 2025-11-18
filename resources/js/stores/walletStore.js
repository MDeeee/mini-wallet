import { defineStore } from 'pinia';
import { ref } from 'vue';
import axios from 'axios';

export const useWalletStore = defineStore('wallet', () => {
  const balance = ref('0.00');
  const balanceCents = ref(0);
  const currency = ref('USD');
  const transactions = ref([]);
  const nextCursor = ref(null);
  const hasMorePages = ref(false);
  const loading = ref(false);
  const loadingMore = ref(false);
  const error = ref(null);

  async function fetchTransactions(cursor = null) {
    if (cursor) {
      loadingMore.value = true;
    } else {
      loading.value = true;
    }
    error.value = null;

    try {
      const url = cursor
        ? `/api/v1/transactions?cursor=${cursor}`
        : '/api/v1/transactions';

      const response = await axios.get(url);
      const balanceData = response.data.balance;

      if (balanceData && typeof balanceData === 'object') {
        balance.value = balanceData.amount;
        balanceCents.value = balanceData.amount_cents;
        currency.value = balanceData.currency || 'USD';
      } else {
        balance.value = '0.00';
        balanceCents.value = 0;
        currency.value = 'USD';
      }

      const transactionsData = response.data.transactions;

      if (cursor) {
        // Append to existing transactions (Load More)
        transactions.value = [...transactions.value, ...(transactionsData?.data || [])];
      } else {
        // Replace transactions (initial load or refresh)
        transactions.value = transactionsData?.data || [];
      }

      // Store pagination metadata
      nextCursor.value = transactionsData?.next_cursor || null;
      hasMorePages.value = !!transactionsData?.next_cursor;
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch transactions';
      console.error('Fetch transactions error:', err);
    } finally {
      loading.value = false;
      loadingMore.value = false;
    }
  }

  async function loadMoreTransactions() {
    if (nextCursor.value && !loadingMore.value) {
      await fetchTransactions(nextCursor.value);
    }
  }

  async function transfer(receiverId, amount) {
    loading.value = true;
    error.value = null;

    try {
      const response = await axios.post('/api/v1/transactions', {
        receiver_id: receiverId,
        amount: parseFloat(amount),
      });

      // Update balance immediately from new_balance object
      const newBalanceData = response.data.new_balance;
      if (newBalanceData && typeof newBalanceData === 'object') {
        balance.value = newBalanceData.amount;
        balanceCents.value = newBalanceData.amount_cents;
        currency.value = newBalanceData.currency || 'USD';
      }

      // Refresh transactions to show the new one
      await fetchTransactions();

      return { success: true, data: response.data };
    } catch (err) {
      error.value = err.response?.data?.message || 'Transfer failed';
      return {
        success: false,
        errors: err.response?.data?.errors || { general: [error.value] },
      };
    } finally {
      loading.value = false;
    }
  }

  return {
    balance,
    balanceCents,
    currency,
    transactions,
    nextCursor,
    hasMorePages,
    loading,
    loadingMore,
    error,
    fetchTransactions,
    loadMoreTransactions,
    transfer,
  };
});
