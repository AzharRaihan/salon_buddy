<script setup>
import { onMounted, ref } from 'vue'
import { toast } from 'vue3-toastify'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

// Email Marketing Form
const emailForm = ref({
    campaign_type: 'birthday',
    subject: '',
    message: ''
})

// SMS Marketing Form
const smsForm = ref({
    campaign_type: 'birthday',
    message: ''
})

// WhatsApp Marketing Form
const whatsappForm = ref({
    campaign_type: 'birthday',
    message: ''
})

const emailErrors = ref({})
const smsErrors = ref({})
const whatsappErrors = ref({})
const emailLoading = ref(false)
const smsLoading = ref(false)
const whatsappLoading = ref(false)

// Statistics
const stats = ref({
    birthdayCustomers: 0,
    anniversaryCustomers: 0,
    whatsappCustomers: 0
})

// Fetch marketing statistics
const getMarketingStats = async () => {
    try {
        const res = await $api('/marketing-stats', {
            method: 'GET'
        })

        if (res.success == true) {
            stats.value = res.data
        }
    } catch (err) {
        console.error(err)
        toast(t('Failed to fetch marketing statistics'), {
            type: 'error'
        })
    }
}

// Validate Email Form
const validateEmailForm = () => {
    emailErrors.value = {}

    if (!emailForm.value.campaign_type) emailErrors.value.campaign_type = t('Campaign type is required')
    if (!emailForm.value.subject) emailErrors.value.subject = t('Subject is required')
    if (!emailForm.value.message) emailErrors.value.message = t('Message is required')

    return Object.keys(emailErrors.value).length == 0
}

// Validate SMS Form
const validateSmsForm = () => {
    smsErrors.value = {}

    if (!smsForm.value.campaign_type) smsErrors.value.campaign_type = t('Campaign type is required')
    if (!smsForm.value.message) smsErrors.value.message = t('Message is required')

    return Object.keys(smsErrors.value).length == 0
}

// Validate WhatsApp Form
const validateWhatsappForm = () => {
    whatsappErrors.value = {}

    if (!whatsappForm.value.campaign_type) whatsappErrors.value.campaign_type = t('Campaign type is required')
    if (!whatsappForm.value.message) whatsappErrors.value.message = t('Message is required')

    return Object.keys(whatsappErrors.value).length == 0
}

// Send Email Campaign
const sendEmailCampaign = async () => {
    emailLoading.value = true
    if (!validateEmailForm()) {
        emailLoading.value = false
        return
    }

    try {
        const res = await $api('/send-marketing-email', {
            method: 'POST',
            body: emailForm.value,
            headers: {
                'Accept': 'application/json',
            },
            onResponseError({ response }) {
                toast(response._data.message, {
                    type: 'error',
                })
                emailLoading.value = false
                return Promise.reject(response._data)
            },
        })
        
        const { status, message } = res
        if (status == 'error') {
            toast(message, {
                type: 'error',
            })
            emailLoading.value = false
            return
        }
        
        toast(message || t('Email campaign sent successfully'), {
            type: "success",
        })
        emailLoading.value = false
        getMarketingStats()
    } catch (err) {
        if (err.errors) {
            for (const [field, messages] of Object.entries(err.errors)) {
                messages.forEach(msg => {
                    toast(msg, { type: 'error' })
                })
            }
        } else {
            toast(err.message || t('Failed to send email campaign'), {
                type: 'error',
            })
        }
        emailLoading.value = false
    }
}

// Send SMS Campaign
const sendSmsCampaign = async () => {
    smsLoading.value = true
    if (!validateSmsForm()) {
        smsLoading.value = false
        return
    }

    try {
        const res = await $api('/send-marketing-sms', {
            method: 'POST',
            body: smsForm.value,
            headers: {
                'Accept': 'application/json',
            },
            onResponseError({ response }) {
                toast(response._data.message, {
                    type: 'error',
                })
                smsLoading.value = false
                return Promise.reject(response._data)
            },
        })
        
        const { status, message } = res
        if (status == 'error') {
            toast(message, {
                type: 'error',
            })
            smsLoading.value = false
            return
        }
        
        toast(message || t('SMS campaign sent successfully'), {
            type: "success",
        })
        smsLoading.value = false
        getMarketingStats()
    } catch (err) {
        if (err.errors) {
            for (const [field, messages] of Object.entries(err.errors)) {
                messages.forEach(msg => {
                    toast(msg, { type: 'error' })
                })
            }
        } else {
            toast(err.message || t('Failed to send SMS campaign'), {
                type: 'error',
            })
        }
        smsLoading.value = false
    }
}


