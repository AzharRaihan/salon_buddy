<script setup>
import { useI18n } from 'vue-i18n'
import { watch } from 'vue'
import { useCompanySettings } from '@/composables/useCompanySettings'
import BookingForm from './BookingForm.vue'

// Company Settings for default checkbox values
const { defaultEmailSelect, defaultSmsSelect, defaultWhatsappSelect } = useCompanySettings()

const sendSMS = ref(false)
const sendEmail = ref(false)
const sendWhatsapp = ref(false)

// Watch company settings and update checkbox defaults when they change
watch([defaultSmsSelect, defaultEmailSelect, defaultWhatsappSelect], ([sms, email, whatsapp]) => {
  sendSMS.value = sms
  sendEmail.value = email
  sendWhatsapp.value = whatsapp
}, { immediate: true })

const { t } = useI18n()

const props = defineProps({
    modelValue: {
        type: Boolean,
        required: true
    },
    title: {
        type: String,
        required: true
    },
    form: {
        type: Object,
        required: true
    },
    errors: {
        type: Object,
        default: () => ({})
    },
    customers: {
        type: Array,
        default: () => []
    },
    branches: {
        type: Array,
        default: () => []
    },
    servicePackages: {
        type: Array,
        default: () => []
    },
    serviceSellers: {
        type: Array,
        default: () => []
    },
    isCompleted: {
        type: Boolean,
        default: false
    },
    loading: {
        type: Boolean,
        default: false
    },
    showSubmitButton: {
        type: Boolean,
        default: true
    },
    submitButtonText: {
        type: String,
        default: 'Submit'
    },
})

const emit = defineEmits(['update:modelValue', 'submit', 'add-detail', 'remove-detail'])

const closeModal = () => {
    emit('update:modelValue', false)
}

const handleSubmit = () => {
    emit('submit', {
        send_sms: sendSMS.value,
        send_email: sendEmail.value,
        send_whatsapp: sendWhatsapp.value
    })
}

const handleAddDetail = () => {
    emit('add-detail')
}

const handleRemoveDetail = (index) => {
    emit('remove-detail', index)
}
</script>

<template>
    <VDialog :model-value="modelValue" @update:model-value="$emit('update:modelValue', $event)" class="booking-modal">
        <VCard class="modal-card modal-card-lg">
            <VCardTitle>{{ title }}</VCardTitle>
            <VCardText>
                <BookingForm
                    :form="form"
                    :errors="errors"
                    :customers="customers"
                    :branches="branches"
                    :service-packages="servicePackages"
                    :service-sellers="serviceSellers"
                    :is-completed="isCompleted"
                    @add-detail="handleAddDetail"
                    @remove-detail="handleRemoveDetail"
                />
            </VCardText>
            <VCardActions>
                <VSpacer />

                <!-- Checbox for Whatsapp message -->   
                <VCheckbox v-if="!isCompleted"
                    v-model="sendSMS"
                    label="Send SMS"
                    />
                <VCheckbox v-if="!isCompleted"
                    v-model="sendEmail"
                    label="Send Email"
                    />
                <VCheckbox v-if="!isCompleted"
                    v-model="sendWhatsapp"
                    label="Send Whatsapp Message"
                    />

                <VBtn 
                    v-if="showSubmitButton"
                    variant="tonal" 
                    color="primary" 
                    :loading="loading" 
                    :disabled="loading" 
                    @click="handleSubmit"
                >
                    <VIcon icon="tabler-checkbox" class="me-2" />
                    {{ $t(submitButtonText) }}
                </VBtn>
                <VBtn variant="tonal" color="error" @click="closeModal">
                    <VIcon icon="tabler-x" class="me-2" />
                    {{ $t('Close') }}
                </VBtn>
            </VCardActions>
        </VCard>
    </VDialog>
</template>

<style scoped>
.booking-modal {
    width: 1152px !important;
}
@media screen and (max-width: 1199.98px) {
    .booking-modal {
        width: 90% !important;
    }
}
@media screen and (max-width: 991.98px) {
    .booking-modal {
        width: 95% !important;
    }
}
</style> 