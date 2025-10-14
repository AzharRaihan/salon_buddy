<script setup>
import { computed, ref, watch, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { toast } from 'vue3-toastify';
import { useI18n } from 'vue-i18n';
import { useCompanyFormatters } from '@/composables/useCompanyFormatters';
import ExportTable from '@/components/ExportTable.vue';
// Computed headers for export (converts computed refs to strings)
const exportHeaders = computed(() => {
    return headers.map(header => ({
        ...header,
        title: typeof header.title == 'object' && header.title.value !== undefined 
            ? header.title.value 
            : header.title
    }))
})
const { t } = useI18n()
const router = useRouter()
const searchQuery = ref('')

// Data table options
const itemsPerPage = ref(10)
const page = ref(1)
const sortBy = ref()
const orderBy = ref()
const isConfirmDialogOpen = ref(false)
const selectedCategoryId = ref(null)
const categoryData = ref(null)

// Company formatters
const { fetchCompanySettings, formatDate, formatAmount, getSerialNumber } = useCompanyFormatters()

// Computed properties for i18n translations
const SN = computed(() => t('SN'))
const Name = computed(() => t('Name'))
const Description = computed(() => t('Description'))
const Image = computed(() => t('Image'))
const Status = computed(() => t('Status'))
const Action = computed(() => t('Action'))
const Type = computed(() => t('Type'))
// Data table Headers
const headers = [
    {
        title: SN,
        key: 'serial_number',
        sortable: false,
    },
    {
        title: Name,
        key: 'name',
        sortable: true,
    },
    {
        title: Type,
        key: 'type',
        sortable: true,
    },
    {
        title: Description,
        key: 'description',
        sortable: true,
    },
    {
        title: Image,
        key: 'photo_url',
        sortable: false,
    },
    {
        title: Status,
        key: 'status',
        sortable: true,
    },
    {
        title: Action,
        key: 'action',
        sortable: false,
        align: 'center',
    },
]

const updateOptions = options => {
    sortBy.value = options.sortBy[0]?.key
    orderBy.value = options.sortBy[0]?.order
}

const fetchCategories = async () => {
    const response = await $api('/categories', {
        method: 'GET',
        query: {
            q: searchQuery.value,
            itemsPerPage: itemsPerPage.value,
            page: page.value,
            sortBy: sortBy.value,
            orderBy: orderBy.value,
        },
    })
    categoryData.value = response.data
}

// Initial fetch
await fetchCategories()

const categories = computed(() => {
    const data = categoryData.value?.categories || []
    return data.map((category, index) => ({
        ...category,
        serial_number: getSerialNumber(index, totalCategories.value, page.value, itemsPerPage.value),
    }))
})
const totalCategories = computed(() => categoryData.value?.total || 0)

const openConfirmDialog = (categoryId) => {
    isConfirmDialogOpen.value = true
    selectedCategoryId.value = categoryId
}

const handleDelete = async (confirmed) => {
    if (!confirmed) return;
    try {
        await $api(`/categories/${selectedCategoryId.value}`, {
            method: 'DELETE',
        })
        selectedCategoryId.value = null
        isConfirmDialogOpen.value = false
        await fetchCategories()
        toast(t('Category deleted successfully'), {
            "type": "success",
        });
    } catch (error) {
        isConfirmDialogOpen.value = false
        selectedCategoryId.value = null
        console.error('Error deleting category:', error)
        toast(t('Failed to delete category'), {
            "type": "error",
        });
    }
}

// Watch for changes in search query
watch(searchQuery, () => {
    page.value = 1 // Reset to first page when searching
    fetchCategories() 
})

// Watch for changes in pagination
watch([page, itemsPerPage], () => {
    fetchCategories()
})

// Initialize company settings on mount
onMounted(async () => {
    await fetchCompanySettings()
})

</script>

<template>
    <div>
        <VCard :title="$t('List Category')">
            <VCardText>
                <div class="d-flex justify-space-between flex-wrap gap-y-4">
                    <AppTextField v-model="searchQuery" style="max-inline-size: 280px; min-inline-size: 280px;"
                        :placeholder="$t('Search Category')" />
                    <div class="d-flex flex-row gap-4 align-center flex-wrap">
                        <VBtn prepend-icon="tabler-plus" :to="{ name: 'category-create' }">
                            {{ $t('Add Category') }}
                        </VBtn>
                        <AppSelect v-model="itemsPerPage" :items="[5, 10, 20, 50, 100]" />

                        <ExportTable 
                            :data="categories" 
                            :headers="exportHeaders" 
                            filename="category-report"
                            :title="$t('List Category')"
                        />
                    </div>
                </div>
            </VCardText>

            <VDivider />
            <VDataTableServer v-model:items-per-page="itemsPerPage" v-model:page="page" :items="categories" item-value="id"
                :headers="headers" :items-length="totalCategories" class="text-no-wrap" @update:options="updateOptions">

                <!-- Serial Number -->
                <template #item.serial_number="{ item }">
                    {{ item.serial_number }}
                </template>

                <!-- Loading state -->
                <template #loading>
                    <VSkeletonLoader type="table-row" :rows="itemsPerPage" />
                </template>

                <!-- Type column -->
                <template #item.type="{ item }">
                    <VChip
                        :color="item.type == 'Product' ? 'primary' : 'info'"
                        size="small"
                    >
                        {{ item.type }}
                    </VChip>
                </template> 

                <!-- Image column -->
                <template #item.photo_url="{ item }">
                    <VImg :src="item.photo_url" width="50" height="50" cover class="rounded" />
                </template>

                <!-- Status column -->
                <template #item.status="{ item }">
                    <VChip
                        :color="item.status == 'Enabled' ? 'success' : 'error'"
                        size="small"
                    >
                        {{ item.status }}
                    </VChip>
                </template>

                <!-- No data state -->
                <template #no-data>
                    <div class="d-flex align-center justify-center pa-4">
                        <VIcon icon="tabler-alert-circle" class="me-2" />
                        {{ $t('No categories found') }}   
                    </div>
                </template>

                <template #item.action="{ item }">
                    <div class="d-flex justify-center gap-1">
                        <VBtn icon variant="text" color="info" size="small"
                            @click="$router.push({ name: 'category-edit', query: { id: item.id } })">
                            <VIcon size="22" icon="tabler-edit" />
                        </VBtn>

                        <VBtn icon variant="text" color="error" size="small" @click="openConfirmDialog(item.id)">
                            <VIcon size="22" icon="tabler-trash" />
                        </VBtn>
                    </div>
                </template>
            </VDataTableServer>
        </VCard>

        <ConfirmDialog v-model:is-dialog-visible="isConfirmDialogOpen"
            :confirmation-question="$t('Are you sure you want to delete this category?')" :confirm-title="$t('Deleted!')"
            :confirm-msg="$t('Category has been deleted successfully.')" :cancel-title="$t('Cancelled')"
            :cancel-msg="$t('Category Deletion Cancelled!')" @confirm="handleDelete" />
    </div>
</template>

<style lang="scss" scoped>
.text-link {
    color: rgb(var(--v-theme-primary));
    text-decoration: none;

    &:hover {
        color: rgba(var(--v-theme-primary), 0.8);
    }
}
</style>
