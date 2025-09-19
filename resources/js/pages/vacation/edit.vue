<script setup>
import { useRouter, useRoute } from 'vue-router';
import { toast } from 'vue3-toastify';
import { ref, onMounted, watch } from 'vue';
import AppDateTimePicker from '@core/components/date-time-picker/DemoDateTimePickerHumanFriendly.vue';
import DemoEditorCustomEditor from '@core/components/editor/DemoEditorCustomEditor.vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n()
const router = useRouter()
const route = useRoute()
const loadings = ref(false)
const editorContent = ref('')

const form = ref({
    title: null,
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

const validateEndDate = (endDate) => {
    if (!endDate) {
        endDateError.value = t('End date is required')
        return false
    }
    endDateError.value = ''
    return true
}

const validateAutoResponse = () => {
    if (form.value.auto_response == 'Yes' && !form.value.mail_subject) {
        mailSubjectError.value = t('Mail subject is required');
        return false;
    }
    mailSubjectError.value = '';
    return true;
}

const validateMailBody = () => {
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
    const isAutoResponseValid = validateAutoResponse();
    const isMailBodyValid = validateMailBody();

    return isTitleValid && isStartDateValid && isEndDateValid && isAutoResponseValid && isMailBodyValid;
}

const resetForm = () => {
    router.push({ name: 'vacation' })
}

const handleEditorInput = (content) => {
    const htmlContent = content.target?.innerHTML || content
    editorContent.value = htmlContent
    form.value.mail_body = htmlContent
}

const fetchVacation = async () => {
    try {
        const res = await $api(`/vacations/${route.query.id}`)
        const vacation = res.data
        form.value = {
            title: vacation.title,
            start_date: vacation.start_date,
            end_date: vacation.end_date,
            auto_response: vacation.auto_response,
            mail_subject: vacation.mail_subject || '',
            mail_body: vacation.mail_body || ''
        }
        editorContent.value = vacation.mail_body || ''
    } catch (err) {
        console.error('Error fetching vacation:', err)
        toast('Error loading vacation data', { type: 'error' })
    }
}

const updateVacation = async () => {
    loadings.value = true
    if (!validateForm()) {
        loadings.value = false
        return
    }

    try {
        const formData = new FormData()
        Object.keys(form.value).forEach(key => {
            formData.append(key, form.value[key])
        })
        formData.append('_method', 'PUT')

        const res = await $api(`/vacations/${route.query.id}`, {
            method: 'POST',
            body: formData,
            headers: {
                'Accept': 'application/json',
            },
            onResponseError({ response }) {
                toast(response._data.message, { type: 'error' })
                loadings.value = false
                return Promise.reject(response._data)
            },
        })

        const { status, message } = res

        if (status == 'error') {
            toast(message, { type: 'error' })
            loadings.value = false
            return
        }

        toast(message, { type: "success" })
        loadings.value = false
        router.push({ name: 'vacation' })
    } catch (err) {
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
watch(() => form.value.auto_response, (newVal) => {
  if (newVal === 'No') {
    form.value.mail_body = ''
    editorContent.value = ''
  }
})
onMounted(() => {
    if (route.query.id) {
        fetchVacation()
    }
})
</script>

<template>
    <VRow>
        <VCol cols="12">
            <VCard :title="t('Edit Vacation')">
                <VCardText>
                    <VForm class="mt-3" @submit.prevent="updateVacation">
                        <VRow>
                            <!-- Title -->
                            <VCol cols="12" md="6" lg="4">
                                <AppTextField 
                                    v-model="form.title" 
                                    :label="t('Title')" :required="true"
                                    :placeholder="t('Enter title')" 
                                    :error-messages="titleError" 
                                    @update:model-value="validateTitle" 
                                    clearable
                                />
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
                                    }" 
                                />
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
                                <AppTextField 
                                    v-model="form.mail_subject" 
                                    :label="t('Mail Subject')" :required="true"
                                    type="text" 
                                    :placeholder="t('Enter mail subject')"
                                    :error-messages="mailSubjectError" 
                                    @input="validateAutoResponse" 
                                />
                            </VCol>

                            <!-- Mail Body -->
                            <VCol cols="12" v-if="form.auto_response == 'Yes'">
                                <div :title="t('Mail Body')">
                                    <DemoEditorCustomEditor
                                        :content="editorContent"
                                        v-model="editorContent"
                                        @input="handleEditorInput" 
                                    />
                                </div>
                                <div class="text-error" v-if="mailBodyError">{{ mailBodyError }}</div>
                            </VCol>

                            <!-- Form Actions -->
                            <VCol cols="12" class="d-flex flex-wrap gap-4">
                                <VBtn type="submit" :loading="loadings" :disabled="loadings">
                                    <VIcon start icon="tabler-checkbox" />
                                    {{ t('Update Vacation') }}
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
