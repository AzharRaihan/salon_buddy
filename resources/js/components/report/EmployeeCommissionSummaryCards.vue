<template>
    <div class="employee-commission-summary-cards">
        <VRow class="mb-6">
            <VCol cols="12" sm="6" md="4">
                <VCard variant="outlined" class="summary-card">
                    <VCardText class="d-flex align-center justify-space-between">
                        <div>
                            <div class="text-h6 font-weight-bold text-info">
                                {{ (summary.totalOrders) }}
                            </div>
                            <div class="text-body-2 text-medium-emphasis">
                                Total Orders
                            </div>
                        </div>
                        <VIcon 
                            icon="tabler-shopping-cart" 
                            size="32" 
                            color="info"
                        />
                    </VCardText>
                </VCard>
            </VCol>
            <VCol cols="12" sm="6" md="4">
                <VCard variant="outlined" class="summary-card">
                    <VCardText class="d-flex align-center justify-space-between">
                        <div>
                            <div class="text-h6 font-weight-bold text-primary">
                                {{ formatAmount(summary.totalCommissionAmount) }}
                            </div>
                            <div class="text-body-2 text-medium-emphasis">
                                Total Commission Amount
                            </div>
                        </div>
                        <VIcon 
                            icon="tabler-currency-dollar" 
                            size="32" 
                            color="primary"
                        />
                    </VCardText>
                </VCard>
            </VCol>
            <VCol cols="12" sm="6" md="4">
                <VCard variant="outlined" class="summary-card">
                    <VCardText class="d-flex align-center justify-space-between">
                        <div>
                            <div class="text-h6 font-weight-bold text-success">
                                {{ formatAmount(summary.avgCommissionRate) }}
                            </div>
                            <div class="text-body-2 text-medium-emphasis">
                                Average Commission Rate
                            </div>
                        </div>
                        <VIcon 
                            icon="tabler-chart-line" 
                            size="32" 
                            color="success"
                        />
                    </VCardText>
                </VCard>
            </VCol>
        </VRow>
    </div>
</template>

<script setup>
import { useCompanyFormatters } from '@/composables/useCompanyFormatters'
const { formatAmount, formatNumber } = useCompanyFormatters()

const props = defineProps({
    summary: {
        type: Object,
        default: () => ({
            total_orders: 0,
            total_commission: 0,
            average_commission: 0
        })
    },
    totalFiltered: {
        type: Number,
        default: 0
    }
})
</script>

<style lang="scss" scoped>
.employee-commission-summary-cards {
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