import { ref, computed } from 'vue'
import { $api } from '@/utils/api'
import { useErrorHandler } from '@/composables/useErrorHandler'

export function useSaleEdit() {
    const { handleError } = useErrorHandler()
    
    // State
    const isEditing = ref(false)
    const editingSaleId = ref(null)
    const originalSaleData = ref(null)
    const isLoadingSaleData = ref(false)
    
    // Computed
    const isInEditMode = computed(() => isEditing.value && editingSaleId.value)
    
    // Methods
    const loadSaleForEdit = async (saleId) => {
        if (!saleId) return null
    
        try {
            isLoadingSaleData.value = true
            isEditing.value = true
            editingSaleId.value = saleId
            
            const response = await $api(`/get-sale-for-edit/${saleId}`, { method: 'GET' })
            
            if (response.success && response.data) {
                originalSaleData.value = response.data
                return response.data
            } else {
                throw new Error(response.message || 'Failed to load sale data')
            }
        } catch (error) {
            handleError('load-sale-edit', error)
            return null
        } finally {
            isLoadingSaleData.value = false
        }
    }
    
    const updateSale = async (saleId, orderData) => {
        if (!saleId) {
            throw new Error('Sale ID is required for update')
        }
        
        try {
            const response = await $api(`/update-order/${saleId}`, {
                method: 'PUT',
                body: JSON.stringify(orderData)
            })
            
            if (response.success) {
                // Reset edit mode after successful update
                resetEditMode()
                return response.data
            } else {
                throw new Error(response.message || 'Failed to update sale')
            }
        } catch (error) {
            handleError('update-sale', error)
            throw error
        }
    }
    
    const resetEditMode = () => {
        isEditing.value = false
        editingSaleId.value = null
        originalSaleData.value = null
        isLoadingSaleData.value = false
    }
    
    const cancelEdit = () => {
        resetEditMode()
    }
    
    return {
        // State
        isEditing,
        editingSaleId,
        originalSaleData,
        isLoadingSaleData,
        
        // Computed
        isInEditMode,
        
        // Methods
        loadSaleForEdit,
        updateSale,
        resetEditMode,
        cancelEdit
    }
}