// Send WhatsApp Campaign
const sendWhatsappCampaign = async () => {
    whatsappLoading.value = true
    if (!validateWhatsappForm()) {
        whatsappLoading.value = false
        return
    }

    try {
        const res = await $api('/send-marketing-whatsapp', {
            method: 'POST',
            body: whatsappForm.value,
            headers: {
                'Accept': 'application/json',
            },
            onResponseError({ response }) {
                toast(response._data.message, {
                    type: 'error',
                })
                smsLoading.value = false
                return Promise.reject(response._data)
            },
        })
        
        const { status, message } = res
        if (status == 'error') {
            toast(message, {
                type: 'error',
            })
            smsLoading.value = false
            return
        }
        
        toast(message || t('SMS campaign sent successfully'), {
            type: "success",
        })
        smsLoading.value = false
        getMarketingStats()
    } catch (err) {
        if (err.errors) {
            for (const [field, messages] of Object.entries(err.errors)) {
                messages.forEach(msg => {
                    toast(msg, { type: 'error' })
                })
            }
        } else {
            toast(err.message || t('Failed to send SMS campaign'), {
                type: 'error',
            })
        }
        smsLoading.value = false
    }
}

// Set default messages when campaign type changes
const updateEmailMessage = () => {
    if (emailForm.value.campaign_type == 'birthday') {
        emailForm.value.subject = ''
        emailForm.value.message = t('Happy Birthday! We wish you a wonderful day filled with joy and happiness. As a token of our appreciation, enjoy a special discount on your next visit!')
    } else {
        emailForm.value.subject = ''
        emailForm.value.message = t('Happy Anniversary! Thank you for being a valued customer. Celebrate your special day with us and enjoy exclusive anniversary offers!')
    }
}

const updateSmsMessage = () => {
    if (smsForm.value.campaign_type == 'birthday') {
        smsForm.value.message = t('Happy Birthday! Wishing you a wonderful day. Get special offers on your next visit!')
    } else {
        smsForm.value.message = t('Happy Anniversary! Thank you for being with us. Enjoy exclusive anniversary offers!')
    }
}

const updateWhatsappMessage = () => {
    if (whatsappForm.value.campaign_type == 'birthday') {
        whatsappForm.value.message = t('Happy Birthday! Wishing you a wonderful day. Get special offers on your next visit!')
    } else {
        whatsappForm.value.message = t('Happy Anniversary! Thank you for being with us. Enjoy exclusive anniversary offers!')
    }
}






onMounted(() => {
    getMarketingStats()
    updateEmailMessage()
    updateSmsMessage()
})
</script>

