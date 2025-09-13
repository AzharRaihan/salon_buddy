import { defineStore } from 'pinia';
import { ref } from 'vue';
import { $api } from '@/utils/api';

export const useNotificationStore = defineStore('pos-notification', () => {
  const count = ref(0);

  const fetchCount = async () => {
    try {
      const res = await $api('/notification-count');
      count.value = res.data;
    } catch (error) {
      console.error('Error fetching notification count:', error);
      count.value = 0;
    }
  };

  const decrementCount = (amount = 1) => {
    count.value = Math.max(0, count.value - amount);
  };

  const resetCount = () => {
    count.value = 0;
  };

  return { count, fetchCount, decrementCount, resetCount };
});