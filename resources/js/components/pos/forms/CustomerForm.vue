<template>
    <div class="customer-form">

        <!-- Customer Name -->
        <div class="form-group">
            <AppTextField
                v-model="formData.name"
                :label="t('Customer Name')" :required="true"
                type="text"
                :placeholder="t('Enter customer name')"
                :error-messages="customerNameError"
            />
        </div>

        <!-- Phone Number -->
        <div class="form-group">
            <AppTextField
                v-model="formData.phone"
                :label="t('Phone Number')" :required="true"
                type="text"
                :placeholder="t('Enter phone number')"
                :error-messages="phoneNumberError"
            />
        </div>

        <!-- Email -->
        <div class="form-group">
            <AppTextField
                v-model="formData.email"
                :label="t('Email')" :required="true"
                type="email"
                :placeholder="t('Enter email address')"
                :error-messages="emailError"
            />
        </div>

        <!-- Address -->
        <div class="form-group">
            <AppTextarea
                v-model="formData.address"
                :label="t('Address')"
                type="text"
                :placeholder="t('Enter address')"
                :error-messages="addressError"
            />
        </div>

        <!-- Same or Different State -->
        <div class="form-group" v-if="taxIsGst === 'Yes'">
            <AppAutocomplete
                v-model="formData.same_or_diff_state"
                :label="t('State Status')"
                :items="stateOptions"
                item-title="title"
                item-value="value"
                :required="taxIsGst === 'Yes' ? true : false"
                :placeholder="t('Select state status')"
                :error-messages="sameOrDiffStateError"
                clearable
                />
        </div>

        <!-- GSTIN -->
        <div class="form-group" v-if="taxIsGst === 'Yes'">
            <AppTextField
                v-model="formData.gst_number"
                :label="t('GST')"
                type="text"
                :required="taxIsGst === 'Yes' ? true : false"
                :placeholder="t('Enter GST')"
                :error-messages="gstError"
            />
        </div>
        
    </div>
</template>

<script setup>
import { computed, watch } from 'vue'
import { useWebsiteSettingsStore } from '@/stores/websiteSetting'
const websiteSettingsStore = useWebsiteSettingsStore()
const taxIsGst = computed(() => websiteSettingsStore.getTaxIsGst)
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps({
    modelValue: {
        type: Object,
        required: true
    },
    errors: {
        type: Object,
        default: () => ({})
    },
    loading: {
        type: Boolean,
        default: false
    }
})

const emit = defineEmits(['update:modelValue', 'validate'])

// Form data computed property
const formData = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value)
})

// State options for dropdown
const stateOptions = computed(() => [
    { title: 'Same', value: 'Same' },
    { title: 'Different', value: 'Different' }
])

const customerNameError = computed(() => {
    return props.errors.name || ''
})

const phoneNumberError = computed(() => {
    return props.errors.phone || ''
})

const emailError = computed(() => {
    return props.errors.email || ''
})

const addressError = computed(() => {
    return props.errors.address || ''
})

// Error for same_or_diff_state field
let sameOrDiffStateError = ''
let gstError = ''
if(taxIsGst.value === 'Yes') {
    sameOrDiffStateError = computed(() => {
        return props.errors.same_or_diff_state || ''
    })

    gstError = computed(() => {
        return props.errors.gst_number || ''
    })
}


watch(() => formData.value.same_or_diff_state, (val) => {
  console.log('State selection changed to:', val)
})

// Error handling helpers
const hasError = (field) => {
    return !!props.errors[field]
}

const getError = (field) => {
    return props.errors[field] || ''
}

// Validation helper
const validateField = (field) => {
    emit('validate', field)
}

// Watch for changes and emit validation
watch(formData, (newValue) => {
    emit('update:modelValue', newValue)
}, { deep: true })
</script>

<style scoped>
.form-group {
    margin-bottom: 1rem;
}
.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: #374151;
}
.form-control {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}
.form-control:focus {
    outline: none;
    border-color: #3078f6;
    box-shadow: 0 0 0 3px rgba(48, 120, 246, 0.1);
}
.form-control.is-invalid {
    border-color: #dc3545;
}
.invalid-feedback {
    display: block;
    width: 100%;
    margin-top: 0.25rem;
    font-size: 0.875rem;
    color: #dc3545;
}
.form-control:disabled {
    background-color: #f3f4f6;
    cursor: not-allowed;
}
</style>