<template>
    <VRow>
        <!-- Statistics Cards -->
        <VCol cols="12" md="6">
            <VCard>
                <VCardText>
                    <div class="d-flex align-center">
                        <VAvatar
                            color="primary"
                            variant="tonal"
                            size="40"
                            class="me-3"
                        >
                            <VIcon icon="tabler-cake" />
                        </VAvatar>
                        <div>
                            <h3 class="text-h3 mb-1">{{ stats.birthdayCustomers }}</h3>
                            <p class="text-sm mb-0">{{ t('Customers with Birthday Today') }}</p>
                        </div>
                    </div>
                </VCardText>
            </VCard>
        </VCol>

        <VCol cols="12" md="6">
            <VCard>
                <VCardText>
                    <div class="d-flex align-center">
                        <VAvatar
                            color="success"
                            variant="tonal"
                            size="40"
                            class="me-3"
                        >
                            <VIcon icon="tabler-heart" />
                        </VAvatar>
                        <div>
                            <h3 class="text-h3 mb-1">{{ stats.anniversaryCustomers }}</h3>
                            <p class="text-sm mb-0">{{ t('Customers with Anniversary Today') }}</p>
                        </div>
                    </div>
                </VCardText>
            </VCard>
        </VCol>

        <!-- Email Marketing Section -->
        <VCol cols="12" lg="6">
            <VCard :title="t('Email Marketing Campaign')">
                <VCardText>
                    <VForm @submit.prevent="sendEmailCampaign">
                        <VRow>
                            <!-- Campaign Type -->
                            <VCol cols="12">
                                <AppAutocomplete
                                    v-model="emailForm.campaign_type"
                                    :label="t('Campaign Type')"
                                    :items="[
                                        { title: t('Birthday Wishes'), value: 'birthday' },
                                        { title: t('Anniversary Wishes'), value: 'anniversary' }
                                    ]"
                                    :error-messages="emailErrors.campaign_type"
                                    :required="true"
                                    :placeholder="t('Select campaign type')"
                                    @update:model-value="updateEmailMessage"
                                />
                            </VCol>

                            <!-- Subject -->
                            <VCol cols="12">
                                <AppTextField
                                    v-model="emailForm.subject"
                                    :label="t('Email Subject')"
                                    :placeholder="t('Enter email subject')"
                                    :error-messages="emailErrors.subject"
                                    :required="true"
                                />
                            </VCol>

                            <!-- Message -->
                            <VCol cols="12">
                                <AppTextarea
                                    v-model="emailForm.message"
                                    :label="t('Email Message')"
                                    :placeholder="t('Enter email message')"
                                    :error-messages="emailErrors.message"
                                    rows="6"
                                    :required="true"
                                />
                            </VCol>

                            <!-- Info Alert -->
                            <VCol cols="12">
                                <VAlert
                                    type="info"
                                    variant="tonal"
                                    :title="t('Campaign Information')"
                                >
                                    <template #text>
                                        <p v-if="emailForm.campaign_type == 'birthday'">
                                            {{ t('This email will be sent to all customers who have their birthday today.') }}
                                        </p>
                                        <p v-else>
                                            {{ t('This email will be sent to all customers who have their anniversary today.') }}
                                        </p>
                                        <p class="mb-0">
                                            <strong>{{ t('Total Recipients:') }}</strong> 
                                            {{ emailForm.campaign_type == 'birthday' ? stats.birthdayCustomers : stats.anniversaryCustomers }}
                                        </p>
                                    </template>
                                </VAlert>
                            </VCol>

                            <!-- Action Button -->
                            <VCol cols="12">
                                <VBtn
                                    type="submit"
                                    color="primary"
                                    :loading="emailLoading"
                                    :disabled="emailLoading || (emailForm.campaign_type == 'birthday' ? stats.birthdayCustomers : stats.anniversaryCustomers) == 0"
                                    block
                                >
                                    <VIcon start icon="tabler-mail-forward" />
                                    {{ t('Send Email Campaign') }}
                                </VBtn>
                            </VCol>
                        </VRow>
                    </VForm>
                </VCardText>
            </VCard>
        </VCol>

        <!-- SMS Marketing Section -->
        <VCol cols="12" lg="6">
            <VCard :title="t('SMS Marketing Campaign')">
                <VCardText>
                    <VForm @submit.prevent="sendSmsCampaign">
                        <VRow>
                            <!-- Campaign Type -->
                            <VCol cols="12">
                                <AppAutocomplete
                                    v-model="smsForm.campaign_type"
                                    :label="t('Campaign Type')"
                                    :items="[
                                        { title: t('Birthday Wishes'), value: 'birthday' },
                                        { title: t('Anniversary Wishes'), value: 'anniversary' }
                                    ]"
                                    :error-messages="smsErrors.campaign_type"
                                    :required="true"
                                    :placeholder="t('Select campaign type')"
                                    @update:model-value="updateSmsMessage"
                                />
                            </VCol>

                            <!-- Message -->
                            <VCol cols="12">
                                <AppTextarea
                                    v-model="smsForm.message"
                                    :label="t('SMS Message')"
                                    :placeholder="t('Enter SMS message')"
                                    :error-messages="smsErrors.message"
                                    rows="6"
                                    :required="true"
                                />
                                <small class="text-medium-emphasis">
                                    {{ t('Character count:') }} {{ smsForm.message.length }} / 160
                                </small>
                            </VCol>

                            <!-- Info Alert -->
                            <VCol cols="12">
                                <VAlert
                                    type="info"
                                    variant="tonal"
                                    :title="t('Campaign Information')"
                                >
                                    <template #text>
                                        <p v-if="smsForm.campaign_type == 'birthday'">
                                            {{ t('This SMS will be sent to all customers who have their birthday today.') }}
                                        </p>
                                        <p v-else>
                                            {{ t('This SMS will be sent to all customers who have their anniversary today.') }}
                                        </p>
                                        <p class="mb-0">
                                            <strong>{{ t('Total Recipients:') }}</strong> 
                                            {{ smsForm.campaign_type == 'birthday' ? stats.birthdayCustomers : stats.anniversaryCustomers }}
                                        </p>
                                    </template>
                                </VAlert>
                            </VCol>

                            <!-- Action Button -->
                            <VCol cols="12">
                                <VBtn
                                    type="submit"
                                    color="primary"
                                    :loading="smsLoading"
                                    :disabled="smsLoading || (smsForm.campaign_type == 'birthday' ? stats.birthdayCustomers : stats.anniversaryCustomers) == 0"
                                    block
                                >
                                    <VIcon start icon="tabler-message-forward" />
                                    {{ t('Send SMS Campaign') }}
                                </VBtn>
                            </VCol>
                        </VRow>
                    </VForm>
                </VCardText>
            </VCard>
        </VCol>

        <!-- WhatsApp Marketing Section -->
        <VCol cols="12" lg="6">
            <VCard :title="t('WhatsApp Marketing Campaign')">
                <VCardText>
                    <VForm @submit.prevent="sendWhatsappCampaign">
                        <VRow>
                            <!-- Campaign Type -->
                            <VCol cols="12">
                                <AppAutocomplete
                                    v-model="whatsappForm.campaign_type"
                                    :label="t('Campaign Type')"
                                    :items="[
                                        { title: t('Birthday Wishes'), value: 'birthday' },
                                        { title: t('Anniversary Wishes'), value: 'anniversary' }
                                    ]"
                                    :error-messages="whatsappErrors.campaign_type"
                                    :required="true"
                                    :placeholder="t('Select campaign type')"
                                    @update:model-value="updateSmsMessage"
                                />
                            </VCol>

                            <!-- Message -->
                            <VCol cols="12">
                                <AppTextarea
                                    v-model="whatsappForm.message"
                                    :label="t('SMS Message')"
                                    :placeholder="t('Enter SMS message')"
                                    :error-messages="whatsappErrors.message"
                                    rows="6"
                                    :required="true"
                                />
                                <small class="text-medium-emphasis">
                                    {{ t('Character count:') }} {{ whatsappForm.message.length }} / 160
                                </small>
                            </VCol>

                            <!-- Info Alert -->
                            <VCol cols="12">
                                <VAlert
                                    type="info"
                                    variant="tonal"
                                    :title="t('Campaign Information')"
                                >
                                    <template #text>
                                        <p v-if="whatsappForm.campaign_type == 'birthday'">
                                            {{ t('This WhatsApp will be sent to all customers who have their birthday today.') }}
                                        </p>
                                        <p v-else>
                                            {{ t('This WhatsApp will be sent to all customers who have their anniversary today.') }}
                                        </p>
                                        <p class="mb-0">
                                            <strong>{{ t('Total Recipients:') }}</strong> 
                                            {{ smsForm.campaign_type == 'birthday' ? stats.birthdayCustomers : stats.anniversaryCustomers }}
                                        </p>
                                    </template>
                                </VAlert>
                            </VCol>

                            <!-- Action Button -->
                            <VCol cols="12">
                                <VBtn
                                    type="submit"
                                    color="primary"
                                    :loading="smsLoading"
                                    :disabled="smsLoading || (whatsappForm.campaign_type == 'birthday' ? stats.birthdayCustomers : stats.anniversaryCustomers) == 0"
                                    block
                                >
                                    <VIcon start icon="tabler-message-forward" />
                                    {{ t('Send WhatsApp Campaign') }}
                                </VBtn>
                            </VCol>
                        </VRow>
                    </VForm>
                </VCardText>
            </VCard>
        </VCol>
    </VRow>
</template>
