<script setup>

defineProps({
    summary: {
        type: Object,
        required: true,
        default: () => ({
            totalRecords: 0,
            totalWorkDays: 0,
            totalAbsentDays: 0,
            totalLateDays: 0,
            avgWorkingHours: 0,
        })
    },
    totalFiltered: {
        type: Number,
        default: 0
    }
})
</script>

<template>
    <VRow class="mb-4 summary-card">
        <VCol cols="12" md="2">
            <VCard variant="outlined" class="text-center">
                <VCardText>
                    <div class="text-h6 text-primary">{{ summary.totalRecords ?? 0 }}</div>
                    <div class="text-body-2">Total Records</div>
                </VCardText>
            </VCard>
        </VCol>
        
        <VCol cols="12" md="2">
            <VCard variant="outlined" class="text-center">
                <VCardText>
                    <div class="text-h6 text-success">{{ summary.totalWorkDays ?? 0 }}</div>
                    <div class="text-body-2">Work Days</div>
                </VCardText>
            </VCard>
        </VCol>
        
        <VCol cols="12" md="2">
            <VCard variant="outlined" class="text-center">
                <VCardText>
                    <div class="text-h6 text-error">{{ summary.totalAbsentDays ?? 0 }}</div>
                    <div class="text-body-2">Absent Days</div>
                </VCardText>
            </VCard>
        </VCol>
        
        <VCol cols="12" md="2">
            <VCard variant="outlined" class="text-center">
                <VCardText>
                    <div class="text-h6 text-warning">{{ summary.totalLateDays ?? 0 }}</div>
                    <div class="text-body-2">Late Days</div>
                </VCardText>
            </VCard>
        </VCol>
        
        <VCol cols="12" md="2">
            <VCard variant="outlined" class="text-center">
                <VCardText>
                    <div class="text-h6 text-info">{{ summary.avgWorkingHours ?? 0 }}h</div>
                    <div class="text-body-2">Avg Hours</div>
                </VCardText>
            </VCard>
        </VCol>
        
        <VCol cols="12" md="2">
            <VCard variant="outlined" class="text-center">
                <VCardText>
                    <div class="text-h6 text-secondary">{{ totalFiltered }}</div>
                    <div class="text-body-2">Filtered</div>
                </VCardText>
            </VCard>
        </VCol>
    </VRow>
</template>


<style scoped >
.summary-card .v-card--variant-outlined {
    border: 1px solid #dfdfdf;
}
.summary-card .text-body-2 {
    font-size: 14px !important;
    font-weight: 600;
}
</style>