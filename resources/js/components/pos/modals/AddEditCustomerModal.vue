<template>
  <div v-if="show" class="common-modal select-modal show add-edit-customer-modal" @mousedown.self="emit('close')">
    <div class="modal-content">
      <div class="modal-header">
        <h4>{{ mode === 'edit' ? t('Edit Customer') : t('Add Customer') }}</h4>
        <button class="close-modal" @click="emit('close')">
          <VIcon icon="tabler-x" />
        </button>
      </div>
      <div class="modal-body">
        <CustomerForm
          v-model="formData"
          :errors="errors"
          @validate="handleFieldValidate"
        />
      </div>
      <div class="modal-footer">
        <button class="btn btn-danger" @click="emit('close')">
            <VIcon icon="tabler-x" />
            {{ t('Cancel') }}
        </button>
        <button class="btn btn-primary" @click="handleSave">
            <VIcon icon="tabler-check" />
            {{ mode === 'edit' ? t('Save Changes') : t('Add Customer') }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue'
import CustomerForm from '../forms/CustomerForm.vue'
import { useWebsiteSettingsStore } from '@/stores/websiteSetting'
const websiteSettingsStore = useWebsiteSettingsStore()
const taxIsGst = computed(() => websiteSettingsStore.getTaxIsGst)
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps({
  show: Boolean,
  customer: {
    type: Object,
    default: null
  },
  mode: {
    type: String,
    default: 'add' // or 'edit'
  }
})
const emit = defineEmits(['close', 'saved'])

const formData = ref({
  name: '',
  phone: '',
  email: '',
  same_or_diff_state: null,
  gst_number: '',
})
const errors = ref({})

watch(
  [() => props.show, () => props.customer, () => props.mode],
  ([show, customer, mode]) => {
    if (show) {
      if (mode === 'edit' && customer) {
        formData.value = { ...customer }
      } else {
        formData.value = { name: '', phone: '', email: '', same_or_diff_state: null, gst_number: '' }
      }
      errors.value = {}
    }
  },
  { immediate: true }
)

const validate = () => {
  const errs = {}
  if (!formData.value.name) errs.name = t('Name is required')
  if (!formData.value.phone) errs.phone = t('Phone is required')
  if (taxIsGst.value === 'Yes') {
    if (!formData.value.same_or_diff_state) errs.same_or_diff_state = t('State is required')
    if (!formData.value.gst_number) errs.gst_number = t('GST is required')
  }
  if (!formData.value.email) {
    errs.email = t('Email is required')
  } else if (!/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(formData.value.email)) {
    errs.email = t('Email is invalid')
  }
  errors.value = errs
  return Object.keys(errs).length === 0
}

function handleSave() {
  if (!validate()) return
  emit('saved', { ...formData.value })
}

function handleFieldValidate(field) {
  // Validate a single field on blur
  validate()
}
</script>
