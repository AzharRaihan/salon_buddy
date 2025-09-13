<script setup>
import { onMounted, ref } from 'vue'
import { toast } from 'vue3-toastify'
import { useI18n } from 'vue-i18n'
import { useWebsiteSettingsStore } from '@/stores/websiteSetting'

const { t } = useI18n()
const websiteSettingsStore = useWebsiteSettingsStore()

const form = ref({
    collect_tax: '',
    tax_type: 'Exclusive',
    tax_title: '',
    tax_registration_no: '',
    tax_is_gst: '',
    tax_rates: []
})

const loadings = ref(false)
const errors = ref({})

const validateForm = () => {
    errors.value = {}

    if (!form.value.collect_tax) {
        errors.value.collect_tax = t('Collect tax is required')
    }

    if (form.value.collect_tax == 'Yes') {
        if (!form.value.tax_type) {
            errors.value.tax_type = t('Tax type is required')
        }
        if (!form.value.tax_title) {
            errors.value.tax_title = t('Tax title is required')
        }
        if (form.value.tax_title && form.value.tax_title.length > 55) {
            errors.value.tax_title = t('Tax title must not exceed 55 characters')
        }
        if (!form.value.tax_registration_no) {
            errors.value.tax_registration_no = t('Tax registration no is required')
        }
        if (form.value.tax_registration_no && form.value.tax_registration_no.length > 55) {
            errors.value.tax_registration_no = t('Tax registration no must not exceed 55 characters')
        }
        if (!form.value.tax_is_gst) {
            errors.value.tax_is_gst = t('Tax is GST is required')
        }
        if (form.value.tax_rates.length == 0) {
            errors.value.tax_rates = t('At least one tax rate is required')
        }

        // Validate tax rates fields
        form.value.tax_rates.forEach((rate, index) => {
            if (!rate.name) {
                if (!errors.value.tax_rates) errors.value.tax_rates = {}
                errors.value.tax_rates[`${index}_name`] = t('Tax name is required')
            }
            if (!rate.percentage) {
                if (!errors.value.tax_rates) errors.value.tax_rates = {}
                errors.value.tax_rates[`${index}_percentage`] = t('Tax rate is required')
            }
            if (rate.percentage && (rate.percentage < 0 || rate.percentage > 100)) {
                if (!errors.value.tax_rates) errors.value.tax_rates = {}
                errors.value.tax_rates[`${index}_percentage`] = t('Tax rate must be between 0 and 100')
            }
        })

        // Only validate required GST taxes when tax_is_gst is Yes
        if (form.value.tax_is_gst == 'Yes') {
            const requiredTaxes = ['CGST', 'SGST', 'IGST']
            const existingTaxes = form.value.tax_rates.map(rate => rate.name)
            
            const missingTaxes = requiredTaxes.filter(tax => !existingTaxes.includes(tax))
            if (missingTaxes.length > 0) {
                errors.value.tax_rates = t('When GST is enabled, CGST, SGST and IGST are required. Missing: ') + missingTaxes.join(', ')
            }
        }
    }

    return Object.keys(errors.value).length == 0
}


const handleTaxIsGstChange = () => {
    if (form.value.tax_is_gst == 'No') {
        form.value.tax_rates = []
    }
}


const resetForm = () => {
    form.value = {
        collect_tax: '',
        tax_type: 'Exclusive',
        tax_title: '',
        tax_registration_no: '',
        tax_is_gst: '',
        tax_rates: []
    }
}

const addTaxRate = () => {
    if (form.value.tax_is_gst == 'Yes') {
        // When GST is Yes, first add all required GST taxes
        const existingTaxes = form.value.tax_rates.map(rate => rate.name)
        const requiredGstTaxes = ['CGST', 'SGST', 'IGST'].filter(tax => !existingTaxes.includes(tax))
        
        if (requiredGstTaxes.length) {
            form.value.tax_rates.push({
                name: requiredGstTaxes[0],
                percentage: ''
            })
        } else {
            // After all required GST taxes are added, allow adding custom taxes
            form.value.tax_rates.push({
                name: '',
                percentage: ''
            })
        }
    } else {
        // When GST is No, allow any tax name including GST taxes
        form.value.tax_rates.push({
            name: '',
            percentage: ''
        })
    }
}

