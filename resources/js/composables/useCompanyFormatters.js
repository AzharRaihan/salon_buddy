import { ref, computed } from 'vue'

export function useCompanyFormatters() {
  const companySettings = ref(null)
  const isLoading = ref(false)
  const companyPrecision = ref(2)
  // Fetch company settings
  const fetchCompanySettings = async () => {
    if (companySettings.value) return companySettings.value
    
    try {
      isLoading.value = true
      const response = await $api('/get-company-info')
      if (response.success) {
        companySettings.value = response.data
        companyPrecision.value = response.data.precision
      }
    } catch (error) {
      console.error('Error fetching company settings:', error)
    } finally {
      isLoading.value = false
    }
    
    return companySettings.value
  }

  // Format date based on company settings
  const formatDate = (dateString, settings = null) => {
    if (!dateString) return ''
    
    const settingsToUse = settings || companySettings.value
    if (!settingsToUse?.date_format) {
      // Default format if no company settings
      return new Date(dateString).toLocaleDateString()
    }

    const date = new Date(dateString)
    if (isNaN(date.getTime())) return ''

    const format = settingsToUse.date_format
    
    // Convert PHP date format to JavaScript
    const formatMap = {
      'Y': date.getFullYear(),
      'y': date.getFullYear().toString().slice(-2),
      'm': String(date.getMonth() + 1).padStart(2, '0'),
      'd': String(date.getDate()).padStart(2, '0'),
      'M': date.toLocaleDateString('en-US', { month: 'short' }),
      'F': date.toLocaleDateString('en-US', { month: 'long' })
    }

    let formattedDate = format
    Object.entries(formatMap).forEach(([key, value]) => {
      formattedDate = formattedDate.replace(new RegExp(key, 'g'), value)
    })

    return formattedDate
  }


  // 2025-10-22 04:55:52 thi is my date tim now modify the function
  const formatDateWithTime = (dateString, settings = null) => {
    if (!dateString) return ''
    
    const settingsToUse = settings || companySettings.value
    if (!settingsToUse?.date_format) {
      // Default format if no company settings
      return new Date(dateString).toLocaleString()
    }

    const date = new Date(dateString)
    if (isNaN(date.getTime())) return ''

    const format = settingsToUse.date_format
    
    // Convert PHP date format to JavaScript
    const formatMap = {
      'H': date.getHours(),
      'i': date.getMinutes(),
      's': date.getSeconds(),
    }

    let formattedDateWithTime = format
    Object.entries(formatMap).forEach(([key, value]) => {
      formattedDateWithTime = formattedDateWithTime.replace(new RegExp(key, 'g'), value)
    })

    return formattedDateWithTime
  }

  // Format amount based on company settings
  const formatAmount = (amount, settings = null) => {
    if (amount === null || amount === undefined || isNaN(amount)) return '0'
    
    const settingsToUse = settings || companySettings.value
    if (!settingsToUse) {
      // Default formatting if no company settings
      return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
      }).format(amount)
    }

    const {
      currency = '$',
      currency_position = 'Before Amount',
      precision = 2,
      thousand_separator = ',',
      decimal_separator = '.'
    } = settingsToUse

    // Convert amount to number
    const numAmount = parseFloat(amount)
    
    // Format number with custom separators
    const parts = numAmount.toFixed(precision).split('.')
    const integerPart = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, thousand_separator)
    const decimalPart = parts[1] || '0'.repeat(precision)
    
    let formattedAmount = integerPart
    if (precision > 0) {
      formattedAmount += decimal_separator + decimalPart
    }

    // Add currency symbol based on position
    if (currency_position === 'Before Amount') {
      return currency + formattedAmount
    } else {
      return formattedAmount + currency
    }
  }

  // Format amount based on company settings
  const formatNumber = (number, settings = null) => {
    if (number === null || number === undefined || isNaN(number)) return '0'
    
    const settingsToUse = settings ?? companySettings?.value ?? {}

    const {
      precision = 2,
      thousand_separator = ',',
      decimal_separator = '.'
    } = settingsToUse

    // Convert amount to number
    const numAmount = parseFloat(number)
    
    // Format number with custom separators
    const parts = numAmount.toFixed(precision).split('.')
    const integerPart = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, thousand_separator)
    const decimalPart = parts[1] || '0'.repeat(precision)
    
    let formattedNumber = integerPart
    if (precision > 0) {
      formattedNumber += decimal_separator + decimalPart
    }
    return formattedNumber
  }
  // Format amount based on company settings
  const formatNumberInvoice = (number, settings = null) => {
    if (number === null || number === undefined || isNaN(number)) return '0'
    
    const settingsToUse = settings ?? companySettings?.value ?? {}

    const {
      precision = 2,
      thousand_separator = ',',
      decimal_separator = '.'
    } = settingsToUse

    // Convert amount to number
    const numAmount = parseFloat(number)
    
    // Format number with custom separators
    const parts = numAmount.toFixed(2).split('.')
    const integerPart = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, thousand_separator)
    const decimalPart = parts[1] || '0'.repeat(precision)
    
    let formattedNumber = integerPart
    if (precision > 0) {
      formattedNumber += decimal_separator + decimalPart
    }
    return formattedNumber
  }

  // Get serial number for pagination (reverse order)
  const getSerialNumber = (index, total, currentPage, perPage) => {
    const startIndex = (currentPage - 1) * perPage
    return total - (startIndex + index)
  }

  const formatNumberPrecision = (number) => {
    if (number === null || number === undefined || isNaN(number)) return '0'
    return parseFloat(number).toFixed(companyPrecision.value)
  }

  return {
    companySettings,
    isLoading,
    fetchCompanySettings,
    formatDate,
    formatDateWithTime,
    formatAmount,
    formatNumber,
    formatNumberInvoice,
    getSerialNumber,
    companyPrecision,
    formatNumberPrecision
  }
} 