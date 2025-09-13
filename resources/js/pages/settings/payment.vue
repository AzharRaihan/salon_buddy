<script setup>
import { onMounted, ref } from 'vue'
import { toast } from 'vue3-toastify'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const form = ref({
    paypal_enabled: false,
    paypal_mode: 'sandbox',
    paypal_client_id: '',
    paypal_client_secret: '',
    stripe_enabled: false,
    stripe_mode: 'sandbox',
    stripe_key: '',
    stripe_secret: '',
    razorpay_enabled: false,
    razorpay_key: '',
    razorpay_secret: '',
    paytm_enabled: false,
    paytm_key: '',
    paytm_secret: '',
    paystack_enabled: false,
    paystack_key: ''
})

const errors = ref({})
const loadings = ref(false)

const validateForm = () => {
    errors.value = {}

    if (form.value.paypal_enabled) {
        if (!form.value.paypal_client_id) errors.value.paypal_client_id = t('Client ID is required')
        if (!form.value.paypal_client_secret) errors.value.paypal_client_secret = t('Client Secret is required')
    }

    if (form.value.stripe_enabled) {
        if (!form.value.stripe_key) errors.value.stripe_key = t('API Key is required')
        if (!form.value.stripe_secret) errors.value.stripe_secret = t('API Secret is required')
    }

    return Object.keys(errors.value).length == 0
}

const resetForm = () => {
    form.value = {
        paypal_enabled: false,
        paypal_mode: 'sandbox',
        paypal_client_id: '',
        paypal_client_secret: '',
        stripe_enabled: false,
        stripe_mode: 'sandbox',
        stripe_key: '',
        stripe_secret: '',
        razorpay_enabled: false,
        razorpay_key: '',
        razorpay_secret: '',
        paytm_enabled: false,
        paytm_key: '',
        paytm_secret: '',
        paystack_enabled: false,
        paystack_key: ''
    }
}

const getPaymentSettings = async () => {
    try {
        const res = await $api('/payment-settings', {
            method: 'GET'
        })

        if (res.success == true) {
            form.value = res.data
            form.value.paypal_enabled = res.data.paypal_enabled == 1
            form.value.stripe_enabled = res.data.stripe_enabled == 1
            form.value.razorpay_enabled = res.data.razorpay_enabled == 1
            form.value.paytm_enabled = res.data.paytm_enabled == 1
            form.value.paystack_enabled = res.data.paystack_enabled == 1
        }
    } catch (err) {
        console.error(err)
        toast(t('Failed to fetch payment settings'), {
            type: 'error'
        })
    }
}

const updatePaymentSettings = async () => {
    loadings.value = true
    if (!validateForm()) {
        loadings.value = false
        return
    }

    try {
        const res = await $api('/payment-settings', {
            method: 'POST',
            body: form.value,
        })

        if (res.success == true) {
            toast(res.message, {
                type: 'success'
            })
            loadings.value = false
            getPaymentSettings()
        }
    } catch (err) {
        console.error(err)
        toast(err.response.data.message, {
            type: 'error'
        })
        loadings.value = false
    }
}

onMounted(() => {
    getPaymentSettings()
})
</script>

