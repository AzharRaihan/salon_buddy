import { ref, computed } from 'vue'

export function usePromotionRules() {
  // Promotion priority levels
  const PRIORITY_LEVELS = {
    GLOBAL_DISCOUNT: 1,      // Highest priority - blocks all other promotions
    ITEM_SPECIFIC_DISCOUNT: 2, // Second priority - only applies to specific items
    FREE_ITEM: 3             // Lowest priority - only works when no discounts exist
  }

  // Check if a promotion is currently active
  const isPromotionActive = (promotion) => {
    if (promotion.status !== 'Active') {
      return false
    }

    const currentDate = new Date()
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

    return currentDate >= startDate && currentDate <= endDate
  }

  // Get promotion priority level
  const getPromotionPriority = (promotion) => {
    if (promotion.type === 'Discount') {
      if (!promotion.discount_item_id) {
        return PRIORITY_LEVELS.GLOBAL_DISCOUNT
      }
      return PRIORITY_LEVELS.ITEM_SPECIFIC_DISCOUNT
    }
    
    if (promotion.type === 'Free Item') {
      return PRIORITY_LEVELS.FREE_ITEM
    }
    
    return 999 // Unknown type
  }

  // Sort promotions by priority (ascending - lower number = higher priority)
  const sortPromotionsByPriority = (promotions) => {
    return [...promotions].sort((a, b) => {
      const priorityA = getPromotionPriority(a)
      const priorityB = getPromotionPriority(b)
      return priorityA - priorityB
    })
  }

  // Check if promotions can coexist based on priority rules
  const canPromotionsCoexist = (promotion1, promotion2) => {
    const priority1 = getPromotionPriority(promotion1)
    const priority2 = getPromotionPriority(promotion2)

    // Global discount blocks everything else
    if (priority1 === PRIORITY_LEVELS.GLOBAL_DISCOUNT || 
        priority2 === PRIORITY_LEVELS.GLOBAL_DISCOUNT) {
      return false
    }

    // Item-specific discounts can coexist with free items
    if ((priority1 === PRIORITY_LEVELS.ITEM_SPECIFIC_DISCOUNT && 
         priority2 === PRIORITY_LEVELS.FREE_ITEM) ||
        (priority1 === PRIORITY_LEVELS.FREE_ITEM && 
         priority2 === PRIORITY_LEVELS.ITEM_SPECIFIC_DISCOUNT)) {
      return true
    }

    // Same type promotions cannot coexist
    if (promotion1.type === promotion2.type) {
      return false
    }

    return true
  }

  // Validate promotion date conflicts
  const hasDateConflict = (promotion1, promotion2) => {
    const start1 = new Date(promotion1.start_date)
    const end1 = new Date(promotion1.end_date)
    const start2 = new Date(promotion2.start_date)
    const end2 = new Date(promotion2.end_date)

    // Check if date ranges overlap
    return (start1 <= end2 && end1 >= start2)
  }

  // Get conflicting promotions for a given promotion
  const getConflictingPromotions = (newPromotion, existingPromotions) => {
    const conflicts = []

    for (const existing of existingPromotions) {
      if (existing.id === newPromotion.id) {
        continue // Skip self
      }

      // Check date conflicts
      if (hasDateConflict(newPromotion, existing)) {
        // Check priority conflicts
        if (!canPromotionsCoexist(newPromotion, existing)) {
          conflicts.push({
            promotion: existing,
            reason: 'Priority conflict - promotions cannot coexist',
            conflictType: 'priority'
          })
        } else if (newPromotion.type === existing.type) {
          // Same type promotions cannot exist in same date range
          if (newPromotion.type === 'Discount' && 
              newPromotion.discount_item_id === existing.discount_item_id) {
            conflicts.push({
              promotion: existing,
              reason: 'Duplicate discount for same item in same date range',
              conflictType: 'duplicate'
            })
          } else if (newPromotion.type === 'Free Item' && 
                     newPromotion.buy_item_id === existing.buy_item_id) {
            conflicts.push({
              promotion: existing,
              reason: 'Duplicate free item promotion for same item in same date range',
              conflictType: 'duplicate'
            })
          }
        }
      }
    }

    return conflicts
  }

  // Validate promotion data
  const validatePromotionData = (promotionData) => {
    const errors = []

    // Required fields validation
    if (!promotionData.title?.trim()) {
      errors.push('Title is required')
    }

    if (!promotionData.start_date) {
      errors.push('Start date is required')
    }

    if (!promotionData.end_date) {
      errors.push('End date is required')
    }

    if (promotionData.start_date && promotionData.end_date) {
      const startDate = new Date(promotionData.start_date)
      const endDate = new Date(promotionData.end_date)
      
      if (startDate >= endDate) {
        errors.push('End date must be after start date')
      }
      
      // Check if start date is in the past
      const today = new Date()
      today.setHours(0, 0, 0, 0)
      if (startDate < today) {
        errors.push('Start date cannot be in the past')
      }
    }

    if (!promotionData.type) {
      errors.push('Promotion type is required')
    }

    if (!promotionData.status) {
      errors.push('Status is required')
    }

    // Type-specific validation
    if (promotionData.type === 'Discount') {
      if (!promotionData.discount && promotionData.discount !== 0) {
        errors.push('Discount value is required')
      }

      if (!promotionData.discount_type) {
        errors.push('Discount type is required')
      }

      if (promotionData.discount_type === 'Percentage') {
        const discount = parseFloat(promotionData.discount)
        if (isNaN(discount) || discount < 0 || discount > 100) {
          errors.push('Percentage discount must be between 0 and 100')
        }
      }

      if (promotionData.discount_type === 'Fixed') {
        const discount = parseFloat(promotionData.discount)
        if (isNaN(discount) || discount < 0) {
          errors.push('Fixed discount must be a positive number')
        }
      }
    }

    if (promotionData.type === 'Free Item') {
      if (!promotionData.buy_item_id) {
        errors.push('Buy item is required')
      }

      if (!promotionData.buy_qty || promotionData.buy_qty < 1) {
        errors.push('Buy quantity must be at least 1')
      }

      if (!promotionData.get_item_id) {
        errors.push('Free item is required')
      }

      if (!promotionData.get_qty || promotionData.get_qty < 1) {
        errors.push('Free quantity must be at least 1')
      }
    }

    return errors
  }

  // Get promotion summary for display
  const getPromotionSummary = (promotion) => {
    if (promotion.type === 'Discount') {
      const discountText = promotion.discount_type === 'Percentage' 
        ? `${promotion.discount}%` 
        : `$${promotion.discount}`
      
      if (!promotion.discount_item_id) {
        return `Global ${promotion.discount_type.toLowerCase()} discount: ${discountText}`
      } else {
        return `Item-specific ${promotion.discount_type.toLowerCase()} discount: ${discountText}`
      }
    }

    if (promotion.type === 'Free Item') {
      return `Buy ${promotion.buy_qty} get ${promotion.get_qty} free`
    }

    return 'Unknown promotion type'
  }

  return {
    PRIORITY_LEVELS,
    isPromotionActive,
    getPromotionPriority,
    sortPromotionsByPriority,
    canPromotionsCoexist,
    hasDateConflict,
    getConflictingPromotions,
    validatePromotionData,
    getPromotionSummary
  }
}
