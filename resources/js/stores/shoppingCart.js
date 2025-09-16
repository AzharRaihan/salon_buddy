import { defineStore } from 'pinia'

export const useShoppingCartStore = defineStore('shoppingCart', {
  state: () => ({
    items: [],
    deliveryCharge: 0, // Fixed delivery charge as requested
    companyInfo: null,
    itemsWithTax: [],
    deliveryAreas: [],
    selectedDeliveryArea: null,
    selectedDeliveryAreaId: null,
    taxBreakdown: {}, // Store detailed tax breakdown
    totalPaid: 0, // Total amount paid
    totalDue: 0, // Total amount due
    paymentMethodId: null, // Selected payment method
  }),

  getters: {
    // Cart item count
    itemCount: (state) => state.items.reduce((total, item) => total + item.quantity, 0),
    
    // Check if cart has items
    hasItems: (state) => state.items.length > 0,
    
    // Subtotal (before tax)
    subtotal: (state) => {
      return state.items.reduce((total, item) => {
        return total + (item.price * item.quantity)
      }, 0)
    },
    
    // Tax amount using proper tax calculation with breakdown
    taxAmount: (state) => {
      if (!state.companyInfo || state.companyInfo.collect_tax !== 'Yes') {
        return 0
      }
      
      let totalTax = 0
      const taxBreakdown = {}
      const isInclusive = state.companyInfo.tax_type === 'Inclusive'

      state.items.forEach(item => {
        const itemSubtotal = item.quantity * item.price
        const itemTaxResult = calculateItemTaxWithBreakdown(state, item.id, item.quantity, item.price)
        
        totalTax += itemTaxResult.totalTax
        
        // Aggregate tax breakdown
        Object.keys(itemTaxResult.taxBreakdown).forEach(taxType => {
          if (!taxBreakdown[taxType]) {
            taxBreakdown[taxType] = 0
          }
          taxBreakdown[taxType] += itemTaxResult.taxBreakdown[taxType]
        })
      })

      // Update tax breakdown in state
      state.taxBreakdown = taxBreakdown

      return totalTax
    },
    
    // Total including tax and delivery
    total: (state) => {
      const subtotal = state.items.reduce((total, item) => {
        return total + (item.price * item.quantity)
      }, 0)

      if (!state.companyInfo || state.companyInfo.collect_tax !== 'Yes') {
        return subtotal + state.deliveryCharge
      }
      
      const taxAmount = state.taxAmount
      const isInclusive = state.companyInfo.tax_type === 'Inclusive'
      
      if (isInclusive) {
        // For inclusive tax, total is subtotal + delivery charge
        return subtotal + state.deliveryCharge
      } else {
        // For exclusive tax, total is subtotal + tax + delivery charge
        return subtotal + taxAmount + state.deliveryCharge
      }
    },

    // Total paid amount
    totalPaidAmount: (state) => {
      return state.totalPaid
    },

    // Total due amount
    totalDueAmount: (state) => {
      return Math.max(0, state.total - state.totalPaid)
    },
    
    // Format currency helper
    formatCurrency: () => (amount) => {
      return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
      }).format(amount)
    }
  },

  actions: {
    // Initialize tax settings
    async initializeTaxSettings() {
      try {
        // Fetch company info
        const companyResponse = await $api('/get-company-info')
        if (companyResponse.success) {
          this.companyInfo = companyResponse.data
        }

        // Fetch delivery area
        const deliveryAreaResponse = await $api('/get-delivery-areas')
        if (deliveryAreaResponse.success) {
          this.deliveryAreas = deliveryAreaResponse.data
        }
        
        // Fetch items with tax info
        const itemsResponse = await $api('/get-all-type-item-list')
        if (itemsResponse.success) {
          this.itemsWithTax = itemsResponse.data
        }
      } catch (error) {
        console.error('Error initializing tax settings:', error)
        // Set default company info if API fails
        this.companyInfo = { 
          collect_tax: 'Yes',
          tax_type: 'Exclusive',
          tax_is_gst: 'No'
        }
      }
    },


    setDeliveryArea(areaId) {
      const area = this.deliveryAreas.find(a => a.id === areaId)
      if (area) {
        this.selectedDeliveryArea = area
        this.selectedDeliveryAreaId = areaId
        this.deliveryCharge = parseFloat(area.delivery_charge) || 0
      } else {
        this.selectedDeliveryArea = null
        this.selectedDeliveryAreaId = null
        this.deliveryCharge = 0
      }
    },
    

    // Add item to cart
    addItem(item) {
      const existingItemIndex = this.items.findIndex(cartItem => 
        cartItem.id === item.id && cartItem.type === item.type
      )
      if (existingItemIndex !== -1) {
        // Item exists, increment quantity
        this.items[existingItemIndex].quantity += item.quantity || 1
      } else {
        // New item, add to cart
        this.items.push({
          id: item.id,
          name: item.name,
          price: parseFloat(item.price),
          quantity: item.quantity || 1,
          image: item.image || null,
          type: item.type || 'Product',
          services: item.services || null,
          description: item.description || null
        })
      }
      
      // Save to localStorage
      this.saveToStorage()
    },
    
    // Remove item from cart
    removeItem(itemId, itemType = 'Product') {
      const index = this.items.findIndex(item => 
        item.id === itemId && item.type === itemType
      )
      if (index !== -1) {
        this.items.splice(index, 1)
        this.saveToStorage()
      }
    },
    
    // Update item quantity
    updateQuantity(itemId, quantity, itemType = 'Product') {
      const item = this.items.find(item => 
        item.id === itemId && item.type === itemType
      )
      if (item) {
        if (quantity <= 0) {
          this.removeItem(itemId, itemType)
        } else {
          item.quantity = quantity
          this.saveToStorage()
        }
      }
    },
    
    // Increment quantity
    incrementQuantity(itemId, itemType = 'Product') {
      const item = this.items.find(item => 
        item.id === itemId && item.type === itemType
      )
      if (item) {
        item.quantity++
        this.saveToStorage()
      }
    },
    
    // Decrement quantity
    decrementQuantity(itemId, itemType = 'Product') {
      const item = this.items.find(item => 
        item.id === itemId && item.type === itemType
      )
      if (item) {
        if (item.quantity > 1) {
          item.quantity--
        } else {
          this.removeItem(itemId, itemType)
        }
        this.saveToStorage()
      }
    },
    
    // Clear entire cart
    clearCart() {
      this.items = []
      this.saveToStorage()
    },
    
    // Save cart to localStorage
    saveToStorage() {
      localStorage.setItem('shopping_cart', JSON.stringify(this.items))
    },
    
    // Load cart from localStorage
    loadFromStorage() {
      const saved = localStorage.getItem('shopping_cart')
      if (saved) {
        try {
          this.items = JSON.parse(saved)
        } catch (error) {
          console.error('Error loading cart from storage:', error)
          this.items = []
        }
      }
    },

    
    
    // Set payment amount
    setPaymentAmount(amount) {
      this.totalPaid = parseFloat(amount) || 0
      this.totalDue = Math.max(0, this.total - this.totalPaid)
    },

    // Set payment method
    setPaymentMethod(methodId) {
      this.paymentMethodId = methodId
    },

    // Get cart data for checkout
    getCartData() {
      return {
        items: this.items,
        subtotal: this.subtotal,
        taxAmount: this.taxAmount,
        taxBreakdown: this.taxBreakdown,
        deliveryCharge: this.deliveryCharge,
        total: this.total,
        totalPaid: this.totalPaid,
        totalDue: this.totalDue,
        itemCount: this.itemCount,
        selectedDeliveryArea: this.selectedDeliveryArea,
        paymentMethodId: this.paymentMethodId
      }
    }
  }
})

