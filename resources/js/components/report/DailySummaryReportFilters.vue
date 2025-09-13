<template>
    <div class="daily-summary-report-filters">
        <!-- Filter Row -->
        <VRow class="mb-4">
            <!-- Date -->
            <VCol cols="12" md="4">
                <AppDateTimePicker 
                    :model-value="selectedDate" 
                    @update:model-value="(value) => emit('update:selectedDate', value)"
                    label="Date"
                    type="date"
                />
            </VCol>

            <!-- Branch Filter -->
            <VCol cols="12" md="4">
                <AppSelect 
                    :model-value="branchId" 
                    @update:model-value="(value) => emit('update:branchId', value)"
                    :items="[{ id: '', name: t('Select Branch') }, ...branches]"
                    item-title="name"
                    item-value="id"
                    label="Filter by Outlet"
                    clearable
                    />
            </VCol>
        </VRow>

        <!-- Action Buttons -->
        <div class="action">
            <VBtn 
                prepend-icon="tabler-refresh" 
                variant="outlined" 
                @click="handleResetFilters"
            >
                Reset Filters
            </VBtn>
            <!-- <VBtn 
                prepend-icon="tabler-upload" 
                variant="tonal" 
                color="secondary"
                @click="handleExport"
            >
                Export
            </VBtn> -->
        </div>
    </div>
</template>

<script setup>
import { useI18n } from 'vue-i18n'
const { t } = useI18n()



const props = defineProps({
    selectedDate: {
        type: String,
        default: ''
    },
    branchId: {
        type: [String, Number],
        default: ''
    },
    branches: {
        type: Array,
        default: () => []
    }
})

const emit = defineEmits([
    'update:selectedDate',
    'update:branchId',
    'reset-filters',
    'export-report'
])

// Handle filter updates
const handleResetFilters = () => {
    emit('reset-filters')
}

const handleExport = () => {
    emit('export-report')
}
</script>

<style lang="scss" scoped>
.action {
    display: flex;
    justify-content: end;
    gap: 10px;
}
</style>
