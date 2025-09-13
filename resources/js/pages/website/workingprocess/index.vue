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
const selectedWorkingProcessId = ref(null)
const workingProcessData = ref(null)

// Company formatters
const { fetchCompanySettings, formatDate, formatAmount, getSerialNumber } = useCompanyFormatters()

// Computed properties for i18n translations
const SN = computed(() => t('SN'))
const Title = computed(() => t('Title'))
const Description = computed(() => t('Description'))
const Image = computed(() => t('Image'))
const Status = computed(() => t('Status'))
const Position = computed(() => t('Position'))
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
        title: Position,
        key: 'position',
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

const fetchWorkingProcesses = async () => {
    try {
        const response = await $api('/working-processes', {
            method: 'GET',
            query: {
                q: searchQuery.value,
                itemsPerPage: itemsPerPage.value,
                page: page.value,
                sortBy: sortBy.value,
                orderBy: orderBy.value,
            },
        })
        workingProcessData.value = response.data
        console.log('API Response:', response.data) // Debug log
    } catch (error) {
        console.error('Error fetching working processes:', error)
        toast(t('Failed to fetch working processes'), {
            type: 'error',
        })
    }
}

// Initial fetch
onMounted(async () => {
    await fetchCompanySettings()
    await fetchWorkingProcesses()
})

const workingProcesses = computed(() => {
    if (!workingProcessData.value?.workingProcesses) {
        return []
    }
    
    return workingProcessData.value.workingProcesses.map((workingProcess, index) => ({
        ...workingProcess,
        serial_number: getSerialNumber(index, totalWorkingProcesses.value, page.value, itemsPerPage.value),
    }))
})

const totalWorkingProcesses = computed(() => workingProcessData.value?.total || 0)

const openConfirmDialog = (workingProcessId) => {
    isConfirmDialogOpen.value = true
    selectedWorkingProcessId.value = workingProcessId
}

const handleDelete = async (confirmed) => {
    if (!confirmed) return;
    try {
        await $api(`/working-processes/${selectedWorkingProcessId.value}`, {
            method: 'DELETE',
        })
        selectedWorkingProcessId.value = null
        isConfirmDialogOpen.value = false
        await fetchWorkingProcesses()
        toast(t('Working process deleted successfully'), {
            type: 'success',
        });
    } catch (error) {
        isConfirmDialogOpen.value = false
        selectedWorkingProcessId.value = null
        console.error('Error deleting working process:', error)
        toast(t('Failed to delete working process'), {
            type: 'error',
        });
    }
}

// Watch for changes in search query
watch(searchQuery, () => {
    page.value = 1 // Reset to first page when searching
    fetchWorkingProcesses() 
})

// Watch for changes in pagination
watch([page, itemsPerPage], () => {
    fetchWorkingProcesses()
})
const truncateWords = (text, wordLimit = 20) => {
  if (!text) return ''
  const words = text.split(' ')
  return words.length > wordLimit ? words.slice(0, wordLimit).join(' ') + ' ...' : text
}
</script>

<template>
    <div>
        <VCard :title="$t('List Working Process')">
            <VCardText>
                <div class="d-flex justify-space-between flex-wrap gap-y-4">
                    <AppTextField v-model="searchQuery" style="max-inline-size: 280px; min-inline-size: 280px;"
                        :placeholder="$t('Search Working Process')" />
                    <div class="d-flex flex-row gap-4 align-center flex-wrap">
                        <VBtn prepend-icon="tabler-plus" :to="{ name: 'website-workingprocess-create' }">
                            {{ $t('Add Working Process') }}
                        </VBtn>
                        <AppSelect v-model="itemsPerPage" :items="[5, 10, 20, 50, 100]" />

                        <ExportTable 
                            :data="workingProcesses" 
                            :headers="exportHeaders" 
                            filename="working-process-report"
                            :title="$t('List Working Process')"
                        />
                    </div>
                </div>
            </VCardText>

            <VDivider />
            <VDataTableServer v-model:items-per-page="itemsPerPage" v-model:page="page" :items="workingProcesses" item-value="id"
                :headers="headers" :items-length="totalWorkingProcesses" class="text-no-wrap" @update:options="updateOptions">

                <!-- Serial Number -->
                <template #item.serial_number="{ item }">
                    {{ item.serial_number }}
                </template>

                <!-- Loading state -->
                <template #loading>
                    <VSkeletonLoader type="table-row" :rows="itemsPerPage" />
                </template>

                <!-- Description column -->
                <template #item.description="{ item }">
                    {{ truncateWords(item.description, 10) }}
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
                        {{ $t('No working processes found') }}   
                    </div>
                </template>

                <template #item.action="{ item }">
                    <div class="d-flex justify-center gap-1">
                        <VBtn icon variant="text" color="info" size="small"
                            @click="$router.push({ name: 'website-workingprocess-edit', query: { id: item.id } })">
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
            :confirmation-question="$t('Are you sure you want to delete this working process?')" :confirm-title="$t('Deleted!')"
            :confirm-msg="$t('Working process has been deleted successfully.')" :cancel-title="$t('Cancelled')"
            :cancel-msg="$t('Working process Deletion Cancelled!')" @confirm="handleDelete" />
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
