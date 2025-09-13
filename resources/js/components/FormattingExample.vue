<template>
  <div class="formatting-example">
    <VCard title="Formatting Examples">
      <VCardText>
        <VRow>
          <VCol cols="12" md="6">
            <h4>Date Formatting Examples</h4>
            <div class="mb-2">
              <strong>Raw Date:</strong> 2025-07-10
            </div>
            <div class="mb-2">
              <strong>Formatted (Y/m/d):</strong> {{ formatDate('2025-07-10', { date_format: 'Y/m/d' }) }}
            </div>
            <div class="mb-2">
              <strong>Formatted (m/d/Y):</strong> {{ formatDate('2025-07-10', { date_format: 'm/d/Y' }) }}
            </div>
            <div class="mb-2">
              <strong>Formatted (d/m/Y):</strong> {{ formatDate('2025-07-10', { date_format: 'd/m/Y' }) }}
            </div>
          </VCol>
          
          <VCol cols="12" md="6">
            <h4>Amount Formatting Examples</h4>
            <div class="mb-2">
              <strong>Raw Amount:</strong> 1000
            </div>
            <div class="mb-2">
              <strong>Prefix ($1,000.00):</strong> {{ formatAmount(1000, { currency: '$', currency_position: 'Before Amount', precision: 2, thousand_separator: ',', decimal_separator: '.' }) }}
            </div>
            <div class="mb-2">
              <strong>Suffix (1,000.00$):</strong> {{ formatAmount(1000, { currency: '$', currency_position: 'After Amount', precision: 2, thousand_separator: ',', decimal_separator: '.' }) }}
            </div>
            <div class="mb-2">
              <strong>3 Decimal (€1.000,000):</strong> {{ formatAmount(1000, { currency: '€', currency_position: 'Before Amount', precision: 3, thousand_separator: '.', decimal_separator: ',' }) }}
            </div>
          </VCol>
        </VRow>
      </VCardText>
    </VCard>
  </div>
</template>

<script setup>
import { useCompanyFormatters } from '@/composables/useCompanyFormatters'

const { formatDate, formatAmount } = useCompanyFormatters()
</script>

<style scoped>
.formatting-example {
  padding: 1rem;
}
</style> 