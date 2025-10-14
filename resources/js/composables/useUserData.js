/**
 * Composable for managing user data reactively across components
 * Singleton pattern ensures all components share the same reactive state
 */

import { ref, computed, watch } from 'vue'

// Singleton reactive reference shared across all components
let sharedUserData = null
let initialized = false

export function useUserData() {
  const userDataCookie = useCookie('userData', { default: () => null })
  
  // Initialize only once
  if (!initialized) {
    sharedUserData = ref(userDataCookie.value)
    
    // Sync cookie changes to shared ref
    watch(userDataCookie, (newVal) => {
      sharedUserData.value = newVal
    }, { deep: true })
    
    initialized = true
  }
  
  // Computed to ensure reactivity
  const userData = computed(() => sharedUserData.value)
  
  // Update method that triggers reactivity everywhere
  const updateUserData = (newUserData) => {
    console.log('Updating user data:', newUserData)
    // Update both cookie and shared ref
    userDataCookie.value = newUserData
    sharedUserData.value = { ...newUserData }
  }
  
  return {
    userData,
    updateUserData
  }
}
