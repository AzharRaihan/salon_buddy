<script setup>
import { useRouter, useRoute } from 'vue-router';
import { toast } from 'vue3-toastify';
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n()
const router = useRouter()
const route = useRoute()
const loadings = ref(false)

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
    router.push({ name: 'supplier' })
}

const fetchSupplier = async () => {
    try {
        const res = await $api(`/suppliers/${route.query.id}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
            }
        })

        const { data } = res
        form.value = {
            ...data,
            email: data.email || '',
            address: data.address || '',
            description: data.description || ''
        }
    } catch (err) {
        console.error(err)
        toast(t('Error fetching supplier'), {
            type: 'error'
        })
        router.push({ name: 'supplier' })
    }
}

const updateSupplier = async () => {
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
        formData.append('_method', 'PUT')

        const res = await $api(`/suppliers/${route.query.id}`, {
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

onMounted(() => {
    fetchSupplier()
})
</script>

<template>
    <VRow>
        <VCol cols="12">
            <VCard :title="t('Edit Supplier')">
                <VCardText>
                    <VForm class="mt-3" @submit.prevent="updateSupplier">
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
                                    {{ t('Update') }}
                                </VBtn>
                                <VBtn color="primary" variant="tonal" @click="router.push({ name: 'supplier' })">
                                    <VIcon start icon="tabler-arrow-back" />
                                    {{ t('Back') }}
                                </VBtn>
                            </VCol>
                        </VRow>
                    </VForm>
                </VCardText>
            </VCard>
        </VCol>
    </VRow>
</template>
