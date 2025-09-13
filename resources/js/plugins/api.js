import { $api } from '@/utils/api'

export default function (app) {
  // Make $api globally available
  app.config.globalProperties.$api = $api
  
  // Provide $api for composition API
  app.provide('$api', $api)
  
  // Make it available globally on window (optional, for debugging)
  if (typeof window !== 'undefined') {
    window.$api = $api
  }
} 