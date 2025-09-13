<script setup>
import NotificationsList from './NotificationsList.vue'

// Page configuration
definePage({
  meta: {
    layout: 'default',
    requiresAuth: true,
    title: 'Notifications',
  },
})
</script>

<template>
  <div class="notifications-page">
    <VContainer fluid>
      <VRow>
        <VCol cols="12">
          <NotificationsList />
        </VCol>
      </VRow>
    </VContainer>
  </div>
</template>

<style lang="scss" scoped>
.notifications-page {
  padding: 1rem 0;
}
</style>
