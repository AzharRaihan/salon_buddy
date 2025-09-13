<script setup>
import { useNotificationStore } from '@/stores/notification'

// Page configuration
definePage({
  meta: {
    layout: 'default',
    requiresAuth: true,
  },
})

const notificationStore = useNotificationStore()

// Local state
const selectAll = ref(false)
const selectedNotifications = ref([])

// Computed properties
const selectedCount = computed(() => selectedNotifications.value.length)

const hasSelectedNotifications = computed(() => selectedCount.value > 0)

const allNotificationsSelected = computed(() => {
  return notificationStore.notifications.length > 0 && selectedNotifications.value.length == notificationStore.notifications.length
})

// Methods
const toggleSelectAll = () => {
  if (selectAll.value) {
    selectedNotifications.value = notificationStore.notifications.map(n => n.id)
  } else {
    selectedNotifications.value = []
  }
}

const toggleNotificationSelection = (notificationId) => {
  const index = selectedNotifications.value.indexOf(notificationId)
  if (index > -1) {
    selectedNotifications.value.splice(index, 1)
  } else {
    selectedNotifications.value.push(notificationId)
  }
}

const handleMarkSelectedAsRead = async () => {
  if (selectedNotifications.value.length > 0) {
    await notificationStore.markAsRead(selectedNotifications.value)
    selectedNotifications.value = []
    selectAll.value = false
  }
}

const handleMarkSelectedAsUnread = async () => {
  if (selectedNotifications.value.length > 0) {
    await notificationStore.markAsUnread(selectedNotifications.value)
    selectedNotifications.value = []
    selectAll.value = false
  }
}

const handleDeleteSelected = async () => {
  if (selectedNotifications.value.length > 0) {
    const promises = selectedNotifications.value.map(id => 
      notificationStore.deleteNotification(id)
    )
    await Promise.all(promises)
    selectedNotifications.value = []
    selectAll.value = false
  }
}

const handleMarkAllAsRead = async () => {
  const unreadIds = notificationStore.notifications
    .filter(n => !n.isSeen)
    .map(n => n.id)
  
  if (unreadIds.length > 0) {
    await notificationStore.markAsRead(unreadIds)
  }
}

const handleMarkAllAsUnread = async () => {
  const readIds = notificationStore.notifications
    .filter(n => n.isSeen)
    .map(n => n.id)
  
  if (readIds.length > 0) {
    await notificationStore.markAsUnread(readIds)
  }
}

const handleDeleteAll = async () => {
  const allIds = notificationStore.notifications.map(n => n.id)
  const promises = allIds.map(id => notificationStore.deleteNotification(id))
  await Promise.all(promises)
}

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  
  const date = new Date(dateString)
  const now = new Date()
  const diffTime = Math.abs(now - date)
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
  
  if (diffDays == 1) return 'Today'
  if (diffDays == 2) return 'Yesterday'
  if (diffDays <= 7) return `${diffDays - 1} days ago`
  
  return date.toLocaleDateString()
}

// Watch for changes in selected notifications
watch(selectedNotifications, (newSelection) => {
  selectAll.value = newSelection.length == notificationStore.notifications.length && notificationStore.notifications.length > 0
}, { deep: true })

// Load notifications on mount
onMounted(() => {
  notificationStore.fetchNotifications()
})
</script>

