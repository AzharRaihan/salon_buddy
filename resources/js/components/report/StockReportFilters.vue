<template>
    <div class="stock-report-filters">
        <VRow>
            <VCol cols="12" sm="6" md="4" lg="3">
                <AppAutocomplete
                    :model-value="itemId"
                    :items="itemsWithDefault"
                    :item-title="item => `${item.name}  ${ item.code ? `(${item.code})` : ''}`"
                    item-value="id"
                    label="Item"
                    placeholder="Select Item"
                    clearable
                    @update:model-value="$emit('update:itemId', $event)"
                />
            </VCol>
            
            <VCol cols="12" sm="6" md="4" lg="3">
                <AppAutocomplete
                    :model-value="supplierId"
                    :items="suppliersWithDefault"
                    :item-title="item => `${item.name}  ${ item.phone ? `(${item.phone})` : ''}`"
                    item-value="id"
                    label="Supplier"
                    placeholder="Select Supplier"
                    clearable
                    @update:model-value="$emit('update:supplierId', $event)"
                />
            </VCol>
            
            <VCol cols="12" sm="6" md="4" lg="3">
                <AppAutocomplete
                    :model-value="categoryId"
                    :items="categoriesWithDefault"
                    item-title="name"
                    item-value="id"
                    label="Category"
                    placeholder="Select Category"
                    clearable
                    @update:model-value="$emit('update:categoryId', $event)"
                />
            </VCol>
        </VRow>
    </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    itemId: {
        type: [String, Number],
        default: null
    },
    supplierId: {
        type: [String, Number],
        default: null
    },
    categoryId: {
        type: [String, Number],
        default: null
    },
    items: {
        type: Array,
        default: () => []
    },
    suppliers: {
        type: Array,
        default: () => []
    },
    categories: {
        type: Array,
        default: () => []
    }
})

defineEmits([
    'update:itemId',
    'update:supplierId', 
    'update:categoryId'
])

// Add default "All" options to each filter
const itemsWithDefault = computed(() => [
    { id: '', name: 'All Items' },
    ...props.items
])

const suppliersWithDefault = computed(() => [
    { id: '', name: 'All Suppliers' },
    ...props.suppliers
])

const categoriesWithDefault = computed(() => [
    { id: '', name: 'All Categories' },
    ...props.categories
])
</script>

<style lang="scss" scoped>
.stock-report-filters {
    .v-btn {
        height: 40px;
    }
}
</style>