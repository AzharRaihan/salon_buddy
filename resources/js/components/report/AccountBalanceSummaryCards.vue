<template>
    <div class="account-balance-summary-cards">
        <VRow class="mb-6">
            <VCol cols="12" sm="6" md="6">
                <VCard variant="outlined" class="summary-card">
                    <VCardText class="d-flex align-center justify-space-between">
                        <div>
                            <div class="text-h6 font-weight-bold text-primary">
                                {{ summary.totalBalance >= 0 ? '+' : '' }}{{ formatAmount(summary.totalBalance) }}
                            </div>
                            <div class="text-body-2 text-medium-emphasis">
                                Total Balance
                            </div>
                        </div>
                        <VIcon 
                            icon="tabler-wallet" 
                            size="32" 
                            :color="summary.totalBalance >= 0 ? 'success' : 'error'"
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
            totalBalance: 0
        })
    }
})
</script>

<style lang="scss" scoped>
.account-balance-summary-cards {
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