// Helper function to calculate tax for a single item with breakdown
function calculateItemTaxWithBreakdown(state, itemId, quantity, price, customerState = null) {
  if (!state.companyInfo || state.companyInfo.collect_tax !== 'Yes') {
    return { totalTax: 0, taxBreakdown: {} }
  }

  const item = state.itemsWithTax.find(item => item.id === itemId)
  if (!item || !item.tax_information) {
    return { totalTax: 0, taxBreakdown: {} }
  }

  const subtotal = quantity * price

  try {
    const taxInfo = JSON.parse(item.tax_information)
    const isInclusive = state.companyInfo.tax_type === 'Inclusive'
    
    if (state.companyInfo.tax_is_gst === 'Yes') {
      // GST calculation (Indian tax system)
      return calculateGSTTaxWithBreakdown(taxInfo, subtotal, isInclusive, customerState)
    } else {
      // Regular tax calculation
      return calculateRegularTaxWithBreakdown(taxInfo, subtotal, isInclusive)
    }
  } catch (error) {
    console.error('Error parsing tax information:', error)
    return { totalTax: 0, taxBreakdown: {} }
  }
}

// Helper function to calculate tax for a single item (legacy)
function calculateItemTax(state, itemId, quantity, price, customerState = null) {
  const result = calculateItemTaxWithBreakdown(state, itemId, quantity, price, customerState)
  return result.totalTax
}

// Calculate GST tax with breakdown (Indian tax system)
function calculateGSTTaxWithBreakdown(taxInfo, subtotal, isInclusive, customerState = null) {
  let totalTaxAmount = 0
  const taxBreakdown = {}
  
  // Determine if customer is in same state or different state
  const isSameState = customerState === 'Same'
  
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

// Calculate GST tax (Indian tax system) - legacy function
function calculateGSTTax(taxInfo, subtotal, isInclusive, customerState = null) {
  const result = calculateGSTTaxWithBreakdown(taxInfo, subtotal, isInclusive, customerState)
  return result.totalTax
}

// Calculate regular tax with breakdown
function calculateRegularTaxWithBreakdown(taxInfo, subtotal, isInclusive) {
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

// Calculate regular tax - legacy function
function calculateRegularTax(taxInfo, subtotal, isInclusive) {
  const result = calculateRegularTaxWithBreakdown(taxInfo, subtotal, isInclusive)
  return result.totalTax
} 