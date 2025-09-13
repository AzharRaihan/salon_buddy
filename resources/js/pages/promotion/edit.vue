<script setup>
import { useRouter, useRoute } from 'vue-router';
import { toast } from 'vue3-toastify';
import { ref, computed, onMounted } from 'vue';
import AppDateTimePicker from '@core/components/date-time-picker/DemoDateTimePickerHumanFriendly.vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n()
const router = useRouter()
const route = useRoute()
const loadings = ref(false)
const itemList = ref([])
const branch_info = useCookie("branch_info").value || 0;

const form = ref({
    type: null,
    title: '',
    start_date: '',
    end_date: '',
    buy_item_id: null,
    buy_qty: null,
    get_item_id: null,
    get_qty: null,
    discount_item_id: null,
    discount_type: null,
    discount: null,
    status: null,
    branch_id: branch_info.id
})

onMounted(async () => {
    try {
        // Fetch item list
        const itemResponse = await $api('/get-all-type-item-list')
        itemList.value = itemResponse.data

        // Fetch promotion data
        const promotionId = route.query.id
        const promotionResponse = await $api(`/promotions/${promotionId}`)
        const promotionData = promotionResponse.data.promotion

        // Format dates from database format to display format
        const formatDate = (dateStr) => {
            const date = new Date(dateStr)
            const month = date.toLocaleString('en-US', { month: 'short' })
            const day = date.getDate()
            const year = date.getFullYear()
            const hours = date.getHours()
            const minutes = date.getMinutes().toString().padStart(2, '0')
            const period = hours >= 12 ? 'PM' : 'AM'
            const displayHours = hours % 12 || 12
            return `${month} ${day}, ${year} ${displayHours}:${minutes} ${period}`
        }

        // Populate form with existing data
        form.value = {
            type: promotionData.type,
            title: promotionData.title,
            start_date: (promotionData.start_date),
            end_date: (promotionData.end_date),
            buy_item_id: promotionData.buy_item?.id,
            buy_qty: promotionData.buy_qty,
            get_item_id: promotionData.get_item?.id,
            get_qty: promotionData.get_qty,
            discount_item_id: promotionData.discount_item?.id,
            discount: promotionData.discount,
            discount_type: promotionData.discount_type,
            status: promotionData.status,
            branch_id: branch_info.id
        }
    } catch (error) {
        console.error('Error fetching data:', error)
        toast('Error fetching data', {
            type: 'error'
        })
    }
})

const isDiscountType = computed(() => form.value.type == 'Discount')
const isFreeItemType = computed(() => form.value.type == 'Free Item')

const titleError = ref('')
const startDateError = ref('')
const endDateError = ref('')
const typeError = ref('')
const buyItemIdError = ref('')
const buyQtyError = ref('')
const getItemIdError = ref('')
const getQtyError = ref('')
const discountItemIdError = ref('')
const discountError = ref('')
const statusError = ref('')
const discountTypeError = ref('')


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

const validateType = (type) => {
    if (!type) {
        typeError.value = t('Type is required')
        return false
    }
    typeError.value = ''
    return true
}

const validateBuyItemId = (id) => {
    if (isFreeItemType.value && !id) {
        buyItemIdError.value = t('Buy item is required')
        return false
    }
    buyItemIdError.value = ''
    return true
}

const validateBuyQty = (qty) => {
    if (isFreeItemType.value && !qty) {
        buyQtyError.value = t('Buy quantity is required')
        return false
    }
    buyQtyError.value = ''
    return true
}

const validateGetItemId = (id) => {
    if (isFreeItemType.value && !id) {
        getItemIdError.value = t('Get item is required')
        return false
    }
    getItemIdError.value = ''
    return true
}

const validateGetQty = (qty) => {
    if (isFreeItemType.value && !qty) {
        getQtyError.value = t('Get quantity is required')
        return false
    }
    getQtyError.value = ''
    return true
}

const validateDiscount = (discount) => {
    if (isDiscountType.value && !discount) {
        discountError.value = t('Discount is required')
        return false
    }
    discountError.value = ''
    return true
}

const validateDiscountType = (discountType) => {
    if (isDiscountType.value && !discountType) {
        discountTypeError.value = t('Discount type is required')
        return false
    }
    discountTypeError.value = ''
    return true
}


