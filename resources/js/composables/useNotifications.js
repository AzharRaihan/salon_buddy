import { ref, computed } from 'vue'
import { $api } from '@/utils/api'
import { toast } from 'vue3-toastify'

export function useNotifications() {
  const notifications = ref([])
  const loading = ref(false)
  const unreadCount = ref(0)

  // Computed properties
  const hasUnreadNotifications = computed(() => unreadCount.value > 0)
  const totalNotifications = computed(() => notifications.value.length)

  // Fetch notifications from API
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
      toast.error('Failed to fetch notifications')
    } finally {
      loading.value = false
    }
  }

  // Mark notification as read
  const markAsRead = async (notificationIds) => {
    try {
      // Update local state immediately for better UX
      const ids = Array.isArray(notificationIds) ? notificationIds : [notificationIds]
      
      notifications.value.forEach(notification => {
        if (ids.includes(notification.id)) {
          notification.isSeen = true
        }
      })
      
      updateUnreadCount()
      
      // Call API to mark as read
      await $api(`mark-notification-read`, {
        method: 'POST',
        data: { notification_ids: ids }
      })
      
      toast.success('Notification marked as read')
    } catch (error) {
      console.error('Error marking notification as read:', error)
      toast.error('Failed to mark notification as read')
    }
  }

  // Mark notification as unread
  const markAsUnread = async (notificationIds) => {
    try {
      const ids = Array.isArray(notificationIds) ? notificationIds : [notificationIds]
      
      notifications.value.forEach(notification => {
        if (ids.includes(notification.id)) {
          notification.isSeen = false
        }
      })
      
      updateUnreadCount()
      
      // Call API to mark as unread
      await $api(`mark-notification-unread`, {
        method: 'POST',
        data: { notification_ids: ids }
      })
      
      toast.success('Notification marked as unread')
    } catch (error) {
      console.error('Error marking notification as unread:', error)
      toast.error('Failed to mark notification as unread')
    }
  }

  // Delete notification
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
          unreadCount.value = Math.max(0, unreadCount.value - 1)
        }
        notifications.value.splice(index, 1)
      }
      
      toast.success('Notification deleted successfully')
    } catch (error) {
      console.error('Error deleting notification:', error)
      toast.error('Failed to delete notification')
    }
  }

  // Delete multiple notifications
  const deleteMultipleNotifications = async (notificationIds) => {
    try {
      const promises = notificationIds.map(id => 
        $api(`delete-notification/${id}`, { method: 'DELETE' })
      )
      
      await Promise.all(promises)
      
      // Remove from local state
      const deletedCount = notificationIds.length
      const unreadDeletedCount = notifications.value
        .filter(n => notificationIds.includes(n.id) && !n.isSeen)
        .length
      
      notifications.value = notifications.value.filter(n => !notificationIds.includes(n.id))
      unreadCount.value = Math.max(0, unreadCount.value - unreadDeletedCount)
      
      toast.success(`${deletedCount} notification(s) deleted successfully`)
    } catch (error) {
      console.error('Error deleting notifications:', error)
      toast.error('Failed to delete notifications')
    }
  }

  // Mark all notifications as read
  const markAllAsRead = async () => {
    const unreadIds = notifications.value
      .filter(n => !n.isSeen)
      .map(n => n.id)
    
    if (unreadIds.length > 0) {
      await markAsRead(unreadIds)
    }
  }

  // Mark all notifications as unread
  const markAllAsUnread = async () => {
    const readIds = notifications.value
      .filter(n => n.isSeen)
      .map(n => n.id)
    
    if (readIds.length > 0) {
      await markAsUnread(readIds)
    }
  }

  // Update unread count
  const updateUnreadCount = () => {
    unreadCount.value = notifications.value.filter(n => !n.isSeen).length
  }

  // Fetch notification count
  const fetchNotificationCount = async () => {
    try {
      const response = await $api('notification-count')
      unreadCount.value = response.data
    } catch (error) {
      console.error('Error fetching notification count:', error)
    }
  }

  return {
    // State
    notifications,
    loading,
    unreadCount,
    
    // Computed
    hasUnreadNotifications,
    totalNotifications,
    
    // Methods
    fetchNotifications,
    markAsRead,
    markAsUnread,
    deleteNotification,
    deleteMultipleNotifications,
    markAllAsRead,
    markAllAsUnread,
    updateUnreadCount,
    fetchNotificationCount
  }
}
