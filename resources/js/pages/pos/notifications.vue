<template>
    <div class="container notification-view">
        <div class="notification-content">
            
            <h2 class="title">{{ t('Notification List') }}</h2>
            <button class="close-btn" @click="closeNotifications">
                <i class="fas fa-times-circle"></i>
            </button>
            <div class="notification-list-container">
                <!-- Back Button -->
                <div class="booking-list-header mb-3" >
                    <button class="btn btn-primary" @click="router.push('/pos')">
                        <VIcon icon="tabler-arrow-left" />
                        {{ t('Back to POS') }}
                    </button>
                </div>
                <div class="select-all-container">

                    <label for="selectAll" class="notification-label d-flex align-items-center custom-checkbox-wrapper">
                        <input
                            type="checkbox"
                            id="selectAll"
                            v-model="selectAll"
                            class="custom-checkbox"
                            @change="toggleSelectAll"
                            :disabled="notifications.length == 0"
                        >
                        <span class="checkmark"></span>
                        {{ t('Select All') }}
                    </label>
                </div>

                <div class="notification-list">
                    <div
                        v-for="(notification, index) in notifications"
                        :key="index"
                        class="notification-item"
                    >
                        <label :for="`notification-${index}`" class="notification-label d-flex align-items-center custom-checkbox-wrapper">
                            <input
                                type="checkbox"
                                :id="`notification-${index}`"
                                v-model="notification.selected"
                                class="custom-checkbox"
                            >
                            <span class="checkmark"></span>
                            <div class="notification-content">
                                <span class="notification-text">{{ notification.notifications_details }}</span>
                                <span class="notification-type">{{ notification.booking_no }}</span>
                            </div>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div class="action-buttons">
            <button class="delete-btn" @click="deleteSelected" :disabled="selectedCount == 0">
                <VIcon icon="tabler-trash" />
                {{ t('Delete Selected') }}({{ selectedCount }})
            </button>
            <button class="btn delete-btn btn-danger" @click="closeNotifications">
                <VIcon icon="tabler-x" /> {{ t('Close') }}
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import { useApi } from '@/composables/useApi';
import { toast } from 'vue3-toastify';
import { useNotificationStore } from '@/stores/pos/notification';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

// Page configuration
definePage({
    meta: {
        layout: 'pos',
        public: true,
    },
})
const router = useRouter();
const api = useApi();
const selectAll = ref(false);
const notifications = ref([]);
const notificationStore = useNotificationStore();

// Computed property to count selected notifications
const selectedCount = computed(() => {
    return notifications.value.filter(notif => notif.selected).length;
});

// Toggle all notifications selection
const toggleSelectAll = () => {
    notifications.value.forEach(notification => {
        notification.selected = selectAll.value;
    });
};

const deleteSelected = async () => {
  const selectedNotifications = notifications.value.filter(notification => notification.selected);
  const selectedCount = selectedNotifications.length;

  // Immediately decrement the count locally for better UX
  notificationStore.decrementCount(selectedCount);

  for (const notification of selectedNotifications) {
    try {
      await $api(`/delete-notification/${notification.id}`, {
        method: 'DELETE',
      });
    } catch (error) {
      console.error('Error deleting notification:', error);
      // If deletion fails, revert the count
      notificationStore.decrementCount(-selectedCount);
    }
  }

  // Refresh the local list
  await fetchNotifications();

  // Refresh the global count to ensure accuracy
  await notificationStore.fetchCount();

  selectAll.value = false;

  toast('Notification deleted successfully', {
    type: 'success',
  });
};

// Close notification view
const closeNotifications = () => {
    router.go(-1);
};

// Fetch notifications from API
const fetchNotifications = async () => {
    try {
        const response = await $api('get-notifications');
        notifications.value = response.data.map(n => ({
            ...n,
            selected: false, // add selected property
        }));
    } catch (error) {
        console.error('Error fetching notifications:', error);
    }
};

watch(
  () => notifications.value.map(n => n.selected),
  (newSelections) => {
    const allSelected = newSelections.length > 0 && newSelections.every(val => val == true);
    selectAll.value = allSelected;
  },
  { deep: true }
);

// Load notifications on mount
onMounted(() => {
    fetchNotifications();
    notificationStore.fetchCount();
});
</script>

