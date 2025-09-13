<template>
  <div class="promotion-display" v-if="hasActivePromotions">
    <div class="promotion-header">
      <VIcon icon="tabler-tag" class="promotion-icon" />
      <span class="promotion-title">{{ t('Active Promotions') }}</span>
    </div>
    
    <div class="promotion-list">
      <!-- Global Discount -->
      <div v-if="globalDiscount" class="promotion-item global-discount">
        <div class="promotion-info">
          <VIcon icon="tabler-percentage" class="promotion-type-icon" />
          <div class="promotion-details">
            <div class="promotion-name">{{ globalDiscount.title }}</div>
            <div class="promotion-description">
              {{ t('Global') }} {{ globalDiscount.discount_type === 'Percentage' ? globalDiscount.discount + '%' : '$' + globalDiscount.discount }} {{ t('discount') }}
            </div>
          </div>
        </div>
        <div class="promotion-priority">
          <span class="priority-badge priority-high">{{ t('Highest Priority') }}</span>
        </div>
      </div>

      <!-- Item-Specific Discounts -->
      <div v-for="discount in itemSpecificDiscounts" :key="discount.id" class="promotion-item item-discount">
        <div class="promotion-info">
          <VIcon icon="tabler-discount" class="promotion-type-icon" />
          <div class="promotion-details">
            <div class="promotion-name">{{ discount.title }}</div>
            <div class="promotion-description">
              {{ discount.discount_type === 'Percentage' ? discount.discount + '%' : '$' + discount.discount }} {{ t('discount on specific item') }}
            </div>
          </div>
        </div>
        <div class="promotion-priority">
          <span class="priority-badge priority-medium">{{ t('Medium Priority') }}</span>
        </div>
      </div>

      <!-- Free Item Promotions -->
      <div v-for="freeItem in freeItemPromotions" :key="freeItem.id" class="promotion-item free-item">
        <div class="promotion-info">
          <VIcon icon="tabler-gift" class="promotion-type-icon" />
          <div class="promotion-details">
            <div class="promotion-name">{{ freeItem.title }}</div>
            <div class="promotion-description">
              {{ t('Buy') }} {{ freeItem.buy_qty }} {{ t('get') }} {{ freeItem.get_qty }} {{ t('free') }}
            </div>
          </div>
        </div>
        <div class="promotion-priority">
          <span class="priority-badge priority-low">{{ t('Low Priority') }}</span>
        </div>
      </div>
    </div>

    <!-- Promotion Rules Info -->
    <div class="promotion-rules">
      <div class="rules-header">
        <VIcon icon="tabler-info-circle" class="rules-icon" />
        <span>{{ t('Promotion Rules') }}</span>
      </div>
      <ul class="rules-list">
        <li>{{ t('Global discounts have highest priority and block other promotions') }}</li>
        <li>{{ t('Item-specific discounts work alongside free item promotions') }}</li>
        <li>{{ t('Free item promotions only work when no global discount exists') }}</li>
      </ul>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const props = defineProps({
  promotions: {
    type: Array,
    default: () => []
  }
})

// Computed properties
const hasActivePromotions = computed(() => props.promotions.length > 0)

const globalDiscount = computed(() => 
  props.promotions.find(promotion => 
    promotion.type === 'Discount' && !promotion.discount_item_id
  )
)

const itemSpecificDiscounts = computed(() => 
  props.promotions.filter(promotion => 
    promotion.type === 'Discount' && promotion.discount_item_id
  )
)

const freeItemPromotions = computed(() => 
  props.promotions.filter(promotion => 
    promotion.type === 'Free Item'
  )
)
</script>

<style scoped>
.promotion-display {
  background: #f8f9fa;
  border: 1px solid #dee2e6;
  border-radius: 8px;
  padding: 1rem;
  margin-bottom: 1rem;
}

.promotion-header {
  display: flex;
  align-items: center;
  margin-bottom: 1rem;
  padding-bottom: 0.5rem;
  border-bottom: 1px solid #dee2e6;
}

.promotion-icon {
  color: #6c757d;
  margin-right: 0.5rem;
  font-size: 1.2rem;
}

.promotion-title {
  font-weight: 600;
  color: #495057;
  font-size: 1rem;
}

.promotion-list {
  margin-bottom: 1rem;
}

.promotion-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem;
  margin-bottom: 0.5rem;
  border-radius: 6px;
  background: white;
  border-left: 4px solid transparent;
}

.promotion-item.global-discount {
  border-left-color: #dc3545;
  background: #fff5f5;
}

.promotion-item.item-discount {
  border-left-color: #fd7e14;
  background: #fff8f0;
}

.promotion-item.free-item {
  border-left-color: #28a745;
  background: #f0fff4;
}

.promotion-info {
  display: flex;
  align-items: center;
  flex: 1;
}

.promotion-type-icon {
  margin-right: 0.75rem;
  font-size: 1.1rem;
}

.global-discount .promotion-type-icon {
  color: #dc3545;
}

.item-discount .promotion-type-icon {
  color: #fd7e14;
}

.free-item .promotion-type-icon {
  color: #28a745;
}

.promotion-details {
  flex: 1;
}

.promotion-name {
  font-weight: 600;
  color: #212529;
  margin-bottom: 0.25rem;
}

.promotion-description {
  font-size: 0.875rem;
  color: #6c757d;
}

.promotion-priority {
  margin-left: 1rem;
}

.priority-badge {
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: 500;
  text-transform: uppercase;
}

.priority-high {
  background: #dc3545;
  color: white;
}

.priority-medium {
  background: #fd7e14;
  color: white;
}

.priority-low {
  background: #28a745;
  color: white;
}

.promotion-rules {
  background: #e9ecef;
  border-radius: 6px;
  padding: 1rem;
}

.rules-header {
  display: flex;
  align-items: center;
  margin-bottom: 0.75rem;
  font-weight: 600;
  color: #495057;
}

.rules-icon {
  margin-right: 0.5rem;
  color: #6c757d;
}

.rules-list {
  margin: 0;
  padding-left: 1.5rem;
  color: #6c757d;
  font-size: 0.875rem;
}

.rules-list li {
  margin-bottom: 0.25rem;
}

.rules-list li:last-child {
  margin-bottom: 0;
}

/* Responsive design */
@media (max-width: 768px) {
  .promotion-item {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .promotion-priority {
    margin-left: 0;
    margin-top: 0.5rem;
  }
  
  .promotion-info {
    margin-bottom: 0.5rem;
  }
}
</style>
