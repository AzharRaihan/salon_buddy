<template>
    <div class="trial-balance-summary-cards">
        <VRow class="mb-6">
            <VCol cols="12" sm="6" md="4">
                <VCard variant="outlined" class="summary-card">
                    <VCardText class="d-flex align-center justify-space-between">
                        <div>
                            <div class="text-h6 font-weight-bold text-info">
                                {{ formatAmount(summary.totalDebit) }}
                            </div>
                            <div class="text-body-2 text-medium-emphasis">
                                Total Debit
                            </div>
                        </div>
                        <VIcon 
                            icon="tabler-arrow-up-circle" 
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
                            <div class="text-h6 font-weight-bold text-warning">
                                {{ formatAmount(summary.totalCredit) }}
                            </div>
                            <div class="text-body-2 text-medium-emphasis">
                                Total Credit
                            </div>
                        </div>
                        <VIcon 
                            icon="tabler-arrow-down-circle" 
                            size="32" 
                            color="warning"
                        />
                    </VCardText>
                </VCard>
            </VCol>

            <VCol cols="12" sm="6" md="4">
                <VCard 
                    variant="outlined" 
                    class="summary-card"
                    :class="summary.difference === 0 ? 'balanced' : 'not-balanced'"
                >
                    <VCardText class="d-flex align-center justify-space-between">
                        <div>
                            <div 
                                class="text-h6 font-weight-bold"
                                :class="summary.difference === 0 ? 'text-success' : 'text-error'"
                            >
                                {{ formatAmount(Math.abs(summary.difference)) }}
                                <VChip 
                                    v-if="summary.difference === 0" 
                                    color="success" 
                                    size="x-small" 
                                    class="ml-2"
                                >
                                    ✓
                                </VChip>
                            </div>
                            <div class="text-body-2 text-medium-emphasis">
                                Difference
                            </div>
                        </div>
                        <VIcon 
                            icon="tabler-balance" 
                            size="32" 
                            :color="summary.difference === 0 ? 'success' : 'error'"
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
            totalDebit: 0,
            totalCredit: 0,
            difference: 0
        })
    }
})
</script>

<style lang="scss" scoped>
.trial-balance-summary-cards {
    .summary-card {
        transition: transform 0.2s ease-in-out;
        
        &:hover {
            transform: translateY(-2px);
        }

        &.balanced {
            border-color: rgb(var(--v-theme-success));
        }

        &.not-balanced {
            border-color: rgb(var(--v-theme-error));
        }
    }
    
    .v-card-text {
        padding: 1.5rem;
    }
}
</style>

