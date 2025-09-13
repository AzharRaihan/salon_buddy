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
const branch_info = useCookie("branch_info").value || 0;

// Data table options
const itemsPerPage = ref(10)
const page = ref(1)
const sortBy = ref()
const orderBy = ref()
const isConfirmDialogOpen = ref(false)
const selectedDamageId = ref(null)
const damageData = ref(null)

// Company formatters
const { fetchCompanySettings, formatDate, formatAmount, getSerialNumber } = useCompanyFormatters()

// Computed properties for i18n translations
const SN = computed(() => t('SN'))
const ReferenceNo = computed(() => t('Reference No'))
const Date = computed(() => t('Date'))
const EmployeeName = computed(() => t('Employee Name'))
const TotalLoss = computed(() => t('Total Loss'))
const Action = computed(() => t('Action'))


// Data table Headers
const headers = [
    {
        title: SN,
        key: 'serial_number',
        sortable: false,
    },
    {
        title: ReferenceNo,
        key: 'reference_no',
        sortable: true,
    },
    {
        title: Date,
        key: 'date',
        sortable: true,
    },
    {
        title: EmployeeName,
        key: 'employee_name',
        sortable: true,
    },
    {
        title: TotalLoss,
        key: 'total_loss',
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

const fetchDamages = async () => {
    const response = await $api('/damages', {
        method: 'GET',
        query: {
            q: searchQuery.value,
            itemsPerPage: itemsPerPage.value,
            page: page.value,
            branch_id: branch_info.id,
            sortBy: sortBy.value,
            orderBy: orderBy.value,
        },
    })
    damageData.value = response.data
}

// Initial fetch
await fetchDamages()


const damages = computed(() => {
    const damages = damageData.value?.damages || [];
    return damages.map((damage, index) => ({
        ...damage,
        date: formatDate(damage.date),
        serial_number: getSerialNumber(index, totalDamages.value, page.value, itemsPerPage.value),
    }));
})
const totalDamages = computed(() => damageData.value?.total || 0)

const openConfirmDialog = (damageId) => {
    isConfirmDialogOpen.value = true
    selectedDamageId.value = damageId
}

const handleDelete = async (confirmed) => {
    if (!confirmed) return;

    try {
        await $api(`/damages/${selectedDamageId.value}`, {
            method: 'DELETE',
        })
        selectedDamageId.value = null
        isConfirmDialogOpen.value = false
        await fetchDamages()
        toast('Damage deleted successfully', {
            "type": "success",
        });
    } catch (error) {
        isConfirmDialogOpen.value = false
        selectedDamageId.value = null
        console.error('Error deleting damage:', error)
        toast('Failed to delete damage', {
            "type": "error",
        });
    }
}

// Watch for changes in search query
watch(searchQuery, () => {
    page.value = 1 // Reset to first page when searching
    fetchDamages() 
})

// Watch for changes in pagination
watch([page, itemsPerPage], () => {
    fetchDamages()
})

// Initialize company settings on mount
onMounted(async () => {
    await fetchCompanySettings()
})

</script>

<template>
    <div>
        <VCard :title="t('List Damage')">
            <VCardText>
                <div class="d-flex justify-space-between flex-wrap gap-y-4">
                    <AppTextField v-model="searchQuery" style="max-inline-size: 280px; min-inline-size: 280px;"
                        :placeholder="t('Search Damage')" />
                    <div class="d-flex flex-row gap-4 align-center flex-wrap">
                        <VBtn prepend-icon="tabler-plus" :to="{ name: 'damage-create' }">
                            {{ t('Add Damage') }}
                        </VBtn>
                        <AppSelect v-model="itemsPerPage" :items="[5, 10, 20, 50, 100]" />

                        <ExportTable 
                            :data="damages" 
                            :headers="exportHeaders" 
                            filename="damage-report"
                            :title="$t('List Damage')"
                        />
                    </div>
                </div>
            </VCardText>

            <VDivider />
            <VDataTableServer v-model:items-per-page="itemsPerPage" v-model:page="page" :items="damages" item-value="id"
                :headers="headers" :items-length="totalDamages" class="text-no-wrap" @update:options="updateOptions">
                <!-- Loading state -->
                <template #loading>
                    <VSkeletonLoader type="table-row" :rows="itemsPerPage" />
                </template>

                <!-- Serial Number -->
                <template #item.serial_number="{ item }">
                    {{ item.serial_number }}
                </template>

                

                <!-- Formatted Date -->
                <template #item.date="{ item }">
                    {{ formatDate(item.date) }}
                </template>

                <!-- Employee name with mobile number -->
                <template #item.employee_name="{ item }">
                    {{ item.employee_name }}
                    <span class="text-muted" v-if="item.employee_phone">
                        ({{ item.employee_phone }})
                    </span>
                </template>

                <!-- Amount -->
                <template #item.total_loss="{ item }">
                    {{ formatAmount(item.total_loss) }}
                </template>

                <!-- No data state -->
                <template #no-data>
                    <div class="d-flex align-center justify-center pa-4">
                        <VIcon icon="tabler-alert-circle" class="me-2" />
                        {{ t('No damages found') }}   
                    </div>
                </template>

                <template #item.action="{ item }">
                    <div class="d-flex justify-center gap-1">
                        <VBtn icon variant="text" color="info" size="small"
                            @click="$router.push({ name: 'damage-edit', query: { id: item.id } })"
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
            :confirmation-question="t('Are you sure you want to delete this damage?')" :confirm-title="t('Deleted!')"
            :confirm-msg="t('Damage has been deleted successfully.')" :cancel-title="t('Cancelled')"
            :cancel-msg="t('Damage Deletion Cancelled!')" @confirm="handleDelete" />
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
