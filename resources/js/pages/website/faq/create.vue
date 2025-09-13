<script setup>
import { useRouter } from 'vue-router';
import { toast } from 'vue3-toastify';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n()
const router = useRouter()
const loadings = ref(false)

const form = ref({
    title: '',
    description: '',
    status: 'Enabled'
})

const titleError = ref('')
const descriptionError = ref('')
const statusError = ref('')

const validateTitle = (title) => {
    if (!title) {
        titleError.value = t('Title is required')
        return false
    }
    titleError.value = ''
    return true
}

const validateDescription = (description) => {
    if (!description) {
        descriptionError.value = t('FAQ Answer is required')
        return false
    }
    descriptionError.value = ''
    return true
}

const validateStatus = (status) => {
    if (!status) {
        statusError.value = t('Status is required')
        return false
    }
    statusError.value = ''
    return true
}

const validateForm = () => {
    // Validate all fields at once
    const isTitleValid = validateTitle(form.value.title)
    const isDescriptionValid = validateDescription(form.value.description)
    const isStatusValid = validateStatus(form.value.status)

    return isTitleValid && isDescriptionValid && isStatusValid
}

const resetForm = () => {
    form.value = {
        title: '',
        description: '',
        status: 'Enabled'
    }
    titleError.value = ''
    descriptionError.value = ''
    statusError.value = ''
}

const createFaq = async () => {
    loadings.value = true
    if (!validateForm()) {
        loadings.value = false
        return
    }

    try {
        const res = await $api('/faqs', {
            method: 'POST',
            body: form.value,
            onResponseError({ response }) {
                toast(response._data.message, {
                    type: 'error',
                })
                loadings.value = false
                return Promise.reject(response._data)
            },
        })

        const { status, message } = res

        if (status == 'error') {
            toast(message, {
                type: 'error',
            })
            loadings.value = false
            return
        }

        toast(message, {
            type: "success",
        })
        loadings.value = false
        router.push({ name: 'website-faq' })
    }
    catch (err) {
        if (err.errors) {
            // Show each validation error as a toast
            for (const [field, messages] of Object.entries(err.errors)) {
                messages.forEach(msg => {
                    toast(msg, { type: 'error' })
                })
            }
        } else {
            // Show general error if no field-specific errors
            toast(message, {
                type: 'error',
            })
        }
        loadings.value = false
        return
    }
}
</script>

<template>
    <VRow>
        <VCol cols="12">
            <VCard :title="t('Create FAQ')">
                <VCardText>
                    <VForm class="mt-3" @submit.prevent="createFaq">
                        <VRow>
                            <!-- Name -->
                            <VCol cols="12" md="6" lg="4">
                                <AppTextField v-model="form.title" :label="t('FAQ Question') + '*'" type="text" :placeholder="t('Enter FAQ Question')"
                                    :error-messages="titleError" @input="validateTitle($event.target.value)" />
                            </VCol>

                            <!-- Description -->
                            <VCol cols="12" md="6" lg="4">
                                <AppTextarea v-model="form.description" :label="t('FAQ Answer') + '*'" type="text"
                                    :placeholder="t('Enter FAQ Answer')" :error-messages="descriptionError" 
                                    @input="validateDescription($event.target.value)" />
                            </VCol>

                            <!-- Status -->
                            <VCol cols="12" md="6" lg="4">
                                <AppAutocomplete
                                    v-model="form.status"
                                    :label="t('FAQ Status') + '*'"
                                    :items="[
                                        { title: t('Enabled'), value: 'Enabled' },
                                        { title: t('Disabled'), value: 'Disabled' }
                                    ]"
                                    :placeholder="t('Select FAQ Status')"
                                    :error-messages="statusError"
                                    @update:modelValue="validateStatus"
                                    clearable
                                />
                            </VCol>

                            <!-- Form Actions -->
                            <VCol cols="12" class="d-flex flex-wrap gap-4">
                                <VBtn type="submit" :loading="loadings" :disabled="loadings">
                                    <VIcon start icon="tabler-checkbox" />
                                    {{ t('Submit') }}
                                </VBtn>
                                <VBtn color="primary" variant="tonal" type="reset" @click.prevent="router.push({ name: 'website-faq' })">
                                    <VIcon start icon="tabler-arrow-back" />
                                    {{ t('Back') }}
                                </VBtn>
                                <VBtn color="error" variant="tonal" type="reset" @click.prevent="resetForm">
                                    <VIcon start icon="tabler-refresh" />
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