const validateStatus = (status) => {
    if (!status) {
        statusError.value = t('Status is required')
        return false
    }
    statusError.value = ''
    return true
}

const resetForm = () => {
    router.push({ name: 'promotion' })
}

const validateAllFields = () => {
    // Validate basic fields
    validateTitle(form.value.title)
    validateStartDate(form.value.start_date)
    validateEndDate(form.value.end_date)
    validateType(form.value.type)
    validateStatus(form.value.status)

    // Validate type specific fields
    if (isDiscountType.value) {
        validateDiscount(form.value.discount)
        validateDiscountType(form.value.discount_type)
    } else if (isFreeItemType.value) {
        validateBuyItemId(form.value.buy_item_id)
        validateBuyQty(form.value.buy_qty)
        validateGetItemId(form.value.get_item_id)
        validateGetQty(form.value.get_qty)
    }

    // Check if any error exists
    if (isDiscountType.value) {
        return !titleError.value && !startDateError.value && !endDateError.value && 
               !typeError.value && !statusError.value && !discountItemIdError.value && 
               !discountError.value && !discountTypeError.value && !statusError.value
    } else {
        return !titleError.value && !startDateError.value && !endDateError.value && 
               !typeError.value && !statusError.value && !buyItemIdError.value && 
               !buyQtyError.value && !getItemIdError.value && !getQtyError.value && !statusError.value
    }
}

