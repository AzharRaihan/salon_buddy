<script setup>
import AppDateTimePicker from '@core/components/date-time-picker/DemoDateTimePickerHumanFriendly.vue'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

// Define props
const props = defineProps({
  dateFrom: {
    type: String,
    default: null,
  },
  dateTo: {
    type: String,
    default: null,
  },
  paymentMethodId: {
    type: [String, Number],
    default: null,
  },
  branchId: {
    type: [String, Number],
    default: null,
  },
  branches: {
    type: Array,
    default: () => [],
  },
  paymentMethods: {
    type: Array,
    default: () => [],
  },
})

// Define emits
const emit = defineEmits(['update:dateFrom', 'update:dateTo', 'update:paymentMethodId', 'update:branchId'])

// Local computed for v-model binding
const localDateFrom = computed({
  get: () => props.dateFrom,
  set: (value) => emit('update:dateFrom', value),
})

const localDateTo = computed({
  get: () => props.dateTo,
  set: (value) => emit('update:dateTo', value),
})

const localPaymentMethodId = computed({
  get: () => props.paymentMethodId,
  set: (value) => emit('update:paymentMethodId', value),
})

const localBranchId = computed({
  get: () => props.branchId,
  set: (value) => emit('update:branchId', value),
})

// Transform branches for autocomplete
const branchOptions = computed(() => {
  return props.branches.map(branch => ({
    title: branch.name,
    value: branch.id,
  }))
})

// Transform payment methods for autocomplete
const paymentMethodOptions = computed(() => {
  return props.paymentMethods.map(method => ({
    title: method.name,
    value: method.id,
  }))
})
</script>

<template>
  <VRow>
    <!-- Payment Method (Required) -->
    <VCol cols="12" md="6" lg="3">
      <AppAutocomplete
        v-model="localPaymentMethodId"
        :items="paymentMethodOptions"
        :label="t('Payment Account')"
        :placeholder="t('Select Payment Account')"
        clearable
        :required="true"
      />
    </VCol>

    <!-- Branch Filter -->
    <VCol cols="12" md="6" lg="3">
      <AppAutocomplete
        v-model="localBranchId"
        :items="branchOptions"
        :label="t('Branch')"
        :placeholder="t('Select Branch')"
        clearable
      />
    </VCol>

    <!-- Date From -->
    <VCol cols="12" md="6" lg="3">
      <AppDateTimePicker
        v-model="localDateFrom"
        :label="t('Date From')"
        :placeholder="t('Select Date From')"
        :config="{
          enableTime: false,
          dateFormat: 'Y-m-d',
          maxDate: new Date(),
        }"
      />
    </VCol>

    <!-- Date To -->
    <VCol cols="12" md="6" lg="3">
      <AppDateTimePicker
        v-model="localDateTo"
        :label="t('Date To')"
        :placeholder="t('Select Date To')"
        :config="{
          enableTime: false,
          dateFormat: 'Y-m-d',
          maxDate: new Date(),
        }"
      />
    </VCol>
  </VRow>
</template>

<style lang="scss" scoped>
.text-error {
  color: rgb(var(--v-theme-error));
}
</style>

