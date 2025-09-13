<script setup>
import { ref, onMounted } from 'vue'
import { $api } from '@/utils/api'
import { useCompanyFormatters } from '@/composables/useCompanyFormatters';
const { fetchCompanySettings, formatDate, formatAmount, formatNumber, getSerialNumber } = useCompanyFormatters()


const statistics = ref([
  {
    title: 'Sales',
    stats: '0',
    icon: 'tabler-chart-pie-2',
    color: 'primary',
  },
  {
    title: 'Customers',
    stats: '0',
    icon: 'tabler-users',
    color: 'info',
  },
  {
    title: 'Products',
    stats: '0',
    icon: 'tabler-shopping-cart',
    color: 'error',
  },
  {
    title: 'Revenue',
    stats: '$0',
    icon: 'tabler-currency-dollar',
    color: 'success',
  },
])

const isLoading = ref(false)

const fetchStatistics = async () => {
  try {
    isLoading.value = true
    const response = await $api('/dashboard/statistics')
    
    if (response) {
      statistics.value = [
        {
          title: 'Sales',
          stats: formatAmount(response.totalSales?.toLocaleString()) || formatAmount(0),
          icon: 'tabler-chart-pie-2',
          color: 'primary',
        },
        {
          title: 'Customers',
          stats: formatNumber(response.totalCustomers?.toLocaleString()) || formatNumber(0),
          icon: 'tabler-users',
          color: 'info',
        },
        {
          title: 'Products',
          stats: formatNumber(response.totalItems?.toLocaleString()) || formatNumber(0),
          icon: 'tabler-shopping-cart',
          color: 'error',
        },
        {
          title: 'Revenue',
          stats: formatAmount(response.totalRevenue) || formatAmount(0),
          icon: 'tabler-currency-dollar',
          color: 'success',
        },
      ]
    }
  } catch (error) {
    console.error('Error fetching statistics:', error)
    // Keep default values on error
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  fetchStatistics()
})
</script>

<template>
  <VCard title="Statistics">
    <template #append>
      <span class="text-sm text-disabled">Current Month</span>
    </template>

    <VCardText>
      <VRow>
        <VCol
          v-for="item in statistics"
          :key="item.title"
          cols="6"
          md="3"
        >
          <div class="d-flex align-center gap-4 mt-md-3 mt-0">
            <VAvatar
              :color="item.color"
              variant="tonal"
              rounded
              size="40"
            >
              <VIcon :icon="item.icon" />
            </VAvatar>

            <div class="d-flex flex-column">
              <h5 class="text-h5">
                {{ isLoading ? '...' : item.stats }}
              </h5>
              <div class="text-sm">
                {{ item.title }}
              </div>
            </div>
          </div>
        </VCol>
      </VRow>
    </VCardText>
  </VCard>
</template>
