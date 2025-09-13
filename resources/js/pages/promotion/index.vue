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
const selectedPromotionId = ref(null)
const promotionData = ref(null)
const branch_info = useCookie("branch_info").value || 0;

// Company formatters
const { fetchCompanySettings, formatDate, formatAmount, getSerialNumber } = useCompanyFormatters()

// Computed properties for i18n translations
const SN = computed(() => t('SN'))
const Type = computed(() => t('Type'))
const Name = computed(() => t('Name'))
const Title = computed(() => t('Title'))
const StartDate = computed(() => t('Start Date'))
const EndDate = computed(() => t('End Date'))
const Action = computed(() => t('Action'))

// Data table Headers
const headers = [
    {
        title: SN,
        key: 'serial_number',
        sortable: false,
    },
    {
        title: Type,
        key: 'type',
        sortable: true,
    },
    {
        title: Title,
        key: 'title',
        sortable: true,
    },
    {
        title: StartDate,
        key: 'start_date',
        sortable: true,
    },
    {
        title: EndDate,
        key: 'end_date',
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

const fetchPromotions = async () => {
    const response = await $api('/promotions', {
        method: 'GET',
        query: {
            q: searchQuery.value,
            itemsPerPage: itemsPerPage.value,
            page: page.value,
            sortBy: sortBy.value,
            orderBy: orderBy.value,
        },
    })
    promotionData.value = response.data
}

// Initial fetch
await fetchPromotions()

const formatDate2 = (date) => {
    return new Date(date).toLocaleString('en-US', {
        month: 'long',
        day: 'numeric', 
        year: 'numeric',
        hour: 'numeric',
        minute: 'numeric',
        hour12: true
    });
}

const promotions = computed(() => {
    const promos = promotionData.value?.promotions || [];
    return promos.map((promo, index) => ({
        ...promo,
        start_date: formatDate(promo.start_date),
        end_date: formatDate(promo.end_date),
        serial_number: getSerialNumber(index, totalPromotions.value, page.value, itemsPerPage.value),
    }));
})
const totalPromotions = computed(() => promotionData.value?.total || 0)




const openConfirmDialog = (promotionId) => {
    isConfirmDialogOpen.value = true
    selectedPromotionId.value = promotionId
}

const handleDelete = async (confirmed) => {
    if (!confirmed) return;

    try {
        await $api(`/promotions/${selectedPromotionId.value}`, {
            method: 'DELETE',
        })
        selectedPromotionId.value = null
        isConfirmDialogOpen.value = false
        await fetchPromotions()
        toast('Promotion deleted successfully', {
            "type": "success",
        });
    } catch (error) {
        isConfirmDialogOpen.value = false
        selectedPromotionId.value = null
        console.error('Error deleting promotion:', error)
        toast('Failed to delete promotion', {
            "type": "error",
        });
    }
}

// Watch for changes in search query
watch(searchQuery, () => {
    page.value = 1 // Reset to first page when searching
    fetchPromotions() 
})

// Watch for changes in pagination
watch([page, itemsPerPage], () => {
    fetchPromotions()
})

// Initialize company settings on mount
onMounted(async () => {
    await fetchCompanySettings()
})
</script>

<template>
    <div>
        <VCard :title="$t('List Promotion')">
            <VCardText>
                <div class="d-flex justify-space-between flex-wrap gap-y-4">
                    <AppTextField v-model="searchQuery" style="max-inline-size: 280px; min-inline-size: 280px;"
                        :placeholder="$t('Search Promotion')" />
                    <div class="d-flex flex-row gap-4 align-center flex-wrap">
                        <VBtn prepend-icon="tabler-plus" :to="{ name: 'promotion-create' }">
                            {{ $t('Add Promotion') }}
                        </VBtn>
                        <AppSelect v-model="itemsPerPage" :items="[5, 10, 20, 50, 100]" />

                        <ExportTable 
                            :data="promotions" 
                            :headers="exportHeaders" 
                            filename="promotion-report"
                            :title="$t('List Promotion')"
                        />
                    </div>
                </div>
            </VCardText>

            <VDivider />
            <VDataTableServer v-model:items-per-page="itemsPerPage" v-model:page="page" :items="promotions" item-value="id"
                :headers="headers" :items-length="totalPromotions" class="text-no-wrap" @update:options="updateOptions">
                <!-- Loading state -->
                <template #loading>
                    <VSkeletonLoader type="table-row" :rows="itemsPerPage" />
                </template>

                <!-- Serial Number -->
                <template #item.serial_number="{ item }">
                    {{ item.serial_number }}
                </template>

                <!-- No data state -->
                <template #no-data>
                    <div class="d-flex align-center justify-center pa-4">
                        <VIcon icon="tabler-alert-circle" class="me-2" />
                        {{ $t('No promotions found') }}
                    </div>
                </template>

                <template #item.action="{ item }">
                    <div class="d-flex justify-center gap-1">
                        <VBtn icon variant="text" color="info" size="small"
                            @click="$router.push({ name: 'promotion-edit', query: { id: item.id } })"
                            >
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
            :confirmation-question="$t('Are you sure you want to delete this promotion?')" :confirm-title="$t('Deleted!')"
            :confirm-msg="$t('Promotion has been deleted successfully.')" :cancel-title="$t('Cancelled')"
            :cancel-msg="$t('Promotion Deletion Cancelled!')" @confirm="handleDelete" />
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
