<template>
    <div class="staff-evaluation-report-filters">
        <!-- Filter Row -->
        <VRow class="mb-4">
            <!-- Date From -->
            <VCol cols="12" :md="hideBranch ? 4 : 3">
                <AppDateTimePicker 
                    :model-value="dateFrom" 
                    @update:model-value="(value) => emit('update:dateFrom', value)"
                    label="Start Date"
                    clearable
                />
            </VCol>

            <!-- Date To -->
            <VCol cols="12" :md="hideBranch ? 4 : 3">
                <AppDateTimePicker 
                    :model-value="dateTo" 
                    @update:model-value="(value) => emit('update:dateTo', value)"
                    label="End Date"
                    clearable
                />
            </VCol>

            <!-- Branch Filter -->
            <VCol v-if="!hideBranch" cols="12" md="3">
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
            
            <!-- Employee Filter -->
            <VCol cols="12" :md="hideBranch ? 4 : 3">
                <AppAutocomplete 
                    :model-value="employeeId" 
                    :required="employeeRequired ? true : false"
                    @update:model-value="(value) => emit('update:employeeId', value)"
                    :items="[{ id: '', name: t('Select Employee') }, ...employees]"
                    :item-title="item => `${item.name} ${item.phone ? `(${item.phone})` : ''}`"
                    item-value="id"
                    label="Select Employee"
                    :rules="employeeRequired ? [v => !!v || 'Employee is required'] : []"
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
    employeeId: {
        type: [String, Number],
        default: ''
    },
    branches: {
        type: Array,
        default: () => []
    },
    employees: {
        type: Array,
        default: () => []
    },
    hideBranch: {
        type: Boolean,
        default: false
    },
    employeeRequired: {
        type: Boolean,
        default: false
    }
})

const emit = defineEmits([
    'update:dateFrom',
    'update:dateTo',
    'update:branchId',
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

