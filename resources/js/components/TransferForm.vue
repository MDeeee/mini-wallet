<template>
  <div class="bg-white/90 backdrop-blur-xl rounded-3xl shadow-xl p-6 border border-purple-100">
    <div class="flex items-center space-x-3 mb-6">
      <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center">
        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
        </svg>
      </div>
      <div>
        <h3 class="text-xl font-bold text-gray-900">Quick Transfer</h3>
        <p class="text-sm text-gray-500">Send money instantly</p>
      </div>
    </div>

    <!-- Error Message -->
    <div v-if="errorMessage" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm">
      {{ errorMessage }}
    </div>

    <!-- Success Message -->
    <div v-if="successMessage" class="mb-4 p-3 bg-green-50 border border-green-200 rounded-xl text-green-700 text-sm">
      {{ successMessage }}
    </div>

    <form @submit.prevent="handleTransfer" class="space-y-4">
      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">
          Recipient ID
        </label>
        <input
          v-model="form.receiverId"
          type="number"
          required
          min="1"
          class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
          placeholder="Enter user ID"
        />
        <p class="mt-1 text-xs text-gray-500">Enter the recipient's user ID</p>
      </div>

      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">
          Amount ($)
        </label>
        <div class="relative">
          <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-medium">$</span>
          <input
            v-model="form.amount"
            type="number"
            step="0.01"
            min="0.01"
            max="1000000"
            required
            class="w-full pl-8 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
            placeholder="0.00"
          />
        </div>
        <p class="mt-1 text-xs text-gray-500">Min: $0.01 • Max: $1,000,000</p>
      </div>

      <!-- Commission Info -->
      <div v-if="commission > 0" class="p-4 bg-purple-50 border border-purple-200 rounded-xl">
        <div class="flex justify-between items-center text-sm mb-2">
          <span class="text-gray-600">Amount:</span>
          <span class="font-semibold text-gray-900">${{ parseFloat(form.amount).toFixed(2) }}</span>
        </div>
        <div class="flex justify-between items-center text-sm mb-2">
          <span class="text-gray-600">Commission (1.5%):</span>
          <span class="font-semibold text-purple-600">${{ commission.toFixed(2) }}</span>
        </div>
        <div class="flex justify-between items-center text-sm pt-2 border-t border-purple-200">
          <span class="font-semibold text-gray-900">Total Debit:</span>
          <span class="font-bold text-gray-900">${{ totalAmount.toFixed(2) }}</span>
        </div>
      </div>

      <button
        type="submit"
        :disabled="loading"
        class="w-full py-3 bg-gradient-to-r from-green-500 to-emerald-600 text-white font-semibold rounded-xl hover:from-green-600 hover:to-emerald-700 focus:ring-4 focus:ring-green-300 transition-all duration-200 shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center"
      >
        <svg v-if="!loading" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
        </svg>
        <svg v-else class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        {{ loading ? 'Processing...' : 'Send Money' }}
      </button>
    </form>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import { useWalletStore } from '../stores/walletStore';

const emit = defineEmits(['transfer-success']);

const walletStore = useWalletStore();

const loading = ref(false);
const errorMessage = ref('');
const successMessage = ref('');

const form = reactive({
  receiverId: '',
  amount: '',
});

const commission = computed(() => {
  const amount = parseFloat(form.amount);
  return amount > 0 ? amount * 0.015 : 0;
});

const totalAmount = computed(() => {
  const amount = parseFloat(form.amount) || 0;
  return amount + commission.value;
});

async function handleTransfer() {
  loading.value = true;
  errorMessage.value = '';
  successMessage.value = '';

  const result = await walletStore.transfer(form.receiverId, form.amount);

  if (result.success) {
    successMessage.value = `Successfully sent $${parseFloat(form.amount).toFixed(2)} to user #${form.receiverId}!`;
    form.receiverId = '';
    form.amount = '';
    emit('transfer-success');

    setTimeout(() => {
      successMessage.value = '';
    }, 5000);
  } else {
    errorMessage.value = Object.values(result.errors).flat().join(', ');
  }

  loading.value = false;
}
</script>