const removeTaxRate = (index) => {
    const rate = form.value.tax_rates[index]
    // When GST is Yes, prevent removing required GST taxes
    if (form.value.tax_is_gst == 'Yes' && ['CGST', 'SGST', 'IGST'].includes(rate.name)) {
        toast(t('Cannot remove required GST taxes'), { type: 'error' })
        return
    }
    form.value.tax_rates.splice(index, 1)
}

const getTaxSettings = async () => {
    try {
        const res = await $api('/tax-settings', {
            method: 'GET'
        })

        if (res.success == true) {
            const taxRates = res.data.tax_setting ? JSON.parse(res.data.tax_setting) : []
            const formattedTaxRates = taxRates.map(rate => ({
                name: rate.tax,
                percentage: rate.tax_rate
            }))

            form.value = {
                collect_tax: res.data.collect_tax || '',
                tax_type: res.data.tax_type || 'Exclusive',
                tax_title: res.data.tax_title || '',
                tax_registration_no: res.data.tax_registration_no || '',
                tax_is_gst: res.data.tax_is_gst || '',
                tax_rates: formattedTaxRates
            }
        }
    } catch (err) {
        console.error(err)
        toast(t('Failed to fetch tax settings'), {
            type: 'error'
        })
    }
}

const updateTaxSettings = async () => {
    loadings.value = true
    if (!validateForm()) {
        loadings.value = false
        return
    }

    try {
        const res = await $api('/tax-settings', {
            method: 'POST',
            body: form.value
        })

        if (res.success == true) {
            toast(res.message, {
                type: 'success'
            })
            loadings.value = false
            getTaxSettings()
            websiteSettingsStore.resetSettings()
        }
    } catch (err) {
        console.error(err)
        if (err.response?.status == 422) {
            errors.value = err.response.data.errors
            toast(t('Please check the form for errors'), {
                type: 'error'
            })
        } else {
            toast(err.response?.data?.message || t('An unexpected error occurred'), {
                type: 'error'
            })
        }
        loadings.value = false
    }
}

onMounted(() => {
    getTaxSettings()
    websiteSettingsStore.resetSettings()
})
</script>

