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
const selectedHolidayId = ref(null)
const holidayData = ref(null)

// Company formatters
const { fetchCompanySettings, formatDate, formatAmount, getSerialNumber } = useCompanyFormatters()

// Computed properties for i18n translations
const SN = computed(() => t('SN'))
const Day = computed(() => t('Day'))
const StartTime = computed(() => t('Start Time'))
const EndTime = computed(() => t('End Time'))
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
        title: Day,
        key: 'day',
        sortable: true,
    },
    {
        title: StartTime,
        key: 'start_time',
        sortable: true,
    },
    {
        title: EndTime,
        key: 'end_time',
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

const fetchHolidays = async () => {
    const response = await $api('/holidays', {
        method: 'GET',
        query: {
            q: searchQuery.value,
            itemsPerPage: itemsPerPage.value,
            page: page.value,
            sortBy: sortBy.value,
            orderBy: orderBy.value,
        },
    })
    holidayData.value = response.data
}

// Initial fetch
await fetchHolidays()

const holidays = computed(() => {
    const data = holidayData.value?.holidays || []
    return data.map((holiday, index) => ({
        ...holiday,
        serial_number: getSerialNumber(index, totalHolidays.value, page.value, itemsPerPage.value),
    }))
})

const totalHolidays = computed(() => holidayData.value?.total || 0)

const openConfirmDialog = (holidayId) => {
    isConfirmDialogOpen.value = true
    selectedHolidayId.value = holidayId
}

const handleDelete = async (confirmed) => {
    if (!confirmed) return;

    try {
        await $api(`/holidays/${selectedHolidayId.value}`, {
            method: 'DELETE',
        })
        selectedHolidayId.value = null
        isConfirmDialogOpen.value = false
        await fetchHolidays()
        toast('Holiday deleted successfully', {
            "type": "success",
        });
    } catch (error) {
        isConfirmDialogOpen.value = false
        selectedHolidayId.value = null
        console.error('Error deleting holiday:', error)
        toast('Failed to delete holiday', {
            "type": "error",
        });
    }
}

// Watch for changes in search query
watch(searchQuery, () => {
    page.value = 1 // Reset to first page when searching
    fetchHolidays() 
})

// Watch for changes in pagination
watch([page, itemsPerPage], () => {
    fetchHolidays()
})

// Initialize company settings on mount
onMounted(async () => {
    await fetchCompanySettings()
})

</script>

<template>
    <div>
        <VCard :title="t('List Holiday')">
            <VCardText>
                <div class="d-flex justify-space-between flex-wrap gap-y-4">
                    <AppTextField v-model="searchQuery" style="max-inline-size: 280px; min-inline-size: 280px;"
                        :placeholder="t('Search Holiday')" />
                    <div class="d-flex flex-row gap-4 align-center flex-wrap">
                        <VBtn prepend-icon="tabler-plus" :to="{ name: 'holiday-create' }">
                            {{ t('Add Holiday') }}
                        </VBtn>
                        <AppSelect v-model="itemsPerPage" :items="[5, 10, 20, 50, 100]" />

                        <ExportTable 
                            :data="holidays" 
                            :headers="exportHeaders" 
                            filename="holiday-report"
                            :title="$t('List Holiday')"
                        />
                    </div>
                </div>
            </VCardText>

            <VDivider />
            <VDataTableServer v-model:items-per-page="itemsPerPage" v-model:page="page" :items="holidays" item-value="id"
                :headers="headers" :items-length="totalHolidays" class="text-no-wrap" @update:options="updateOptions">

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
                        {{ t('No holidays found') }}   
                    </div>
                </template>

                <template #item.action="{ item }">
                    <div class="d-flex justify-center gap-1">
                        <VBtn icon variant="text" color="info" size="small"
                            @click="$router.push({ name: 'holiday-edit', query: { id: item.id } })">
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
            :confirmation-question="t('Are you sure you want to delete this holiday?')" :confirm-title="t('Deleted!')"
            :confirm-msg="t('Holiday has been deleted successfully.')" :cancel-title="t('Cancelled')"
            :cancel-msg="t('Holiday Deletion Cancelled!')" @confirm="handleDelete" />
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
