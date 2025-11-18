<template>
  <Transition
    enter-active-class="transition ease-out duration-300 transform"
    enter-from-class="translate-y-2 opacity-0"
    enter-to-class="translate-y-0 opacity-100"
    leave-active-class="transition ease-in duration-200 transform"
    leave-from-class="translate-y-0 opacity-100"
    leave-to-class="translate-y-2 opacity-0"
  >
    <div
      v-if="notificationStore.visible"
      class="fixed bottom-8 right-8 z-50 max-w-md animate-slide-in-up"
    >
      <div
        class="bg-white rounded-2xl shadow-2xl p-6 border-l-4"
        :class="[
          notificationStore.notification?.type === 'sent'
            ? 'border-red-500'
            : 'border-green-500'
        ]"
      >
        <div class="flex items-start space-x-4">
          <div
            class="w-12 h-12 rounded-full flex items-center justify-center flex-shrink-0"
            :class="[
              notificationStore.notification?.type === 'sent'
                ? 'bg-red-100'
                : 'bg-green-100'
            ]"
          >
            <svg
              class="w-6 h-6"
              :class="[
                notificationStore.notification?.type === 'sent'
                  ? 'text-red-600'
                  : 'text-green-600'
              ]"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                v-if="notificationStore.notification?.type === 'sent'"
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

          <div class="flex-1">
            <div class="flex items-center justify-between mb-2">
              <h4 class="font-bold text-gray-900">
                {{ notificationStore.notification?.type === 'sent' ? 'Money Sent!' : 'Money Received!' }}
              </h4>
              <button
                @click="notificationStore.hide"
                class="text-gray-400 hover:text-gray-600 transition-colors"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <p class="text-sm text-gray-600 mb-3">
              {{ notificationStore.notification?.type === 'sent'
                ? `Successfully sent $${notificationStore.notification?.amount.toFixed(2)}`
                : `Received $${notificationStore.notification?.amount.toFixed(2)}`
              }}
            </p>

            <div class="flex items-center justify-between pt-3 border-t border-gray-100">
              <span class="text-xs text-gray-500">New Balance</span>
              <span class="text-sm font-bold text-gray-900">
                ${{ notificationStore.notification?.newBalance.toFixed(2) }}
              </span>
            </div>
          </div>
        </div>

        <!-- Progress Bar -->
        <div class="mt-4 h-1 bg-gray-100 rounded-full overflow-hidden">
          <div
            class="h-full animate-progress"
            :class="[
              notificationStore.notification?.type === 'sent'
                ? 'bg-red-500'
                : 'bg-green-500'
            ]"
          ></div>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { useNotificationStore } from '../stores/notificationStore';

const notificationStore = useNotificationStore();
</script>

<style scoped>
@keyframes progress {
  from {
    width: 100%;
  }
  to {
    width: 0%;
  }
}

.animate-progress {
  animation: progress 5s linear;
}
</style>
