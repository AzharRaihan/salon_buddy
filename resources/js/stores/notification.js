import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { $api } from '@/utils/api'

export const useNotificationStore = defineStore('notification', () => {
  // State
  const unreadCount = ref(0)
  const notifications = ref([])
  const loading = ref(false)

  // Getters
  const hasUnreadNotifications = computed(() => unreadCount.value > 0)
  const totalNotifications = computed(() => notifications.value.length)

  // Actions
  const fetchNotificationCount = async () => {
    try {
      const response = await $api('notification-count')
      unreadCount.value = response.data
    } catch (error) {
      console.error('Error fetching notification count:', error)
      unreadCount.value = 0
    }
  }

  const fetchNotifications = async () => {
    loading.value = true
    try {
      const response = await $api('get-notifications')
      notifications.value = response.data.map(notification => ({
        ...notification,
        isSeen: notification.read_status === 'Yes',
        id: notification.id,
        title: notification.notifications_details,
        subtitle: notification.booking_no || 'No booking reference',
        time: notification.notifications_date || notification.created_at,
        color: 'primary'
      }))
      updateUnreadCount()
    } catch (error) {
      console.error('Error fetching notifications:', error)
    } finally {
      loading.value = false
    }
  }

  const updateUnreadCount = () => {
    unreadCount.value = notifications.value.filter(n => !n.isSeen).length
  }

  const decrementCount = (amount = 1) => {
    unreadCount.value = Math.max(0, unreadCount.value - amount)
  }

  const incrementCount = (amount = 1) => {
    unreadCount.value += amount
  }

  const resetCount = () => {
    unreadCount.value = 0
  }

  const markAsRead = async (notificationIds) => {
    try {
      const ids = Array.isArray(notificationIds) ? notificationIds : [notificationIds]
      
      // Update local state immediately
      notifications.value.forEach(notification => {
        if (ids.includes(notification.id)) {
          notification.isSeen = true
        }
      })
      
      updateUnreadCount()
      
      // Call API
      await $api('mark-notification-read', {
        method: 'POST',
        data: { notification_ids: ids }
      })
    } catch (error) {
      console.error('Error marking notification as read:', error)
    }
  }

  const markAsUnread = async (notificationIds) => {
    try {
      const ids = Array.isArray(notificationIds) ? notificationIds : [notificationIds]
      
      // Update local state immediately
      notifications.value.forEach(notification => {
        if (ids.includes(notification.id)) {
          notification.isSeen = false
        }
      })
      
      updateUnreadCount()
      
      // Call API
      await $api('mark-notification-unread', {
        method: 'POST',
        data: { notification_ids: ids }
      })
    } catch (error) {
      console.error('Error marking notification as unread:', error)
    }
  }

  const deleteNotification = async (notificationId) => {
    try {
      await $api(`delete-notification/${notificationId}`, {
        method: 'DELETE'
      })
      
      // Remove from local state
      const index = notifications.value.findIndex(n => n.id === notificationId)
      if (index > -1) {
        const notification = notifications.value[index]
        if (!notification.isSeen) {
          decrementCount(1)
        }
        notifications.value.splice(index, 1)
      }
    } catch (error) {
      console.error('Error deleting notification:', error)
    }
  }

  return {
    // State
    unreadCount,
    notifications,
    loading,
    
    // Getters
    hasUnreadNotifications,
    totalNotifications,
    
    // Actions
    fetchNotificationCount,
    fetchNotifications,
    updateUnreadCount,
    decrementCount,
    incrementCount,
    resetCount,
    markAsRead,
    markAsUnread,
    deleteNotification
  }
})
