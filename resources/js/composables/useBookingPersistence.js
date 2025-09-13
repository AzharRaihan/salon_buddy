import { ref, watch } from 'vue'

export function useBookingPersistence() {
  const BOOKING_DATA_KEY = 'booking_data_temp'
  
  // Save booking data to localStorage
  const saveBookingData = (bookingData) => {
    try {
      // Convert date to string if it's a Date object
      let dateToSave = bookingData.selectedDate
      if (bookingData.selectedDate instanceof Date) {
        dateToSave = bookingData.selectedDate.toISOString()
      }
      
      const dataToSave = {
        currentStep: bookingData.currentStep,
        selectedBranch: bookingData.selectedBranch,
        selectedServices: bookingData.selectedServices,
        selectedDate: dateToSave,
        selectedTime: bookingData.selectedTime,
        customerForm: bookingData.customerForm,
        savedAt: new Date().toISOString()
      }
      
      localStorage.setItem(BOOKING_DATA_KEY, JSON.stringify(dataToSave))
      console.log('Booking data saved:', dataToSave)
    } catch (error) {
      console.error('Error saving booking data:', error)
    }
  }

  // Load booking data from localStorage
  const loadBookingData = () => {
    try {
      const savedData = localStorage.getItem(BOOKING_DATA_KEY)
      if (savedData) {
        const parsedData = JSON.parse(savedData)
        
        // Check if data is not too old (24 hours)
        const savedTime = new Date(parsedData.savedAt)
        const now = new Date()
        const hoursDiff = (now - savedTime) / (1000 * 60 * 60)
        
        if (hoursDiff > 24) {
          // Data is too old, remove it
          clearBookingData()
          return null
        }
        
        console.log('Booking data loaded:', parsedData)
        return parsedData
      }
    } catch (error) {
      console.error('Error loading booking data:', error)
    }
    return null
  }

  // Clear booking data from localStorage
  const clearBookingData = () => {
    try {
      localStorage.removeItem(BOOKING_DATA_KEY)
      console.log('Booking data cleared')
    } catch (error) {
      console.error('Error clearing booking data:', error)
    }
  }

  // Check if there's saved booking data
  const hasSavedBookingData = () => {
    return !!localStorage.getItem(BOOKING_DATA_KEY)
  }

  // Restore booking data to the provided reactive objects
  const restoreBookingData = (bookingFlowRefs) => {
    const savedData = loadBookingData()
    
    if (!savedData) {
      return false
    }

    try {
      // Restore the booking flow state
      if (savedData.currentStep) {
        bookingFlowRefs.currentStep.value = savedData.currentStep
      }
      
      if (savedData.selectedBranch) {
        bookingFlowRefs.selectedBranch.value = savedData.selectedBranch
      }
      
      if (savedData.selectedServices && Array.isArray(savedData.selectedServices)) {
        bookingFlowRefs.selectedServices.value = [...savedData.selectedServices]
      }
      
      if (savedData.selectedDate) {
        // Convert string back to Date object for calendar composable
        try {
          bookingFlowRefs.selectedDate.value = new Date(savedData.selectedDate)
        } catch (e) {
          console.error('Error converting date:', savedData.selectedDate)
          bookingFlowRefs.selectedDate.value = null
        }
      }
      
      if (savedData.selectedTime) {
        bookingFlowRefs.selectedTime.value = savedData.selectedTime
      }
      
      if (savedData.customerForm) {
        Object.assign(bookingFlowRefs.customerForm.value, savedData.customerForm)
      }

      console.log('Booking data restored successfully')
      return true
    } catch (error) {
      console.error('Error restoring booking data:', error)
      return false
    }
  }

  // Create a watcher to auto-save booking data when it changes
  const createAutoSaveWatcher = (bookingFlowRefs) => {
    const saveData = () => {
      if (bookingFlowRefs.selectedBranch.value || bookingFlowRefs.selectedServices.value.length > 0) {
        saveBookingData({
          currentStep: bookingFlowRefs.currentStep.value,
          selectedBranch: bookingFlowRefs.selectedBranch.value,
          selectedServices: bookingFlowRefs.selectedServices.value,
          selectedDate: bookingFlowRefs.selectedDate.value,
          selectedTime: bookingFlowRefs.selectedTime.value,
          customerForm: bookingFlowRefs.customerForm.value,
        })
      }
    }

    // Watch for changes in key booking data
    const stopWatchers = [
      watch(bookingFlowRefs.selectedBranch, saveData, { deep: true }),
      watch(bookingFlowRefs.selectedServices, saveData, { deep: true }),
      watch(bookingFlowRefs.selectedDate, saveData),
      watch(bookingFlowRefs.selectedTime, saveData),
      watch(bookingFlowRefs.customerForm, saveData, { deep: true })
    ]

    return () => {
      stopWatchers.forEach(stop => stop())
    }
  }

  return {
    saveBookingData,
    loadBookingData,
    clearBookingData,
    hasSavedBookingData,
    restoreBookingData,
    createAutoSaveWatcher
  }
} 