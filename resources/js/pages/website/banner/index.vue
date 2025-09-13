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
const selectedBannerId = ref(null)
const bannerData = ref(null)

// Company formatters
const { fetchCompanySettings, formatDate, formatAmount, getSerialNumber } = useCompanyFormatters()

// Computed properties for i18n translations
const SN = computed(() => t('SN'))
const Tag = computed(() => t('Tag'))
const Title = computed(() => t('Title'))
const Photo = computed(() => t('Photo'))
const Status = computed(() => t('Status'))
const Action = computed(() => t('Action'))

// Data table Headers
const headers = [
    {
        title: SN,
        key: 'id',
        sortable: false,
    },
    {
        title: Tag,
        key: 'banner_tag',
        sortable: true,
    },
    {
        title: Title,
        key: 'banner_title',
        sortable: true,
    },
    {
        title: Photo,
        key: 'banner_image',
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

const fetchBanners = async () => {
    const response = await $api('/banners', {
        method: 'GET',
        query: {
            q: searchQuery.value,
            itemsPerPage: itemsPerPage.value,
            page: page.value,
            sortBy: sortBy.value,
            orderBy: orderBy.value,
        },
    })
    bannerData.value = response.data
}

// Initial fetch
await fetchBanners()

const banners = computed(() => {
    const data = bannerData.value?.banners || []
    return data.map((banner, index) => ({
        ...banner,
        serial_number: getSerialNumber(index, totalBanners.value, page.value, itemsPerPage.value),
    }))
})
const totalBanners = computed(() => bannerData.value?.total || 0)

const openConfirmDialog = (bannerId) => {
    isConfirmDialogOpen.value = true
    selectedBannerId.value = bannerId
}

const handleDelete = async (confirmed) => {
    if (!confirmed) return;

    try {
        await $api(`/banners/${selectedBannerId.value}`, {
            method: 'DELETE',
        })
        selectedBannerId.value = null
        isConfirmDialogOpen.value = false
        await fetchBanners()
        toast(t('Banner deleted successfully'), {
            "type": "success",
        });
    } catch (error) {
        isConfirmDialogOpen.value = false
        selectedBannerId.value = null
        console.error(t('Error deleting banner:'), error)
        toast(t('Failed to delete banner'), {
            "type": "error",
        });
    }
}

// Watch for changes in search query
watch(searchQuery, () => {
    page.value = 1 // Reset to first page when searching
    fetchBanners() 
})

// Watch for changes in pagination
watch([page, itemsPerPage], () => {
    fetchBanners()
})

onMounted(async () => {
    await fetchCompanySettings()
})
</script>

<template>
    <div>
        <VCard :title="t('List Banner')">
            <VCardText>
                <div class="d-flex justify-space-between flex-wrap gap-y-4">
                    <AppTextField v-model="searchQuery" style="max-inline-size: 280px; min-inline-size: 280px;"
                        :placeholder="t('Search Banner')" />
                    <div class="d-flex flex-row gap-4 align-center flex-wrap">
                        <VBtn prepend-icon="tabler-plus" :to="{ name: 'website-banner-create' }">
                            {{ t('Add Banner') }}
                        </VBtn>
                        <AppSelect v-model="itemsPerPage" :items="[5, 10, 20, 50, 100]" />

                        <ExportTable 
                            :data="banners" 
                            :headers="exportHeaders" 
                            filename="banner-report"
                            :title="$t('List Banner')"
                        />
                    </div>
                </div>
            </VCardText>

            <VDivider />
            <VDataTableServer v-model:items-per-page="itemsPerPage" v-model:page="page" :items="banners" item-value="id"
                :headers="headers" :items-length="totalBanners" class="text-no-wrap" @update:options="updateOptions">

                <template #item.id="{ item }">
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
                        {{ t('No banners found') }}   
                    </div>
                </template>

                <!-- Photo column -->
                <template #item.banner_image="{ item }">
                    <VImg :src="item.banner_image_url" width="150" height="70" cover class="rounded banner-image" />
                </template>

                <template #item.status="{ item }">
                    <VChip :color="item.status == 'Enabled' ? 'success' : 'error'" size="small">
                        {{ item.status }}
                    </VChip>
                </template>


                <template #item.action="{ item }">
                    <div class="d-flex justify-center gap-1">
                        <VBtn icon variant="text" color="info" size="small"
                            @click="$router.push({ name: 'website-banner-edit', query: { id: item.id } })">
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
            :confirmation-question="t('Are you sure you want to delete this banner?')" :confirm-title="t('Deleted!')"
            :confirm-msg="t('Banner has been deleted successfully.')" :cancel-title="t('Cancelled')"
            :cancel-msg="t('Banner Deletion Cancelled!')" @confirm="handleDelete" />
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
