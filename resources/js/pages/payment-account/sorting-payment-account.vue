<script setup>
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { usePaymentMethodSort } from '@/composables/usePaymentMethodSort'
import draggable from 'vuedraggable'

const { t } = useI18n()
const router = useRouter()

// Use sorting composable
const {
    paymentMethods,
    loading,
    saving,
    hasPaymentMethods,
    isProcessing,
    fetchPaymentMethodsForSorting,
    updateSortOrder,
    resetSortOrder,
} = usePaymentMethodSort()

// Local state for drag
const drag = ref(false)
const isResetDialogOpen = ref(false)

/**
 * Handle drag start
 */
const onDragStart = () => {
    drag.value = true
}

/**
 * Handle drag end and save new order
 */
const onDragEnd = async () => {
    drag.value = false
    await updateSortOrder(paymentMethods.value)
}

/**
 * Handle reset confirmation
 */
const handleResetConfirm = async (confirmed) => {
    if (!confirmed) {
        isResetDialogOpen.value = false
        return
    }

    const success = await resetSortOrder()
    if (success) {
        isResetDialogOpen.value = false
    }
}

/**
 * Navigate back to payment account list
 */
const goBack = () => {
    router.push({ name: 'payment-method' })
}

// Fetch payment account on mount
onMounted(async () => {
    await fetchPaymentMethodsForSorting()
})
</script>

