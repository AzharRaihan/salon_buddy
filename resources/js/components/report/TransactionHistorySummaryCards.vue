<script setup>
import { useI18n } from 'vue-i18n'
import { useCompanyFormatters } from '@/composables/useCompanyFormatters'

const { t } = useI18n()
const { formatAmount } = useCompanyFormatters()

// Define props
const props = defineProps({
  summary: {
    type: Object,
    default: () => ({
      totalTransactions: 0,
      totalAmount: 0,
    }),
  },
})
</script>

<template>
  <VRow>
    <!-- Total Transactions -->
    <VCol cols="12" md="6">
      <VCard variant="outlined">
        <VCardText class="d-flex align-center justify-space-between">
          <div>
            <div class="text-h6">
              {{ t('Total Transactions') }}
            </div>
            <div class="text-h4 mt-2">
              {{ summary.totalTransactions }}
            </div>
          </div>
          <div>
            <VIcon
              icon="tabler-receipt"
              size="32"
            />
          </div>
        </VCardText>
      </VCard>
    </VCol>

    <!-- Total Amount -->
    <VCol cols="12" md="6">
      <VCard variant="outlined">
        <VCardText class="d-flex align-center justify-space-between">
          <div>
            <div class="text-h6">
              {{ t('Total Amount') }}
            </div>
            <div class="text-h4  mt-2">
              {{ formatAmount(summary.totalAmount) }}
            </div>
          </div>
          <div>
            <VIcon
              icon="tabler-currency-dollar"
              size="32"
            />
          </div>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>

<style lang="scss" scoped>
.opacity-50 {
  opacity: 0.5;
}
</style>

