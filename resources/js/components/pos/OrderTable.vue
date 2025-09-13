<template>
    <div class="order-table">
        <!-- Order Items Table -->
        <div class="table-custom">
            <div class="table-header" :class="!hasItems ? 'no-items-header' : ''">
                <div>{{ t('Item') }}</div>
                <div class="text-center">{{ t('Price') }}</div>
                <div class="text-center">{{ t('Quantity') }}</div>
                <div class="text-end">{{ t('Total') }}</div>
            </div>
            <div class="table-body" :class="bookingData ? 'booking-table-body' : ''">
                <template v-if="hasItems" v-for="(item, index) in groupedItems" :key="`item-${item.id}-${index}`">
                    <!-- Main Item -->
                    <div class="item-row"
                        :class="{ 'active': selectedItemIndex === getOriginalIndex(item), 'table-row': true }"
                        @click="handleItemSelection(getOriginalIndex(item))">
                        <div>
                            {{ item.name }}
                            <!-- Product Type -->
                            <div class="product-type text-muted" :class="item.type">
                                <small>{{ item.type || 'Product' }}</small>
                            </div>

                            <!-- Show Product Discount Or Offer -->
                            <div v-if="item.discount && item.discount > 0" class="product-discount text-success">
                                <VIcon icon="tabler-discount" size="14" />
                                <small>{{ t('Discount') }}: ${{ formatPrice(item.discount) }}</small>
                            </div>
                            
                            <!-- Assigned Employee for Services -->
                            <div v-if="item.type === 'Service' && item.assignedEmployee" class="assigned-employee text-info">
                                <VIcon icon="tabler-user" size="14" />
                                {{ item.assignedEmployee.name }}
                            </div>
                        </div>

                        <div class="text-center">
                            {{ formatPrice(getItemPrice(item)) }}
                        </div>

                        <div class="text-center">
                            {{ item.qty || 0 }}
                        </div>

                        <!-- Total -->
                        <div class="text-right">
                            {{ formatPrice(getItemTotal(item)) }}
                        </div>
                    </div>

                    <!-- Free Items for this main item -->
                    <div v-for="freeItem in getFreeItemsForItem(item)" :key="`free-${freeItem.id}-${index}`" 
                            class="table-row free-item" :class="{ 'active': selectedItemIndex === getOriginalIndex(freeItem) }">
                        <div>
                            {{ freeItem.name }}
                            <!-- Show Promotion Product -->
                            <div class="promotion-product">
                                <VIcon icon="tabler-gift" size="14" />
                                <small>{{ freeItem.name }} ({{ freeItem.qty }}) - FREE</small>
                            </div>
                        </div>

                        <div class="text-center">
                            {{ formatPrice(getItemPrice(freeItem)) }}
                        </div>

                        <div class="text-center">
                            {{ freeItem.qty || 0 }}
                        </div>

                        <!-- Total -->
                        <div class="text-right">
                            {{ formatPrice(getItemTotal(freeItem)) }}
                        </div>
                    </div>
                </template>

                <template v-else>
                    <div class="empty-cart-message">
                        <div class="empty-cart-icon">
                            <VIcon icon="tabler-shopping-cart-plus" />
                        </div>
                        <div class="empty-cart-text">
                            <h5>{{ t('Your cart is empty') }}</h5>
                            <p>{{ t('Add items from the menu to get started') }}</p>
                        </div>
                    </div>
                </template>

            </div>
        </div>
        <!-- Empty Cart Message -->
        

        <!-- Loading Overlay -->
        <div v-if="loading" class="loading-overlay">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">{{ t('Loading...') }}</span>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useOrderTableLogic } from '@/composables/pos/useOrderTableLogic'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps({
    items: {
        type: Array,
        default: () => [],
        validator: (value) => Array.isArray(value)
    },
    selectedItemIndex: {
        type: Number,
        default: -1
    },
    loading: {
        type: Boolean,
        default: false
    },
    debugMode: {
        type: Boolean,
        default: false
    },
    bookingData: {
        type: Boolean,
        default: false,
    }
})

const emit = defineEmits([
    'select-item',
    'item-remove',
    'quantity-change'
])

const {
    hasVariations,
    getItemPrice,
    formatPrice,
    calculateItemTotal,
    hasItems
} = useOrderTableLogic(props)

const itemsCount = computed(() => props.items?.length || 0)

// Group items with their free items - only show main items (non-free items)
const groupedItems = computed(() => {
    return props.items.filter(item => !item.isFree)
})

// Get free items for a specific main item
const getFreeItemsForItem = (mainItem) => {
    return props.items.filter(item => 
        item.isFree && item.sourceItemId === mainItem.id
    )
}

// Get the original index of an item in the props.items array
const getOriginalIndex = (item) => {
    return props.items.findIndex(originalItem => 
        originalItem.id === item.id && 
        originalItem.isFree === item.isFree &&
        originalItem.sourceItemId === item.sourceItemId
    )
}

// const handleItemSelection = (index) => {
//     const item = props.items[index]
    
//     // Prevent selection of free items
//     if (item && item.isFree) {
//         console.log('Cannot select free item:', item.name)
//         return
//     }
    
//     emit('select-item', index)
// }
const handleItemSelection = (index) => {
    const item = props.items[index]

    // Prevent selection of free items
    if (item && item.isFree) {
        console.log('Cannot select free item:', item.name)
        return
    }

    // Toggle logic
    if (props.selectedItemIndex === index) {
        emit('select-item', -1)   // deselect if already active
    } else {
        emit('select-item', index) // select new one
    }
}

// Calculate item total including discounts
const getItemTotal = (item) => {
    const basePrice = getItemPrice(item) * (item.qty || 0)
    const discount = parseFloat(item.discount || 0)
    
    if (item.isFree) {
        return 0 // Free items have no cost
    }
    
    return basePrice - discount
}

defineExpose({
    itemsCount,
    hasItems
})


</script>

