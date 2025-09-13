<template>
    <div v-if="show" class="customer-modal select-modal show" @mousedown.self="emit('close')">
        <div class="modal-content">
            <div class="modal-header">
                <span></span>
                <button class="close-modal" @click="emit('close')">
                    <VIcon icon="tabler-x" />
                </button>
            </div>
            <div class="modal-body">
                <!-- Loading State -->
                <div v-if="isLoading" class="loading-state">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">{{ t('Loading...') }}</span>
                    </div>
                    <p class="mt-2 text-muted">{{ t('Loading customers...') }}</p>
                </div>

                <!-- Error State -->
                <div v-else-if="error" class="error-state">
                    <div class="alert alert-danger" role="alert">
                        <VIcon icon="tabler-alert-circle" />
                        {{ t('Something went wrong. Please try again') }}
                    </div>
                    <button class="btn btn-outline-primary" @click="handleRetry">
                        <VIcon icon="tabler-refresh" /> {{ t('Retry') }}
                    </button>
                </div>

                <!-- Main Content -->
                <div v-else>
                    <div class="search-input mb-3">
                        <input type="text" :placeholder="t('Search Customer...')" v-model="searchQuery"
                            @input="handleSearchInput" @focus="$event.target.select()" />
                        <VIcon icon="tabler-search" class="search-icon" />
                        <VIcon icon="tabler-x" class="search-close" v-if="searchQuery" @click="handleSearchClose" />
                    </div>

                    <div class="customer-list">
                        <!-- Empty State -->
                        <div v-if="filteredCustomers.length === 0" class="empty-state">
                            <VIcon icon="tabler-user-x" size="48" />
                            <p class="text-muted">
                                {{ searchQuery ? t('No customers found matching your search') : t('No customers available') }}
                            </p>
                        </div>

                        <!-- Customer List -->
                        <ul v-else>
                            <li v-for="customer in filteredCustomers" :key="customer.id">
                                <div class="customer-details" @click="selectCustomer(customer)" :class="{ 'selected-customer': customer.id === orderStore.selectedCustomerId }">
                                    <h5>{{ customer.name }}{{ customer.phone ? ' (' + customer.phone + ')' : '' }}</h5>
                                </div>
                                <button v-if="customer.name != 'Walk-in Customer'" class="btn btn-sm btn-outline-primary edit-btn" @click.stop="editCustomer(customer)">
                                    <VIcon icon="tabler-edit" />
                                </button>
                            </li>
                        </ul>
                    </div>

                    <button class="btn btn-primary add-customer-btn mt-3" @click="handleAddCustomer">
                        <VIcon icon="tabler-plus" />
                        {{ t('Add New Customer') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add/Edit Customer Modal - Moved outside to fix z-index issues -->
    <AddEditCustomerModal
        v-if="showAddEditModal"
        :show="showAddEditModal"
        :customer="editingCustomer"
        :mode="addEditMode"
        @close="showAddEditModal = false"
        @saved="handleCustomerSaved"
    />
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import { useCustomers } from '@/composables/useCustomers'
import AddEditCustomerModal from './AddEditCustomerModal.vue'
import { useOrderStore } from '@/stores/pos/orderStore'
const orderStore = useOrderStore()
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

// Props and emits
const props = defineProps({
    show: Boolean,
    formData: Object
})

// Search Close
const handleSearchClose = () => {
    searchQuery.value = ''
}

const emit = defineEmits(['confirm', 'close', 'edit-customer', 'add-customer'])

// Use customer composable
const {
    customers,
    isLoading,
    error,
    searchQuery,
    filteredCustomers,
    fetchCustomers,
    setSearchQuery,
    clearCustomers,
    getCustomer,
    createCustomer, // <-- Use createCustomer
    updateCustomer // <-- Use updateCustomer 
} = useCustomers()

// Add/Edit modal state
const showAddEditModal = ref(false)
const addEditMode = ref('add') // 'add' or 'edit'
const editingCustomer = ref(null)

// Methods
const selectCustomer = (customer) => {
    emit('confirm', customer)
    emit('close')
}

const editCustomer = async (customer) => {
  // Fetch latest customer data
  const latest = await getCustomer(customer.id);
  editingCustomer.value = latest || customer;
  addEditMode.value = 'edit';
  showAddEditModal.value = true;
};

const handleAddCustomer = () => {
    editingCustomer.value = null
    addEditMode.value = 'add'
    showAddEditModal.value = true
}

const handleSearchInput = (event) => {
    const query = event.target.value
    setSearchQuery(query)
}

const handleRetry = async () => {
    await fetchCustomers()
}

// Handle save from AddEditCustomerModal
const handleCustomerSaved = async (customerData) => {
    if (addEditMode.value === 'add') {
        // Add customer via API
        const result = await createCustomer(customerData)
        if (result && result.success && result.data && result.data.id) {
            await fetchCustomers() // Refresh list
            setSearchQuery('')
            // Select the new customer
            const found = customers.value.find(c => c.id === result.data.id)
            if (found) selectCustomer(found)
            showAddEditModal.value = false
        }
    } else if (addEditMode.value === 'edit') {
        // Update customer via API
        const result = await updateCustomer(customerData.id, customerData)
        if (result && result.success && result.data && result.data.id) {
            await fetchCustomers() // Refresh list
            setSearchQuery('')
            // Select the updated customer
            const found = customers.value.find(c => c.id === result.data.id)
            if (found) selectCustomer(found)
            showAddEditModal.value = false
        }
    }
}

// Watchers
watch(() => props.show, (newValue) => {
    if (newValue) {
        // Fetch customers when modal opens
        if (customers.value.length === 0) {
            fetchCustomers()
        }
    } else {
        // Clear search when modal closes
        setSearchQuery('')
        showAddEditModal.value = false
    }
})

// Lifecycle
onMounted(() => {
    // Pre-load customers if modal is already showing
    if (props.show && customers.value.length === 0) {
        fetchCustomers()
    }
})
</script>
<style scoped>
/* Loading State */
.loading-state {
    text-align: center;
    padding: 2rem 0;
}
.spinner-border {
    width: 2rem;
    height: 2rem;
}
/* Error State */
.error-state {
    text-align: center;
    padding: 1rem 0;
}
.alert {
    margin-bottom: 1rem;
    padding: 0.75rem 1rem;
    border-radius: 6px;
    border: 1px solid transparent;
}
.alert-danger {
    color: #721c24;
    background-color: #f8d7da;
    border-color: #f5c6cb;
}
/* Empty State */
.empty-state {
    text-align: center;
    padding: 2rem 1rem;
}
</style>
