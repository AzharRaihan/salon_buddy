<template>
    <div class="trial-balance-report-filters">
        <!-- Filter Row -->
        <VRow class="mb-4">
            <!-- Branch Filter -->
            <VCol cols="12" md="6">
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
        </VRow>
    </div>
</template>

<script setup>
import { useI18n } from 'vue-i18n'
const { t } = useI18n()

const props = defineProps({
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

