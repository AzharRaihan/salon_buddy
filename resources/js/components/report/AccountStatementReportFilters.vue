<template>
    <div class="account-statement-report-filters">
        <!-- Filter Row -->
        <VRow class="mb-4">
            <!-- Account Filter -->
            <VCol cols="12" md="3">
                <AppAutocomplete 
                    :model-value="paymentMethodId" 
                    @update:model-value="(value) => emit('update:paymentMethodId', value)"
                    :items="paymentMethods"
                    item-title="name"
                    item-value="id"
                    label="Payment Account"
                    placeholder="Select Payment Account"
                    :required="true"
                    clearable
                />
            </VCol>
            <!-- Branch Filter -->
            <VCol cols="12" md="3">
                <AppSelect 
                    :model-value="branchId" 
                    @update:model-value="(value) => emit('update:branchId', value)"
                    :items="[{ id: '', name: t('All Outlets') }, ...branches]"
                    item-title="name"
                    item-value="id"
                    label="Filter by Outlet"
                    clearable
                />
            </VCol>
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
    paymentMethodId: {
        type: [String, Number],
        default: ''
    },
    branchId: {
        type: [String, Number],
        default: ''
    },
    paymentMethods: {
        type: Array,
        default: () => []
    },
    branches: {
        type: Array,
        default: () => []
    }
})


const emit = defineEmits([
    'update:dateFrom',
    'update:dateTo',
    'update:paymentMethodId',
    'update:branchId'
])
</script>

<style lang="scss" scoped>
.action {
    display: flex;
    justify-content: end;
    gap: 10px;
}
</style>

