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
    description: ''
})

const nameError = ref('')
const descriptionError = ref('')

// Fetch expense category data on mount
const fetchExpenseCategory = async () => {
    try {
        const res = await $api(`/expense-categories/${route.query.id}`, {
            method: 'GET',
        })
        
        const { data } = res
        form.value = {
            name: data.name,
            description: data.description
        }
    } catch (err) {
        console.error(err)
        toast('Error fetching expense category data', {
            type: 'error'
        })
        router.push({ name: 'expense-category' })
    }
}

onMounted(() => {
    fetchExpenseCategory()
})

const validateName = (name) => {
    if (!name) {
        nameError.value = t('Name is required')
        return false
    }
    nameError.value = ''
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
    // Validate all fields at once
    const isNameValid = validateName(form.value.name)
    const isDescriptionValid = validateDescription(form.value.description)

    return isNameValid && isDescriptionValid
}

const resetForm = () => {
    router.push({ name: 'expense-category' })
}

const updateExpenseCategory = async () => {
    loadings.value = true
    if (!validateForm()) {
        loadings.value = false
        return
    }

    try {
        const res = await $api(`/expense-categories/${route.query.id}`, {
            method: 'PUT',
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
        router.push({ name: 'expense-category' })
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
            <VCard :title="t('Edit Expense Category')">
                <VCardText>
                    <VForm class="mt-3" @submit.prevent="updateExpenseCategory">
                        <VRow>
                            <!-- Name -->
                            <VCol cols="12" md="6" lg="4">
                                <AppTextField v-model="form.name" :label="t('Name')" :required="true" type="text" :placeholder="t('Enter Name')"
                                    :error-messages="nameError" @input="validateName($event.target.value)" />
                            </VCol>

                            <!-- Description -->
                            <VCol cols="12" md="6" lg="4">
                                <AppTextarea v-model="form.description" :label="t('Description')" type="text"
                                    :placeholder="t('Enter Description')" :error-messages="descriptionError" 
                                    @input="validateDescription($event.target.value)" />
                            </VCol>

                            <!-- Form Actions -->
                            <VCol cols="12" class="d-flex flex-wrap gap-4">
                                <VBtn type="submit" :loading="loadings" :disabled="loadings">
                                    <VIcon start icon="tabler-checkbox" />
                                    {{ t('Update') }}
                                </VBtn>
                                <VBtn type="button" @click="router.push({ name: 'expense-category' })" color="primary" variant="tonal">
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
