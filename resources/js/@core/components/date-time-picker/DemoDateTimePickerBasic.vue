<script setup>
import { useI18n } from 'vue-i18n'
const { t } = useI18n()

const date = ref('')
const label = ref('')
const props = defineProps({
  label: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: ''
  }
})

// Computed properties for i18n translations
const labelText = computed(() => props.label || '')
const placeholderText = computed(() => props.placeholder || t('Select date'))
</script>

<template>
  <AppDateTimePicker
    v-model="date"
    :label="labelText"
    :placeholder="placeholderText"
  />
</template>
