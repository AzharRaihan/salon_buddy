<script setup>
import { useRouter } from 'vue-router';
import { toast } from 'vue3-toastify';
import { ref } from 'vue';
import AppDateTimePicker from '@core/components/date-time-picker/DemoDateTimePickerHumanFriendly.vue';
import DemoEditorCustomEditor from '@core/components/editor/DemoEditorCustomEditor.vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n()
const router = useRouter()
const loadings = ref(false)
const editorContent = ref('')

const form = ref({
    title: '',
    start_date: '',
    end_date: '',
    auto_response: 'No',
    mail_subject: '',
    mail_body: ''
})

const titleError = ref('')
const startDateError = ref('')
const endDateError = ref('')
const autoResponseError = ref('')
const mailSubjectError = ref('')
const mailBodyError = ref('')

const validateTitle = (title) => {
    if (!title) {
        titleError.value = t('Title is required')
        return false
    }
    titleError.value = ''
    return true
}

const validateStartDate = (startDate) => {
    if (!startDate) {
        startDateError.value = t('Start date is required')
        return false
    }
    startDateError.value = ''
    return true
}

const validateEndDate  = (endDate) => {
    if (!endDate) {
        endDateError.value = t('End date is required')
        return false
    }
    endDateError.value = ''
    return true
}

const validateAutoResponse = (autoResponse) => {
    if (form.value.auto_response == 'Yes' && !form.value.mail_subject) {
        mailSubjectError.value = t('Mail subject is required');
        return false;
    }
    mailSubjectError.value = '';
    return true;
}

const validateMailBody = (mailBody) => {
    if (form.value.auto_response == 'Yes' && !editorContent.value) {
        mailBodyError.value = t('Mail body is required');
        return false;
    }
    mailBodyError.value = '';
    return true;
}

const validateForm = () => {
    const isTitleValid = validateTitle(form.value.title);
    const isStartDateValid = validateStartDate(form.value.start_date);
    const isEndDateValid = validateEndDate(form.value.end_date);
    const isAutoResponseValid = validateAutoResponse(form.value.auto_response);
    const isMailBodyValid = validateMailBody(form.value.mail_body);

    return isTitleValid && isStartDateValid && isEndDateValid && isAutoResponseValid && isMailBodyValid;
}

const resetForm = () => {
    form.value = {
        title: '',
        start_date: '',
        end_date: '',
        auto_response: 'No',
        mail_subject: '',
        mail_body: ''
    }
    editorContent.value = ''
    titleError.value = ''
    startDateError.value = ''
    endDateError.value = ''
    autoResponseError.value = ''
    mailSubjectError.value = ''
    mailBodyError.value = ''
}

const handleEditorInput = (content) => {
    // Extract the actual HTML content if it's an event object
    const htmlContent = content.target?.innerHTML || content
    editorContent.value = htmlContent
    form.value.mail_body = htmlContent
    validateMailBody()
}

const createVacation = async () => {
    loadings.value = true
    if (!validateForm()) {
        loadings.value = false
        return
    }

    try {
        const res = await $api('/vacations', {
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
        router.push({ name: 'vacation' })
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
            toast(err.message, {
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
            <VCard :title="t('Create Vacation')">
                <VCardText>
                    <VForm class="mt-3" @submit.prevent="createVacation">
                        <VRow>
                            <!-- Title -->
                            <VCol cols="12" md="6" lg="4">
                                <AppTextField v-model="form.title" :label="t('Title')" :required="true" type="text" :placeholder="t('Enter title')" :error-messages="titleError" @input="validateTitle($event.target.value)" />
                            </VCol>

                            <!-- Start Time -->
                            <VCol cols="12" md="6" lg="4">
                                <AppDateTimePicker 
                                    v-model="form.start_date" 
                                    :label="t('Start Date')" :required="true"
                                    :placeholder="t('Start Date')"
                                    :error-messages="startDateError" 
                                    @update:model-value="validateStartDate"
                                    :config="{ 
                                        enableTime: false,
                                        noCalendar: false,
                                        dateFormat: 'Y-m-d',
                                    }" />
                            </VCol>

                            <!-- End Time -->
                            <VCol cols="12" md="6" lg="4">
                                <AppDateTimePicker
                                    v-model="form.end_date"
                                    :label="t('End Date')" :required="true"
                                    :placeholder="t('End Date')"
                                    :error-messages="endDateError"
                                    @update:model-value="validateEndDate"
                                    :config="{ 
                                        enableTime: false,
                                        noCalendar: false,
                                        dateFormat: 'Y-m-d',
                                    }"
                                />
                            </VCol>

                            <!-- Auto Response -->
                            <VCol cols="12" md="6">
                                <VLabel>{{ t('Auto Response') }}</VLabel>
                                <VRadioGroup v-model="form.auto_response" inline>
                                    <VRadio :label="t('Yes')" value="Yes" />
                                    <VRadio :label="t('No')" value="No" />
                                </VRadioGroup>
                            </VCol>

                            <!-- Mail Subject -->
                            <VCol cols="12" v-if="form.auto_response == 'Yes'">
                                <AppTextField v-model="form.mail_subject" :label="t('Mail Subject')" :required="true" type="text" :placeholder="t('Enter mail subject')"
                                    :error-messages="mailSubjectError" @input="validateAutoResponse" />
                            </VCol>

                            <!-- Mail Body -->
                            <VCol cols="12" v-if="form.auto_response == 'Yes'">
                                <div
                                    :title="t('Mail Body')"
                                    :code="DemoEditorCustomEditor.basicEditor"
                                >
                                    <DemoEditorCustomEditor v-model="editorContent" @input="handleEditorInput" />
                                </div>
                                <div class="text-error" v-if="mailBodyError">{{ mailBodyError }}</div>
                            </VCol>

                            <!-- Form Actions -->
                            <VCol cols="12" class="d-flex flex-wrap gap-4">
                                <VBtn type="submit" :loading="loadings" :disabled="loadings">
                                    <VIcon start icon="tabler-checkbox" />
                                    {{ t('Create Vacation') }}
                                </VBtn>
                                <VBtn color="error" variant="tonal" type="reset" @click.prevent="resetForm">
                                    <VIcon start icon="tabler-circle-minus" />
                                    {{ t('Cancel') }}
                                </VBtn>
                            </VCol>
                        </VRow>
                    </VForm>
                </VCardText>
            </VCard>
        </VCol>
    </VRow>
</template>
