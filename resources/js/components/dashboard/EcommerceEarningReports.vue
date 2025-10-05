<script setup>
import { useTheme } from 'vuetify'
import { hexToRgb } from '@layouts/utils'
import { ref, onMounted, computed } from 'vue'
import { $api } from '@/utils/api'
// import VueApexCharts from 'vue3-apexcharts'

import { useI18n } from 'vue-i18n';

const { t } = useI18n({ useScope: 'global' })


const vuetifyTheme = useTheme()

const series = ref([{
  data: [0, 0, 0, 0, 0, 0, 0],
}])


const earningReports = ref([
  {
    avatarIcon: 'tabler-chart-pie-2',
    avatarColor: 'primary',
    title: 'Net Profit',
    subtitle: '0 Sales',
    earnings: '$0',
    percentage: '0%',
  },
  {
    avatarIcon: 'tabler-currency-dollar',
    avatarColor: 'success',
    title: 'Total Income',
    subtitle: 'Sales, Affiliation',
    earnings: '$0',
    percentage: '0%',
  },
  {
    avatarIcon: 'tabler-credit-card',
    avatarColor: 'secondary',
    title: 'Total Expenses',
    subtitle: 'Rent, Salary',
    earnings: '$0',
    percentage: '0%',
  },
])

const chartOptions = computed(() => {
  const currentTheme = vuetifyTheme.current.value.colors
  const variableTheme = vuetifyTheme.current.value.variables
  const labelColor = `rgba(${ hexToRgb(currentTheme['on-background']) },${ variableTheme['disabled-opacity'] })`
  const labelPrimaryColor = `rgba(${ hexToRgb(currentTheme.primary) },0.1)`
  
  return {
    chart: {
      type: 'bar',
      toolbar: { show: false },
    },
    tooltip: { enabled: false },
    plotOptions: {
      bar: {
        barHeight: '60%',
        columnWidth: '60%',
        startingShape: 'rounded',
        endingShape: 'rounded',
        borderRadius: 4,
        distributed: true,
      },
    },
    grid: {
      show: false,
      padding: {
        top: -20,
        bottom: 0,
        left: -10,
        right: -10,
      },
    },
    colors: [
      labelPrimaryColor,
      labelPrimaryColor,
      labelPrimaryColor,
      labelPrimaryColor,
      `rgba(${ hexToRgb(currentTheme.background) }, 1)`,
      labelPrimaryColor,
      labelPrimaryColor,
    ],
    dataLabels: { enabled: false },
    legend: { show: false },
    xaxis: {
      categories: [
        'Mo',
        'Tu',
        'We',
        'Th',
        'Fr',
        'Sa',
        'Su',
      ],
      axisBorder: { show: false },
      axisTicks: { show: false },
      labels: {
        style: {
          colors: labelColor,
          fontSize: '13px',
        },
      },
    },
    yaxis: { labels: { show: false } },
  }
})

const fetchEarningData = async () => {
  try {
    const response = await $api('/dashboard/earning-reports')
    
    if (response) {
      series.value = response.series || [{ data: [0, 0, 0, 0, 0, 0, 0] }]
      earningReports.value = response.reports || [
        {
          avatarIcon: 'tabler-chart-pie-2',
          avatarColor: 'primary',
          title: 'Net Profit',
          subtitle: '0 Sales',
          earnings: '$0',
          percentage: '0%',
        },
        {
          avatarIcon: 'tabler-currency-dollar',
          avatarColor: 'success',
          title: 'Total Income',
          subtitle: 'Sales, Affiliation',
          earnings: '$0',
          percentage: '0%',
        },
        {
          avatarIcon: 'tabler-credit-card',
          avatarColor: 'secondary',
          title: 'Total Expenses',
          subtitle: 'Rent, Salary',
          earnings: '$0',
          percentage: '0%',
        },
      ]
    }
  } catch (error) {
    console.error('Error fetching earning data:', error)
  }
}

onMounted(() => {
  fetchEarningData()
})
</script>

<template>
  <VCard
    :title="t('Earning Reports')"
    :subtitle="t('Weekly Earnings Overview')"
  >

    <VCardText>
      <VList class="card-list mb-5">
        <VListItem
          v-for="report in earningReports"
          :key="report.title"
        >
          <template #prepend>
            <VAvatar
              rounded
              size="34"
              variant="tonal"
              :color="report.avatarColor"
              class="me-1"
            >
              <VIcon
                :icon="report.avatarIcon"
                size="22"
              />
            </VAvatar>
          </template>

          <VListItemTitle class="font-weight-medium me-4">
            {{ report.title }}
          </VListItemTitle>
          <VListItemSubtitle class="me-4">
            {{ report.subtitle }}
          </VListItemSubtitle>

          <template #append>
            <div class="d-flex align-center text-body-2">
              <span class="me-4">{{ report.earnings }}</span>
              <VIcon
                color="success"
                icon="tabler-chevron-up"
                size="20"
                class="me-1"
              />
              <span class="text-disabled">{{ report.percentage }}</span>
            </div>
          </template>
        </VListItem>
      </VList>

      <div>
        <VueApexCharts
          :options="chartOptions"
          :series="series"
          :height="196"
        />
      </div>
    </VCardText>
  </VCard>
</template>

<style lang="scss" scoped>
.card-list {
  --v-card-list-gap: 1.25rem;
}
</style>
