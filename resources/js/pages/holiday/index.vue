<script setup>
import { onMounted, ref, watch } from 'vue'
import { toast } from 'vue3-toastify'
import { useI18n } from 'vue-i18n'
import AppDateTimePicker from '@core/components/date-time-picker/DemoDateTimePickerHumanFriendly.vue'

const { t } = useI18n()

const form = ref({
  saturday_start: '',
  saturday_end: '',
  saturday_is_holiday: 'No',
  sunday_start: '',
  sunday_end: '',
  sunday_is_holiday: 'No',
  monday_start: '',
  monday_end: '',
  monday_is_holiday: 'No',
  tuesday_start: '',
  tuesday_end: '',
  tuesday_is_holiday: 'No',
  wednesday_start: '',
  wednesday_end: '',
  wednesday_is_holiday: 'No',
  thursday_start: '',
  thursday_end: '',
  thursday_is_holiday: 'No',
  friday_start: '',
  friday_end: '',
  friday_is_holiday: 'No',
  holiday_message: '',
})

const loadings = ref(false)
const errors = ref({})

const days = [
  { key: 'saturday', label: 'Saturday' },
  { key: 'sunday', label: 'Sunday' },
  { key: 'monday', label: 'Monday' },
  { key: 'tuesday', label: 'Tuesday' },
  { key: 'wednesday', label: 'Wednesday' },
  { key: 'thursday', label: 'Thursday' },
  { key: 'friday', label: 'Friday' },
]


watch(
  () => form.value,
  (newVal) => {
    for (const day of days) {
      if (newVal[`${day.key}_is_holiday`] === 'Yes') {
        newVal[`${day.key}_start`] = ''
        newVal[`${day.key}_end`] = ''
      }
    }
  },
  { deep: true }
)

// ✅ Validate form before submitting
const validateForm = () => {
  errors.value = {}

  for (const day of days) {
    const isHoliday = form.value[`${day.key}_is_holiday`] === 'Yes'
    const start = form.value[`${day.key}_start`]
    const end = form.value[`${day.key}_end`]

    // If it's not a holiday, start & end are required
    if (!isHoliday) {
      if (!start) {
        errors.value[`${day.key}_start`] = `Star mark field is required, ${day.label} opening time is required`
      }
      if (!end) {
        errors.value[`${day.key}_end`] = `Star mark field is required, ${day.label} closing time is required`
      }
    }
  }

  // Show error toast if any error
  if (Object.keys(errors.value).length > 0) {
    const firstError = Object.values(errors.value)[0]
    toast(firstError, { type: 'error' })
    return false
  }

  return true
}

const resetForm = () => {
  form.value = {
    saturday_start: '',
    saturday_end: '',
    saturday_is_holiday: 'No',
    sunday_start: '',
    sunday_end: '',
    sunday_is_holiday: 'No',
    monday_start: '',
    monday_end: '',
    monday_is_holiday: 'No',
    tuesday_start: '',
    tuesday_end: '',
    tuesday_is_holiday: 'No',
    wednesday_start: '',
    wednesday_end: '',
    wednesday_is_holiday: 'No',
    thursday_start: '',
    thursday_end: '',
    thursday_is_holiday: 'No',
    friday_start: '',
    friday_end: '',
    friday_is_holiday: 'No',
    holiday_message: '',
  }
  errors.value = {}
}