<template>
  <VCard>
    <!-- Header -->
    <VCardItem>
      <VCardTitle class="d-flex align-center justify-space-between">
        <div>
          <h2 class="text-h4 mb-1">
            Notifications
          </h2>
          <p class="text-body-2 text-medium-emphasis mb-0">
            {{ notificationStore.totalNotifications }} total notifications
            <span v-if="notificationStore.hasUnreadNotifications" class="text-primary">
              ({{ notificationStore.unreadCount }} unread)
            </span>
          </p>
        </div>
        
        <div class="d-flex gap-2">
          <VBtn
            v-if="notificationStore.hasUnreadNotifications"
            variant="outlined"
            size="small"
            @click="handleMarkAllAsRead"
          >
            <VIcon icon="tabler-mail-opened" class="me-2" />
            Mark All as Read
          </VBtn>
          
          <VBtn
            v-if="notificationStore.notifications.some(n => n.isSeen)"
            variant="outlined"
            size="small"
            @click="handleMarkAllAsUnread"
          >
            <VIcon icon="tabler-mail" class="me-2" />
            Mark All as Unread
          </VBtn>
          
          <VBtn
            v-if="notificationStore.totalNotifications > 0"
            variant="outlined"
            color="error"
            size="small"
            @click="handleDeleteAll"
          >
            <VIcon icon="tabler-trash" class="me-2" />
            Delete All
          </VBtn>
        </div>
      </VCardTitle>
    </VCardItem>

    <VDivider />

    <!-- Bulk Actions -->
    <VCardText v-if="hasSelectedNotifications" class="pa-4 bg-primary-lighten-5">
      <div class="d-flex align-center justify-space-between">
        <span class="text-body-2">
          {{ selectedCount }} notification(s) selected
        </span>
        
        <div class="d-flex gap-2">
          <VBtn
            size="small"
            variant="outlined"
            @click="handleMarkSelectedAsRead"
          >
            <VIcon icon="tabler-mail-opened" class="me-2" />
            Mark as Read
          </VBtn>
          
          <VBtn
            size="small"
            variant="outlined"
            @click="handleMarkSelectedAsUnread"
          >
            <VIcon icon="tabler-mail" class="me-2" />
            Mark as Unread
          </VBtn>
          
          <VBtn
            size="small"
            color="error"
            variant="outlined"
            @click="handleDeleteSelected"
          >
            <VIcon icon="tabler-trash" class="me-2" />
            Delete Selected
          </VBtn>
        </div>
      </div>
    </VCardText>

    <!-- Loading State -->
    <div v-if="notificationStore.loading" class="d-flex justify-center align-center pa-8">
      <VProgressCircular indeterminate size="64" />
    </div>

    <!-- Notifications List -->
    <div v-else>
      <!-- Select All Checkbox -->
      <VCardText v-if="notificationStore.totalNotifications > 0" class="pa-4">
        <VCheckbox
          v-model="selectAll"
          :label="`Select all ${notificationStore.totalNotifications} notifications`"
          @change="toggleSelectAll"
        />
      </VCardText>

      <!-- Notifications -->
      <VList v-if="notificationStore.totalNotifications > 0" class="pa-0">
        <template
          v-for="(notification, index) in notificationStore.notifications"
          :key="notification.id"
        >
          <VDivider v-if="index > 0" />
          
          <VListItem class="notification-item">
            <template #prepend>
              <VCheckbox
                :model-value="selectedNotifications.includes(notification.id)"
                @change="toggleNotificationSelection(notification.id)"
                class="me-3"
              />
            </template>
            
            <VListItemContent>
              <div class="d-flex align-start gap-3 w-100">
                <VAvatar
                  :color="notification.color"
                  variant="tonal"
                  size="40"
                >
                  <VIcon
                    :icon="notification.icon || 'tabler-bell'"
                    size="20"
                  />
                </VAvatar>

                <div class="flex-grow-1">
                  <div class="d-flex align-center gap-2 mb-1">
                    <p class="text-sm font-weight-medium mb-0">
                      {{ notification.title }}
                    </p>
                    
                    <VChip
                      v-if="!notification.isSeen"
                      size="x-small"
                      color="primary"
                    >
                      New
                    </VChip>
                  </div>
                  
                  <p
                    class="text-body-2 mb-2"
                    style="letter-spacing: 0.4px !important; line-height: 18px;"
                  >
                    {{ notification.subtitle }}
                  </p>
                  
                  <p
                    class="text-sm text-disabled mb-0"
                    style="letter-spacing: 0.4px !important; line-height: 18px;"
                  >
                    {{ formatDate(notification.time) }}
                  </p>
                </div>

                <div class="d-flex flex-column align-end gap-2">
                  <VBtn
                    size="small"
                    variant="text"
                    :color="notification.isSeen ? 'default' : 'primary'"
                    @click="notification.isSeen ? notificationStore.markAsUnread([notification.id]) : notificationStore.markAsRead([notification.id])"
                  >
                    <VIcon
                      :icon="notification.isSeen ? 'tabler-mail' : 'tabler-mail-opened'"
                      size="16"
                    />
                  </VBtn>
                  
                  <VBtn
                    size="small"
                    variant="text"
                    color="error"
                    @click="notificationStore.deleteNotification(notification.id)"
                  >
                    <VIcon icon="tabler-trash" size="16" />
                  </VBtn>
                </div>
              </div>
            </VListItemContent>
          </VListItem>
        </template>
      </VList>

      <!-- Empty State -->
      <VCardText
        v-else
        class="text-center pa-8"
      >
        <VIcon
          icon="tabler-bell-off"
          size="64"
          color="disabled"
          class="mb-4"
        />
        <h3 class="text-h6 mb-2">
          No Notifications
        </h3>
        <p class="text-body-2 text-medium-emphasis">
          You're all caught up! No new notifications at the moment.
        </p>
      </VCardText>
    </div>
  </VCard>
</template>

<style lang="scss" scoped>
// .notification-item {
//   &:hover {
//     background-color: rgb(var(--v-theme-surface-variant));
//   }
// }

// .v-list-item {
//   border-radius: 0 !important;
//   margin: 0 !important;
// }
</style>
