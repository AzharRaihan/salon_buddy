import { ref, computed } from 'vue'
import { $api } from '@/utils/api'

export function usePromotions() {
  const promotions = ref([])
  const isLoading = ref(false)
  const error = ref(null)

  // Fetch all active promotions
  const fetchPromotions = async () => {
    try {
      isLoading.value = true
      error.value = null

      const response = await $api('/promotions', {
        method: 'GET'
      })
      
      if (response.success) {
        promotions.value = response.data?.promotions || []
      } else {
        console.error('Failed to fetch promotions:', response)
      }
    } catch (err) {
      error.value = err.message
      console.error('Error fetching promotions:', err)
    } finally {
      isLoading.value = false
    }
  }

  // Get promotions for a specific item with priority rules
  const getItemPromotions = (itemId) => {
    const currentDate = new Date()
    
    const applicablePromotions = promotions.value.filter(promotion => {
      // Check if promotion is active and within date range
      if (promotion.status !== 'Active') {
        return false
      }
      
      // Handle different date formats
      let startDate, endDate
      try {
        startDate = new Date(promotion.start_date)
        endDate = new Date(promotion.end_date)
        
        // Check if dates are valid
        if (isNaN(startDate.getTime()) || isNaN(endDate.getTime())) {
          console.log('Invalid date format for promotion:', promotion.id, promotion.start_date, promotion.end_date)
          return false
        }
      } catch (error) {
        console.log('Error parsing dates for promotion:', promotion.id, error)
        return false
      }
      
      if (currentDate < startDate || currentDate > endDate) {
        return false
      }
      
      // Check if this promotion applies to the item
      if (promotion.type === 'Discount') {
        // Global discount applies to all items
        if (!promotion.discount_item_id) {
          return true
        }
        // Item-specific discount applies only to specified item
        if (promotion.discount_item_id == itemId) {
          return true
        }
      }
      
      if (promotion.type === 'Free Item' && promotion.buy_item_id == itemId) {
        return true
      }
      
      return false
    })

    return applicablePromotions
  }

  // Get all applicable promotions for the entire order
  const getOrderPromotions = () => {
    const currentDate = new Date()
    
    return promotions.value.filter(promotion => {
      if (promotion.status !== 'Active') {
        return false
      }
      
      let startDate, endDate
      try {
        startDate = new Date(promotion.start_date)
        endDate = new Date(promotion.end_date)
        
        if (isNaN(startDate.getTime()) || isNaN(endDate.getTime())) {
          return false
        }
      } catch (error) {
        return false
      }
      
      if (currentDate < startDate || currentDate > endDate) {
        return false
      }
      
      return true
    })
  }

  // Calculate discount for an item with priority rules
  const calculateItemDiscount = (item, quantity = 1, orderPromotions = null) => {
    // If no order promotions provided, get them
    if (!orderPromotions) {
      orderPromotions = getOrderPromotions()
    }
    
    // Priority 1: Check for global discount (highest priority)
    const globalDiscount = orderPromotions.find(promotion => 
      promotion.type === 'Discount' && !promotion.discount_item_id
    )
    
    if (globalDiscount) {
      const discountValue = parseFloat(globalDiscount.discount) || 0
      const itemPrice = parseFloat(item.sale_price || item.price || 0)
      
      if (globalDiscount.discount_type === 'Percentage') {
        const itemDiscount = (itemPrice * quantity * discountValue / 100)
        console.log('Global percentage discount applied:', discountValue + '%', 'Amount:', itemDiscount)
        return itemDiscount
      } else if (globalDiscount.discount_type === 'Fixed') {
        // Fixed discount is applied per item, not per quantity
        const itemDiscount = Math.min(discountValue, itemPrice) * quantity
        console.log('Global fixed discount applied:', discountValue, 'Amount:', itemDiscount)
        return itemDiscount
      }
    }
    
    // Priority 2: Check for item-specific discount
    const itemSpecificDiscount = orderPromotions.find(promotion => 
      promotion.type === 'Discount' && 
      promotion.discount_item_id == item.id
    )
    
    if (itemSpecificDiscount) {
      const discountValue = parseFloat(itemSpecificDiscount.discount) || 0
      const itemPrice = parseFloat(item.sale_price || item.price || 0)
      
      if (itemSpecificDiscount.discount_type === 'Percentage') {
        const itemDiscount = (itemPrice * quantity * discountValue / 100)
        console.log('Item-specific percentage discount applied:', discountValue + '%', 'Amount:', itemDiscount)
        return itemDiscount
      } else if (itemSpecificDiscount.discount_type === 'Fixed') {
        // Fixed discount is applied per item, not per quantity
        const itemDiscount = Math.min(discountValue, itemPrice) * quantity
        console.log('Item-specific fixed discount applied:', discountValue, 'Amount:', itemDiscount)
        return itemDiscount
      }
    }
    
    return 0
  }

  // Get free items for buy-get promotions (only if no global discount exists)
  const getFreeItems = async (item, quantity = 1, orderPromotions = null) => {
    // If no order promotions provided, get them
    if (!orderPromotions) {
      orderPromotions = getOrderPromotions()
    }
    
    // Check if global discount exists - if so, no free items
    const globalDiscount = orderPromotions.find(promotion => 
      promotion.type === 'Discount' && !promotion.discount_item_id
    )
    
    if (globalDiscount) {
      console.log('Global discount exists, skipping free items')
      return []
    }
    
    const itemPromotions = orderPromotions.filter(promotion => 
      promotion.type === 'Free Item' && promotion.buy_item_id == item.id
    )
    
    const freeItems = []
    
    for (const promotion of itemPromotions) {
      const buyQty = parseInt(promotion.buy_qty) || 1
      const getQty = parseInt(promotion.get_qty) || 1
      
      // Calculate how many free items the customer gets
      const freeItemCount = Math.floor(quantity / buyQty) * getQty
      
      if (freeItemCount > 0) {
        try {
          // Fetch item details for the free item
          const response = await $api(`/item/${promotion.get_item_id}`)
          if (response.success) {
            freeItems.push({
              promotionId: promotion.id,
              promotionTitle: promotion.title,
              itemId: promotion.get_item_id,
              itemName: response.data.name,
              quantity: freeItemCount,
              isFree: true,
              sourceItemId: item.id,
              sourceItemName: item.name
            })
          }
        } catch (error) {
          // Fallback with just the ID
          freeItems.push({
            promotionId: promotion.id,
            promotionTitle: promotion.title,
            itemId: promotion.get_item_id,
            itemName: `Free Item (ID: ${promotion.get_item_id})`,
            quantity: freeItemCount,
            isFree: true,
            sourceItemId: item.id,
            sourceItemName: item.name
          })
        }
      }
    }
    
    return freeItems
  }

  // Apply promotions to order items with priority rules
  const applyPromotionsToOrder = async (orderItems) => {
    // Get all applicable promotions for the order
    const orderPromotions = getOrderPromotions()
    
    console.log('Applying promotions to order:', orderItems.length, 'items')
    console.log('Active promotions:', orderPromotions.length)
    
    // Separate regular items from free items
    const regularItems = orderItems.filter(item => !item.isFree)
    const existingFreeItems = orderItems.filter(item => item.isFree)
    
    const updatedItems = [...regularItems]
    const newFreeItems = []
    
    // Check if global discount exists
    const globalDiscount = orderPromotions.find(promotion => 
      promotion.type === 'Discount' && !promotion.discount_item_id
    )
    
    if (globalDiscount) {
      console.log('Global discount found:', globalDiscount.title, globalDiscount.discount, globalDiscount.discount_type)
    }
    
    // Process each regular item for promotions
    for (const item of updatedItems) {
      // Calculate discount for this item
      const discount = calculateItemDiscount(item, item.qty, orderPromotions)
      if (discount > 0) {
        item.discount = discount
        item.discountedPrice = (parseFloat(item.sale_price || item.price || 0) * item.qty) - discount
        console.log(`Applied discount to ${item.name}:`, discount, 'Final price:', item.discountedPrice)
      }
      
      // Only get free items if no global discount exists
      if (!globalDiscount) {
        const itemFreeItems = await getFreeItems(item, item.qty, orderPromotions)
        newFreeItems.push(...itemFreeItems)
        if (itemFreeItems.length > 0) {
          console.log(`Free items for ${item.name}:`, itemFreeItems.length)
        }
      }
    }
    
    // Remove all existing free items and add new ones
    const finalItems = [...updatedItems]
    
    // Add new free items to order (only if no global discount)
    if (!globalDiscount) {
      for (const freeItem of newFreeItems) {
        // Check if free item already exists in order
        const existingIndex = finalItems.findIndex(item => 
          item.id === freeItem.itemId && item.isFree
        )
        
        if (existingIndex >= 0) {
          // Update quantity of existing free item
          finalItems[existingIndex].qty = freeItem.quantity
        } else {
          // Add new free item
          const newFreeItem = {
            id: freeItem.itemId,
            name: freeItem.itemName,
            qty: freeItem.quantity,
            price: 0,
            sale_price: 0,
            isFree: true,
            promotionId: freeItem.promotionId,
            discount: 0,
            discountedPrice: 0,
            type: 'Product', // Default type for free items
            sourceItemId: freeItem.sourceItemId,
            sourceItemName: freeItem.sourceItemName
          }
          finalItems.push(newFreeItem)
        }
      }
    }
    
    console.log('Final order items:', finalItems.length, 'Total promotion discount:', calculateOrderDiscount(finalItems))
    return finalItems
  }

  // Calculate total discount for order
  const calculateOrderDiscount = (orderItems) => {
    return orderItems.reduce((total, item) => {
      return total + (item.discount || 0)
    }, 0)
  }

  // Calculate subtotal excluding free items and including promotion discounts
  // Note: Promotion discounts are already applied to individual items, so we use the discounted price
  const calculateSubtotalExcludingFree = (orderItems) => {
    return orderItems.reduce((total, item) => {
      if (item.isFree) return total
      
      // If item has a discounted price (from promotions), use that
      if (item.discountedPrice !== undefined && item.discountedPrice > 0) {
        return total + item.discountedPrice
      }
      
      // Otherwise calculate normally
      const itemPrice = parseFloat(item.sale_price || item.price || 0)
      const itemDiscount = parseFloat(item.discount || 0)
      return total + (itemPrice * item.qty) - itemDiscount
    }, 0)
  }

  // Check if global discount exists in current promotions
  const hasGlobalDiscount = () => {
    const orderPromotions = getOrderPromotions()
    return orderPromotions.some(promotion => 
      promotion.type === 'Discount' && !promotion.discount_item_id
    )
  }

  // Get promotion summary for debugging
  const getPromotionSummary = () => {
    const orderPromotions = getOrderPromotions()
    const globalDiscount = orderPromotions.find(promotion => 
      promotion.type === 'Discount' && !promotion.discount_item_id
    )
    
    return {
      totalPromotions: orderPromotions.length,
      hasGlobalDiscount: !!globalDiscount,
      globalDiscountDetails: globalDiscount ? {
        id: globalDiscount.id,
        title: globalDiscount.title,
        discount: globalDiscount.discount,
        discountType: globalDiscount.discount_type
      } : null,
      itemSpecificDiscounts: orderPromotions.filter(promotion => 
        promotion.type === 'Discount' && promotion.discount_item_id
      ).length,
      freeItemPromotions: orderPromotions.filter(promotion => 
        promotion.type === 'Free Item'
      ).length
    }
  }

  return {
    // State
    promotions,
    isLoading,
    error,
    
    // Methods
    fetchPromotions,
    getItemPromotions,
    getOrderPromotions,
    calculateItemDiscount,
    getFreeItems,
    applyPromotionsToOrder,
    calculateOrderDiscount,
    calculateSubtotalExcludingFree,
    hasGlobalDiscount,
    getPromotionSummary
  }
}
