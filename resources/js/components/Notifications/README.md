# Notification System

This directory contains the refactored notification system components for the Vue 3 + Inertia + Laravel application.

## Structure

```
components/Notifications/
├── NotificationDropdown.vue    # Main notification dropdown component
└── README.md                   # This documentation file

pages/Notifications/
├── index.vue                   # Main notifications page
└── NotificationsList.vue       # Notifications list component

stores/
└── notification.js             # Pinia store for notification state

composables/
└── useNotifications.js         # Composable for notification operations
```

## Components

### NotificationDropdown.vue
The main notification dropdown component that appears in the navbar. Features:
- Real-time notification count badge
- Dropdown with notification list
- Mark as read/unread functionality
- Delete individual notifications
- Mark all as read/unread
- View all notifications link

### NotificationsList.vue
A comprehensive notifications page component with:
- Full notification list display
- Bulk selection with checkboxes
- Bulk actions (mark as read/unread, delete)
- Individual notification actions
- Loading states
- Empty state handling

## Store (Pinia)

### useNotificationStore
Global state management for notifications:

**State:**
- `unreadCount`: Number of unread notifications
- `notifications`: Array of notification objects
- `loading`: Loading state

**Getters:**
- `hasUnreadNotifications`: Boolean for unread notifications
- `totalNotifications`: Total notification count

**Actions:**
- `fetchNotificationCount()`: Get unread count from API
- `fetchNotifications()`: Get all notifications from API
- `markAsRead(ids)`: Mark notifications as read
- `markAsUnread(ids)`: Mark notifications as unread
- `deleteNotification(id)`: Delete a notification
- `updateUnreadCount()`: Update local unread count

## API Endpoints

The system uses the following Laravel API endpoints:

- `GET /api/get-notifications` - Fetch all notifications
- `GET /api/notification-count` - Get unread count
- `DELETE /api/delete-notification/{id}` - Delete a notification
- `POST /api/mark-notification-read` - Mark notifications as read
- `POST /api/mark-notification-unread` - Mark notifications as unread

## Usage

### In Navbar
```vue
<template>
  <NotificationDropdown />
</template>
```

### In Pages
```vue
<template>
  <NotificationsList />
</template>
```

### Using the Store
```javascript
import { useNotificationStore } from '@/stores/notification'

const notificationStore = useNotificationStore()

// Fetch notifications
await notificationStore.fetchNotifications()

// Mark as read
await notificationStore.markAsRead([1, 2, 3])

// Delete notification
await notificationStore.deleteNotification(1)
```

## Features

### ✅ Implemented
- [x] Real-time notification display
- [x] Mark as read/unread functionality
- [x] Delete notifications
- [x] Bulk actions (select all, mark all, delete all)
- [x] Loading states
- [x] Empty state handling
- [x] Responsive design
- [x] Toast notifications for actions
- [x] Global state management with Pinia
- [x] API integration with Laravel backend

### 🔄 Removed Dependencies
- [x] Removed image dependencies (avatars, icons)
- [x] Removed hardcoded notification data
- [x] Simplified component structure

### 🎨 UI/UX Improvements
- [x] Clean, modern design
- [x] Hover effects for actions
- [x] Proper loading indicators
- [x] Consistent spacing and typography
- [x] Accessible button and icon usage

## Data Structure

Each notification object has the following structure:
```javascript
{
  id: number,
  title: string,           // notifications_details from DB
  subtitle: string,        // booking_no from DB
  time: string,           // notifications_date or created_at
  isSeen: boolean,        // read_status === 'Yes'
  color: string,          // Default: 'primary'
  icon: string,           // Default: 'tabler-bell'
  read_status: string,    // 'Yes' or 'No'
  booking_no: string,     // Booking reference
  notifications_date: string,
  company_id: number,
  branch_id: number,
  del_status: string      // 'Live' or 'Deleted'
}
```

## Styling

The components use Vuetify 3 components and follow the existing design system:
- Uses `VCard`, `VList`, `VListItem` for layout
- Consistent spacing with Vuetify's spacing system
- Proper color usage with theme variables
- Responsive design with Vuetify's grid system

## Future Enhancements

- [ ] Real-time notifications with WebSockets
- [ ] Notification categories/filtering
- [ ] Notification preferences
- [ ] Push notifications
- [ ] Email notifications integration
- [ ] Notification history/archiving
