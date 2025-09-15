<template>
    <div class="attendance-report-filters">
        <!-- Filter Row -->
        <VRow class="mb-4">
            <!-- Date From -->
            <VCol cols="12" md="4">
                <AppDateTimePicker 
                    :model-value="dateFrom" 
                    @update:model-value="(value) => emit('update:dateFrom', value)"
                    label="Start Date"
                    clearable
                />
            </VCol>

            <!-- Date To -->
            <VCol cols="12" md="4">
                <AppDateTimePicker 
                    :model-value="dateTo" 
                    @update:model-value="(value) => emit('update:dateTo', value)"
                    label="End Date"
                    clearable
                />
            </VCol>
            
            <!-- Employee Filter -->
            <VCol cols="12" md="4">
                <AppSelect 
                    :model-value="employeeId" 
                    @update:model-value="(value) => emit('update:employeeId', value)"
                    :items="[{ id: '', name: t('Select Employee') }, ...employees]"
                    :item-title="(item) => `${item.name}  ${ item.phone ? `(${item.phone})` : ''}`"
                    item-value="id"
                    label="Filter by Employee"
                    clearable
                />
            </VCol>
        </VRow>
    </div>
</template>

<script setup>
import { useI18n } from 'vue-i18n'
const { t } = useI18n()

const props = defineProps({
    dateFrom: {
        type: String,
        default: ''
    },
    dateTo: {
        type: String,
        default: ''
    },
    employeeId: {
        type: [String, Number],
        default: ''
    },
    employees: {
        type: Array,
        default: () => []
    }
})

const emit = defineEmits([
    'update:dateFrom',
    'update:dateTo',
    'update:employeeId',
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