<template>
    <div class="staff-evaluation-details-summary-cards">
        <VRow class="mb-6">
            <VCol cols="12" sm="6" md="4">
                <VCard variant="outlined" class="summary-card">
                    <VCardText class="d-flex align-center justify-space-between">
                        <div>
                            <div class="text-h6 font-weight-bold text-info">
                                {{ (summary.totalRatings) }}
                            </div>
                            <div class="text-body-2 text-medium-emphasis">
                                Total Ratings
                            </div>
                        </div>
                        <VIcon 
                            icon="tabler-star" 
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
                                {{ (summary.avgRating) }}
                            </div>
                            <div class="text-body-2 text-medium-emphasis">
                                Average Rating
                            </div>
                        </div>
                        <VIcon 
                            icon="tabler-chart-line" 
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
                                {{ (summary.totalRatingSum) }}
                            </div>
                            <div class="text-body-2 text-medium-emphasis">
                                Total Rating Sum
                            </div>
                        </div>
                        <VIcon 
                            icon="tabler-sum" 
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
const props = defineProps({
    summary: {
        type: Object,
        default: () => ({
            totalRatings: 0,
            avgRating: 0,
            totalRatingSum: 0
        })
    },
    totalFiltered: {
        type: Number,
        default: 0
    }
})
</script>

<style lang="scss" scoped>
.staff-evaluation-details-summary-cards {
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

