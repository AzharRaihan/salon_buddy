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
                    <tr>
                        <td>{{ t('Discount') }}</td>
                        <td class="text-end">{{ (discount ?? 0).toFixed(2) }}</td>
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
    'show-charge-modal'
])

// Local state
const showCancelConfirmation = ref(false)

// Methods
const handleCancelConfirm = () => {
    emit('cancel-order')
}
</script>
