import { ref, computed } from 'vue'
import { $api } from '@/utils/api'

export function useLoyaltyPoints() {
    const customerLoyaltyPoints = ref(0)
    const companyLoyaltySettings = ref({
        minimum_point_to_redeem: 0,
        loyalty_rate: 0
    })
    const loyaltyPointsNeeded = ref(0)
    const hasEnoughLoyaltyPoints = ref(false)
    const isLoading = ref(false)
    const error = ref(null)

    /**
     * Fetch customer loyalty points
     */
    const fetchCustomerLoyaltyPoints = async (customerId) => {
        if (!customerId) {
            customerLoyaltyPoints.value = 0
            return
        }

        try {
            isLoading.value = true
            error.value = null
            
            const response = await $api(`/customer-loyalty-points/${customerId}`, { method: 'GET' })
            
            if (response.success) {
                customerLoyaltyPoints.value = response.data.loyalty_points || 0
                companyLoyaltySettings.value = response.data.settings || {
                    minimum_point_to_redeem: 0,
                    loyalty_rate: 0
                }
            }
        } catch (err) {
            error.value = err.message || 'Failed to fetch loyalty points'
            console.error('Error fetching loyalty points:', err)
        } finally {
            isLoading.value = false
        }
    }

    /**
     * Fetch company loyalty settings
     */
    const fetchCompanyLoyaltySettings = async () => {
        try {
            isLoading.value = true
            error.value = null
            
            const response = await $api('/company-loyalty-settings', { method: 'GET' })
            
            if (response.success) {
                companyLoyaltySettings.value = response.data || {
                    minimum_point_to_redeem: 0,
                    loyalty_rate: 0
                }
            }
        } catch (err) {
            error.value = err.message || 'Failed to fetch loyalty settings'
            console.error('Error fetching loyalty settings:', err)
        } finally {
            isLoading.value = false
        }
    }

    /**
     * Calculate loyalty points needed for a sale
     */
    const calculateLoyaltyPointsNeeded = async (totalAmount, customerId) => {
        if (!customerId || !totalAmount || totalAmount <= 0) {
            loyaltyPointsNeeded.value = 0
            hasEnoughLoyaltyPoints.value = false
            return
        }

        try {
            isLoading.value = true
            error.value = null

            console.log(totalAmount, customerId)
            
            const response = await $api('/calculate-loyalty-points-needed', {
                method: 'POST',
                body: {
                    total_amount: totalAmount,
                    customer_id: customerId
                },
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            
            if (response.success) {
                loyaltyPointsNeeded.value = response.data.points_needed || 0
                hasEnoughLoyaltyPoints.value = response.data.has_enough_points || false
                
                // Update customer loyalty points if provided
                if (response.data.current_points !== undefined) {
                    customerLoyaltyPoints.value = response.data.current_points
                }
            }
        } catch (err) {
            error.value = err.message || 'Failed to calculate loyalty points'
            console.error('Error calculating loyalty points:', err)
        } finally {
            isLoading.value = false
        }
    }

    /**
     * Check if customer can use loyalty points
     */
    const canUseLoyaltyPoints = computed(() => {
        return customerLoyaltyPoints.value >= companyLoyaltySettings.value.minimum_point_to_redeem
    })

    /**
     * Calculate loyalty point value in currency
     */
    const calculateLoyaltyPointValue = (points) => {
        if (!points || !companyLoyaltySettings.value.loyalty_rate) {
            return 0
        }
        return points * companyLoyaltySettings.value.loyalty_rate
    }

    /**
     * Reset loyalty point state
     */
    const resetLoyaltyPoints = () => {
        customerLoyaltyPoints.value = 0
        loyaltyPointsNeeded.value = 0
        hasEnoughLoyaltyPoints.value = false
        error.value = null
    }

    /**
     * Check if customer is Walk-in Customer
     */
    const isWalkInCustomer = (customer) => {
        return !customer || 
               customer.name === 'Walk-in Customer' || 
               customer.name === 'Walking-in Customer'
    }

    return {
        // State
        customerLoyaltyPoints,
        companyLoyaltySettings,
        loyaltyPointsNeeded,
        hasEnoughLoyaltyPoints,
        isLoading,
        error,

        // Computed
        canUseLoyaltyPoints,

        // Methods
        fetchCustomerLoyaltyPoints,
        fetchCompanyLoyaltySettings,
        calculateLoyaltyPointsNeeded,
        calculateLoyaltyPointValue,
        resetLoyaltyPoints,
        isWalkInCustomer
    }
}

