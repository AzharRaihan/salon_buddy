<script setup>
import { PerfectScrollbar } from 'vue3-perfect-scrollbar'
import { useNotificationStore } from '@/stores/notification'

// Define component name to avoid conflicts
defineOptions({
  name: 'NotificationDropdown'
})

const props = defineProps({
  badgeProps: {
    type: Object,
    required: false,
    default: undefined,
  },
  location: {
    type: null,
    required: false,
    default: 'bottom end',
  },
})

const emit = defineEmits([
  'click:notification',
])

const notificationStore = useNotificationStore()

// Computed properties
const isAllMarkRead = computed(() => {
  return notificationStore.notifications.some(item => !item.isSeen)
})

const markAllReadOrUnread = () => {
  const allNotificationsIds = notificationStore.notifications.map(item => item.id)
  if (!isAllMarkRead.value) {
    notificationStore.markAsUnread(allNotificationsIds)
  } else {
    notificationStore.markAsRead(allNotificationsIds)
  }
}

const toggleReadUnread = (isSeen, Id) => {
  if (isSeen) {
    notificationStore.markAsUnread([Id])
  } else {
    notificationStore.markAsRead([Id])
  }
}

const handleNotificationClick = (notification) => {
  if (!notification.isSeen) {
    notificationStore.markAsRead([notification.id])
  }
  emit('click:notification', notification)
}

// Load notifications on mount
onMounted(() => {
  notificationStore.fetchNotifications()
})
</script>

<template>
  <IconBtn id="notification-btn">
    <VBadge
      v-bind="props.badgeProps"
      :model-value="notificationStore.hasUnreadNotifications"
      color="error"
      dot
      offset-x="2"
      offset-y="3"
    >
      <VIcon icon="tabler-bell" />
    </VBadge>

    <VMenu
      activator="parent"
      width="380px"
      :location="props.location"
      offset="12px"
      :close-on-content-click="false"
    >
      <VCard class="d-flex flex-column">
        <!-- 👉 Header -->
        <VCardItem class="notification-section">
          <VCardTitle class="text-h6">
            Notifications
          </VCardTitle>

          <template #append>
            <VChip
              v-show="notificationStore.hasUnreadNotifications"
              size="small"
              color="primary"
              class="me-2"
            >
              {{ notificationStore.unreadCount }} New
            </VChip>
            <IconBtn
              v-show="notificationStore.totalNotifications > 0"
              size="34"
              @click="markAllReadOrUnread"
            >
              <VIcon
                size="20"
                color="high-emphasis"
                :icon="!isAllMarkRead ? 'tabler-mail' : 'tabler-mail-opened'"
              />

              <VTooltip
                activator="parent"
                location="start"
              >
                {{ !isAllMarkRead ? 'Mark all as unread' : 'Mark all as read' }}
              </VTooltip>
            </IconBtn>
          </template>
        </VCardItem>

        <VDivider />

        <!-- 👉 Loading State -->
        <div v-if="notificationStore.loading" class="d-flex justify-center align-center pa-4">
          <VProgressCircular indeterminate />
        </div>

        <!-- 👉 Notifications list -->
        <PerfectScrollbar
          v-else
          :options="{ wheelPropagation: false }"
          style="max-block-size: 23.75rem;"
        >
          <VList class="notification-list rounded-0 py-0">
            <template
              v-for="(notification, index) in notificationStore.notifications"
              :key="notification.id"
            >
              <VDivider v-if="index > 0" />
              <VListItem
                link
                lines="one"
                min-height="66px"
                class="list-item-hover-class"
                @click="handleNotificationClick(notification)"
              >
                <!-- Notification Content -->
                <div class="d-flex align-start gap-3 w-100">
                  <VAvatar
                    :color="notification.color"
                    variant="tonal"
                  >
                    <VIcon
                      :icon="notification.icon || 'tabler-bell'"
                      size="20"
                    />
                  </VAvatar>

                  <div class="flex-grow-1">
                    <p class="text-sm font-weight-medium mb-1">
                      {{ notification.title }}
                    </p>
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
                      {{ notification.time }}
                    </p>
                  </div>

                  <div class="d-flex flex-column align-end">
                    <VIcon
                      size="10"
                      icon="tabler-circle-filled"
                      :color="!notification.isSeen ? 'primary' : '#a8aaae'"
                      :class="`${notification.isSeen ? 'visible-in-hover' : ''}`"
                      class="mb-2"
                      @click.stop="toggleReadUnread(notification.isSeen, notification.id)"
                    />

                    <VIcon
                      size="20"
                      icon="tabler-x"
                      class="visible-in-hover"
                      @click.stop="notificationStore.deleteNotification(notification.id)"
                    />
                  </div>
                </div>
              </VListItem>
            </template>

            <VListItem
              v-show="!notificationStore.notifications.length && !notificationStore.loading"
              class="text-center text-medium-emphasis"
              style="block-size: 56px;"
            >
              <VListItemTitle>No Notification Found!</VListItemTitle>
            </VListItem>
          </VList>
        </PerfectScrollbar>

        <VDivider />

        <!-- 👉 Footer -->
        <VCardText
          v-show="notificationStore.totalNotifications > 0"
          class="pa-4"
        >
          <VBtn
            block
            size="small"
            @click="$router.push('/notifications')"
          >
            View All Notifications
          </VBtn>
        </VCardText>
      </VCard>
    </VMenu>
  </IconBtn>
</template>

<style lang="scss">
.notification-section {
  padding-block: 0.75rem;
  padding-inline: 1rem;
}

.list-item-hover-class {
  .visible-in-hover {
    display: none;
  }

  &:hover {
    .visible-in-hover {
      display: block;
    }
  }
}

.notification-list.v-list {
  .v-list-item {
    border-radius: 0 !important;
    margin: 0 !important;
    padding-block: 0.75rem !important;
  }
}

// Badge Style Override for Notification Badge
.notification-badge {
  .v-badge__badge {
    /* stylelint-disable-next-line liberty/use-logical-spec */
    min-width: 18px;
    padding: 0;
    block-size: 18px;
  }
}
</style>
