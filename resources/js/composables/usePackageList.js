import { ref, computed } from 'vue'
import { usePagination } from './usePagination'
import { toast } from 'vue3-toastify'

export function usePackageList() {
  // Initialize pagination
  const {
    currentPage,
    perPage,
    totalItems,
    totalPages,
    searchQuery,
    isLoading,
    error,
    paginationInfo,
    updatePagination,
    goToPage,
    setSearchQuery,
    setLoading,
    setError,
    clearError
  } = usePagination()

  // Reactive data
  const packages = ref([])
  const selectedPackage = ref(null)
  const packageDetails = ref(null)
  const usageRows = ref([])
  const sendSMS = ref(false)
  const sendEmail = ref(false)
  const sendWhatsapp = ref(false)
  const loadingPackageDetails = ref(false)

  // Debounced search
  let searchTimeout = null

  // Methods
  const fetchPackages = async () => {
    try {
      setLoading(true)
      clearError()
      
      const res = await $api('/packages', { 
        method: 'GET',
        query: {
          page: currentPage.value,
          itemsPerPage: perPage.value,
          q: searchQuery.value
        }
      })
      
      if (res.success) {
        packages.value = res.data.packages || []
        updatePagination({
          total: res.data.total || 0,
          current_page: res.data.current_page || 1,
          last_page: res.data.last_page || 1
        })
      } else {
        setError('Failed to fetch packages')
      }
    } catch (error) {
      console.error('Error fetching packages:', error)
      setError('Failed to fetch packages. Please try again.')
    } finally {
      setLoading(false)
    }
  }

  const showPackageDetails = async (pkg) => {
    try {
      setLoading(true)
      const res = await $api(`/packages/${pkg.sale_id}`, { method: 'GET' })
      selectedPackage.value = pkg
      if (res.success) {
        packageDetails.value = res.data
        usageRows.value = [] // Reset usage rows when showing new package details
        loadingPackageDetails.value = false
      }
    } catch (error) {
      console.error('Error fetching package details:', error)
    } finally {
      setLoading(false)
      loadingPackageDetails.value = false
    }
  }

  const selectPackage = (pkg) => {
    loadingPackageDetails.value = true
    selectedPackage.value = pkg
    showPackageDetails(pkg)
    document.body.style.paddingRight = '0px'
  }

  const handleSearch = () => {
    // Clear previous timeout
    if (searchTimeout) {
      clearTimeout(searchTimeout)
    }
    
    // Set new timeout for debounced search
    searchTimeout = setTimeout(() => {
      goToPage(1) // Reset to first page when searching
      fetchPackages()
    }, 300)
  }

  const handlePageChange = (page) => {
    goToPage(page)
    fetchPackages()
  }

  const clearSelection = () => {
    selectedPackage.value = null
    packageDetails.value = null
    usageRows.value = []
  }

  // Usage management methods
  const addServiceToUsage = () => {
    const maxUsageRows = packageDetails.value?.included_items?.length || 0
    const availableServices = packageDetails.value?.included_items?.filter(service => service.remaining > 0) || []
    
    if (usageRows.value.length >= maxUsageRows) {
      toast(`You can only add usage for ${maxUsageRows} services`, {
        type: 'warning'
      })
      return
    }

    // Check if all available services are already added
    const usedServiceIds = usageRows.value
      .filter(row => row.service)
      .map(row => row.service.item_id)
    
    const remainingServices = availableServices.filter(service => 
      !usedServiceIds.includes(service.item_id)
    )

    if (remainingServices.length === 0) {
      toast('All available services have been added to usage', {
        type: 'warning'
      })
      return
    }

    usageRows.value.push({
      service: null,
      date: new Date().toISOString().split('T')[0],
      time: new Date().toTimeString().split(' ')[0],
      qty: 1,
      item_id: null
    })
  }

  const removeUsage = (index) => {
    usageRows.value.splice(index, 1)
  }

  const getMaxQty = (service) => {
    if (!service) return 0
    return service.remaining || 0
  }

  

  const onServiceChange = (event, index) => {
    const selected = usageRows.value[index].service

    if (!selected) return

    const isDuplicate = usageRows.value.some((row, i) =>
      i !== index && row.service?.item_id === selected.item_id
    )
  
    if (isDuplicate) {
      toast("This service is already selected", { type: 'error' })
      usageRows.value[index].service = null
      usageRows.value[index].item_id = null
      return
    }
  
    usageRows.value[index].service = selected
    usageRows.value[index].item_id = selected.item_id
    usageRows.value[index].qty = Math.min(usageRows.value[index].qty, selected.remaining)
  }
  

  const validateQty = (event, index) => {
    const service = usageRows.value[index].service
    if (service) {
      const maxQty = service.remaining
      const currentQty = parseInt(event.target.value)
      if (currentQty > maxQty) {
        usageRows.value[index].qty = maxQty
        toast("You can't put more then remaining quantity. Remaining quantity is " + maxQty, {
          type: 'error'
        })
      }
    }
  }

  const submitUsage = async () => {
    try {
      // Validate that all rows have required data
      const validRows = usageRows.value.filter(row => 
        row.service && row.service.item_id && row.qty > 0
      )
      
      if (validRows.length === 0) {
        toast('Please add at least one service usage', {
          type: 'error'
        })
        return
      }

      // Check for duplicate services
      const serviceIds = validRows.map(row => row.service.item_id)
      const uniqueServiceIds = [...new Set(serviceIds)]
      
      if (serviceIds.length !== uniqueServiceIds.length) {
        toast('Duplicate services are not allowed. Please remove duplicates.', {
          type: 'error'
        })
        return
      }

      // Validate quantities don't exceed remaining
      for (const row of validRows) {
        if (row.qty > row.service.remaining) {
          toast(`Quantity for ${row.service.service_name} exceeds remaining quantity (${row.service.remaining})`, {
            type: 'error'
          })
          return
        }
      }

      const pkg = selectedPackage.value
      const customer_id = packageDetails.value.package_summary.customer.id

      const endDate = new Date(packageDetails.value.package_summary.end_date)
      const today = new Date()

      if (packageDetails.value.package_summary.end_date) {
        if (today >= endDate) {
          toast('Package has expired. Usage cannot be submitted.', {
            type: 'error'
          })
          return
        }
      }

      const branch_info = useCookie("branch_info").value || 0

      // Prepare usage data array
      const usages = validRows.map(row => ({
        package_item_id: row.service.item_id,
        usages_qty: row.qty,
        usages_date: row.date,
        usages_time: row.time
      }))

      const res = await $api('/packages/add-usage', {
        method: 'POST',
        body: {
          customer_id: customer_id,
          package_id: pkg.package_id,
          sale_id: pkg.sale_id,
          branch_id: branch_info.id,
          usages: usages, // Send as array
          send_sms: sendSMS.value,
          send_email: sendEmail.value,
          send_whatsapp: sendWhatsapp.value
        }
      })

      if (res.success) {
        usageRows.value = []
        await showPackageDetails(pkg) // Refresh details
        toast(res.message || 'Usage added successfully', {
          type: 'success'
        })
      } else {
        toast(res.message || 'Failed to add usage', {
          type: 'error'
        })
      }
    } catch (error) {
      console.error('Error submitting usage:', error)
      toast('Failed to add usage', {
        type: 'error'
      })
    }
  }

  // Computed properties
  const hasPackages = computed(() => packages.value.length > 0)
  const hasSelection = computed(() => !!selectedPackage.value)
  const hasPackageDetails = computed(() => !!packageDetails.value)
  const availableServices = computed(() => 
    packageDetails.value?.included_items?.filter(service => service.remaining > 0) || []
  )
  const usedServiceIds = computed(() => 
    usageRows.value
      .filter(row => row.service)
      .map(row => row.service.item_id)
  )
  const remainingServices = computed(() => 
    availableServices.value.filter(service => 
      !usedServiceIds.value.includes(service.item_id)
    )
  )
  const canAddMoreServices = computed(() => 
    usageRows.value.length < (packageDetails.value?.included_items?.length || 0) && 
    remainingServices.value.length > 0
  )

  return {
    // State
    packages,
    selectedPackage,
    packageDetails,
    usageRows,
    sendSMS,
    sendEmail,
    sendWhatsapp,
    
    // Pagination state
    currentPage,
    perPage,
    totalItems,
    totalPages,
    searchQuery,
    isLoading,
    error,
    paginationInfo,
    
    // Computed
    hasPackages,
    hasSelection,
    hasPackageDetails,
    availableServices,
    usedServiceIds,
    remainingServices,
    canAddMoreServices,
    
    // Methods
    fetchPackages,
    showPackageDetails,
    selectPackage,
    handleSearch,
    handlePageChange,
    clearSelection,
    
    // Usage methods
    addServiceToUsage,
    removeUsage,
    getMaxQty,
    onServiceChange,
    validateQty,
    submitUsage,
    
    // Pagination methods
    updatePagination,
    goToPage,
    setSearchQuery,
    setLoading,
    setError,
    clearError,
    loadingPackageDetails
  }
}