<template>
    <VRow>
        <VCol cols="12">
            <VCard :title="t('Tax Settings')">
                <VCardText>
                    <VForm @submit.prevent="updateTaxSettings">
                        <VRow>
                            <!-- Collect Tax -->
                            <VCol cols="12" md="6">
                                <VLabel>{{ t('Collect Tax') }} *</VLabel>
                                <VRadioGroup v-model="form.collect_tax" inline>
                                    <VRadio :label="t('Yes')" value="Yes" />
                                    <VRadio :label="t('No')" value="No" />
                                </VRadioGroup>
                                <div v-if="errors.collect_tax" class="text-error">
                                    {{ errors.collect_tax }}
                                </div>
                            </VCol>

                            <VCol v-if="form.collect_tax == 'Yes'" cols="12">
                                <VRow>
                                    <!-- Tax Type -->
                                    <VCol cols="12" md="4">
                                        <VLabel>{{ t('Tax Type') }} *</VLabel>
                                        <VRadioGroup v-model="form.tax_type" inline>
                                            <VRadio :label="t('Inclusive')" value="Inclusive" />
                                            <VRadio :label="t('Exclusive')" value="Exclusive" />
                                        </VRadioGroup>
                                        <div v-if="errors.tax_type" class="text-error">
                                            {{ errors.tax_type }}
                                        </div>
                                    </VCol>

                                    <!-- Tax Title -->
                                    <VCol cols="12" md="4">
                                        <AppTextField 
                                            v-model="form.tax_title" 
                                            :label="t('Tax Title')" :required="true"
                                            :error-messages="errors.tax_title" 
                                            :placeholder="t('Enter tax title')"
                                            maxlength="55"
                                        />
                                    </VCol>

                                    <!-- Tax Registration -->
                                    <VCol cols="12" md="4">
                                        <AppTextField 
                                            v-model="form.tax_registration_no" 
                                            :label="t('Tax Registration')" :required="true"
                                            :error-messages="errors.tax_registration_no"
                                            :placeholder="t('Enter tax registration no')"
                                            maxlength="55"
                                        />
                                    </VCol>

                                    <!-- Is GST -->
                                    <VCol cols="12" md="6">
                                        <VLabel>{{ t('My Tax Is GST') }} *</VLabel>
                                        <VRadioGroup v-model="form.tax_is_gst" inline @change="handleTaxIsGstChange">
                                            <VRadio :label="t('Yes')" value="Yes" />
                                            <VRadio :label="t('No')" value="No" />
                                        </VRadioGroup>
                                        <div v-if="errors.tax_is_gst" class="text-error">
                                            {{ errors.tax_is_gst }}
                                        </div>
                                        <VAlert v-if="form.tax_is_gst == 'Yes'" color="info" variant="tonal">
                                            {{ t('If Yes then system will apply Indian GST (CGST, SGST or IGST), If customer state is same as your state then system will apply CGST(9%) and SGST(9%), If customer state is different from your state then system will apply IGST(18%)') }}
                                        </VAlert>
                                    </VCol>
                                </VRow>

                                <!-- Tax Rates -->
                                <VRow class="mt-4">
                                    <VCol cols="12">
                                        <div class="d-flex justify-space-between align-center mb-4">
                                            <h3 class="text-h6">{{ t('Tax Rates') }} *</h3>
                                            <VBtn color="primary" @click="addTaxRate">
                                                <VIcon start icon="tabler-plus" />
                                                {{ t('Add Tax Rate') }}
                                            </VBtn>
                                        </div>

                                        <VTable v-if="form.tax_rates.length">
                                            <thead>
                                                <tr>
                                                    <th>{{ t('SN') }}</th>
                                                    <th>{{ t('Tax Name') }}</th>
                                                    <th>{{ t('Tax Percentage') }}</th>
                                                    <th>{{ t('Action') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="(rate, index) in form.tax_rates" :key="index">
                                                    <td>{{ index + 1 }}</td>
                                                    <td>
                                                        <AppTextField
                                                            v-model="rate.name"
                                                            :placeholder="t('Enter tax name')"
                                                            density="compact"
                                                            :error-messages="errors.tax_rates?.[`${index}_name`]"
                                                            hide-details
                                                        />
                                                        <!-- <AppSelect
                                                            v-if="form.tax_is_gst == 'Yes' && !rate.name"
                                                            v-model="rate.name"
                                                            :items="['CGST', 'SGST', 'IGST']"
                                                            placeholder="Select GST type"
                                                            density="compact"
                                                            :error-messages="errors.tax_rates?.[`${index}_name`]"
                                                            hide-details
                                                        />
                                                        <AppTextField
                                                            v-else
                                                            v-model="rate.name"
                                                            placeholder="Enter tax name"
                                                            density="compact"
                                                            :error-messages="errors.tax_rates?.[`${index}_name`]"
                                                            hide-details
                                                        /> -->
                                                    </td>
                                                    <td>
                                                        <div class="d-flex align-center">
                                                            <AppTextField 
                                                                v-model="rate.percentage"
                                                                type="number"
                                                                :placeholder="t('Enter tax percentage')"
                                                                density="compact"
                                                                :error-messages="errors.tax_rates?.[`${index}_percentage`]"
                                                                hide-details
                                                                min="0"
                                                                max="100"
                                                                class="flex-grow-1"
                                                            />
                                                            <span class="ms-2">%</span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <VBtn 
                                                            color="error" 
                                                            icon 
                                                            variant="text" 
                                                            size="small"
                                                            @click="removeTaxRate(index)"
                                                            :disabled="form.tax_is_gst == 'Yes' && ['CGST', 'SGST', 'IGST'].includes(rate.name)"
                                                        >
                                                            <VIcon>tabler-trash</VIcon>
                                                        </VBtn>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </VTable>

                                        <VAlert v-else color="info" variant="tonal">
                                            {{ t('No tax rates added yet. Click the button above to add one.') }}
                                        </VAlert>

                                        <div v-if="errors.tax_rates && typeof errors.tax_rates == 'string'" class="text-error mt-2">
                                            {{ errors.tax_rates }}
                                        </div>
                                    </VCol>
                                </VRow>
                            </VCol>

                            <!-- Form Actions -->
                            <VCol cols="12" class="d-flex flex-wrap gap-4">
                                <VBtn type="submit" color="primary" :loading="loadings" :disabled="loadings">
                                    <VIcon start icon="tabler-checkbox" />
                                    {{ t('Save Changes') }}
                                </VBtn>
                            </VCol>
                        </VRow>
                    </VForm>
                </VCardText>
            </VCard>
        </VCol>
    </VRow>
</template>