<template>
    <VRow>
        <VCol cols="12">
            <VCard :title="t('Payment Settings')">
                <VCardText>
                    <VForm @submit.prevent="updatePaymentSettings">
                        <VRow>
                            <!-- PayPal Section -->
                            <VCol cols="12">
                                <VRow>
                                    <VCol cols="12">
                                        <VCheckbox v-model="form.paypal_enabled" :label="t('Paypal')" />
                                    </VCol>

                                    <VCol v-if="form.paypal_enabled" cols="12" class="pt-0">
                                        <VRow>
                                            <VCol cols="12" class="d-flex align-center">
                                                <div class="me-4">{{ t('Type') }}:</div>
                                                <VRadioGroup v-model="form.paypal_mode" inline>
                                                    <VRadio :label="t('Sandbox')" value="sandbox" />
                                                    <VRadio :label="t('Live')" value="live" />
                                                </VRadioGroup>
                                            </VCol>

                                            <VCol cols="12" md="6">
                                                <AppTextField v-model="form.paypal_client_id" :label="t('Client ID')"
                                                    :error-messages="errors.paypal_client_id"
                                                    :placeholder="t('Enter paypal client id')" />
                                            </VCol>

                                            <VCol cols="12" md="6">
                                                <AppTextField v-model="form.paypal_client_secret" :label="t('Client Secret')"
                                                    :error-messages="errors.paypal_client_secret"
                                                    :placeholder="t('Enter paypal client secret')" />
                                            </VCol>
                                        </VRow>
                                    </VCol>
                                </VRow>
                            </VCol>

                            <!-- Stripe Section -->
                            <VCol cols="12">
                                <VRow>
                                    <VCol cols="12">
                                        <VCheckbox v-model="form.stripe_enabled" :label="t('Stripe')" />
                                    </VCol>

                                    <VCol v-if="form.stripe_enabled" cols="12" class="pt-0">
                                        <VRow>
                                            <VCol cols="12" class="d-flex align-center">
                                                <div class="me-4">{{ t('Type') }}:</div>
                                                <VRadioGroup v-model="form.stripe_mode" inline>
                                                    <VRadio :label="t('Sandbox')" value="sandbox" />
                                                    <VRadio :label="t('Live')" value="live" />
                                                </VRadioGroup>
                                            </VCol>

                                            <VCol cols="12" md="6">
                                                <AppTextField v-model="form.stripe_key" :label="t('API Key')"
                                                    :error-messages="errors.stripe_key"
                                                    :placeholder="t('Enter stripe api key')" />
                                            </VCol>

                                            <VCol cols="12" md="6">
                                                <AppTextField v-model="form.stripe_secret" :label="t('API Secret')"
                                                    :error-messages="errors.stripe_secret"
                                                    :placeholder="t('Enter stripe api secret')" />
                                            </VCol>
                                        </VRow>
                                    </VCol>
                                </VRow>
                            </VCol>

                            <!-- Razorpay Section -->
                            <VCol cols="12">
                                <VRow>
                                    <VCol cols="12">
                                        <VCheckbox v-model="form.razorpay_enabled" :label="t('Razorpay')" />
                                    </VCol>

                                    <VCol v-if="form.razorpay_enabled" cols="12" class="pt-0">
                                        <VRow>
                                            <VCol cols="12" md="6">
                                                <AppTextField v-model="form.razorpay_key" :label="t('API Key')"
                                                    :error-messages="errors.razorpay_key"
                                                    :placeholder="t('Enter razorpay api key')" />
                                            </VCol>

                                            <VCol cols="12" md="6">
                                                <AppTextField v-model="form.razorpay_secret" :label="t('API Secret')"
                                                    :error-messages="errors.razorpay_secret"
                                                    :placeholder="t('Enter razorpay api secret')" />
                                            </VCol>
                                        </VRow>
                                    </VCol>
                                </VRow>
                            </VCol>

                            <!-- Paytm Section -->
                            <VCol cols="12">
                                <VRow>
                                    <VCol cols="12">
                                        <VCheckbox v-model="form.paytm_enabled" :label="t('Paytm')" />
                                    </VCol>
                                    <VCol v-if="form.paytm_enabled" cols="12" class="pt-0">
                                        <VRow>
                                            <VCol cols="12" md="6">
                                                <AppTextField v-model="form.paytm_key" :label="t('API Key')"
                                                    :error-messages="errors.paytm_key"
                                                    :placeholder="t('Enter paytm api key')" />
                                            </VCol>

                                            <VCol cols="12" md="6">
                                                <AppTextField v-model="form.paytm_secret" :label="t('API Secret')"
                                                    :error-messages="errors.paytm_secret"
                                                    :placeholder="t('Enter paytm api secret')" />
                                            </VCol>
                                        </VRow>
                                    </VCol>
                                </VRow>
                            </VCol>

                            <!-- Paystack Section -->
                            <VCol cols="12">
                                <VRow>
                                    <VCol cols="12">
                                        <VCheckbox v-model="form.paystack_enabled" :label="t('Paystack')" />
                                    </VCol>
                                    <VCol v-if="form.paystack_enabled" cols="12" class="pt-0">
                                        <VRow>
                                            <VCol cols="12" md="6">
                                                <AppTextField v-model="form.paystack_key" :label="t('API Key')"
                                                    :error-messages="errors.paystack_key"
                                                    :placeholder="t('Enter paystack api key')" />
                                            </VCol>
                                        </VRow>
                                    </VCol>
                                </VRow>
                            </VCol>


                            <!-- Form Actions -->
                            <VCol cols="12" class="d-flex flex-wrap gap-4">
                                <VBtn type="submit" color="primary" :loading="loadings" :disabled="loadings">
                                    <VIcon start icon="tabler-checkbox" />
                                    {{ t('Save Changes') }}
                                </VBtn>
                                <VBtn color="error" variant="tonal" @click="resetForm">
                                    <VIcon start icon="tabler-circle-minus" />
                                    {{ t('Reset') }}
                                </VBtn>
                            </VCol>
                        </VRow>
                    </VForm>
                </VCardText>
            </VCard>
        </VCol>
    </VRow>
</template>
