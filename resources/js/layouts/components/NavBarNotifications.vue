<script setup>
import NotificationDropdown from '@/components/Notifications/NotificationDropdown.vue'

const emit = defineEmits(['click:notification'])

const handleNotificationClick = (notification) => {
  emit('click:notification', notification)
}
</script>

<template>
  <NotificationDropdown
    @click:notification="handleNotificationClick"
  />
</template>
