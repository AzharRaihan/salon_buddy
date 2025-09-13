<script setup>
import { useRouter } from 'vue-router';
import { toast } from 'vue3-toastify';
import { ref, watch, onMounted } from 'vue';
import AppDate from '@core/components/date-time-picker/DemoDateTimePickerBasic.vue';
import AppDateTimePicker from '@core/components/date-time-picker/DemoDateTimePickerHumanFriendly.vue';
import { useI18n } from 'vue-i18n'
const { t } = useI18n()

const router = useRouter()
const loadings = ref(false)
const users = ref([])

const form = ref({
    date: '',
    note: '',
    in_time: '',
    out_time: '',
    total_time: '',
    user_id: null
})

const dateError = ref('')
const noteError = ref('')
const inTimeError = ref('')
const outTimeError = ref('')
const totalTimeError = ref('')
const userIdError = ref('')

const fetchUsers = async () => {
    try {
        const res = await $api('/get-all-users')
        users.value = res.data
    } catch (err) {
        console.error(err)
        toast('Error fetching users', {
            type: 'error'
        })
    }
}

onMounted(() => {
    fetchUsers()
})

const validateDate = (date) => {
    if (!date) {
        dateError.value = t('Date is required')
        return false
    }
    if (date.length > 55) {
        dateError.value = t('Date cannot exceed 55 characters')
        return false
    }
    dateError.value = ''
    return true
}

const validateNote = (note) => {
    if (note && note.length > 255) {
        noteError.value = t('Note cannot exceed 255 characters')
        return false
    }
    noteError.value = ''
    return true
}

const validateInTime = (inTime) => {
    if (!inTime) {
        inTimeError.value = t('In time is required')
        return false
    }
    inTimeError.value = ''
    return true
}

const validateOutTime = (outTime) => {
    if (!outTime) {
        outTimeError.value = t('Out time is required')
        return false
    }
    outTimeError.value = ''
    return true
}

const validateTotalTime = (totalTime) => {
    if (!totalTime) {
        totalTimeError.value = t('Total time is required')
        return false
    }
    totalTimeError.value = ''
    return true
}

const validateUserId = (userId) => {
    if (!userId) {
        userIdError.value = t('User is required')
        return false
    }
    userIdError.value = ''
    return true
}

const calculateTotalTime = () => {
    if (form.value.in_time && form.value.out_time) {
        // Convert 12-hour format to 24-hour for calculation
        const inTimeParts = form.value.in_time.match(/(\d+):(\d+)\s*(AM|PM)/)
        const outTimeParts = form.value.out_time.match(/(\d+):(\d+)\s*(AM|PM)/)

        if (inTimeParts && outTimeParts) {
            let inHours = parseInt(inTimeParts[1])
            const inMinutes = parseInt(inTimeParts[2])
            const inPeriod = inTimeParts[3]

            let outHours = parseInt(outTimeParts[1])
            const outMinutes = parseInt(outTimeParts[2])
            const outPeriod = outTimeParts[3]

            // Convert to 24 hour format
            if (inPeriod == 'PM' && inHours != 12) inHours += 12
            if (inPeriod == 'AM' && inHours == 12) inHours = 0
            if (outPeriod == 'PM' && outHours != 12) outHours += 12
            if (outPeriod == 'AM' && outHours == 12) outHours = 0

            // Calculate difference in minutes
            const inTimeMinutes = inHours * 60 + inMinutes
            const outTimeMinutes = outHours * 60 + outMinutes
            let diffMinutes = outTimeMinutes - inTimeMinutes

            // Handle case when out time is next day
            if (diffMinutes < 0) {
                diffMinutes += 24 * 60
            }

            // Convert to hours and minutes
            const hours = Math.floor(diffMinutes / 60)
            const minutes = diffMinutes % 60

            form.value.total_time = `${hours}:${minutes.toString().padStart(2, '0')}`
        }
    }
}

// Watch for changes in in_time and out_time
watch([() => form.value.in_time, () => form.value.out_time], () => {
    calculateTotalTime()
})

const resetForm = () => {
    form.value = {
        date: '',
        note: '',
        in_time: '',
        out_time: '', 
        total_time: '',
        user_id: null
    }
    dateError.value = ''
    noteError.value = ''
    inTimeError.value = ''
    outTimeError.value = ''
    totalTimeError.value = ''
    userIdError.value = ''
}

