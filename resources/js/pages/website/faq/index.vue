<script setup>
import { computed, ref, watch, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { toast } from 'vue3-toastify';
import { useI18n } from 'vue-i18n';
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
const selectedFaqId = ref(null)
const faqData = ref(null)

// Company formatters
const { fetchCompanySettings, formatDate, formatAmount, getSerialNumber } = useCompanyFormatters()

// Computed properties for i18n translations
const SN = computed(() => t('SN'))
const Title = computed(() => t('Title'))
const Description = computed(() => t('Description'))
const Status = computed(() => t('Status'))
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

const fetchFaqs = async () => {
    const response = await $api('/faqs', {
        method: 'GET',
        query: {
            q: searchQuery.value,
            itemsPerPage: itemsPerPage.value,
            page: page.value,
            sortBy: sortBy.value,
            orderBy: orderBy.value,
        },
    })
    faqData.value = response.data
}

// Initial fetch
await fetchFaqs()

const faqs = computed(() => {
    const data = faqData.value?.faqs || []
    return data.map((faq, index) => ({
        ...faq,
        serial_number: getSerialNumber(index, totalFaqs.value, page.value, itemsPerPage.value),
    }))
})

const totalFaqs = computed(() => faqData.value?.total || 0)

const openConfirmDialog = (faqId) => {
    isConfirmDialogOpen.value = true
    selectedFaqId.value = faqId
}

const handleDelete = async (confirmed) => {
    if (!confirmed) return;

    try {
        await $api(`/faqs/${selectedFaqId.value}`, {
            method: 'DELETE',
        })
        selectedFaqId.value = null
        isConfirmDialogOpen.value = false
        await fetchFaqs()
        toast('FAQ deleted successfully', {
            "type": "success",
        });
    } catch (error) {
        isConfirmDialogOpen.value = false
        selectedFaqId.value = null
        console.error('Error deleting FAQ:', error)
        toast('Failed to delete FAQ', {
            "type": "error",
        });
    }
}

// Watch for changes in search query
watch(searchQuery, () => {
    page.value = 1 // Reset to first page when searching
    fetchFaqs() 
})

// Watch for changes in pagination
watch([page, itemsPerPage], () => {
    fetchFaqs()
})

onMounted(async () => {
    await fetchCompanySettings()
})
</script>

<template>
    <div>
        <VCard :title="t('List FAQ')">
            <VCardText>
                <div class="d-flex justify-space-between flex-wrap gap-y-4">
                    <AppTextField v-model="searchQuery" style="max-inline-size: 280px; min-inline-size: 280px;"
                        :placeholder="t('Search FAQ')" />
                    <div class="d-flex flex-row gap-4 align-center flex-wrap">
                        <VBtn prepend-icon="tabler-plus" :to="{ name: 'website-faq-create' }">
                            {{ t('Add FAQ') }}
                        </VBtn>
                        <AppSelect v-model="itemsPerPage" :items="[5, 10, 20, 50, 100]" />

                        <ExportTable 
                            :data="faqs" 
                            :headers="exportHeaders" 
                            filename="faq-report"
                            :title="$t('List FAQ')"
                        />
                    </div>
                </div>
            </VCardText>

            <VDivider />
            <VDataTableServer v-model:items-per-page="itemsPerPage" v-model:page="page" :items="faqs" item-value="id"
                :headers="headers" :items-length="totalFaqs" class="text-no-wrap" @update:options="updateOptions">

                <template #item.serial_number="{ item }">
                    {{ item.serial_number }}
                </template>


                <template #item.description="{ item }">
                    {{ item.description.length > 45 ? item.description.substring(0, 45) + '...' : item.description }}
                </template>

                <!-- Loading state -->
                <template #loading>
                    <VSkeletonLoader type="table-row" :rows="itemsPerPage" />
                </template>

                <!-- No data state -->
                <template #no-data>
                    <div class="d-flex align-center justify-center pa-4">
                        <VIcon icon="tabler-alert-circle" class="me-2" />
                        {{ t('No FAQs found') }}   
                    </div>
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

                <template #item.action="{ item }">
                    <div class="d-flex justify-center gap-1">
                        <VBtn icon variant="text" color="info" size="small"
                            @click="$router.push({ name: 'website-faq-edit', query: { id: item.id } })">
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
            :confirmation-question="t('Are you sure you want to delete this FAQ?')" :confirm-title="t('Deleted!')"
            :confirm-msg="t('FAQ has been deleted successfully.')" :cancel-title="t('Cancelled')"
            :cancel-msg="t('FAQ Deletion Cancelled!')" @confirm="handleDelete" />
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