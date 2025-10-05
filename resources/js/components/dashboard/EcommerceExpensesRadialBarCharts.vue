<script setup>
import { useTheme } from 'vuetify'
import { hexToRgb } from '@layouts/utils'
import { ref, onMounted, computed } from 'vue'
import { $api } from '@/utils/api'

import { useI18n } from 'vue-i18n';

const { t } = useI18n({ useScope: 'global' })


const vuetifyTheme = useTheme()
const series = ref([78])

const currentExpenses = ref('0')
const differenceText = ref('$0 Expenses more than last month')

const chartOptions = computed(() => {
  const currentTheme = vuetifyTheme.current.value.colors
  const variableTheme = vuetifyTheme.current.value.variables
  
  return {
    chart: {
      sparkline: { enabled: true },
      parentHeightOffset: 0,
      type: 'radialBar',
    },
    colors: ['rgba(var(--v-theme-warning), 1)'],
    plotOptions: {
      radialBar: {
        offsetY: 0,
        startAngle: -90,
        endAngle: 90,
        hollow: { size: '65%' },
        track: {
          strokeWidth: '45%',
          background: 'rgba(var(--v-track-bg))',
        },
        dataLabels: {
          name: { show: false },
          value: {
            fontSize: '24px',
            color: `rgba(${ hexToRgb(currentTheme['on-background']) },${ variableTheme['high-emphasis-opacity'] })`,
            fontWeight: 600,
            offsetY: -5,
          },
        },
      },
    },
    grid: {
      show: false,
      padding: { bottom: 5 },
    },
    stroke: { lineCap: 'round' },
    labels: ['Progress'],
    responsive: [
      {
        breakpoint: 1442,
        options: {
          chart: { height: 200 },
          plotOptions: {
            radialBar: {
              dataLabels: { value: { fontSize: '24px' } },
              hollow: { size: '60%' },
            },
          },
        },
      },
      {
        breakpoint: 1370,
        options: { chart: { height: 120 } },
      },
      {
        breakpoint: 1280,
        options: {
          chart: { height: 200 },
          plotOptions: {
            radialBar: {
              dataLabels: { value: { fontSize: '18px' } },
              hollow: { size: '70%' },
            },
          },
        },
      },
      {
        breakpoint: 960,
        options: {
          chart: { height: 250 },
          plotOptions: {
            radialBar: {
              hollow: { size: '70%' },
              dataLabels: { value: { fontSize: '24px' } },
            },
          },
        },
      },
    ],
  }
})

const fetchExpenseData = async () => {
  try {
    const response = await $api('/dashboard/total-expenses')
    if (response) {
      series.value = [response.progressPercentage || 78]
      currentExpenses.value = response.currentExpenses || '0'
      differenceText.value = response.differenceText || '$0 Expenses more than last month'
    }
  } catch (error) {
    console.error('Error fetching expense data:', error)
  }
}

onMounted(() => {
  fetchExpenseData()
})
</script>

<template>
  <VCard>
    <VCardItem class="pb-3">
      <VCardTitle>
        {{ currentExpenses }}
      </VCardTitle>
      <VCardSubtitle>
        {{ t('Expenses') }}
      </VCardSubtitle>
    </VCardItem>
    <VCardText>
      <VueApexCharts
        :options="chartOptions"
        :series="series"
        type="radialBar"
        :height="200"
      />

      <div class="text-sm text-center clamp-text text-disabled mt-3">
        {{ differenceText }}
      </div>
    </VCardText>
  </VCard>
</template>
