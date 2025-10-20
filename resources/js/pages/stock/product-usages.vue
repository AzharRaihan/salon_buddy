<script setup>
import { computed, ref, watch, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { toast } from 'vue3-toastify';
import { useI18n } from 'vue-i18n';
import { useCompanyFormatters } from '@/composables/useCompanyFormatters';
import ExportTable from '@/components/ExportTable.vue';

// Computed headers for export (converts computed refs to strings)
const exportHeaders = computed(() => {
    return headers.map(header => ({
        ...header,
        title: typeof header.title == 'object' && header.title.value !== undefined 
            ? header.title.value 
            : header.title
    }))
})

const { t } = useI18n()
const router = useRouter()
const searchQuery = ref('')

// Data table options
const itemsPerPage = ref(10)
const page = ref(1)
const sortBy = ref()
const orderBy = ref()
const isConfirmDialogOpen = ref(false)
const selectedUsageId = ref(null)
const usageData = ref(null)

// Offcanvas state
const showOffcanvas = ref(false)
const isEditing = ref(false)
const editingUsageId = ref(null)

// Form data
const form = ref({
    item_id: null,
    quantity: 1,
    usage_date: new Date().toISOString().split('T')[0],
    notes: ''
})

// Form errors
const formErrors = ref({
    item_id: '',
    quantity: '',
    usage_date: '',
    notes: ''
})

// Products list
const products = ref([])

// Company formatters
const { fetchCompanySettings, formatDate, formatAmount, getSerialNumber } = useCompanyFormatters()

// Computed properties for i18n translations
const SN = computed(() => t('SN'))
const Product = computed(() => t('Product'))
const Quantity = computed(() => t('Quantity'))
const UsageDate = computed(() => t('Usage Date'))
const Notes = computed(() => t('Notes'))
const Action = computed(() => t('Action'))

// Data table Headers
const headers = [
    {
        title: SN,
        key: 'serial_number',
        sortable: false,
    },
    {
        title: Product,
        key: 'item',
        sortable: true,
    },
    {
        title: Quantity,
        key: 'quantity',
        sortable: true,
    },
    {
        title: UsageDate,
        key: 'usage_date',
        sortable: true,
    },
    {
        title: Notes,
        key: 'notes',
        sortable: false,
    },
    {
        title: Action,
        key: 'action',
        sortable: false,
        align: 'center',
    },
]

const updateOptions = options => {
    sortBy.value = options.sortBy[0]?.key
    orderBy.value = options.sortBy[0]?.order
}

const fetchUsages = async () => {
    const response = await $api('/product-usages', {
        method: 'GET',
        query: {
            q: searchQuery.value,
            itemsPerPage: itemsPerPage.value,
            page: page.value,
            sortBy: sortBy.value,
            orderBy: orderBy.value,
        },
    })
    usageData.value = response.data
}

const fetchProducts = async () => {
    try {
        const response = await $api('/get-product-type-item-list')
        products.value = response.data
    } catch (error) {
        console.error('Error fetching products:', error)
        toast(t('Error fetching products'), { type: 'error' })
    }
}

// Initial fetch
await fetchUsages()
await fetchProducts()

const usages = computed(() => {
    const data = usageData.value?.usages || []
    return data.map((usage, index) => ({
        ...usage,
        serial_number: getSerialNumber(index, totalUsages.value, page.value, itemsPerPage.value),
    }))
})

const totalUsages = computed(() => usageData.value?.total || 0)

const openConfirmDialog = (usageId) => {
    isConfirmDialogOpen.value = true
    selectedUsageId.value = usageId
}

const handleDelete = async (confirmed) => {
    if (!confirmed) return;

    try {
        await $api(`/product-usages/${selectedUsageId.value}`, {
            method: 'DELETE',
        })
        selectedUsageId.value = null
        isConfirmDialogOpen.value = false
        await fetchUsages()
        toast(t('Product usage deleted successfully'), {
            type: 'success',
        });
    } catch (error) {
        isConfirmDialogOpen.value = false
        selectedUsageId.value = null
        console.error('Error deleting product usage:', error)
        toast(t('Failed to delete product usage'), {
            type: 'error',
        });
    }
}

// Offcanvas functions
const openOffcanvas = (usage = null) => {
    if (usage) {
        // Edit mode
        isEditing.value = true
        editingUsageId.value = usage.id
        form.value = {
            item_id: usage.item?.id,
            quantity: usage.quantity,
            usage_date: usage.usage_date,
            notes: usage.notes || ''
        }
    } else {
        // Create mode
        isEditing.value = false
        editingUsageId.value = null
        form.value = {
            item_id: null,
            quantity: 1,
            usage_date: new Date().toISOString().split('T')[0],
            notes: ''
        }
    }
    resetFormErrors()
    showOffcanvas.value = true
}

const closeOffcanvas = () => {
    showOffcanvas.value = false
    isEditing.value = false
    editingUsageId.value = null
    resetForm()
}

const resetForm = () => {
    form.value = {
        item_id: null,
        quantity: 1,
        usage_date: new Date().toISOString().split('T')[0],
        notes: ''
    }
    resetFormErrors()
}

const resetFormErrors = () => {
    formErrors.value = {
        item_id: null,
        quantity: '',
        usage_date: '',
        notes: ''
    }
}

const validateDate = () => {
    form.value.usage_date = new Date(form.value.usage_date).toISOString().split('T')[0]
}

const validateItem = () => {
    form.value.item_id = form.value.item_id ? parseInt(form.value.item_id) : null
}

const validateForm = () => {
    let isValid = true
    resetFormErrors()

    if (!form.value.item_id) {
        formErrors.value.item_id = t('Product is required')
        isValid = false
    }

    if (!form.value.quantity || form.value.quantity < 1) {
        formErrors.value.quantity = t('Quantity must be at least 1')
        isValid = false
    }

    if (!form.value.usage_date) {
        formErrors.value.usage_date = t('Usage date is required')
        isValid = false
    }

    if (form.value.notes && form.value.notes.length > 255) {
        formErrors.value.notes = t('Notes cannot exceed 255 characters')
        isValid = false
    }

    return isValid
}

const submitForm = async () => {
    if (!validateForm()) {
        toast(t('Please fix the errors in the form'), { type: 'error' })
        return
    }

    try {
        const url = isEditing.value 
            ? `/product-usages/${editingUsageId.value}`
            : '/product-usages'
        
        const method = isEditing.value ? 'PUT' : 'POST'
        
        const response = await $api(url, {
            method: method,
            body: JSON.stringify(form.value),
            headers: { 'Content-Type': 'application/json' },
        })

        if (response.success) {
            toast(response.message || (isEditing.value ? t('Product usage updated successfully') : t('Product usage recorded successfully')), {
                type: 'success'
            })
            resetForm()
            closeOffcanvas()
            await fetchUsages()
        } else {
            toast(response.message || t('Operation failed'), { type: 'error' })
        }
    } catch (error) {
        console.error('Error submitting form:', error)
        if (error.errors) {
            // Handle validation errors
            Object.keys(error.errors).forEach(key => {
                if (formErrors.value.hasOwnProperty(key)) {
                    formErrors.value[key] = error.errors[key][0]
                }
            })
        } else {
            toast(t('An error occurred while saving'), { type: 'error' })
        }
    }
}

// Watch for changes in search query
watch(searchQuery, () => {
    page.value = 1 // Reset to first page when searching
    fetchUsages() 
})

// Watch for changes in pagination
watch([page, itemsPerPage], () => {
    fetchUsages()
})

onMounted(async () => {
    await fetchCompanySettings()
})
</script>

<template>
    <div>
        <VCard :title="t('Product Usage Tracking')">
            <VCardText>
                <div class="d-flex justify-space-between flex-wrap gap-y-4">
                    <AppTextField 
                        v-model="searchQuery" 
                        style="max-inline-size: 280px; min-inline-size: 280px;"
                        :placeholder="t('Search Product Usage')" 
                    />
                    <div class="d-flex flex-row gap-4 align-center flex-wrap">
                        <VBtn prepend-icon="tabler-plus" @click="openOffcanvas()">
                            {{ t('Add Usage') }}
                        </VBtn>
                        <AppSelect v-model="itemsPerPage" :items="[5, 10, 20, 50, 100]" />
                        <ExportTable 
                            :data="usages" 
                            :headers="exportHeaders" 
                            filename="product-usage-report"
                            :title="$t('Product Usage Tracking')"
                        />
                    </div>
                </div>
            </VCardText>

            <VDivider />
            <VDataTableServer 
                v-model:items-per-page="itemsPerPage" 
                v-model:page="page" 
                :items="usages" 
                item-value="id"
                :headers="headers" 
                :items-length="totalUsages" 
                class="text-no-wrap" 
                @update:options="updateOptions"
            >
                <!-- Serial Number -->
                <template #item.serial_number="{ item }">
                    {{ item.serial_number }}
                </template>

                <!-- Product -->
                <template #item.item="{ item }">
                    <div>
                        <div class="font-weight-medium">{{ item.item?.name }}</div>
                        <div class="text-caption text-medium-emphasis">{{ item.item?.code }}</div>
                    </div>
                </template>

                <!-- Quantity -->
                <template #item.quantity="{ item }">
                    <VChip color="primary" size="small">
                        {{ item.quantity }}
                    </VChip>
                </template>

                <!-- Usage Date -->
                <template #item.usage_date="{ item }">
                    {{ formatDate(item.usage_date) }}
                </template>

                <!-- Notes -->
                <template #item.notes="{ item }">
                    <span v-if="item.notes" class="text-truncate d-inline-block" style="max-width: 200px;">
                        {{ item.notes }}
                    </span>
                    <span v-else class="text-medium-emphasis">-</span>
                </template>

                <!-- Loading state -->
                <template #loading>
                    <VSkeletonLoader type="table-row" :rows="itemsPerPage" />
                </template>

                <!-- No data state -->
                <template #no-data>
                    <div class="d-flex align-center justify-center pa-4">
                        <VIcon icon="tabler-alert-circle" class="me-2" />
                        {{ t('No product usages found') }}   
                    </div>
                </template>

                <!-- Actions -->
                <template #item.action="{ item }">
                    <div class="d-flex justify-center gap-1">
                        <VBtn 
                            icon 
                            variant="text" 
                            color="info" 
                            size="small"
                            @click="openOffcanvas(item)"
                        >
                            <VIcon size="22" icon="tabler-edit" />
                        </VBtn>

                        <VBtn 
                            icon 
                            variant="text" 
                            color="error" 
                            size="small" 
                            @click="openConfirmDialog(item.id)"
                        >
                            <VIcon size="22" icon="tabler-trash" />
                        </VBtn>
                    </div>
                </template>
            </VDataTableServer>
        </VCard>

        <!-- Confirmation Dialog -->
        <ConfirmDialog 
            v-model:is-dialog-visible="isConfirmDialogOpen"
            :confirmation-question="t('Are you sure you want to delete this product usage?')" 
            :confirm-title="t('Deleted!')"
            :confirm-msg="t('Product usage has been deleted successfully.')" 
            :cancel-title="t('Cancelled')"
            :cancel-msg="t('Product Usage Deletion Cancelled!')" 
            @confirm="handleDelete" 
        />

        <!-- Offcanvas for Create/Edit -->
        <VNavigationDrawer
            v-model="showOffcanvas"
            location="end"
            temporary
            width="500"
        >
            <VCard flat>
                <VCardTitle class="d-flex justify-space-between align-center pa-4">
                    <span>Send Test Email</span>
                    <VBtn icon variant="text" @click="closeOffcanvas">
                        <VIcon icon="tabler-x" />
                    </VBtn>
                </VCardTitle>

                <VDivider />

                <VCardText class="pa-4">
                    <VForm @submit.prevent="submitForm">
                        <VRow>
                            <!-- Quantity -->
                            <VCol cols="12" md="6">
                                <AppTextField
                                    v-model="form.quantity"
                                    :label="t('Quantity')"
                                    type="number"
                                    :placeholder="t('Enter Quantity')"
                                    :error-messages="formErrors.quantity"
                                    min="1"
                                    @focus="$event.target.select()"
                                    required
                                />
                            </VCol>

                            <!-- Notes -->
                            <VCol cols="12">
                                <AppTextarea
                                    v-model="form.notes"
                                    :label="t('Notes')"
                                    :placeholder="t('Enter any additional notes')"
                                    :error-messages="formErrors.notes"
                                    rows="3"
                                    @focus="$event.target.select()"
                                />
                            </VCol>
                        </VRow>

                        <!-- Form Actions -->
                        <VRow class="mt-4">
                            <VCol cols="12" class="d-flex gap-3">
                                <VBtn
                                    type="submit"
                                    color="primary"
                                    :loading="false"
                                    block
                                >
                                    <VIcon start icon="tabler-check" />
                                    {{  t('Send Email') }}
                                </VBtn>
                                
                            </VCol>
                        </VRow>
                    </VForm>
                </VCardText>
            </VCard>
        </VNavigationDrawer>
    </div>
</template>

<style lang="scss" scoped>
.text-link {
    color: rgb(var(--v-theme-primary));
    text-decoration: none;

    &:hover {
        color: rgba(var(--v-theme-primary), 0.8);
    }
}
</style>