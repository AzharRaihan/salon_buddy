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
const { t } = useI18n();
const router = useRouter()
const searchQuery = ref('')

// Data table options
const itemsPerPage = ref(10)
const page = ref(1)
const sortBy = ref()
const orderBy = ref()
const isConfirmDialogOpen = ref(false)
const selectedSalaryId = ref(null)
const salaryData = ref(null)
const branch_info = useCookie("branch_info").value || 0;

// Company formatters
const { fetchCompanySettings, formatDate, formatAmount, getSerialNumber } = useCompanyFormatters()

// Computed properties for i18n translations
const SN = computed(() => t('SN'))
const Date = computed(() => t('Date'))
const BranchName = computed(() => t('Branch Name'))
const Year = computed(() => t('Year'))
const Month = computed(() => t('Month'))
const TotalAmount = computed(() => t('Total Amount'))
const Action = computed(() => t('Action'))

// Month names mapping
const monthNames = {
    1: 'January',
    2: 'February', 
    3: 'March',
    4: 'April',
    5: 'May',
    6: 'June',
    7: 'July',
    8: 'August',
    9: 'September',
    10: 'October',
    11: 'November',
    12: 'December'
}

// Data table Headers
const headers = [
    {
        title: SN,
        key: 'serial_number',
        sortable: false,
    },
    {
        title: Date,
        key: 'generated_date',
        sortable: true,
    },
    {
        title: BranchName,
        key: 'branch_name',
        sortable: true,
    },
    {
        title: Year,
        key: 'year',
        sortable: true,
    },
    {
        title: Month,
        key: 'month',
        sortable: true,
    },
    {
        title: TotalAmount,
        key: 'total_amount',
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

const fetchSalaries = async () => {
    const response = await $api('/salaries', {
        method: 'GET',
        query: {
            q: searchQuery.value,
            itemsPerPage: itemsPerPage.value,
            page: page.value,
            sortBy: sortBy.value,
            orderBy: orderBy.value,
        },
    })
    salaryData.value = response.data
}

// Initial fetch
await fetchSalaries()

const salaries = computed(() => {
    const salaries = salaryData.value?.salaries || [];
    return salaries.map((salary, index) => ({
        ...salary,
        month: monthNames[salary.month] || salary.month,
        generated_date: formatDate(salary.generated_date),
        serial_number: getSerialNumber(index, totalSalaries.value, page.value, itemsPerPage.value),
    }));
})
const totalSalaries = computed(() => salaryData.value?.total || 0)


const openConfirmDialog = (salaryId) => {
    isConfirmDialogOpen.value = true
    selectedSalaryId.value = salaryId
}

const handleDelete = async (confirmed) => {
    if (!confirmed) return;

    try {
        await $api(`/salaries/${selectedSalaryId.value}`, {
            method: 'DELETE',
        })
        selectedSalaryId.value = null
        isConfirmDialogOpen.value = false
        await fetchSalaries()
        toast('Salary deleted successfully', {
            "type": "success",
        });
    } catch (error) {
        isConfirmDialogOpen.value = false
        selectedSalaryId.value = null
        console.error('Error deleting salary:', error)
        toast('Failed to delete salary', {
            "type": "error",
        });
    }
}

// Watch for changes in search query
watch(searchQuery, () => {
    page.value = 1 // Reset to first page when searching
    fetchSalaries() 
})

// Watch for changes in pagination
watch([page, itemsPerPage], () => {
    fetchSalaries()
})

onMounted(async () => {
    await fetchCompanySettings()
})

</script>

<template>
    <div>
        <VCard :title="t('Salary List')">
            <VCardText>
                <div class="d-flex justify-space-between flex-wrap gap-y-4">
                    <AppTextField v-model="searchQuery" style="max-inline-size: 280px; min-inline-size: 280px;"
                        :placeholder="t('Search Salary')" />
                    <div class="d-flex flex-row gap-4 align-center flex-wrap">
                        <VBtn prepend-icon="tabler-plus" :to="{ name: 'salary-create' }">
                            {{ t('Add Salary') }}
                        </VBtn>
                        <AppSelect v-model="itemsPerPage" :items="[5, 10, 20, 50, 100]" />

                        <ExportTable 
                            :data="salaries" 
                            :headers="exportHeaders" 
                            filename="salary-report"
                            :title="$t('Salary List')"
                        />
                    </div>
                </div>
            </VCardText>

            <VDivider />
            <VDataTableServer v-model:items-per-page="itemsPerPage" v-model:page="page" :items="salaries" item-value="id"
                :headers="headers" :items-length="totalSalaries" class="text-no-wrap" @update:options="updateOptions">
                <!-- Loading state -->
                <template #loading>
                    <VSkeletonLoader type="table-row" :rows="itemsPerPage" />
                </template>

                <!-- Serial Number -->
                <template #item.serial_number="{ item }">
                    {{ item.serial_number }}
                </template>

                <!-- Formatted Date -->
                <template #item.generated_date="{ item }">
                    {{ formatDate(item.generated_date) }}
                </template>

                <!-- Amount -->
                <template #item.total_amount="{ item }">
                    {{ formatAmount(item.total_amount) }}
                </template>

                <!-- No data state -->
                <template #no-data>
                    <div class="d-flex align-center justify-center pa-4">
                        <VIcon icon="tabler-alert-circle" class="me-2" />
                        {{ t('No salaries found') }}   
                    </div>
                </template>

                <template #item.action="{ item }">
                    <div class="d-flex justify-center gap-1">
                        <VBtn icon variant="text" color="info" size="small"
                            @click="$router.push({ name: 'salary-edit', query: { id: item.id } })">
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
            :confirmation-question="t('Are you sure you want to delete this salary?')" :confirm-title="t('Deleted!')"
            :confirm-msg="t('Salary has been deleted successfully.')" :cancel-title="t('Cancelled')"
            :cancel-msg="t('Salary Deletion Cancelled!')" @confirm="handleDelete" />
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
