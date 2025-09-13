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
const selectedVacationId = ref(null)
const vacationData = ref(null)

// Company formatters
const { fetchCompanySettings, formatDate, formatAmount, getSerialNumber } = useCompanyFormatters()

// Computed properties for i18n translations
const SN = computed(() => t('SN'))
const Title = computed(() => t('Title'))
const StartDate = computed(() => t('Start Date'))
const EndDate = computed(() => t('End Date'))
const AutoResponse = computed(() => t('Auto Response'))
const Action = computed(() => t('Action'))

// Data table Headers
const headers = [
    {
        title: SN,
        key: 'serial_number',
        sortable: false,
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
        title: AutoResponse,
        key: 'auto_response',
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

const fetchVacations = async () => {
    const response = await $api('/vacations', {
        method: 'GET',
        query: {
            q: searchQuery.value,
            itemsPerPage: itemsPerPage.value,
            page: page.value,
            sortBy: sortBy.value,
            orderBy: orderBy.value,
        },
    })
    vacationData.value = response.data
}

// Initial fetch
await fetchVacations()

const vacations = computed(() => {
    const data = vacationData.value?.vacations || []
    return data.map((vacation, index) => ({
        ...vacation,
        serial_number: getSerialNumber(index, totalVacations.value, page.value, itemsPerPage.value),
    }))
})
const totalVacations = computed(() => vacationData.value?.total || 0)

const openConfirmDialog = (vacationId) => {
    isConfirmDialogOpen.value = true
    selectedVacationId.value = vacationId
}

const handleDelete = async (confirmed) => {
    if (!confirmed) return;

    try {
        await $api(`/vacations/${selectedVacationId.value}`, {
            method: 'DELETE',
        })
        selectedVacationId.value = null
        isConfirmDialogOpen.value = false
        await fetchVacations()
        toast('Vacation deleted successfully', {
            "type": "success",
        });
    } catch (error) {
        isConfirmDialogOpen.value = false
        selectedVacationId.value = null
        console.error('Error deleting vacation:', error)
        toast('Failed to delete vacation', {
            "type": "error",
        });
    }
}

// Watch for changes in search query
watch(searchQuery, () => {
    page.value = 1 // Reset to first page when searching
    fetchVacations() 
})

// Watch for changes in pagination
watch([page, itemsPerPage], () => {
    fetchVacations()
})

onMounted(async () => {
    await fetchCompanySettings()
})

</script>

<template>
    <div>
        <VCard :title="t('List Vacation')">
            <VCardText>
                <div class="d-flex justify-space-between flex-wrap gap-y-4">
                    <AppTextField v-model="searchQuery" style="max-inline-size: 280px; min-inline-size: 280px;"
                        :placeholder="t('Search Vacation')" />
                    <div class="d-flex flex-row gap-4 align-center flex-wrap">
                        <VBtn prepend-icon="tabler-plus" :to="{ name: 'vacation-create' }">
                            {{ t('Add Vacation') }}
                        </VBtn>
                        <AppSelect v-model="itemsPerPage" :items="[5, 10, 20, 50, 100]" />

                        <ExportTable 
                            :data="vacations" 
                            :headers="exportHeaders" 
                            filename="vacation-report"
                            :title="$t('List Vacation')"
                        />
                    </div>
                </div>
            </VCardText>

            <VDivider />
            <VDataTableServer v-model:items-per-page="itemsPerPage" v-model:page="page" :items="vacations" item-value="id"
                :headers="headers" :items-length="totalVacations" class="text-no-wrap" @update:options="updateOptions">

                <!-- Serial Number -->
                <template #item.serial_number="{ item }">
                    {{ item.serial_number }}
                </template>

                <!-- Loading state -->
                <template #loading>
                    <VSkeletonLoader type="table-row" :rows="itemsPerPage" />
                </template>

                <!-- No data state -->
                <template #no-data>
                    <div class="d-flex align-center justify-center pa-4">
                        <VIcon icon="tabler-alert-circle" class="me-2" />
                        {{ t('No vacations found') }}   
                    </div>
                </template>

                <template #item.action="{ item }">
                    <div class="d-flex justify-center gap-1">
                        <VBtn icon variant="text" color="info" size="small"
                            @click="$router.push({ name: 'vacation-edit', query: { id: item.id } })">
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
            :confirmation-question="t('Are you sure you want to delete this vacation?')" :confirm-title="t('Deleted!')"
            :confirm-msg="t('Vacation has been deleted successfully.')" :cancel-title="t('Cancelled')"
            :cancel-msg="t('Vacation Deletion Cancelled!')" @confirm="handleDelete" />
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
