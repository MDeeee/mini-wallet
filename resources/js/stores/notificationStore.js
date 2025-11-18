import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useNotificationStore = defineStore('notification', () => {
  const visible = ref(false);
  const notification = ref(null);

  function show(data) {
    notification.value = data;
    visible.value = true;

    setTimeout(() => {
      visible.value = false;
    }, 8000);
  }

  function hide() {
    visible.value = false;
  }

  return {
    visible,
    notification,
    show,
    hide,
  };
});
