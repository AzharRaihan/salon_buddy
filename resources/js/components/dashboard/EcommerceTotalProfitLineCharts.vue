<script setup>
import { useTheme } from 'vuetify'
import { hexToRgb } from '@layouts/utils'
import { ref, onMounted, computed } from 'vue'
import { $api } from '@/utils/api'

import { useI18n } from 'vue-i18n';

const { t } = useI18n({ useScope: 'global' })

const vuetifyTheme = useTheme()

const series = ref([{
  data: [0, 0, 0, 0, 0, 0],
}])

const currentProfit = ref('0')
const percentageChange = ref('0')

const chartOptions = computed(() => {
  const currentTheme = vuetifyTheme.current.value.colors
  const variableTheme = vuetifyTheme.current.value.variables
  
  return {
    chart: {
      height: 90,
      type: 'line',
      parentHeightOffset: 0,
      toolbar: { show: false },
    },
    grid: {
      borderColor: `rgba(${ hexToRgb(String(variableTheme['border-color'])) },${ variableTheme['border-opacity'] })`,
      strokeDashArray: 6,
      xaxis: { lines: { show: true } },
      yaxis: { lines: { show: false } },
      padding: {
        top: -18,
        left: -4,
        right: 7,
        bottom: -10,
      },
    },
    colors: [currentTheme.info],
    stroke: { width: 2 },
    tooltip: {
      enabled: false,
      shared: false,
      intersect: true,
      x: { show: false },
    },
    xaxis: {
      labels: { show: false },
      axisTicks: { show: false },
      axisBorder: { show: false },
    },
    yaxis: { labels: { show: false } },
    markers: {
      size: 3.5,
      fillColor: currentTheme.info,
      strokeColors: 'transparent',
      strokeWidth: 3.2,
      discrete: [{
        seriesIndex: 0,
        dataPointIndex: 5,
        fillColor: currentTheme.surface,
        strokeColor: currentTheme.info,
        size: 5,
        shape: 'circle',
      }],
      hover: { size: 5.5 },
    },
    responsive: [{
      breakpoint: 960,
      options: { chart: { height: 110 } },
    }],
  }
})

const fetchProfitData = async () => {
  try {
    const response = await $api('/dashboard/total-profit')
    
    if (response) {
      series.value = [{
        data: response.series || [0, 0, 0, 0, 0, 0],
      }]
      currentProfit.value = response.currentProfit || '0'
      percentageChange.value = response.percentageChange || '0'
    }
  } catch (error) {
    console.error('Error fetching profit data:', error)
  }
}

onMounted(() => {
  fetchProfitData()
})
</script>

<template>
  <VCard>
    <VCardItem class="pb-3">
      <VCardTitle>
        {{ t('Profit') }}
      </VCardTitle>
      <VCardSubtitle>
        {{ t('Last Month') }}
      </VCardSubtitle>
    </VCardItem>
    <VCardText>
      <VueApexCharts
        type="line"
        :options="chartOptions"
        :series="series"
        :height="200"
      />

      <div class="d-flex align-center justify-space-between gap-x-2 mt-3">
        <h4 class="text-h4 text-center font-weight-medium">
          {{ currentProfit }}
        </h4>
        <span class="text-sm text-success">
          +{{ percentageChange }}%
        </span>
      </div>
    </VCardText>
  </VCard>
</template>
