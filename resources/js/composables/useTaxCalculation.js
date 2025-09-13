import { ref, computed } from 'vue'

export function useTaxCalculation() {
    const companyInfo = ref(null)
    const customerInfo = ref(null)
    const items = ref([])

    // Fetch company tax settings
    const fetchCompanyTaxSettings = async () => {
        try {
            const response = await $api('/get-company-info')
            companyInfo.value = response.data
        } catch (error) {
            console.error('Error fetching company tax settings:', error)
        }
    }

    // Fetch customer information
    const fetchCustomerInfo = async (customerId) => {
        if (!customerId) {
            console.warn('No customer ID provided for fetchCustomerInfo')
            return
        }
        try {
            const response = await $api(`/customers/${customerId}`)
            if (response.success) {
                customerInfo.value = response.data
            } else {
                console.error('Failed to fetch customer info:', response.message)
            }
        } catch (error) {
            console.error('Error fetching customer info:', error)
        }
    }

    // Fetch items with tax information
    const fetchItemsWithTax = async () => {
        try {
            const response = await $api('/get-all-type-item-list')
            items.value = response.data
        } catch (error) {
            console.error('Error fetching items:', error)
        }
    }

    // Calculate tax for a single item
    const calculateItemTax = (itemId, quantity, price, customerState = null) => {
        if (!companyInfo.value || companyInfo.value.collect_tax !== 'Yes') {
            return { totalTax: 0, taxBreakdown: {} }
        }

        const item = items.value.find(item => item.id === itemId)
        if (!item || !item.tax_information) {
            return { totalTax: 0, taxBreakdown: {} }
        }

        let taxAmount = 0
        const subtotal = quantity * price

        try {
            const taxInfo = JSON.parse(item.tax_information)
            const isInclusive = companyInfo.value.tax_type === 'Inclusive'
            
            if (companyInfo.value.tax_is_gst === 'Yes') {
                // GST calculation (Indian tax system)
                const result = calculateGSTTax(taxInfo, subtotal, isInclusive, customerState)
                taxAmount = result.totalTax
                return { totalTax: taxAmount, taxBreakdown: result.taxBreakdown }
            } else {
                // Regular tax calculation
                const result = calculateRegularTax(taxInfo, subtotal, isInclusive)
                taxAmount = result.totalTax
                return { totalTax: taxAmount, taxBreakdown: result.taxBreakdown }
            }
        } catch (error) {
            console.error('Error parsing tax information:', error)
            return { totalTax: 0, taxBreakdown: {} }
        }
    }

    // Calculate GST tax (Indian tax system)
    const calculateGSTTax = (taxInfo, subtotal, isInclusive, customerState = null) => {
        let totalTaxAmount = 0
        const taxBreakdown = {}

        let isSameState = true // Default to same state
        
        if (customerState !== null && customerState !== undefined) {
            isSameState = customerState === 'Same'
        } else if (customerInfo.value && customerInfo.value.same_or_diff_state) {
            isSameState = customerInfo.value.same_or_diff_state === 'Same'
        } else {
            console.log('No customer state info available, defaulting to Same state')
        }
        
        // Filter tax rates based on state
        let applicableTaxInfo = []

        
        if (isSameState) {
            // Same state: Apply CGST and SGST, ignore IGST
            applicableTaxInfo = taxInfo.filter(tax => 
                tax.tax === 'CGST' || tax.tax === 'SGST'
            )
        } else {
            // Different state: Apply only IGST, ignore CGST and SGST
            applicableTaxInfo = taxInfo.filter(tax => tax.tax === 'IGST')
        }
        
        const totalTaxRate = applicableTaxInfo.reduce((sum, tax) => sum + (parseFloat(tax.tax_rate) || 0), 0)

        if (isInclusive) {
            // For inclusive tax, calculate backwards from total
            const grossAmount = subtotal
            const netAmount = grossAmount / (1 + (totalTaxRate / 100))
            totalTaxAmount = grossAmount - netAmount
            
            // Calculate individual tax amounts proportionally
            applicableTaxInfo.forEach(tax => {
                const taxRate = parseFloat(tax.tax_rate) || 0
                const taxAmount = (totalTaxAmount * taxRate) / totalTaxRate
                taxBreakdown[tax.tax] = parseFloat(taxAmount.toFixed(2))
            })
        } else {
            // For exclusive tax, calculate forward from subtotal
            applicableTaxInfo.forEach(tax => {
                const taxRate = parseFloat(tax.tax_rate) || 0
                const taxAmount = (subtotal * taxRate) / 100
                totalTaxAmount += taxAmount
                taxBreakdown[tax.tax] = parseFloat(taxAmount.toFixed(2))
            })
        }

        return { totalTax: totalTaxAmount, taxBreakdown }
    }

    // Calculate regular tax
    const calculateRegularTax = (taxInfo, subtotal, isInclusive) => {
        let totalTaxAmount = 0
        const taxBreakdown = {}
        const totalTaxRate = taxInfo.reduce((sum, tax) => sum + (parseFloat(tax.tax_rate) || 0), 0)

        if (isInclusive) {
            // For inclusive tax, calculate backwards from total
            const grossAmount = subtotal
            const netAmount = grossAmount / (1 + (totalTaxRate / 100))
            totalTaxAmount = grossAmount - netAmount
            
            // Calculate individual tax amounts proportionally
            taxInfo.forEach(tax => {
                const taxRate = parseFloat(tax.tax_rate) || 0
                const taxAmount = (totalTaxAmount * taxRate) / totalTaxRate
                taxBreakdown[tax.tax] = parseFloat(taxAmount.toFixed(2))
            })
        } else {
            // For exclusive tax, calculate forward from subtotal
            taxInfo.forEach(tax => {
                const taxRate = parseFloat(tax.tax_rate) || 0
                const taxAmount = (subtotal * taxRate) / 100
                totalTaxAmount += taxAmount
                taxBreakdown[tax.tax] = parseFloat(taxAmount.toFixed(2))
            })
        }

        return { totalTax: totalTaxAmount, taxBreakdown }
    }

    // Get item tax information for display
    const getItemTaxInfo = (itemId) => {
        const item = items.value.find(item => item.id === itemId)
        if (!item || !item.tax_information) {
            return null
        }

        try {
            return JSON.parse(item.tax_information)
        } catch (error) {
            console.error('Error parsing tax information:', error)
            return null
        }
    }

    // Check if tax calculation is enabled
    const isTaxEnabled = computed(() => {
        return companyInfo.value && companyInfo.value.collect_tax === 'Yes'
    })

    // Check if GST is enabled
    const isGSTEnabled = computed(() => {
        return companyInfo.value && companyInfo.value.tax_is_gst === 'Yes'
    })

    // Get customer state for GST calculation
    const getCustomerState = computed(() => {
        return customerInfo.value?.same_or_diff_state || 'Same'
    })

    // Method to manually update customer and trigger tax recalculation
    const updateCustomerAndRecalculateTax = async (customerId) => {
        if (customerId) {
            await fetchCustomerInfo(customerId)
        } else {
            customerInfo.value = null
        }
    }

    return {
        companyInfo,
        customerInfo,
        items,
        fetchCompanyTaxSettings,
        fetchCustomerInfo,
        fetchItemsWithTax,
        calculateItemTax,
        getItemTaxInfo,
        isTaxEnabled,
        isGSTEnabled,
        getCustomerState,
        updateCustomerAndRecalculateTax
    }
} 