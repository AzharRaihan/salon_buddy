<template>
    <div class="sale-report-filters">
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
            
            <!-- Customer Filter -->
            <VCol cols="12" md="3">
                <AppSelect 
                    :model-value="customerId" 
                    @update:model-value="(value) => emit('update:customerId', value)"
                    :items="[{ id: '', name: t('Select Customer') }, ...customers]"
                    :item-title="item => `${item.name}  ${ item.phone ? `(${item.phone})` : ''}`"
                    item-value="id"
                    label="Filter by Customer"
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
    customerId: {
        type: [String, Number],
        default: ''
    },
    branches: {
        type: Array,
        default: () => []
    },
    customers: {
        type: Array,
        default: () => []
    }
})

const emit = defineEmits([
    'update:dateFrom',
    'update:dateTo',
    'update:branchId',
    'update:customerId'
])
</script>
