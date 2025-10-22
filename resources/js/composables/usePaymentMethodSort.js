/**
 * Payment Method Sorting Composable
 * 
 * Handles drag-and-drop sorting functionality for payment methods
 * Provides methods to fetch, reorder, and persist sort order
 */

import { ref, computed } from 'vue'
import { toast } from 'vue3-toastify'
import { useI18n } from 'vue-i18n'

export function usePaymentMethodSort() {
    const { t } = useI18n()

    // State
    const paymentMethods = ref([])
    const loading = ref(false)
    const saving = ref(false)
    const error = ref(null)

    /**
     * Fetch all payment methods for sorting (without pagination)
     */
    const fetchPaymentMethodsForSorting = async () => {
        loading.value = true
        error.value = null

        try {
            const response = await $api('/payment-methods-for-sorting', {
                method: 'GET',
            })

            if (response.data && response.data.paymentMethods) {
                // Sort by sort_id ascending
                paymentMethods.value = response.data.paymentMethods.sort(
                    (a, b) => a.sort_id - b.sort_id
                )
            }
        } catch (err) {
            error.value = err.message || t('Failed to fetch payment methods')
            toast(error.value, { type: 'error' })
            console.error('Error fetching payment methods:', err)
        } finally {
            loading.value = false
        }
    }

    /**
     * Update the sort order of payment methods
     * @param {Array} reorderedItems - Array of payment methods in new order
     */
    const updateSortOrder = async (reorderedItems) => {
        saving.value = true
        error.value = null

        try {
            // Prepare the sort order data
            const sortData = reorderedItems.map((item, index) => ({
                id: item.id,
                sort_id: index + 1
            }))

            const response = await $api('/payment-methods/update-sort-order', {
                method: 'POST',
                body: {
                    sort_order: sortData
                }
            })

            if (response.success) {
                toast(t('Sort order updated successfully'), { type: 'success' })
                await fetchPaymentMethodsForSorting() // Refresh the list
                return true
            }
        } catch (err) {
            error.value = err.message || t('Failed to update sort order')
            toast(error.value, { type: 'error' })
            console.error('Error updating sort order:', err)
            return false
        } finally {
            saving.value = false
        }
    }

    /**
     * Handle drag and drop reordering
     * @param {Object} event - Drag event from draggable library
     */
    const handleDragEnd = async (event) => {
        if (event.oldIndex !== event.newIndex) {
            // Reorder the array
            const items = [...paymentMethods.value]
            const [movedItem] = items.splice(event.oldIndex, 1)
            items.splice(event.newIndex, 0, movedItem)
            
            paymentMethods.value = items
            
            // Update sort order in backend
            await updateSortOrder(items)
        }
    }

    /**
     * Reset sort order to default (by ID)
     */
    const resetSortOrder = async () => {
        try {
            const response = await $api('/payment-methods/reset-sort-order', {
                method: 'POST',
            })

            if (response.success) {
                toast(t('Sort order reset successfully'), { type: 'success' })
                await fetchPaymentMethodsForSorting()
                return true
            }
        } catch (err) {
            error.value = err.message || t('Failed to reset sort order')
            toast(error.value, { type: 'error' })
            return false
        }
    }

    // Computed properties
    const hasPaymentMethods = computed(() => paymentMethods.value.length > 0)
    const isProcessing = computed(() => loading.value || saving.value)

    return {
        // State
        paymentMethods,
        loading,
        saving,
        error,
        isProcessing,

        // Computed
        hasPaymentMethods,

        // Methods
        fetchPaymentMethodsForSorting,
        updateSortOrder,
        handleDragEnd,
        resetSortOrder,
    }
}

