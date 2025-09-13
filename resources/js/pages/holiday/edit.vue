<script setup>
import { useRouter, useRoute } from 'vue-router';
import { toast } from 'vue3-toastify';
import { ref, onMounted } from 'vue';
import AppDateTimePicker from '@core/components/date-time-picker/DemoDateTimePickerHumanFriendly.vue';
import DemoEditorCustomEditor from '@core/components/editor/DemoEditorCustomEditor.vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n()
const router = useRouter()
const route = useRoute()
const loadings = ref(false)
const editorContent = ref('')

const form = ref({
    day: null,
    start_time: '',
    end_time: '',
    auto_response: 'No',
    mail_subject: '',
    mail_body: null
})

const dayError = ref('')
const startTimeError = ref('')
const endTimeError = ref('')
const autoResponseError = ref('')
const mailSubjectError = ref('')
const mailBodyError = ref('')

const validateDay = (day) => {
    if (!day) {
        dayError.value = t('Day is required')
        return false
    }
    dayError.value = ''
    return true
}

const validateStartTime = (startTime) => {
    if (!startTime) {
        startTimeError.value = t('Start time is required')
        return false
    }
    startTimeError.value = ''
    return true
}

const validateEndTime = (endTime) => {
    if (!endTime) {
        endTimeError.value = t('End time is required')
        return false
    }
    endTimeError.value = ''
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
    const isDayValid = validateDay(form.value.day);
    const isStartTimeValid = validateStartTime(form.value.start_time);
    const isEndTimeValid = validateEndTime(form.value.end_time);
    const isAutoResponseValid = validateAutoResponse();
    const isMailBodyValid = validateMailBody();

    return isDayValid && isStartTimeValid && isEndTimeValid && isAutoResponseValid && isMailBodyValid;
}

const resetForm = () => {
    router.push({ name: 'holiday' })
}

const handleEditorInput = (content) => {
    const htmlContent = content.target?.innerHTML || content
    editorContent.value = htmlContent
    form.value.mail_body = htmlContent
}

const fetchHoliday = async () => {
    try {
        const res = await $api(`/holidays/${route.query.id}`)
        const holiday = res.data
        form.value = {
            day: holiday.day,
            start_time: holiday.start_time,
            end_time: holiday.end_time,
            auto_response: holiday.auto_response,
            mail_subject: holiday.mail_subject,
            mail_body: holiday.mail_body
        }
        editorContent.value = holiday.mail_body || ''
    } catch (err) {
        console.error('Error fetching holiday:', err)
        toast('Error loading holiday data', { type: 'error' })
    }
}

const updateHoliday = async () => {
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

        const res = await $api(`/holidays/${route.query.id}`, {
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
        router.push({ name: 'holiday' })
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

onMounted(() => {
    if (route.query.id) {
        fetchHoliday()
    }
})
</script>

<template>
    <VRow>
        <VCol cols="12">
            <VCard :title="t('Edit Holiday')">
                <VCardText>
                    <VForm class="mt-3" @submit.prevent="updateHoliday">
                        <VRow>
                            <!-- Day -->
                            <VCol cols="12" md="6" lg="4">
                                <AppAutocomplete 
                                    v-model="form.day" 
                                    :label="t('Day')" :required="true" 
                                    :items="['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']"
                                    :placeholder="t('Select day')" 
                                    :error-messages="dayError" 
                                    @update:model-value="validateDay" 
                                    clearable
                                />
                            </VCol>

                            <!-- Start Time -->
                            <VCol cols="12" md="6" lg="4">
                                <AppDateTimePicker 
                                    v-model="form.start_time" 
                                    :label="t('Start Time')" :required="true" 
                                    :placeholder="t('Start Time')"
                                    :error-messages="startTimeError" 
                                    @update:model-value="validateStartTime"
                                    :config="{ 
                                        enableTime: true,
                                        noCalendar: true,
                                        dateFormat: 'h:i K',
                                        time_24hr: false
                                    }" 
                                />
                            </VCol>

                            <!-- End Time -->
                            <VCol cols="12" md="6" lg="4">
                                <AppDateTimePicker
                                    v-model="form.end_time"
                                    :label="t('End Time')" :required="true"
                                    :placeholder="t('End Time')"
                                    :error-messages="endTimeError"
                                    @update:model-value="validateEndTime"
                                    :config="{ 
                                        enableTime: true,
                                        noCalendar: true,
                                        dateFormat: 'h:i K',
                                        time_24hr: false
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
                                    {{ t('Update Holiday') }}
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
