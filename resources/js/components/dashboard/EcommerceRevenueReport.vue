<script setup>
import { useTheme } from 'vuetify'
import { hexToRgb } from '@layouts/utils'
import { ref, onMounted, computed } from 'vue'
import { $api } from '@/utils/api'

import { useI18n } from 'vue-i18n';

const { t } = useI18n({ useScope: 'global' })


const vuetifyTheme = useTheme()

const series = ref({
  bar: [
    {
      name: t('Earning'),
      data: [0, 0, 0, 0, 0, 0, 0, 0, 0],
    },
    {
      name: t('Expense'),
      data: [0, 0, 0, 0, 0, 0, 0, 0, 0],
    },
  ],
  line: [
    {
      name: t('Last Month'),
      data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
    },
    {
      name: t('This Month'),
      data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
    },
  ],
})

const totalRevenue = ref('0')
const budget = ref('0')

const chartOptions = computed(() => {
  const currentTheme = vuetifyTheme.current.value.colors
  const variableTheme = vuetifyTheme.current.value.variables
  const labelColor = `rgba(${ hexToRgb(currentTheme['on-surface']) },${ variableTheme['disabled-opacity'] })`
  const legendColor = `rgba(${ hexToRgb(currentTheme['on-background']) },${ variableTheme['high-emphasis-opacity'] })`
  const borderColor = `rgba(${ hexToRgb(String(variableTheme['border-color'])) },${ variableTheme['border-opacity'] })`
  
  return {
    bar: {
      chart: {
        parentHeightOffset: 0,
        stacked: true,
        type: 'bar',
        toolbar: { show: false },
      },
      tooltip: { enabled: false },
      plotOptions: {
        bar: {
          horizontal: false,
          columnWidth: '40%',
          borderRadius: 8,
          borderRadiusApplication: 'around',
          borderRadiusWhenStacked: 'all',
        },
      },
      colors: [
        'rgba(var(--v-theme-primary),1)',
        'rgba(var(--v-theme-warning),1)',
      ],
      dataLabels: { enabled: false },
      stroke: {
        curve: 'smooth',
        width: 6,
        lineCap: 'round',
        colors: [currentTheme.surface],
      },
      legend: {
        show: true,
        horizontalAlign: 'right',
        position: 'top',
        fontFamily: 'Public Sans',
        fontSize: '13px',
        markers: {
          height: 12,
          width: 12,
          radius: 12,
          offsetX: -3,
          offsetY: 2,
        },
        labels: { colors: legendColor },
        itemMargin: { horizontal: 5 },
      },
      grid: {
        show: false,
        padding: {
          bottom: -8,
          top: 20,
        },
      },
      xaxis: {
        categories: [
          'Jan',
          'Feb',
          'Mar',
          'Apr',
          'May',
          'Jun',
          'Jul',
          'Aug',
          'Sep',
        ],
        labels: {
          style: {
            fontSize: '13px',
            colors: labelColor,
            fontFamily: 'Public Sans',
          },
        },
        axisTicks: { show: false },
        axisBorder: { show: false },
      },
      yaxis: {
        labels: {
          offsetX: -16,
          style: {
            fontSize: '13px',
            colors: labelColor,
            fontFamily: 'Public Sans',
          },
        },
        min: -200,
        max: 300,
        tickAmount: 5,
      },
      responsive: [
        {
          breakpoint: 1700,
          options: { plotOptions: { bar: { columnWidth: '43%' } } },
        },
        {
          breakpoint: 1526,
          options: {
            plotOptions: {
              bar: {
                columnWidth: '52%',
                borderRadius: 8,
              },
            },
          },
        },
        {
          breakpoint: 1359,
          options: {
            plotOptions: {
              bar: {
                columnWidth: '60%',
                borderRadius: 8,
              },
            },
          },
        },
        {
          breakpoint: 1280,
          options: {
            plotOptions: {
              bar: {
                columnWidth: '40%',
                borderRadius: 10,
              },
            },
          },
        },
        {
          breakpoint: 1025,
          options: {
            plotOptions: {
              bar: {
                columnWidth: '40%',
                borderRadius: 8,
              },
            },
            chart: { height: 390 },
          },
        },
        {
          breakpoint: 991,
          options: {
            plotOptions: {
              bar: {
                columnWidth: '40%',
                borderRadius: 8,
              },
            },
          },
        },
        {
          breakpoint: 850,
          options: {
            plotOptions: {
              bar: {
                columnWidth: '50%',
                borderRadius: 8,
              },
            },
          },
        },
        {
          breakpoint: 776,
          options: {
            plotOptions: {
              bar: {
                columnWidth: '50%',
                borderRadius: 6,
              },
            },
          },
        },
        {
          breakpoint: 731,
          options: {
            plotOptions: {
              bar: {
                columnWidth: '70%',
                borderRadius: 8,
              },
            },
          },
        },
        {
          breakpoint: 599,
          options: {
            plotOptions: {
              bar: {
                columnWidth: '50%',
                borderRadius: 8,
              },
            },
          },
        },
        {
          breakpoint: 500,
          options: {
            plotOptions: {
              bar: {
                columnWidth: '55%',
                borderRadius: 6,
              },
            },
          },
        },
        {
          breakpoint: 449,
          options: {
            plotOptions: {
              bar: {
                columnWidth: '65%',
                borderRadius: 6,
              },
            },
            chart: { height: 360 },
            xaxis: { labels: { offsetY: -5 } },
          },
        },
        {
          breakpoint: 394,
          options: {
            plotOptions: {
              bar: {
                columnWidth: '80%',
                borderRadius: 6,
              },
            },
          },
        },
      ],
      states: {
        hover: { filter: { type: 'none' } },
        active: { filter: { type: 'none' } },
      },
    },
    line: {
      chart: {
        toolbar: { show: false },
        zoom: { enabled: false },
        type: 'line',
      },
      stroke: {
        curve: 'smooth',
        dashArray: [
          5,
          0,
        ],
        width: [
          1,
          2,
        ],
      },
      legend: { show: false },
      colors: [
        borderColor,
        currentTheme.primary,
      ],
      grid: {
        show: false,
        borderColor,
        padding: {
          top: -30,
          bottom: -15,
          left: 25,
        },
      },
      markers: { size: 0 },
      xaxis: {
        labels: { show: false },
        axisTicks: { show: false },
        axisBorder: { show: false },
      },
      yaxis: { show: false },
      tooltip: { enabled: false },
    },
  }
})

