<template>
    <div class="account-statement-summary-cards">
        <VRow class="mb-6">
            <VCol cols="12" sm="6" md="3">
                <VCard variant="outlined" class="summary-card">
                    <VCardText class="d-flex align-center justify-space-between">
                        <div>
                            <div class="text-h6 font-weight-bold text-info">
                                {{ formatAmount(summary.openingBalance) }}
                            </div>
                            <div class="text-body-2 text-medium-emphasis">
                                Opening Balance
                            </div>
                        </div>
                        <VIcon 
                            icon="tabler-flag" 
                            size="32" 
                            color="info"
                        />
                    </VCardText>
                </VCard>
            </VCol>

            <VCol cols="12" sm="6" md="3">
                <VCard variant="outlined" class="summary-card">
                    <VCardText class="d-flex align-center justify-space-between">
                        <div>
                            <div class="text-h6 font-weight-bold text-success">
                                {{ formatAmount(summary.totalDebit) }}
                            </div>
                            <div class="text-body-2 text-medium-emphasis">
                                Total Debit
                            </div>
                        </div>
                        <VIcon 
                            icon="tabler-plus-circle" 
                            size="32" 
                            color="success"
                        />
                    </VCardText>
                </VCard>
            </VCol>

            <VCol cols="12" sm="6" md="3">
                <VCard variant="outlined" class="summary-card">
                    <VCardText class="d-flex align-center justify-space-between">
                        <div>
                            <div class="text-h6 font-weight-bold text-error">
                                {{ formatAmount(summary.totalCredit) }}
                            </div>
                            <div class="text-body-2 text-medium-emphasis">
                                Total Credit
                            </div>
                        </div>
                        <VIcon 
                            icon="tabler-minus-circle" 
                            size="32" 
                            color="error"
                        />
                    </VCardText>
                </VCard>
            </VCol>

            <VCol cols="12" sm="6" md="3">
                <VCard variant="outlined" class="summary-card highlight">
                    <VCardText class="d-flex align-center justify-space-between">
                        <div>
                            <div 
                                class="text-h6 font-weight-bold"
                                :class="summary.closingBalance >= 0 ? 'text-primary' : 'text-warning'"
                            >
                                {{ formatAmount(summary.closingBalance) }}
                            </div>
                            <div class="text-body-2 text-medium-emphasis">
                                Closing Balance
                            </div>
                        </div>
                        <VIcon 
                            icon="tabler-flag-filled" 
                            size="32" 
                            :color="summary.closingBalance >= 0 ? 'primary' : 'warning'"
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
            openingBalance: 0,
            totalDebit: 0,
            totalCredit: 0,
            closingBalance: 0
        })
    }
})
</script>

<style lang="scss" scoped>
.account-statement-summary-cards {
    .summary-card {
        transition: transform 0.2s ease-in-out;
        
        &:hover {
            transform: translateY(-2px);
        }

        &.highlight {
            border-width: 2px;
            border-color: rgb(var(--v-theme-primary));
        }
    }
    
    .v-card-text {
        padding: 1.5rem;
    }
}
</style>

