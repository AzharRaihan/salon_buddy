<template>
    <div class="balance-sheet-summary-cards">
        <VRow class="mb-6">
            <VCol cols="12" sm="6" md="4">
                <VCard variant="outlined" class="summary-card">
                    <VCardText class="d-flex align-center justify-space-between">
                        <div>
                            <div class="text-h6 font-weight-bold text-success">
                                {{ formatAmount(summary.totalAssets) }}
                            </div>
                            <div class="text-body-2 text-medium-emphasis">
                                Total Assets
                            </div>
                        </div>
                        <VIcon 
                            icon="tabler-trending-up" 
                            size="32" 
                            color="success"
                        />
                    </VCardText>
                </VCard>
            </VCol>

            <VCol cols="12" sm="6" md="4">
                <VCard variant="outlined" class="summary-card">
                    <VCardText class="d-flex align-center justify-space-between">
                        <div>
                            <div class="text-h6 font-weight-bold text-error">
                                {{ formatAmount(summary.totalLiabilities) }}
                            </div>
                            <div class="text-body-2 text-medium-emphasis">
                                Total Liabilities
                            </div>
                        </div>
                        <VIcon 
                            icon="tabler-trending-down" 
                            size="32" 
                            color="error"
                        />
                    </VCardText>
                </VCard>
            </VCol>

            <VCol cols="12" sm="6" md="4">
                <VCard variant="outlined" class="summary-card">
                    <VCardText class="d-flex align-center justify-space-between">
                        <div>
                            <div 
                                class="text-h6 font-weight-bold"
                                :class="summary.netWorth >= 0 ? 'text-primary' : 'text-warning'"
                            >
                                {{ formatAmount(summary.netWorth) }}
                            </div>
                            <div class="text-body-2 text-medium-emphasis">
                                Net Worth
                            </div>
                        </div>
                        <VIcon 
                            icon="tabler-chart-pie" 
                            size="32" 
                            :color="summary.netWorth >= 0 ? 'primary' : 'warning'"
                        />
                    </VCardText>
                </VCard>
            </VCol>
        </VRow>
    </div>
</template>

<script setup>
import { useCompanyFormatters } from '@/composables/useCompanyFormatters'
const { formatAmount } = useCompanyFormatters()

const props = defineProps({
    summary: {
        type: Object,
        default: () => ({
            totalAssets: 0,
            totalLiabilities: 0,
            netWorth: 0
        })
    }
})
</script>

<style lang="scss" scoped>
.balance-sheet-summary-cards {
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