const fetchRevenueData = async () => {
  try {
    const response = await $api('/dashboard/revenue-report')
    
    if (response) {
      series.value = {
        bar: response.barSeries || [
          {
            name: t('Earning'),
            data: [0, 0, 0, 0, 0, 0, 0, 0, 0],
          },
          {
            name: t('Expense'),
            data: [0, 0, 0, 0, 0, 0, 0, 0, 0],
          },
        ],
        line: response.lineSeries || [
          {
            name: t('Last Month'),
            data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
          },
          {
            name: t('This Month'),
            data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
          },
        ],
      }
      totalRevenue.value = response.totalRevenue || '0'
      budget.value = response.budget || '0'
    }
  } catch (error) {
    console.error('Error fetching revenue data:', error)
  }
}

onMounted(() => {
  fetchRevenueData()
})
</script>

<template>
  <VCard class="revenue-report">
    <VRow no-gutters>
      <VCol
        cols="12"
        sm="12"
        lg="12"
        :class="$vuetify.display.smAndUp ? 'border-e' : 'border-b'"
      >
        <VCardText>
          <h6 class="text-h5 mb-sm-n8">
            {{ t('Revenue Report') }}
          </h6>

          <VueApexCharts
            :options="chartOptions.bar"
            :series="series.bar"
            height="300"
          />
        </VCardText>
      </VCol>
    </VRow>
  </VCard>
</template>

<style lang="scss">
.revenue-report {
  .apexcharts-legend {
    gap: 1rem;
  }

  @media (max-width: 599px) {
    .apexcharts-legend.apx-legend-position-top.apexcharts-align-right {
      justify-content: flex-start;
      padding: 0;
    }
  }
}
</style>