const createAttendance = async () => {
    loadings.value = true

    // Validate all required fields first
    validateDate(form.value.date)
    validateInTime(form.value.in_time)
    validateOutTime(form.value.out_time) 
    validateTotalTime(form.value.total_time)
    validateUserId(form.value.user_id)
    validateNote(form.value.note)

    // Check if any validation errors exist
    if (dateError.value || inTimeError.value || outTimeError.value || 
        totalTimeError.value || userIdError.value || noteError.value) {
        loadings.value = false
        return
    }

    try {
        const res = await $api('/attendances', {
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
        router.push({ name: 'attendance' })
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
            <VCard :title="$t('Create Attendance')">
                <VCardText>
                    <VForm class="mt-3" @submit.prevent="createAttendance">
                        <VRow>

                            <!-- Date -->
                            <VCol cols="12" md="6" lg="4">
                                <AppDate
                                    v-model="form.date"
                                    :label="$t('Date')" :required="true"
                                    :error-messages="dateError"
                                    :placeholder="$t('Select date')"
                                    @update:model-value="validateDate"
                                    :config="{ 
                                        dateFormat: 'Y-m-d',
                                        maxDate: 'today'
                                    }"
                                />
                            </VCol>

                            <!-- In Time -->
                            <VCol cols="12" md="6" lg="4">
                                <AppDateTimePicker
                                    v-model="form.in_time"
                                    :label="$t('In Time')" :required="true"
                                    :placeholder="$t('Select time')"
                                    :error-messages="inTimeError"
                                    @update:model-value="validateInTime"
                                    :config="{ 
                                        enableTime: true,
                                        noCalendar: true,
                                        dateFormat: 'h:i K',
                                        time_24hr: false
                                    }"
                                />
                            </VCol>

                            <!-- Out Time -->
                            <VCol cols="12" md="6" lg="4">
                                <AppDateTimePicker
                                    v-model="form.out_time"
                                    :label="$t('Out Time')" :required="true"
                                    :placeholder="$t('Select time')"
                                    :error-messages="outTimeError"
                                    @update:model-value="validateOutTime"
                                    :config="{ 
                                        enableTime: true,
                                        noCalendar: true,
                                        dateFormat: 'h:i K',
                                        time_24hr: false
                                    }"
                                />
                            </VCol>

                            <!-- Total Time -->
                            <VCol cols="12" md="6" lg="4">
                                <AppTextField v-model="form.total_time"
                                    :label="$t('Total Time')" :required="true"
                                    type="text"
                                    :placeholder="$t('Total Time')"
                                    :error-messages="totalTimeError"
                                    @input="validateTotalTime($event.target.value)"
                                    readonly
                                />
                            </VCol>

                            <!-- User Select -->
                            <VCol cols="12" md="6" lg="4">
                                <AppAutocomplete
                                    v-model="form.user_id"
                                    :items="[...users]"
                                    item-title="name"
                                    item-value="id"
                                    :label="$t('User')" :required="true"
                                    :placeholder="$t('Select User')"
                                    :error-messages="userIdError"
                                    @update:model-value="validateUserId"
                                    clearable
                                />
                            </VCol>

                            <!-- Note -->
                            <VCol cols="12" md="6" lg="4">
                                <AppTextarea v-model="form.note"
                                    :label="$t('Note')"
                                    :placeholder="$t('Enter note')"
                                    :error-messages="noteError"
                                    @input="validateNote($event.target.value)"
                            />
                            </VCol>

                            <!-- Form Actions -->
                            <VCol cols="12" class="d-flex flex-wrap gap-4">
                                <VBtn type="submit" :loading="loadings" :disabled="loadings">
                                    <VIcon start icon="tabler-checkbox" />
                                    {{ $t('Submit') }}
                                </VBtn>
                                <VBtn type="button" @click="router.push({ name: 'attendance' })" color="primary" variant="tonal">
                                    <VIcon start icon="tabler-arrow-back" />
                                    {{ $t('Back') }}
                                </VBtn>
                                <VBtn color="error" variant="tonal" type="reset" @click.prevent="resetForm">
                                    <VIcon start icon="tabler-refresh" />
                                    {{ $t('Reset') }}
                                </VBtn>
                            </VCol>
                        </VRow>
                    </VForm>
                </VCardText>
            </VCard>
        </VCol>
    </VRow>
</template>
