<template>
    <div class="salary-summary-cards">
        <VRow class="mb-6">
            <VCol cols="12" sm="6" md="3">
                <VCard variant="outlined" class="summary-card">
                    <VCardText class="d-flex align-center justify-space-between">
                        <div>
                            <div class="text-h6 font-weight-bold text-info">
                                {{ formatNumber(summary.total_employees) }}
                            </div>
                            <div class="text-body-2 text-medium-emphasis">
                                Total Employees
                            </div>
                        </div>
                        <VIcon 
                            icon="tabler-users" 
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
                            <div class="text-h6 font-weight-bold text-primary">
                                {{ formatAmount(summary.total_salary) }}
                            </div>
                            <div class="text-body-2 text-medium-emphasis">
                                Total Salary
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
            <VCol cols="12" sm="6" md="3">
                <VCard variant="outlined" class="summary-card">
                    <VCardText class="d-flex align-center justify-space-between">
                        <div>
                            <div class="text-h6 font-weight-bold text-success">
                                {{ formatAmount(summary.total_paid) }}
                            </div>
                            <div class="text-body-2 text-medium-emphasis">
                                Total Paid
                            </div>
                        </div>
                        <VIcon 
                            icon="tabler-credit-card" 
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
                            <div class="text-h6 font-weight-bold text-warning">
                                {{ formatAmount(summary.total_due) }}
                            </div>
                            <div class="text-body-2 text-medium-emphasis">
                                Total Due
                            </div>
                        </div>
                        <VIcon 
                            icon="tabler-alert-circle" 
                            size="32" 
                            color="warning"
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
            total_salary: 0,
            total_paid: 0,
            total_due: 0,
            total_employees: 0
        })
    },
    totalFiltered: {
        type: Number,
        default: 0
    }
})
</script>

<style lang="scss" scoped>
.salary-summary-cards {
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
