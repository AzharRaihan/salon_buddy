<template>
    <div v-if="show" class="common-modal select-modal show">
        <div class="modal-content">
            <div class="modal-header">
                <h4>{{ t('Update Price') }}</h4>
                <button class="close-modal" @click="handleClose">
                    <VIcon icon="tabler-x" />
                </button>
            </div>
            <div class="modal-body employee-assignment-modal-body">
                <h4>{{ getItemName(props.item) }}</h4>
                <div class="mt-1 text-muted mb-3">
                    <p>{{ t('Current Price') }}: {{ formatPrice(getItemPrice(props.item)) }}</p>
                </div>
                <div class="service-info">
                    <div class="price-edit">
                        <label for="service-price">{{ t('New Price') }} <span class="required-star-2">*</span></label>
                        <input
                            id="service-price"
                            type="number"
                            class="form-control"
                            v-model.number="servicePrice"
                            min="0"
                            step="0.01"
                            :placeholder="formatPrice(getItemPrice(props.item))"
                            required
                        />
                        <small v-if="priceError" class="text-danger">{{ priceError }}</small>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-danger" @click="handleClose">
                    <VIcon icon="tabler-x" />
                    {{ t('Cancel') }}
                </button>
                <button class="btn btn-primary" @click="handleConfirm" :disabled="!canConfirm">
                    <VIcon icon="tabler-edit" />
                    {{ t('Update Price') }}
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useErrorHandler } from '@/composables/useErrorHandler'
import { unref } from 'vue'
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

// Props
const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    item: {
        type: Object,
        default: null
    }
})

const emit = defineEmits(['confirm', 'close'])
const { handleError } = useErrorHandler()

// Helper functions
const getItemPrice = (item) => {
    const itemValue = unref(item);
    if (!itemValue) return 0
    return (
        itemValue.sale_price ||
        itemValue.price ||
        0
    )
}

const getItemName = (item) => {
    const itemValue = unref(item);
    if (!itemValue) return ''
    return (
        itemValue.name || ''
    )
}

// Reactive state
const loading = ref(false)
const servicePrice = ref(getItemPrice(props.item))
const priceError = ref('')

// Computed properties
const canConfirm = computed(() => {
    return servicePrice.value && servicePrice.value > 0
})

// Methods
const formatPrice = (price) => {
    return parseFloat(price || 0).toFixed(2)
}

const handleConfirm = async () => {
    // Validate price
    if (!servicePrice.value || servicePrice.value <= 0) {
        priceError.value = t('Price is required and must be greater than 0')
        return
    }

    try {
        loading.value = true
        const itemValue = unref(props.item)
        emit('confirm', {
            itemId: itemValue?.id,
            price: servicePrice.value
        })
        priceError.value = ''
    } catch (error) {
        handleError('price-update', error)
    } finally {
        loading.value = false
    }
}

const handleClose = () => {
    // Reset form
    servicePrice.value = getItemPrice(props.item)
    priceError.value = ''
    emit('close')
}

// Watch for modal visibility
watch(() => props.show, (newValue) => {
    if (newValue) {
        servicePrice.value = getItemPrice(props.item)
        priceError.value = ''
    }
})

// Watch for item changes
watch(() => props.item, (newItem) => {
    servicePrice.value = getItemPrice(newItem)
    priceError.value = ''
})
</script>

<style scoped>
.employee-assignment-modal-body {
    max-height: 70vh;
    overflow-y: auto;
}

.price-edit {
    margin-bottom: 1rem;
}

.price-edit label {
    font-size: 20px;
    font-weight: 600;
    margin-bottom: 8px;
    display: block;
}

.required-star-2 {
    font-weight: 600;
    font-size: 18px;
    color: red;
}

.text-danger {
    color: #dc3545;
    font-size: 14px;
    margin-top: 4px;
    display: block;
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
</style>