const updatePromotion = async () => {
    loadings.value = true
    
    if (!validateAllFields()) {
        loadings.value = false
        return
    }

    try {
        const promotionId = route.query.id
        const res = await $api(`/promotions/${promotionId}`, {
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
        router.push({ name: 'promotion' })
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
            <VCard :title="$t('Edit Promotion')">
                <VCardText>
                    <VForm class="mt-3" @submit.prevent="updatePromotion">
                        <VRow>
                            <!-- Type -->
                            <VCol cols="12" md="6" lg="4">
                                <AppAutocomplete 
                                    v-model="form.type" 
                                    :items="[
                                        { value: 'Discount', title: 'Discount' },
                                        { value: 'Free Item', title: 'Free Item' }
                                    ]" 
                                    :label="$t('Type')" :required="true"
                                    :placeholder="$t('Select Type')"
                                    :error-messages="typeError"
                                    @update:model-value="validateType"
                                    
                                />
                            </VCol>

                            <!-- Title -->
                            <VCol cols="12" md="6" lg="4">
                                <AppTextField 
                                    v-model="form.title" 
                                    :label="$t('Title')" :required="true"
                                    type="text" 
                                    :placeholder="$t('Enter title')"
                                    :error-messages="titleError" 
                                    @input="validateTitle($event.target.value)" 
                                />
                            </VCol>

                            <!-- Start Date -->
                            <VCol cols="12" md="6" lg="4">
                                <AppDateTimePicker 
                                    v-model="form.start_date" 
                                    :label="$t('Start Date')" :required="true"
                                    :placeholder="$t('Select Start Date')"
                                    type="date"
                                    :error-messages="startDateError" 
                                    @update:model-value="validateStartDate"
                                    :config="{
                                        enableTime: false,
                                        dateFormat: 'Y-m-d'
                                    }"
                                    
                                />
                            </VCol>

                            <!-- End Date -->
                            <VCol cols="12" md="6" lg="4">
                                <AppDateTimePicker 
                                    v-model="form.end_date" 
                                    :label="$t('End Date')" :required="true"
                                    :placeholder="$t('Select End Date')"
                                    type="date"
                                    :error-messages="endDateError" 
                                    @update:model-value="validateEndDate"
                                    :config="{
                                        enableTime: false,
                                        dateFormat: 'Y-m-d'
                                    }"
                                />
                            </VCol>

                            <!-- Discount Type Fields -->
                            <template v-if="isDiscountType">
                                <VCol cols="12" md="6" lg="4">
                                    <AppAutocomplete 
                                        v-model="form.discount_item_id" 
                                        :label="$t('Discount Item')"
                                        :items="[   
                                            ...itemList.map(item => ({
                                                title: item.name + ' (' + item.code + ')',
                                                value: item.id
                                            }))
                                        ]"
                                        :placeholder="$t('Select discount item')"
                                        :error-messages="discountItemIdError"
                                        clearable
                                        :tooltipShow="true"
                                        :tooltipTitle="$t('If an item is selected, the discount will apply only to that item, otherwise the discount will be applied to all items')"
                                    />
                                </VCol>

                                <VCol cols="12" md="6" lg="4">
                                    <VRow>
                                        <VCol cols="6">
                                            <AppTextField 
                                                v-model="form.discount" 
                                                :label="$t('Discount')" :required="true"
                                                type="number"
                                                :placeholder="$t('Enter discount')"
                                                :error-messages="discountError"
                                                @input="validateDiscount($event.target.value)"
                                            />
                                        </VCol>
                                        <VCol cols="6"> 
                                            <AppAutocomplete 
                                                v-model="form.discount_type" 
                                                :label="$t('Discount Type')" :required="true"
                                                :items="[
                                                    { title: 'Percentage', value: 'Percentage' },
                                                    { title: 'Fixed', value: 'Fixed' }
                                                ]"
                                                :placeholder="$t('Select discount type')"
                                                :error-messages="discountTypeError"
                                                @update:model-value="validateDiscountType"
                                            />
                                        </VCol>
                                    </VRow>
                                </VCol>

                            </template>

                            <!-- Free Item Type Fields -->
                            <template v-if="isFreeItemType">
                                <VCol cols="12" md="6" lg="4">
                                    <AppAutocomplete
                                        v-model="form.buy_item_id" 
                                        :label="$t('Buy Item')" :required="true"
                                        :items="[
                                            ...itemList.map(item => ({
                                                title: item.name + ' (' + item.code + ')',
                                                value: item.id
                                            }))
                                        ]"
                                        :placeholder="$t('Select buy item')"
                                        :error-messages="buyItemIdError"
                                        @update:model-value="validateBuyItemId"
                                        clearable
                                    />
                                </VCol>
                                
                                <VCol cols="12" md="6" lg="4">
                                    <AppTextField 
                                        v-model="form.buy_qty" 
                                        :label="$t('Buy Quantity')" :required="true"
                                        type="number"
                                        :placeholder="$t('Enter buy quantity')"
                                        :error-messages="buyQtyError"
                                        @input="validateBuyQty($event.target.value)"
                                    />
                                </VCol>

                                <VCol cols="12" md="6" lg="4">
                                    <AppAutocomplete
                                        v-model="form.get_item_id" 
                                        :label="$t('Get Item')" :required="true"
                                        :items="[
                                            ...itemList.map(item => ({
                                                title: item.name + ' (' + item.code + ')',
                                                value: item.id
                                            }))
                                        ]"
                                        :placeholder="$t('Select get item')"
                                        :error-messages="getItemIdError"
                                        @update:model-value="validateGetItemId"
                                        clearable
                                    />
                                </VCol>

                                <VCol cols="12" md="6" lg="4">
                                    <AppTextField 
                                        v-model="form.get_qty" 
                                        :label="$t('Get Quantity')" :required="true"
                                        type="number"
                                        :placeholder="$t('Enter get quantity')"
                                        :error-messages="getQtyError"
                                        @input="validateGetQty($event.target.value)"
                                    />
                                </VCol>
                            </template>

                            <!-- Status -->
                            <VCol cols="12" md="6" lg="4">
                                <AppAutocomplete
                                    v-model="form.status"
                                    :items="[
                                        { title: 'Active', value: 'Active' },
                                        { title: 'Inactive', value: 'Inactive' }
                                    ]"
                                    :label="$t('Status')" :required="true"
                                    :placeholder="$t('Select Status')"
                                    :error-messages="statusError"
                                    @update:model-value="validateStatus"
                                    clearable
                                />
                            </VCol>

                            <!-- Form Actions -->
                            <VCol cols="12" class="d-flex flex-wrap gap-4">
                                <VBtn type="submit" :loading="loadings" :disabled="loadings">
                                    <VIcon start icon="tabler-checkbox" />
                                    {{ $t('Update') }}
                                </VBtn>
                                <VBtn type="button" @click="router.push({ name: 'promotion' })" color="primary" variant="tonal">
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
