<template>
  <div class="bg-gradient-to-br from-purple-600 to-pink-600 rounded-3xl shadow-2xl p-8 text-white animate-slide-in-up">
    <div class="flex items-center justify-between mb-6">
      <div>
        <p class="text-purple-100 text-sm font-medium mb-1">Current Balance</p>
        <div v-if="loading" class="animate-pulse">
          <div class="h-10 bg-white/20 rounded-lg w-48"></div>
        </div>
        <div v-else class="flex items-baseline space-x-2">
          <h2 class="text-5xl font-bold tracking-tight">
            {{ balance }}
          </h2>
          <span class="text-2xl font-medium text-purple-100">{{ currency }}</span>
        </div>
      </div>
      <div class="w-20 h-20 bg-white/20 backdrop-blur-lg rounded-2xl flex items-center justify-center">
        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  balance: {
    type: String,
    default: '0.00'
  },
  currency: {
    type: String,
    default: 'USD'
  },
  loading: {
    type: Boolean,
    default: false
  }
});
</script>
