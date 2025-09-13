import { defineStore } from 'pinia'

export const useShoppingCartStore = defineStore('shoppingCart', {
  state: () => ({
    items: [],
    deliveryCharge: 60, // Fixed delivery charge as requested
    companyInfo: null,
    itemsWithTax: []
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
    
    // Tax amount using proper tax calculation
    taxAmount: (state) => {
      if (!state.companyInfo || state.companyInfo.collect_tax !== 'Yes') {
        return 0
      }
      
      let totalTax = 0
      const isInclusive = state.companyInfo.tax_type === 'Inclusive'

      state.items.forEach(item => {
        const itemSubtotal = item.quantity * item.price
        const itemTax = calculateItemTax(state, item.id, item.quantity, item.price)
        
        if (isInclusive) {
          // For inclusive tax, subtotal is price minus tax
          totalTax += itemTax
        } else {
          // For exclusive tax, subtotal is just price
          totalTax += itemTax
        }
      })

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
    
    // Get cart data for checkout
    getCartData() {
      return {
        items: this.items,
        subtotal: this.subtotal,
        taxAmount: this.taxAmount,
        deliveryCharge: this.deliveryCharge,
        total: this.total,
        itemCount: this.itemCount
      }
    }
  }
})

// Helper function to calculate tax for a single item
function calculateItemTax(state, itemId, quantity, price, customerState = null) {

  if (!state.companyInfo || state.companyInfo.collect_tax !== 'Yes') {
    return 0
  }

  const item = state.itemsWithTax.find(item => item.id === itemId)
  if (!item || !item.tax_information) {
    return 0
  }

  let taxAmount = 0
  const subtotal = quantity * price

  try {
    const taxInfo = JSON.parse(item.tax_information)
    const isInclusive = state.companyInfo.tax_type === 'Inclusive'
    
    if (state.companyInfo.tax_is_gst === 'Yes') {
      // GST calculation (Indian tax system)
      taxAmount = calculateGSTTax(taxInfo, subtotal, isInclusive, customerState)
    } else {
      // Regular tax calculation
      taxAmount = calculateRegularTax(taxInfo, subtotal, isInclusive)
    }
  } catch (error) {
    console.error('Error parsing tax information:', error)
    return 0
  }

  return taxAmount
}

// Calculate GST tax (Indian tax system)
function calculateGSTTax(taxInfo, subtotal, isInclusive, customerState = null) {
  let totalTaxAmount = 0
  
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
  } else {
    // For exclusive tax, calculate forward from subtotal
    applicableTaxInfo.forEach(tax => {
      const taxRate = parseFloat(tax.tax_rate) || 0
      const taxAmount = (subtotal * taxRate) / 100
      totalTaxAmount += taxAmount
    })
  }

  return totalTaxAmount
}

// Calculate regular tax
function calculateRegularTax(taxInfo, subtotal, isInclusive) {
  let totalTaxAmount = 0
  const totalTaxRate = taxInfo.reduce((sum, tax) => sum + (parseFloat(tax.tax_rate) || 0), 0)

  if (isInclusive) {
    // For inclusive tax, calculate backwards from total
    const grossAmount = subtotal
    const netAmount = grossAmount / (1 + (totalTaxRate / 100))
    totalTaxAmount = grossAmount - netAmount
  } else {
    // For exclusive tax, calculate forward from subtotal
    taxInfo.forEach(tax => {
      const taxRate = parseFloat(tax.tax_rate) || 0
      const taxAmount = (subtotal * taxRate) / 100
      totalTaxAmount += taxAmount
    })
  }

  return totalTaxAmount
} 