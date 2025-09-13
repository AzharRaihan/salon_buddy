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
const selectedAttendanceId = ref(null)
const attendanceData = ref(null)

// Company formatters
const { fetchCompanySettings, formatDate, formatAmount, getSerialNumber } = useCompanyFormatters()

// Computed properties for i18n translations
const SN = computed(() => t('SN'))
const Date = computed(() => t('Date'))
const InTime = computed(() => t('In Time'))
const OutTime = computed(() => t('Out Time'))
const TotalHour = computed(() => t('Total Hour'))
const Employee = computed(() => t('Employee'))
const Action = computed(() => t('Action'))

// Data table Headers
const headers = [
    {
        title: SN,
        key: 'serial_number',
        sortable: false,
    },
    {
        title: Date,
        key: 'date',
        sortable: true,
    },
    {
        title: InTime,
        key: 'in_time',
        sortable: true,
    },
    {
        title: OutTime,
        key: 'out_time',
        sortable: true,
    },
    {
        title: TotalHour,
        key: 'total_time',
        sortable: true,
    },
    {
        title: Employee,
        key: 'name',
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

const fetchAttendances = async () => {
    const response = await $api('/attendances', {
        method: 'GET',
        query: {
            q: searchQuery.value,
            itemsPerPage: itemsPerPage.value,
            page: page.value,
            sortBy: sortBy.value,
            orderBy: orderBy.value,
        },
    })
    attendanceData.value = response.data
}

// Initial fetch
await fetchAttendances()

const attendances = computed(() => {
    const data = attendanceData.value?.attendances || []
    return data.map((attendance, index) => ({
        ...attendance,
        serial_number: getSerialNumber(index, totalAttendances.value, page.value, itemsPerPage.value),
        formatted_date: formatDate(attendance.date)
    }))
})
const totalAttendances = computed(() => attendanceData.value?.total || 0)

const openConfirmDialog = (attendanceId) => {
    isConfirmDialogOpen.value = true
    selectedAttendanceId.value = attendanceId
}

const handleDelete = async (confirmed) => {
    if (!confirmed) return;

    try {
        await $api(`/attendances/${selectedAttendanceId.value}`, {
            method: 'DELETE',
        })
        selectedAttendanceId.value = null
        isConfirmDialogOpen.value = false
        await fetchAttendances()
        toast('Attendance record deleted successfully', {
            "type": "success",
        });
    } catch (error) {
        isConfirmDialogOpen.value = false
        selectedAttendanceId.value = null
        console.error('Error deleting attendance record:', error)
        toast('Failed to delete attendance record', {
            "type": "error",
        });
    }
}

// Watch for changes in search query
watch(searchQuery, () => {
    page.value = 1 // Reset to first page when searching
    fetchAttendances() 
})

// Watch for changes in pagination
watch([page, itemsPerPage], () => {
    fetchAttendances()
})

// Initialize company settings on mount
onMounted(async () => {
    await fetchCompanySettings()
})
</script>

<template>
    <div>
        <VCard :title="$t('Attendance List')">
            <VCardText>
                <div class="d-flex justify-space-between flex-wrap gap-y-4">
                    <AppTextField v-model="searchQuery" style="max-inline-size: 280px; min-inline-size: 280px;"
                        :placeholder="$t('Search Attendance')" />
                    <div class="d-flex flex-row gap-4 align-center flex-wrap">
                        <VBtn prepend-icon="tabler-plus" :to="{ name: 'attendance-create' }">
                            {{ $t('Add Attendance') }}
                        </VBtn>
                        <AppSelect v-model="itemsPerPage" :items="[5, 10, 20, 50, 100]" />

                        <ExportTable 
                            :data="attendances" 
                            :headers="exportHeaders" 
                            filename="attendance-report"
                            :title="$t('Attendance List')"
                        />
                    </div>
                </div>
            </VCardText>

            <VDivider />
            <VDataTableServer v-model:items-per-page="itemsPerPage" v-model:page="page" :items="attendances" item-value="id"
                :headers="headers" :items-length="totalAttendances" class="text-no-wrap" @update:options="updateOptions">
                <!-- Serial Number -->
                <template #item.serial_number="{ item }">
                    {{ item.serial_number }}
                </template>

                <!-- Formatted Date -->
                <template #item.date="{ item }">
                    {{ item.formatted_date }}
                </template>
                
                <!-- Loading state -->
                <template #loading>
                    <VSkeletonLoader type="table-row" :rows="itemsPerPage" />
                </template>

                <!-- No data state -->
                <template #no-data>
                    <div class="d-flex align-center justify-center pa-4">
                        <VIcon icon="tabler-alert-circle" class="me-2" />
                        {{ $t('No attendance records found') }}   
                    </div>
                </template>

                <template #item.action="{ item }">
                    <div class="d-flex justify-center gap-1">
                        <VBtn icon variant="text" color="info" size="small"
                            @click="$router.push({ name: 'attendance-edit', query: { id: item.id } })"
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
            :confirmation-question="$t('Are you sure you want to delete this attendance record?')" :confirm-title="$t('Deleted!')"
            :confirm-msg="$t('Attendance record has been deleted successfully.')" :cancel-title="$t('Cancelled')"
            :cancel-msg="$t('Attendance Record Deletion Cancelled!')" @confirm="handleDelete" />
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