<script setup>
import { useRouter } from 'vue-router';
import { toast } from 'vue3-toastify';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n()
const router = useRouter()
const loadings = ref(false)
const selectedAccountBalance = ref(null)

const form = ref({
    name: '',
    contact_person: '',
    phone: '',
    email: '',
    address: '',
    description: ''
})

const nameError = ref('')
const contactPersonError = ref('')
const phoneError = ref('')
const emailError = ref('')
const addressError = ref('')
const descriptionError = ref('')




const validateName = (name) => {
    if (!name) {
        nameError.value = t('Name is required')
        return false
    }
    if (name.length > 55) {
        nameError.value = t('Name cannot exceed 55 characters')
        return false
    }
    nameError.value = ''
    return true
}

const validateContactPerson = (contactPerson) => {
    if (!contactPerson) {
        contactPersonError.value = t('Contact person is required')
        return false
    }
    if (contactPerson.length > 55) {
        contactPersonError.value = t('Contact person name cannot exceed 55 characters')
        return false
    }
    contactPersonError.value = ''
    return true
}

const validatePhone = (phone) => {
    if (!phone) {
        phoneError.value = t('Phone is required')
        return false
    }
    if (phone.length > 55) {
        phoneError.value = t('Phone cannot exceed 55 characters')
        return false
    }
    phoneError.value = ''
    return true
}

const validateEmail = (email) => {
    if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        emailError.value = t('Invalid email address')
        return false
    }
    emailError.value = ''
    return true
}

const validateAddress = (address) => {
    if (address && address.length > 255) {
        addressError.value = t('Address cannot exceed 255 characters')
        return false
    }
    addressError.value = ''
    return true
}

const validateDescription = (description) => {
    if (description && description.length > 255) {
        descriptionError.value = t('Description cannot exceed 255 characters')
        return false
    }
    descriptionError.value = ''
    return true
}

const validateAmount = (amount) => {
    if (amount < 0) {
        amountError.value = t('Paid amount cannot be negative')
        return false
    }
    if (amount > form.value.grand_total) {
        amountError.value = t('Paid amount cannot exceed grand total')
        return false
    }

    if (selectedAccountBalance.value !== null && selectedAccountBalance.value !== undefined) {
        const bal = parseFloat(selectedAccountBalance.value) || 0
        if (parseFloat(amount) > bal) {
            amountError.value = t(`Insufficient balance. You can't pay more than ${formatNumberPrecision(bal)}`)
            return false
        }
    }

    amountError.value = ''
    return true
}

const validateForm = () => {
    const isNameValid = validateName(form.value.name)
    const isContactPersonValid = validateContactPerson(form.value.contact_person)
    const isPhoneValid = validatePhone(form.value.phone)
    const isEmailValid = validateEmail(form.value.email)
    const isAddressValid = validateAddress(form.value.address)
    const isDescriptionValid = validateDescription(form.value.description)

    return isNameValid && isContactPersonValid && isPhoneValid && isEmailValid && isAddressValid && isDescriptionValid
}

const resetForm = () => {
    form.value = {
        name: '',
        contact_person: '',
        phone: '',
        email: '',
        address: '',
        description: ''
    }
    nameError.value = ''
    contactPersonError.value = ''
    phoneError.value = ''
    emailError.value = ''
    addressError.value = ''
    descriptionError.value = ''
}

const createSupplier = async () => {
    loadings.value = true
    if (!validateForm()) {
        loadings.value = false
        return
    }

    try {
        const formData = new FormData()
        Object.keys(form.value).forEach(key => {
            // Only append if value is not empty string
            if (form.value[key] !== '') {
                formData.append(key, form.value[key])
            }
        })

        const res = await $api('/suppliers', {
            method: 'POST',
            body: formData,
            headers: {
                'Accept': 'application/json',
            },
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
        router.push({ name: 'supplier' })
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
            <VCard :title="t('Add Supplier')">
                <VCardText>
                    <VForm class="mt-3" @submit.prevent="createSupplier">
                        <VRow>
                            <!-- Name -->
                            <VCol cols="12" md="6" lg="4">
                                <AppTextField v-model="form.name" :label="t('Name')" :required="true" type="text" :placeholder="t('Enter Name')"
                                    :error-messages="nameError" @input="validateName($event.target.value)" />
                            </VCol>

                            <!-- Contact Person -->
                            <VCol cols="12" md="6" lg="4">
                                <AppTextField v-model="form.contact_person" :label="t('Contact Person')" :required="true" type="text" 
                                    :placeholder="t('Enter Contact Person')"
                                    :error-messages="contactPersonError" 
                                    @input="validateContactPerson($event.target.value)" />
                            </VCol>

                            <!-- Phone -->
                            <VCol cols="12" md="6" lg="4">
                                <AppTextField v-model="form.phone" :label="t('Phone')" :required="true" type="text" :placeholder="t('Enter phone')"
                                    :error-messages="phoneError" @input="validatePhone($event.target.value)" />
                            </VCol>

                            <!-- Email -->
                            <VCol cols="12" md="6" lg="4">
                                <AppTextField v-model="form.email" :label="t('Email')" type="email" :placeholder="t('Enter email')" :error-messages="emailError" />
                            </VCol>

                        </VRow>
                        <VRow>

                            <!-- Address -->
                            <VCol cols="12" md="6">
                                <AppTextField v-model="form.address" :label="t('Address')" type="text" :placeholder="t('Enter Address')" :error-messages="addressError" />
                            </VCol>

                            <!-- Description -->
                            <VCol cols="12" md="6">
                                <AppTextField v-model="form.description" :label="t('Description')" type="text" 
                                    :placeholder="t('Enter Description')" :error-messages="descriptionError" />
                            </VCol>

                            <!-- Form Actions -->
                            <VCol cols="12" class="d-flex flex-wrap gap-4">
                                <VBtn type="submit" :loading="loadings" :disabled="loadings">
                                    <VIcon start icon="tabler-checkbox" />
                                    {{ t('Submit') }}
                                </VBtn>
                                <VBtn type="button" @click="router.push({ name: 'supplier' })" color="primary" variant="tonal">
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