const getHolidaySettings = async () => {
  try {
    const res = await $api('/holidays')
    if (res.success == true && res.data) {
      form.value = {
        saturday_start: res.data.saturday_start || '',
        saturday_end: res.data.saturday_end || '',
        saturday_is_holiday: res.data.saturday_is_holiday || 'No',
        sunday_start: res.data.sunday_start || '',
        sunday_end: res.data.sunday_end || '',
        sunday_is_holiday: res.data.sunday_is_holiday || 'No',
        monday_start: res.data.monday_start || '',
        monday_end: res.data.monday_end || '',
        monday_is_holiday: res.data.monday_is_holiday || 'No',
        tuesday_start: res.data.tuesday_start || '',
        tuesday_end: res.data.tuesday_end || '',
        tuesday_is_holiday: res.data.tuesday_is_holiday || 'No',
        wednesday_start: res.data.wednesday_start || '',
        wednesday_end: res.data.wednesday_end || '',
        wednesday_is_holiday: res.data.wednesday_is_holiday || 'No',
        thursday_start: res.data.thursday_start || '',
        thursday_end: res.data.thursday_end || '',
        thursday_is_holiday: res.data.thursday_is_holiday || 'No',
        friday_start: res.data.friday_start || '',
        friday_end: res.data.friday_end || '',
        friday_is_holiday: res.data.friday_is_holiday || 'No',
        holiday_message: res.data.holiday_message || '',
      }
    }
  } catch (err) {
    console.error(err)
    toast('Failed to fetch holiday settings', {
      type: 'error',
    })
  }
}

const updateHolidaySettings = async () => {
  try {
    loadings.value = true
    if (!validateForm()) {
      loadings.value = false
      return
    }

    const res = await $api('/holidays', {
      method: 'POST',
      body: form.value,
      headers: {
        Accept: 'application/json',
      },
    })

    if (res.success == true) {
      toast(res.message, {
        type: 'success',
      })
      await getHolidaySettings()
    }
  } catch (err) {
    console.error(err)
    toast(err?.response?.data?.message || 'An error occurred while updating settings', {
      type: 'error',
    })
  } finally {
    loadings.value = false
  }
}

onMounted(() => {
  getHolidaySettings()
})
</script>

<template>
    <VRow>
        <VCol cols="12">
            <VCard :title="t('Holiday Settings (Keep Selected Days as Holiday)')">
            <VCardText>
                    <VForm @submit.prevent="updateHolidaySettings">
                        <VRow>
                            <!-- Days Loop -->
                            <VCol cols="12" v-for="day in days" :key="day.key">
                                <VRow>
                                    <VCol cols="12" md="2" class="d-flex align-center">
                                    <VCheckbox 
                                        v-model="form[`${day.key}_is_holiday`]" 
                                        :label="day.label"
                                        true-value="Yes"
                                        false-value="No"
                                    />
                                    </VCol>

                                    <VCol cols="12" md="3">
                                    <AppDateTimePicker 
                                        v-model="form[`${day.key}_start`]"
                                        :disabled="form[`${day.key}_is_holiday`] === 'Yes'" 
                                        :label="t('Opening Time')"
                                        :placeholder="t('Select opening time')"
                                        :required="form[`${day.key}_is_holiday`] === 'No'"
                                        :config="{ 
                                        enableTime: true,
                                        noCalendar: true,
                                        dateFormat: 'h:i K',
                                        time_24hr: false
                                        }" 
                                    />
                                    </VCol>

                                    <VCol cols="12" md="3">
                                    <AppDateTimePicker 
                                        v-model="form[`${day.key}_end`]"
                                        :disabled="form[`${day.key}_is_holiday`] === 'Yes'"
                                        :label="t('Closing Time')"
                                        :placeholder="t('Select closing time')"
                                        :required="form[`${day.key}_is_holiday`] === 'No'"
                                        :config="{ 
                                        enableTime: true,
                                        noCalendar: true,
                                        dateFormat: 'h:i K',
                                        time_24hr: false
                                        }" 
                                    />
                                    </VCol>
                                </VRow>
                                <VDivider class="my-4" v-if="day.key !== 'friday'" />
                            </VCol>

                            <!-- Holiday Message -->
                            <VCol cols="12">
                                <VDivider class="mb-4" />
                                <AppTextarea 
                                    v-model="form.holiday_message" 
                                    :label="t('Holiday Message')"
                                    :placeholder="t('Enter message to show during booking on holidays')"
                                    rows="4"
                                />
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