<template>
    <div>
        <VCard>
            <!-- Card Header -->
            <VCardTitle class="d-flex align-center justify-space-between flex-wrap gap-4">
                <div class="d-flex align-center gap-2">
                    <VBtn
                        icon
                        variant="text"
                        color="default"
                        size="small"
                        @click="goBack"
                    >
                        <VIcon icon="tabler-arrow-left" />
                    </VBtn>
                    <span>{{ t('Sort Payment Accounts') }}</span>
                </div>

                <div class="d-flex gap-2">
                    <VBtn
                        color="error"
                        variant="tonal"
                        prepend-icon="tabler-refresh"
                        :disabled="isProcessing || !hasPaymentMethods"
                        @click="isResetDialogOpen = true"
                    >
                        {{ t('Reset Order') }}
                    </VBtn>
                </div>
            </VCardTitle>

            <VDivider />

            <!-- Instructions -->
            <VCardText>
                <VAlert
                    type="info"
                    variant="tonal"
                    class="mb-4"
                >
                    <template #prepend>
                        <VIcon icon="tabler-info-circle" />
                    </template>
                    <div>
                        <strong>{{ t('How to use:') }}</strong>
                        <ul class="mt-2">
                            <li>{{ t('Drag and drop payment account to reorder them') }}</li>
                            <li>{{ t('The order will be saved automatically after you drop an item') }}</li>
                            <li>{{ t('This order will be reflected in the POS and website') }}</li>
                        </ul>
                    </div>
                </VAlert>
            </VCardText>

            <!-- Loading State -->
            <VCardText v-if="loading">
                <div class="d-flex justify-center align-center py-8">
                    <VProgressCircular
                        indeterminate
                        color="primary"
                        size="50"
                    />
                </div>
            </VCardText>

            <!-- Empty State -->
            <VCardText v-else-if="!hasPaymentMethods && !loading">
                <div class="d-flex flex-column align-center justify-center py-8">
                    <VIcon
                        icon="tabler-folder-off"
                        size="64"
                        color="disabled"
                        class="mb-4"
                    />
                    <p class="text-h6 text-disabled">
                        {{ t('No payment account found') }}
                    </p>
                    <VBtn
                        color="primary"
                        class="mt-4"
                        @click="goBack"
                    >
                        {{ t('Go Back') }}
                    </VBtn>
                </div>
            </VCardText>

            <!-- Draggable List -->
            <VCardText v-else>
                <draggable
                    v-model="paymentMethods"
                    item-key="id"
                    :animation="200"
                    :disabled="saving"
                    handle=".drag-handle"
                    ghost-class="ghost"
                    @start="onDragStart"
                    @end="onDragEnd"
                >
                    <template #item="{ element, index }">
                        <VCard
                            class="mb-3 payment-method-card"
                            :class="{ 'dragging': drag }"
                            variant="outlined"
                        >
                            <VCardText class="d-flex align-center gap-4 pa-4">
                                <!-- Drag Handle -->
                                <div class="drag-handle" style="cursor: grab;">
                                    <VIcon
                                        icon="tabler-grip-vertical"
                                        size="24"
                                        color="disabled"
                                    />
                                </div>

                                <!-- Sort Number -->
                                <div class="sort-number">
                                    <VChip
                                        color="primary"
                                        variant="tonal"
                                        size="small"
                                    >
                                        #{{ index + 1 }}
                                    </VChip>
                                </div>

                                <!-- Payment account Icon -->
                                <div class="payment-method-icon">
                                    <VAvatar
                                        v-if="element.payment_method_icon"
                                        size="48"
                                        rounded
                                    >
                                        <VImg
                                            :src="`/images/${element.payment_method_icon}`"
                                            :alt="element.name"
                                        />
                                    </VAvatar>
                                    <VAvatar
                                        v-else
                                        size="48"
                                        rounded
                                        color="primary"
                                        variant="tonal"
                                    >
                                        <VIcon icon="tabler-cash" size="24" />
                                    </VAvatar>
                                </div>

                                <!-- Payment account Info -->
                                <div class="flex-grow-1">
                                    <h6 class="text-h6 mb-1">
                                        {{ element.name }}
                                    </h6>
                                    <div class="d-flex align-center gap-2 flex-wrap">
                                        <VChip
                                            size="x-small"
                                            color="info"
                                            variant="tonal"
                                        >
                                            {{ element.account_type }}
                                        </VChip>
                                        <VChip
                                            v-if="element.status"
                                            size="x-small"
                                            :color="element.status === 'Enable' ? 'success' : 'error'"
                                            variant="tonal"
                                        >
                                            {{ element.status }}
                                        </VChip>
                                        <VChip
                                            v-if="element.use_in_website === 'Yes'"
                                            size="x-small"
                                            color="purple"
                                            variant="tonal"
                                        >
                                            <VIcon icon="tabler-world" size="12" class="me-1" />
                                            {{ t('Website') }}
                                        </VChip>
                                    </div>
                                    <p v-if="element.description" class="text-sm text-disabled mt-1 mb-0">
                                        {{ element.description }}
                                    </p>
                                </div>

                                <!-- Saving Indicator -->
                                <VProgressCircular
                                    v-if="saving"
                                    indeterminate
                                    color="primary"
                                    size="24"
                                />
                            </VCardText>
                        </VCard>
                    </template>
                </draggable>

                <!-- Saving Overlay -->
                <VOverlay
                    v-model="saving"
                    contained
                    class="align-center justify-center"
                >
                    <div class="text-center">
                        <VProgressCircular
                            indeterminate
                            color="primary"
                            size="64"
                        />
                        <p class="mt-4 text-white">
                            {{ t('Saving sort order...') }}
                        </p>
                    </div>
                </VOverlay>
            </VCardText>
        </VCard>

        <!-- Reset Confirmation Dialog -->
        <ConfirmDialog
            v-model:is-dialog-visible="isResetDialogOpen"
            :confirmation-question="t('Are you sure you want to reset the sort order?')"
            :confirm-title="t('Reset!')"
            :confirm-msg="t('Sort order has been reset to default.')"
            :cancel-title="t('Cancelled')"
            :cancel-msg="t('Reset operation cancelled!')"
            @confirm="handleResetConfirm"
        />
    </div>
</template>

<style lang="scss" scoped>
.payment-method-card {
    transition: all 0.3s ease;
    
    &:hover {
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    &.dragging {
        opacity: 0.8;
    }
}

.drag-handle {
    &:active {
        cursor: grabbing !important;
    }
}

.ghost {
    opacity: 0.5;
    background: rgba(var(--v-theme-primary), 0.1);
}

.sort-number {
    min-width: 50px;
    text-align: center;
}

.payment-method-icon {
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>

