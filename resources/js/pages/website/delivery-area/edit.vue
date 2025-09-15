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
    delivery_charge: '',
    note: ''
})

const nameError = ref('')
const deliveryChargeError = ref('')
const noteError = ref('')

// Fetch delivery area data on mount
const fetchDeliveryArea = async () => {
    try {
        const res = await $api(`/delivery-areas/${route.query.id}`, {
            method: 'GET',
        })
        
        const { data } = res
        form.value = {
            name: data.name,
            delivery_charge: data.delivery_charge,
            note: data.note
        }
        nameError.value = ''
        deliveryChargeError.value = ''
        noteError.value = ''
    } catch (err) {
        console.error(err)
        toast('Error fetching delivery area data', {
            type: 'error'
        })
        router.push({ name: 'website-delivery-area' })
    }
}

onMounted(() => {
    fetchDeliveryArea()
})

const validateName = (name) => {
    if (!name) {
        nameError.value = t('Name is required')
        return false
    }
    nameError.value = ''
    return true
}

const validateDeliveryCharge = (deliveryCharge) => {
    if (!deliveryCharge) {
        deliveryChargeError.value = t('Delivery Charge is required')
        return false
    }
    deliveryChargeError.value = ''
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

const validateForm = () => {
    // Validate all fields at once
    const isNameValid = validateName(form.value.name)
    const isDeliveryChargeValid = validateDeliveryCharge(form.value.delivery_charge)
    const isNoteValid = validateNote(form.value.note)

    return isNameValid && isDeliveryChargeValid && isNoteValid
}

const resetForm = () => {
    router.push({ name: 'website-delivery-area' })
}

const updateDeliveryArea = async () => {
    loadings.value = true
    if (!validateForm()) {
        loadings.value = false
        return
    }

    try {
        const res = await $api(`/delivery-areas/${route.query.id}`, {
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
        router.push({ name: 'website-delivery-area' })
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
            <VCard :title="$t('Edit Delivery Area')">
                <VCardText>
                    <VForm class="mt-3" @submit.prevent="updateDeliveryArea">
                        <VRow>
                            <!-- Name -->
                            <VCol cols="12" md="6" lg="4">
                                <AppTextField v-model="form.name" :label="$t('Delivery Area Name')" :required="true" type="text" :placeholder="$t('Enter Delivery Area Name')"
                                    :error-messages="nameError" @input="validateName($event.target.value)" />
                            </VCol>

                            <!-- Delivery Charge -->
                            <VCol cols="12" md="6" lg="4">
                                <AppTextField v-model="form.delivery_charge" :label="$t('Delivery Charge')" :required="true" type="number" :placeholder="$t('Enter Delivery Charge')"
                                    :error-messages="deliveryChargeError" @input="validateDeliveryCharge($event.target.value)" />
                            </VCol>

                            <!-- Note -->
                            <VCol cols="12" md="6" lg="4">
                                <AppTextarea v-model="form.note" :label="$t('Note')" type="text"
                                    :placeholder="$t('Enter Note')" :error-messages="noteError" 
                                    @input="validateNote($event.target.value)" />
                            </VCol>

                            <!-- Form Actions -->
                            <VCol cols="12" class="d-flex flex-wrap gap-4">
                                <VBtn type="submit" :loading="loadings" :disabled="loadings">
                                    <VIcon start icon="tabler-checkbox" />
                                    {{ $t('Update') }}
                                </VBtn>
                                <VBtn color="primary" variant="tonal" @click="router.push({ name: 'website-delivery-area' })">
                                    <VIcon start icon="tabler-arrow-back" />
                                    {{ $t('Back') }}
                                </VBtn>
                            </VCol>
                        </VRow>
                    </VForm>
                </VCardText>
            </VCard>
        </VCol>
    </VRow>
</template>
