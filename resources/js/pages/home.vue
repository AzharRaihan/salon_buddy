<script setup>
import UserBioPanel from '@/components/UserBioPanel.vue';
import { computed, ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import { toast } from 'vue3-toastify';
import { useI18n } from 'vue-i18n';
import { useCompanyFormatters } from '@/composables/useCompanyFormatters';

const { t } = useI18n()
const userData = useCookie('userData').value;
const router = useRouter()
const searchQuery = ref('')

// Data table options
const itemsPerPage = ref(10)
const page = ref(1)
const sortBy = ref()
const orderBy = ref()
const isConfirmDialogOpen = ref(false)
const selectedSaleId = ref(null)
const saleData = ref(null)



// Company formatters
const { fetchCompanySettings, formatDate, formatAmount, getSerialNumber } = useCompanyFormatters()

// Computed properties for i18n translations
const SN = computed(() => t('SN'))
const ReferenceNo = computed(() => t('Reference No'))
const Customer = computed(() => t('Customer'))
const OrderDate = computed(() => t('Order Date'))
const TotalPayable = computed(() => t('Total Payable'))
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
      title: Customer,
      key: 'customer',
      sortable: true,
    },
    {
      title: OrderDate,
      key: 'order_date',
      sortable: true,
    },
    {
      title: TotalPayable,
      key: 'total_payable',
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

const fetchSales = async () => {
  const response = await $api('/sales-by-user', {
    method: 'GET',
    query: {
      q: searchQuery.value,
      itemsPerPage: itemsPerPage.value,
      page: page.value,
      sortBy: sortBy.value,
      orderBy: orderBy.value,
    },
  })
  saleData.value = response.data
}

// Initial fetch
await fetchSales()

const sales = computed(() => {
    const data = saleData.value?.sales || []
    return data.map((item, index) => ({
        ...item,
        serial_number: getSerialNumber(index, totalSales.value, page.value, itemsPerPage.value),
    }))
})
const totalSales = computed(() => saleData.value?.total || 0)

const openConfirmDialog = (saleId) => {
    isConfirmDialogOpen.value = true
    selectedSaleId.value = saleId
}

const handleDelete = async (confirmed) => {
    if (!confirmed) return;

    try {
        await $api(`/sales/${selectedSaleId.value}`, {
            method: 'DELETE',
        })
        selectedSaleId.value = null
        isConfirmDialogOpen.value = false
        await fetchSales()
        toast('Product deleted successfully', {
            "type": "success",
        });
    } catch (error) {
        isConfirmDialogOpen.value = false
        selectedSaleId.value = null
        console.error('Error deleting sale:', error)
        toast('Failed to delete sale', {
            "type": "error",
        });
    }
}
// Watch for changes in search query
watch(searchQuery, () => {
    page.value = 1 // Reset to first page when searching
    fetchSales() 
})

// Watch for changes in pagination
watch([page, itemsPerPage], () => {
    fetchSales()
})

onMounted(async () => {
    await fetchCompanySettings()
})

</script>

<template>
  <div>
    <VRow v-if="userData">
      <VCol cols="12" md="5" lg="4">
        <UserBioPanel :user-data="userData" />
      </VCol>
      <VCol cols="12" md="7" lg="8">
        <VCard>
          <VCardText class="d-flex justify-space-between align-center flex-wrap gap-4">
            <h5 class="text-h5">
              {{ t('List Sale') }}
            </h5>

            <div style="inline-size: 250px;">
              <AppTextField v-model="searchQuery" :placeholder="t('Search Sale')" />
            </div>
          </VCardText>
          <VDivider />

          <VDataTableServer v-model:items-per-page="itemsPerPage" v-model:page="page" :items="sales" item-value="id"
            :headers="headers" :items-length="totalSales" class="text-no-wrap" @update:options="updateOptions">

            <template #item.serial_number="{ item }">
              {{ item.serial_number }}
            </template>

            <template #item.customer="{ item }">
              {{ item.customer?.name }}
            </template>

            <!-- Formatted Date -->
            <template #item.order_date="{ item }">
              {{ formatDate(item.order_date) }}
            </template>

            <!-- Amount -->
            <template #item.total_payable="{ item }">
              {{ formatAmount(item.total_payable) }}
            </template>
            
            <!-- Loading state -->
            <template #loading>
              <VSkeletonLoader type="table-row" :rows="itemsPerPage" />
            </template>

            <!-- No data state -->
            <template #no-data>
              <div class="d-flex align-center justify-center pa-4">
                <VIcon icon="tabler-alert-circle" class="me-2" />
                {{ t('No sales found') }}   
              </div>
            </template>

            <template #item.action="{ item }">
              <div class="d-flex justify-center gap-1">
                <VBtn icon variant="text" color="default" size="small" @click="openConfirmDialog(item.id)">
                  <VIcon size="22" icon="tabler-trash" />
                </VBtn>
              </div>
            </template>
          </VDataTableServer>
        </VCard>
        <ConfirmDialog v-model:is-dialog-visible="isConfirmDialogOpen"
            :confirmation-question="t('Are you sure you want to delete this sale?')" :confirm-title="t('Deleted!')"
            :confirm-msg="t('Sale has been deleted successfully.')" :cancel-title="t('Cancelled')"
            :cancel-msg="t('Sale Deletion Cancelled!')" @confirm="handleDelete" />
      </VCol>
    </VRow>
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
