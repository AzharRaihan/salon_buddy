<template>
    <div class="item-grid">
        <!-- Loading State -->
        <div v-if="loading" class="d-flex justify-content-center align-items-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">{{ t('Loading...') }}</span>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else-if="!items || items.length === 0" class="text-center py-5">
            <div class="text-muted">
                <VIcon icon="tabler-adjustments-x" size="48" class="mb-3" />
                <p class="mb-0">{{ searchQuery ? t('No items found') : t('No items found') }}
                </p>
            </div>
        </div>

        <!-- Item Grid -->
        <div v-else class="pos-item-grid-wrap">
            <div v-for="item in items" :key="item.id" class="pos-item-grid">
                <div class="item" :class="{'loading': loading }" @click="handleItemSelect(item)">
                    <div class="item-image-container">
                        <!-- Show placeholder when no image or when image fails to load -->
                        <div v-if="!hasValidImage(item)" class="item-placeholder">
                            <VIcon icon="tabler-adjustments-x" size="48" />
                            <span class="placeholder-text">{{ t('No Image') }}</span>
                        </div>
                        <!-- Show actual image when available -->
                        <img v-else :src="getItemImage(item)" :alt="item.name" class="item-image"
                            @error="handleImageError($event, item)" />
                    </div>

                    <div class="item-info">
                        <div class="item-name" :title="item.name">{{ item.name }}</div>
                        <div class="item-price">
                            ${{ formatPrice(item.sale_price || item.selling_price_dine_in || item.price || item.cost
                                || 0) }}
                        </div>
                        <!-- Alternative name if available -->
                        <div v-if="item.alternative_name" class="item-alt-name text-muted">
                            {{ item.alternative_name }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, reactive, onMounted } from 'vue'
import { toast } from 'vue3-toastify';
import { usePromotions } from '@/composables/pos/usePromotions'
import { useI18n } from 'vue-i18n';
import { useWebsiteSettingsStore } from '@/stores/websiteSetting'
const websiteSettingsStore = useWebsiteSettingsStore()
const printFormate = computed(() => websiteSettingsStore.getPrintFormate)
const overSale = computed(() => websiteSettingsStore.getOverSale)
const { t } = useI18n();

const props = defineProps({
    items: {
        type: Array,
        required: true,
        default: () => []
    },
    loading: {
        type: Boolean,
        default: false
    },
    searchQuery: {
        type: String,
        default: ''
    }
})

const emit = defineEmits([
    'item-select',
    'search',
    'update:searchQuery'
])

// Initialize promotions composable
const { fetchPromotions, getItemPromotions, calculateItemDiscount, getFreeItems } = usePromotions()

const imageErrors = reactive(new Set())

const hasValidImage = (item) => {
    return (item.image || item.photo_url) && !imageErrors.has(item.id)
}

const getItemImage = (item) => {
    if ((item.image || item.photo_url) && !imageErrors.has(item.id)) {
        return item.image || item.photo_url
    }
    return null
}

const handleImageError = (event, item) => {
    imageErrors.add(item.id)
}

const formatPrice = (price) => {
    return (parseFloat(price) || 0).toFixed(2)
}

const handleItemSelect = async (item) => {
    if (item.type === 'Product' && overSale === 'No') {
        try {
            const response = await $api(`/product-stock/${item.id}`)
            if (response > 0) {
                // Check for promotions before adding to order
                const itemWithPromotions = await checkItemPromotions(item)
                emit('item-select', itemWithPromotions)
            } else {
                toast('Product is out of stock', {
                    type: 'error'
                })
            }
        } catch (error) {
            console.error("Failed to fetch product stock:", error)
            toast('Failed to fetch product stock', {
                type: 'error'
            })
        }
    } else {
        // For services, check promotions as well
        const itemWithPromotions = await checkItemPromotions(item)
        emit('item-select', itemWithPromotions)
    }
}

// Check for promotions on the selected item
const checkItemPromotions = async (item) => {
    try {
        const itemPromotions = getItemPromotions(item.id)
        const discount = calculateItemDiscount(item, 1)
        const freeItems = await getFreeItems(item, 1)
        
        const itemWithPromotions = {
            ...item,
            promotions: itemPromotions,
            discount: discount,
            freeItems: freeItems,
            hasDiscount: discount > 0,
            hasFreeItems: freeItems.length > 0
        }
        
        return itemWithPromotions
    } catch (error) {
        console.error('Error checking promotions:', error)
        return item
    }
}

// Fetch promotions when component mounts
onMounted(async () => {
    await fetchPromotions()
})
</script>