<template>
    <div class="profit-loss-report-filters">
        <!-- Filter Row -->
        <VRow class="mb-4">
            <!-- Date From -->
            <VCol cols="12" md="3">
                <AppDateTimePicker 
                :model-value="dateFrom" 
                @update:model-value="(value) => emit('update:dateFrom', value)"
                label="Start Date"
                clearable
                />
            </VCol>

            <!-- Date To -->
            <VCol cols="12" md="3">
                <AppDateTimePicker 
                :model-value="dateTo" 
                @update:model-value="(value) => emit('update:dateTo', value)"
                label="End Date"
                clearable
                />
            </VCol>

            <!-- Branch Filter -->
            <VCol cols="12" md="3">
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
            
            <!-- Costing Method -->
            <VCol cols="12" md="3">
                <AppSelect 
                    :model-value="costingMethod" 
                    @update:model-value="(value) => emit('update:costingMethod', value)"
                    :items="costingMethods"
                    item-title="title"
                    item-value="value"
                    label="Costing Method"
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
            <VBtn 
                prepend-icon="tabler-upload" 
                variant="tonal" 
                color="secondary"
                @click="handleExport"
            >
                Export
            </VBtn>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'
const { t } = useI18n()

const today = new Date()
const sevenDaysAgo = new Date()
sevenDaysAgo.setDate(today.getDate() - 7)

const dateTo = ref(today.toISOString().split('T')[0])      // YYYY-MM-DD
const dateFrom = ref(today.toISOString().split('T')[0])

const props = defineProps({
    dateFrom: {
        type: String,
        default: ''
    },
    dateTo: {
        type: String,
        default: ''
    },
    branchId: {
        type: [String, Number],
        default: ''
    },
    costingMethod: {
        type: String,
        default: 'last_purchase'
    },
    branches: {
        type: Array,
        default: () => []
    },
    costingMethods: {
        type: Array,
        default: () => []
    }
})

const emit = defineEmits([
    'update:dateFrom',
    'update:dateTo',
    'update:branchId',
    'update:costingMethod',
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
