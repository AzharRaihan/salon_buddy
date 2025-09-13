<template>
    <div class="stock-summary-cards">
        <VRow>
            <VCol cols="12" sm="3" class="mb-5">
                <VCard variant="outlined" class="summary-card">
                    <VCardText class="d-flex align-center">
                        <VIcon icon="tabler-package" size="32" color="primary" class="me-3" />
                        <div>
                            <div class="text-h6">{{ formatNumber(summary?.total_items) || formatNumber(0) }}</div>
                            <div class="text-body-2 text-medium-emphasis">Total Products</div>
                        </div>
                    </VCardText>
                </VCard>
            </VCol>

            <VCol cols="12" sm="3" class="mb-5">
                <VCard variant="outlined" class="summary-card">
                    <VCardText class="d-flex align-center">
                        <VIcon icon="tabler-box" size="32" color="success" class="me-3" />
                        <div>
                            <div class="text-h6">{{ formatNumber(summary?.total_stock) || formatNumber(0) }}</div>
                            <div class="text-body-2 text-medium-emphasis">Total Stock</div>
                        </div>
                    </VCardText>
                </VCard>
            </VCol>

            <VCol cols="12" sm="3" class="mb-5">
                <VCard variant="outlined" class="summary-card">
                    <VCardText class="d-flex align-center">
                        <VIcon icon="tabler-currency-dollar" size="32" color="info" class="me-3" />
                        <div>
                            <div class="text-h6">{{ formatAmount(summary?.total_stock_value) || formatAmount(0) }}</div>
                            <div class="text-body-2 text-medium-emphasis">Total Stock Value</div>
                        </div>
                    </VCardText>
                </VCard>
            </VCol>

            <VCol cols="12" sm="3" class="mb-5">
                <VCard variant="outlined" class="summary-card">
                    <VCardText class="d-flex align-center">
                        <VIcon icon="tabler-alert-triangle" size="32" color="warning" class="me-3" />
                        <div>
                            <div class="text-h6">{{ formatNumber(summary?.low_stock_items) || formatNumber(0) }}</div>
                            <div class="text-body-2 text-medium-emphasis">Low Stock Items</div>
                        </div>
                    </VCardText>
                </VCard>
            </VCol>
        </VRow>
    </div>
</template>

<script setup>
import { useCompanyFormatters } from '@/composables/useCompanyFormatters'

const { formatAmount, formatNumber } = useCompanyFormatters()

defineProps({
    summary: {
        type: Object,
        default: () => ({
            total_items: 0,
            total_stock: 0,
            total_stock_value: 0,
            low_stock_items: 0
        })
    },
    totalFiltered: {
        type: Number,
        default: 0
    }
})
</script>

<style lang="scss" scoped>
.stock-summary-cards {
    .summary-card {
        transition: transform 0.2s ease-in-out;
        
        &:hover {
            transform: translateY(-2px);
        }
    }
    
    .v-card-text {
        padding: 1.5rem;
    }
}
</style>