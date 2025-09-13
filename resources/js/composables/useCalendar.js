import { ref, computed } from 'vue'

export const useCalendar = () => {
  const currentDate = ref(new Date())
  const selectedDate = ref(null)

  // Calendar computed properties
  const calendarDays = computed(() => {
    const year = currentDate.value.getFullYear()
    const month = currentDate.value.getMonth()
    
    // Get first day of the month and last day
    const firstDay = new Date(year, month, 1)
    const lastDay = new Date(year, month + 1, 0)
    
    // Get the day of the week for the first day (Monday = 0)
    const firstDayOfWeek = (firstDay.getDay() + 6) % 7
    
    // Get previous month's last days to fill the grid
    const prevMonth = new Date(year, month, 0)
    const days = []
    
    // Add previous month's days
    for (let i = firstDayOfWeek - 1; i >= 0; i--) {
      const day = prevMonth.getDate() - i
      days.push({
        day: day,
        date: new Date(year, month - 1, day),
        otherMonth: true,
        available: false,
        selected: false,
        isToday: false
      })
    }
    
    // Add current month's days
    const today = new Date()
    // Set time to start of day for accurate comparison
    today.setHours(0, 0, 0, 0)
    
    for (let day = 1; day <= lastDay.getDate(); day++) {
      const date = new Date(year, month, day)
      // Set time to start of day for accurate comparison
      date.setHours(0, 0, 0, 0)
      
      const isToday = date.getTime() === today.getTime()
      
      // Handle selectedDate properly - it might be a string or Date object
      let isSelected = false
      if (selectedDate.value) {
        const selectedDateObj = selectedDate.value instanceof Date 
          ? selectedDate.value 
          : new Date(selectedDate.value)
        selectedDateObj.setHours(0, 0, 0, 0)
        isSelected = date.getTime() === selectedDateObj.getTime()
      }
      
      const isPastDate = date < today
      
      days.push({
        day: day,
        date: new Date(year, month, day), // Keep original date object
        otherMonth: false,
        available: !isPastDate, // Only future dates and today are available
        selected: isSelected,
        isToday: isToday
      })
    }
    
    // Add next month's days to complete the grid
    const remainingCells = 42 - days.length // 6 rows × 7 days
    for (let day = 1; day <= remainingCells; day++) {
      days.push({
        day: day,
        date: new Date(year, month + 1, day),
        otherMonth: true,
        available: false,
        selected: false,
        isToday: false
      })
    }
    
    return days
  })

  const currentMonthYear = computed(() => {
    const months = [
      'JANUARY', 'FEBRUARY', 'MARCH', 'APRIL', 'MAY', 'JUNE',
      'JULY', 'AUGUST', 'SEPTEMBER', 'OCTOBER', 'NOVEMBER', 'DECEMBER'
    ]
    return `${months[currentDate.value.getMonth()]} ${currentDate.value.getFullYear()}`
  })

  // Methods
  const selectDate = (day) => {
    if (!day.otherMonth && day.available) {
      selectedDate.value = day.date
    }
  }

  const previousMonth = () => {
    currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() - 1, 1)
  }

  const nextMonth = () => {
    currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() + 1, 1)
  }

  const resetCalendar = () => {
    currentDate.value = new Date()
    selectedDate.value = null
  }

  return {
    currentDate,
    selectedDate,
    calendarDays,
    currentMonthYear,
    selectDate,
    previousMonth,
    nextMonth,
    resetCalendar
  }
} 