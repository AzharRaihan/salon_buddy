import { ref, computed } from 'vue'
import { $api } from '@/utils/api'
import { useI18n } from 'vue-i18n';
/**
 * Composable for managing About Us section data and statistics
 * @returns {Object} About section data and methods
 */
export function useAboutSection() {
// Reactive state
const aboutUs = ref({})
const isLoading = ref(false)
const error = ref(null)
const { t } = useI18n()


// Counter states
const countTotalServices = ref(0)
const countTotalStaff = ref(0) 
const countTotalCustomers = ref(0)
const countTotalDoneServices = ref(0)

// Base URL for assets
const baseUrl = import.meta.env.VITE_APP_URL || ''

// Computed properties
const stats = computed(() => [
  {
    value: countTotalServices.value,
    label: t('Total Services'),
    image: `${baseUrl}/public/assets/images/default-images/total-service.png`
  },
  {
    value: countTotalStaff.value,
    label: t('Expertise Staffs'), 
    image: `${baseUrl}/public/assets/images/default-images/expertise-staff.png`
  },
  {
    value: countTotalCustomers.value,
    label: t('Satisfied Clients'),
    image: `${baseUrl}/public/assets/images/default-images/satiesfied-clients.png`
  },
  {
    value: countTotalDoneServices.value,
    label: t('Done Services'),
    image: `${baseUrl}/public/assets/images/default-images/done-service.png`
  }
])

  // Methods
  const fetchAboutUs = async () => {
    try {
      isLoading.value = true
      error.value = null
      
      const [
        aboutUsRes,
        // servicesRes, 
        // staffRes,
        // customersRes,
        // doneServicesRes
      ] = await Promise.all([
        $api('/get-about-us'),
        // $api('/get-service-counter'),
        // $api('/get-staff-counter'), 
        // $api('/get-customer-counter'),
        // $api('/get-done-service-counter')
      ])

      aboutUs.value = aboutUsRes.data
      countTotalServices.value = aboutUsRes.data?.total_services_count
      countTotalStaff.value = aboutUsRes.data?.total_staff_count
      countTotalCustomers.value = aboutUsRes.data?.total_customers_count
      countTotalDoneServices.value = aboutUsRes.data?.total_done_services_count

    } catch (err) {
      error.value = err
      console.error('Error fetching about us data:', err)
    } finally {
      isLoading.value = false
    }
  }

  const resetData = () => {
    aboutUs.value = {}
    countTotalServices.value = 0
    countTotalStaff.value = 0
    countTotalCustomers.value = 0
    countTotalDoneServices.value = 0
    error.value = null
  }

  return {
    // State
    aboutUs,
    stats,
    isLoading,
    error,
    
    // Methods
    fetchAboutUs,
    resetData
  }
} 