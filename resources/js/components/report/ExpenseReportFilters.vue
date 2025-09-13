<template>
    <div class="expense-report-filters">
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
            
            <!-- Expense Category Filter -->
            <VCol cols="12" md="3">
                <AppSelect 
                    :model-value="categoryId" 
                    @update:model-value="(value) => emit('update:categoryId', value)"
                    :items="[{ id: '', name: t('Select Category') }, ...categories]"
                    item-title="name"
                    item-value="id"
                    label="Filter by Expense Category"
                    clearable
                />
            </VCol>
            <VCol cols="12" md="3">
                <AppSelect 
                    :model-value="employeeId" 
                    @update:model-value="(value) => emit('update:employeeId', value)"
                    :items="[{ id: '', name: t('Select Employee') }, ...employees]"
                    :item-title="item => `${item.name}  ${ item.phone ? `(${item.phone})` : ''}`"
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
    branchId: {
        type: [String, Number],
        default: ''
    },
    categoryId: {
        type: [String, Number],
        default: ''
    },
    employeeId: {
        type: [String, Number],
        default: ''
    },
    branches: {
        type: Array,
        default: () => []
    },
    categories: {
        type: Array,
        default: () => []
    },
    employees: {
        type: Array,
        default: () => []
    }
})

const emit = defineEmits([
    'update:dateFrom',
    'update:dateTo',
    'update:branchId',
    'update:categoryId',
    'update:employeeId'
])
</script>

<style lang="scss" scoped>
.action {
    display: flex;
    justify-content: end;
    gap: 10px;
}
</style>