<template>
    <div class="order-summary-section">
        <!-- Order Summary -->
        <div class="order-summary">
            <table class="table summary-table">
                <tbody>
                    <tr>
                        <td>{{ t('Subtotal') }}</td>
                        <td class="text-end">{{ (subtotal ?? 0).toFixed(2) }}</td>
                    </tr>
                    <tr v-if="discount > 0">
                         <td>
                            {{ t('Discount') }} 
                         </td>
                         <td class="text-end">
                            - {{ (discount ?? 0).toFixed(2) }}
                            <!-- <button class="btn btn-sm btn-outline-danger ms-2" @click="clearDiscount" title="Clear discount">
                                 <VIcon icon="tabler-x" size="16" />
                            </button> -->
                         </td>
                     </tr>
                    <!-- <tr v-if="promotionDiscount > 0">
                        <td>
                            {{ t('Promotion Discount') }}
                            <VTooltip location="top">
                                <template #activator="{ props }">
                                    <VIcon
                                    v-bind="props"
                                    icon="tabler-info-circle" 
                                    size="20" 
                                    color="primary" 
                                    class="ms-1"
                                    />
                                </template>
                                <span>{{ t('Promotion discount is already applied to individual items and included in subtotal') }}</span>
                            </VTooltip>
                        </td>
                        <td class="text-end text-success">
                            {{ (promotionDiscount ?? 0).toFixed(2) }}
                         </td>
                    </tr> -->
                    <tr>
                        <td>
                            {{ t('Tips') }}
                            <button class="btn-icon-edit ms-2" @click="$emit('show-tips-distribution-modal')" :title="t('Edit Tips')">
                                <VIcon icon="tabler-edit" size="18" />
                            </button>
                        </td>
                        <td class="text-end">{{ (tips ?? 0).toFixed(2) }}</td>
                    </tr>
                    <tr>
                        <td>{{ t('Tax') }}</td>
                        <td class="text-end">{{ (tax ?? 0).toFixed(2) }}</td>
                    </tr>
                    <tr class="grand-total">
                        <td colspan="2">
                            <div class="grand-wrap">
                                <span>{{ t('Total') }}</span>
                                <span class="grand-total-amount">{{ (grandTotal ?? 0).toFixed(2) }}</span>
                            </div>
                        </td>
                    </tr>
                </tbody>
                
            </table>
        </div>

        <!-- Bottom Actions -->
        <div class="bottom-actions">
            <div class="btn-container">
                <button class="btn btn-danger" @click="showCancelConfirmation = true">
                    <VIcon icon="tabler-x" />
                    <span>{{ t('Cancel') }}</span>
                </button>
                <button class="btn btn-primary" @click="$emit('place-order')">
                    <VIcon icon="tabler-credit-card-pay" />
                    <span>{{ props.isEditMode ? t('Update') : t('Payment') }}</span>
                </button>
            </div>
            <div class="btn-container">
                <button class="btn btn-default" @click="$emit('show-date-modal')">
                    <VIcon icon="tabler-calendar-week" />
                    <span>{{ t('Date') }}</span>
                </button>
                <button class="btn btn-discount" @click="$emit('show-discount-modal')">
                    <VIcon icon="tabler-rosette-discount" />
                    <span>{{ t('Discount') }}</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <ConfirmationModal
        :show="showCancelConfirmation"
        :title="props.isEditMode ? t('Cancel Edit') : t('Cancel')"
        :message="props.isEditMode 
            ? t('Are you sure you want to cancel editing this sale? All changes will be lost.') 
            : t('Are you sure you want to cancel this sale? This action cannot be undone and all items will be removed from the sale.')"
        icon="tabler-alert-triangle"
        icon-class="text-danger"
        :confirm-text="props.isEditMode ? t('Yes, Cancel Edit') : t('Yes, Cancel Sale')"
        :cancel-text="props.isEditMode ? t('Keep Editing') : t('Keep Sale')"
        @confirm="handleCancelConfirm"
        @close="showCancelConfirmation = false"
    />
</template>

<script setup>
import { ref } from 'vue'
import ConfirmationModal from './modals/ConfirmationModal.vue'
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

// Define props
const props = defineProps({
    subtotal: {
        type: Number,
        default: 0
    },
    discount: {
        type: Number,
        default: 0
    },
    promotionDiscount: {
        type: Number,
        default: 0
    },
    tips: {
        type: Number,
        default: 0
    },
    tax: {
        type: Number,
        default: 0
    },
    charge: {
        type: Number,
        default: 0
    },
    grandTotal: {
        type: Number,
        default: 0
    },
    isEditMode: {
        type: Boolean,
        default: false
    }
})

// Define emits
const emit = defineEmits([
    'cancel-order',
    'quick-invoice',
    'place-order',
    'save-draft',
    'show-date-modal',
    'show-details-modal',
    'show-discount-modal',
    'show-tips-modal',
    'show-charge-modal',
    'show-tips-distribution-modal',
    'clear-discount'
])

// Local state
const showCancelConfirmation = ref(false)

// Methods
const handleCancelConfirm = () => {
    emit('cancel-order')
}

const clearDiscount = () => {
    emit('clear-discount')
}
</script>

<style scoped>
.text-success {
    color: #28a745 !important;
}
.text-end {
    text-align: right;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
    border-radius: 0.375rem;
}

.ms-2 {
    margin-left: 0.5rem;
}

.btn-icon-edit {
    background: none;
    border: none;
    padding: 4px;
    cursor: pointer;
    color: #007bff;
    transition: all 0.2s;
    border-radius: 4px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.btn-icon-edit:hover {
    background-color: #e7f3ff;
    color: #0056b3;
}

.btn-icon-edit:active {
    transform: scale(0.95);
}
</style>
