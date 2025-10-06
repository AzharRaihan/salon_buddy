<template>
    <div class="container">
        <!-- Edit Mode Indicator -->
        <!-- <div v-if="isInEditMode" class="edit-mode-indicator">
            <div class="alert alert-warning d-flex align-items-center" role="alert">
                <VIcon icon="tabler-edit" class="me-2" />
                <strong>{{ t('Edit Mode') }}</strong> - {{ t('Editing Sale') }} #{{ editingSaleId }}
                <button class="btn btn-sm btn-outline-warning ms-auto" @click="handleCancelEdit">
                    {{ t('Cancel Edit') }}
                </button>
            </div>
        </div> -->

        <!-- Mobile Top Navigation -->
        <div class="mobile-top-navigation-wrapper" v-if="isMobile">
            <div class="mobile-top-navigation">
                <div class="mobile-top-navigation-left">
                    <button class="btn btn-primary btn-lg" @click="handleCartClick">
                        <VIcon icon="tabler-shopping-cart" />
                        {{ t('Cart') }}
                    </button>
                </div>
                <div class="mobile-top-navigation-right">
                    <button class="btn btn-primary btn-lg" @click="handleProductGridClick">
                        <VIcon icon="tabler-clipboard-list" /> 
                        {{ t('Items') }}
                    </button>
                </div>
            </div>
        </div>
        

        <div class="pos-main-content-wrapper main-content">
            <!-- Left Panel - Order Management -->
            <div class="order-section" v-show="!isMobile || activePanel == 'order'">

                <!-- Filter Controls -->
                <FilterButtons :employee="selectedEmployee" :customer="selectedCustomer"
                    @show-employee-modal="modals.toggleEmployeeModal(selectedEmployee)"
                    @show-customer-modal="modals.toggleCustomerModal" />

                <!-- Loyalty Points Display -->
                <!-- <LoyaltyPointsDisplay 
                    :customer-loyalty-points="customerLoyaltyPoints"
                    :loyalty-points-needed="loyaltyPointsNeeded"
                    :loyalty-points-value="calculateLoyaltyPointValue(loyaltyPointsNeeded)"
                    :has-enough-loyalty-points="hasEnoughLoyaltyPoints"
                    :company-loyalty-settings="companyLoyaltySettings"
                    :selected-customer="selectedCustomer"
                /> -->

                <!-- Promotion Display -->
                <!-- <PromotionDisplay :promotions="activePromotions" /> -->

                <div class="frame"></div>

                <!-- Booking Information if has -->
                <div v-if="bookingData" class="booking-info-section">
                    <!-- <div class="booking-header">
                        <h5>
                            <VIcon icon="tabler-calendar-check" />
                            Booking Information
                        </h5>
                    </div> -->
                    <!-- Booking Details -->
                    <div class="booking-details">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="booking-info-item">
                                    <span class="label">{{ t('Booking Code') }}:</span>
                                    <span class="value">{{ bookingData.reference_no }}</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="booking-info-item">
                                    <span class="label">{{ t('Customer') }}:</span>
                                    <span class="value">{{ bookingData.customer.name }} {{ bookingData.customer.phone }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Table -->
                <OrderTable :items="orderItems" :selected-item-index="orderStore.selectedItemIndex"
                    :loading="orderLoading" :debug-mode="true" :booking-data="bookingData ? true : false" @select-item="handleOrderItemSelect"
                    @item-remove="handleItemRemove" @quantity-change="handleQuantityChange" />

                <div class="frame">
                    <!-- <img :src="Frame" alt="Frame" /> -->
                </div>

                <!-- Order Actions -->
                <div v-if="orderStore.hasSelectedItem" class="order-actions" :class="bookingData ? 'booking-order-actions' : ''">
                    <button class="btn btn-dark decrement" 
                        @click="handleDecrementQuantity"
                        :disabled="orderStore.selectedItem?.isFree"
                        :title="orderStore.selectedItem?.isFree ? t('Cannot modify free items') : t('Decrease quantity')">
                        <VIcon icon="tabler-minus" />
                    </button>
                    <button class="btn btn-dark increment" 
                        @click="handleIncrementQuantity"
                        :disabled="orderStore.selectedItem?.isFree"
                        :title="orderStore.selectedItem?.isFree ? t('Cannot modify free items') : t('Increase quantity')">
                        <VIcon icon="tabler-plus" />
                    </button>
                    <button class="btn btn-dark" 
                        @click="handleItemEditClick"
                        :disabled="orderStore.selectedItem?.isFree"
                        :title="orderStore.selectedItem?.isFree ? t('Cannot edit free items') : t('Edit item')">
                        <VIcon icon="tabler-pencil" />
                    </button>
                    <button class="btn btn-dark" 
                        @click="handleTipsClick"
                        :disabled="orderStore.selectedItem?.isFree"
                        :title="orderStore.selectedItem?.isFree ? t('Cannot edit free items') : t('Tips')">
                        <VIcon icon="tabler-coin" />
                    </button>
                    <button class="btn btn-dark delete" 
                        @click="handleRemoveSelectedItem"
                        :disabled="orderStore.selectedItem?.isFree"
                        :title="orderStore.selectedItem?.isFree ? t('Cannot remove free items') : t('Remove item')">
                        <VIcon icon="tabler-trash" />
                    </button>
                </div>

                <!-- Order Summary -->
                <OrderSummary 
                    :subtotal="orderSubtotal" 
                    :tax="orderTax" 
                    :discount="orderDiscount"
                    :promotion-discount="orderStore.promotionDiscountValue"
                    :tips="orderStore.totalTips"
                    :charge="orderStore.serviceCharge" 
                    :grand-total="orderTotal" 
                    :loading="orderLoading" 
                    :is-edit-mode="isInEditMode"
                    @cancel-order="handleClearOrder"
                    @quick-invoice="handlePaymentClick" 
                    @place-order="handlePaymentClick"
                    @save-draft="handleSaveOrder" 
                    @show-discount-modal="modals.toggleDiscountModal"
                    @show-charge-modal="handleChargeModal"
                    @show-date-modal="handleDateClick" 
                    @clear-discount="handleClearDiscount"
                />
            </div>

            <!-- Right Panel - Menu -->
            <div class="menu-section" v-show="!isMobile || activePanel == 'menu'">
                <div class="search-container">
                    <VIcon icon="tabler-search" class="search-icon" />
                    <input type="text" class="form-control" :placeholder="t('Search...')" v-model="searchQuery"
                        @input="handleSearch" @focus="$event.target.select()" />
                    <VIcon icon="tabler-x" class="search-close" v-if="searchQuery" @click="handleSearchClose" />

                </div>
                <div class="category-item-section">
                    <!-- Category Buttons -->
                    <CategoryButtons :categories="menuCategories" :selected-category="selectedCategory"
                        :loading="categoryLoading" @category-select="handleCategorySelect" />

                    <!-- Item Grid -->
                    <ItemGrid :items="filteredMenuItems" :loading="menuLoading" :search-query="searchQuery"
                        @item-select="handleItemSelect" @search="handleSearch" />
                </div>
            </div>
        </div>

        <!-- Modal Components -->
        <EmployeeSelectionModal :show="modals.isModalOpen('employee-selection')" :employees="employees"
            :selected="modals.selectedEmployeeCandidate" @employee-selected="modals.handleEmployeeSelect"
            @confirm="handleEmployeeConfirm" @close="modals.handleEmployeeClose" />

        <CustomerModal :show="modals.isModalOpen('customer-selection')" :form-data="forms.customerForm.formData"
            @confirm="handleCustomerConfirm" @close="handleCustomerClose" @edit-customer="handleEditCustomer"
            @add-customer="handleAddCustomer" />

        <DiscountModal :show="modals.isModalOpen('discount-selection')" :order-subtotal="orderSubtotal"
            @confirm="handleDiscountConfirm" @close="handleDiscountClose" />

        <PaymentModal
            :show="modals.isModalOpen('payment-processing')"
            :form-data="forms.paymentForm.formData"
            :order-total="orderTotal"
            :order-subtotal="orderSubtotal"
            :order-tax="orderTax"
            :order-tax-breakdown="orderTaxBreakdown"
            :order-discount="orderDiscount"
            :promotion-discount="orderStore.promotionDiscountValue"
            :order-items="orderItems"
            :payment-methods="paymentMethods"
            :booking-data="bookingData"
            :selected-customer="selectedCustomer"
            :selected-customer-id="selectedCustomerId"
            @confirm="handlePaymentConfirm"
            @close="handlePaymentClose"
        />
        <ChargeModal :show="modals.isModalOpen('charge-selection')" @confirm="handleChargeConfirm"
            @close="handleChargeClose" />

        <DateModal :show="modals.isModalOpen('date-selection')" @confirm="handleDateConfirm" @close="handleDateClose" />

        <EmployeeAssignmentModal 
            :show="modals.isModalOpen('employee-assignment')" 
            :item="modals.selectedServiceItem"
            :employees="employees"
            @confirm="handleEmployeeAssignmentConfirm" 
            @close="handleEmployeeAssignmentClose" 
        />

        <TipsModal 
            :show="modals.isModalOpen('tips-selection')" 
            :item="modals.selectedServiceItem"
            :employees="employees"
            @confirm="handleTipsConfirm" 
            @close="handleTipsClose" 
        />

        <!-- Loading Overlay -->
        <LoadingSpinner v-if="globalLoading || isLoadingSaleData" :size="'large'" :message="loadingMessage" overlay />

        <!-- <AddEditCustomerModal
            :show="showAddEditCustomerModal"
            :customer="addEditCustomerData"
            :mode="addEditCustomerMode"
            @close="handleCustomerModalClose"
            @saved="handleCustomerModalSaved"
        /> -->

        <BillModal
            :model-value="showBillModal"
            :order="lastOrderData"
            @update:model-value="showBillModal = false"
        />
    </div>
</template>

<script setup>
import { storeToRefs } from 'pinia'
import { computed, onMounted, ref, watch, onUnmounted, onBeforeUnmount } from 'vue'
import { useRoute, useRouter } from 'vue-router'

// Composables
import { $api } from '@/utils/api'
import { usePOSForms } from '@/composables/pos/usePOSForms'
import { usePOSModals } from '@/composables/pos/usePOSModals'
import { usePOSPayment } from '@/composables/pos/usePOSPayment'
import { useErrorHandler } from '@/composables/useErrorHandler'
import { useEmployees } from '@/composables/useEmployees'
import { usePromotions } from '@/composables/pos/usePromotions'
import { useSaleEdit } from '@/composables/pos/useSaleEdit'
import { useLoyaltyPoints } from '@/composables/pos/useLoyaltyPoints'

// Stores
import { useMenuStore } from '@/stores/pos/menuStore'
import { useOrderStore } from '@/stores/pos/orderStore'

// Components
import CategoryButtons from '@/components/pos/CategoryButtons.vue'
import FilterButtons from '@/components/pos/FilterButtons.vue'
import LoadingSpinner from '@/components/pos/LoadingSpinner.vue'
import OrderSummary from '@/components/pos/OrderSummary.vue'
import OrderTable from '@/components/pos/OrderTable.vue'
import LoyaltyPointsDisplay from '@/components/pos/LoyaltyPointsDisplay.vue'
import PromotionDisplay from '@/components/pos/PromotionDisplay.vue'

// Modal Components
import CustomerModal from '@/components/pos/modals/CustomerModal.vue'
import DateModal from '@/components/pos/modals/DateModal.vue'
import DiscountModal from '@/components/pos/modals/DiscountModal.vue'
import PaymentModal from '@/components/pos/modals/PaymentModal.vue'
import EmployeeSelectionModal from '@/components/pos/modals/EmployeeSelectionModal.vue'
import EmployeeAssignmentModal from '@/components/pos/modals/EmployeeAssignmentModal.vue'
import TipsModal from '@/components/pos/modals/TipsModal.vue'
import AddEditCustomerModal from '@/components/pos/modals/AddEditCustomerModal.vue'
import BillModal from '@/components/pos/modals/BillModal.vue'
import { toast } from 'vue3-toastify';


import Frame from '@images/pos/images/frame.png'
import { useI18n } from 'vue-i18n';

// URL Encryption
import Hashids from "hashids";
// Initialize hashids for encryption/decryption
const salt = import.meta.env.VITE_HASHIDS_SALT;
const MIN_LEN = 8;
const hashids = new Hashids(salt, MIN_LEN);

// encrypt ID
const encryptID = (id) => {
    if (!id) return "";
    return hashids.encode(id);
};

// decrypt ID
const decryptID = (id) => {
    if (!id) return "";
    const decoded = hashids.decode(id);
    return decoded.length ? decoded[0] : null;
};


const { t } = useI18n();

// Page configuration
definePage({
    meta: {
        layout: 'pos',
        public: true,
    },
})

// Search Close
const handleSearchClose = () => {
    searchQuery.value = ''
}





// Initialize composables
const { handleError, notifications, dismissNotification } = useErrorHandler()
const forms = usePOSForms()
const modals = usePOSModals()
const { isProcessingPayment, paymentError, paymentSuccess, resetPaymentState } = usePOSPayment()
const route = useRoute()
const router = useRouter()

// Employee composable
const { employees, fetchEmployee } = useEmployees()

// Promotions composable
const { fetchPromotions, getOrderPromotions } = usePromotions()

// Sale edit composable
const { 
    isEditing, 
    editingSaleId, 
    originalSaleData, 
    isLoadingSaleData, 
    isInEditMode,
    loadSaleForEdit, 
    updateSale, 
    resetEditMode, 
    cancelEdit 
} = useSaleEdit()

// Loyalty points composable
const {
    customerLoyaltyPoints,
    companyLoyaltySettings,
    loyaltyPointsNeeded,
    hasEnoughLoyaltyPoints,
    canUseLoyaltyPoints,
    fetchCustomerLoyaltyPoints,
    fetchCompanyLoyaltySettings,
    calculateLoyaltyPointsNeeded,
    calculateLoyaltyPointValue,
    resetLoyaltyPoints,
    isWalkInCustomer
} = useLoyaltyPoints()

// Store initialization
const orderStore = useOrderStore()
const menuStore = useMenuStore()

// Store state - Fix property names to match what stores actually return
const {
    orderItems,
    subtotal: orderSubtotal,
    taxAmount: orderTax,
    taxBreakdown: orderTaxBreakdown,
    discountValue: orderDiscount,
    grandTotal: orderTotal,
    isLoadingOrder: orderLoading
} = storeToRefs(orderStore)

// Computed properties
const activePromotions = computed(() => getOrderPromotions())

const {
    availableCategories: menuCategories,
    filteredMenuItems,
    isLoadingMenu: menuLoading,
    selectedCategory,
    searchQuery
} = storeToRefs(menuStore)

// Local reactive state
const selectedEmployee = ref(null)
const selectedEmployeeId = ref(null)
const selectedCustomer = ref(null)
const selectedCustomerId = ref(null)
const categoryLoading = ref(false)
const globalLoading = ref(false)
const loadingMessage = ref('')
const bookingData = ref(null)


const paymentMethods = ref([
    { id: 'cash', name: 'Cash', icon: 'money-bill' },
    { id: 'card', name: 'Credit/Debit Card', icon: 'credit-card' },
    { id: 'digital', name: 'Digital Wallet', icon: 'wallet' }
])

// Add/Edit Customer Modal State
const showAddEditCustomerModal = ref(false)
const addEditCustomerMode = ref('add') // 'add' or 'edit'
const addEditCustomerData = ref(null)

// Add BillModal state
const showBillModal = ref(false)
const lastOrderData = ref(null)

// Event Handlers - Menu & Order Management
const handleCategorySelect = async (category) => {
    try {
        categoryLoading.value = true
        menuStore.setCategory(category)
    } catch (error) {
        handleError('category-select', error)
    } finally {
        categoryLoading.value = false
    }
}

const handleSearch = (query) => {
    // Handle both direct calls and event objects
    const searchValue = typeof query == 'string' ? query : query?.target?.value || ''
    menuStore.setSearchQuery(searchValue)
}

const handleItemSelect = async (item) => {
    try {
        await orderStore.addToOrder(item)
    } catch (error) {
        console.error('Error in handleItemSelect:', error)
        handleError('item-select', error)
    }
}

const handleOrderItemSelect = (index) => {
    orderStore.selectItem(index)
}

const handleItemRemove = async (itemId) => {
    try {
        const itemIndex = orderItems.value.findIndex(item => item.id == itemId)
        if (itemIndex >= 0) {
            await orderStore.removeItem(itemIndex)
        }
    } catch (error) {
        handleError('item-remove', error)
    }
}

const handleQuantityChange = async (itemId, quantity) => {
    try {
        const itemIndex = orderItems.value.findIndex(item => item.id == itemId)
        if (itemIndex >= 0) {
            await orderStore.updateItemQuantity(itemIndex, quantity)
        }
    } catch (error) {
        handleError('quantity-change', error)
    }
}

const handleDecrementQuantity = async () => {
    try {
        const selectedItem = orderStore.selectedItem
        if (selectedItem && selectedItem.isFree) {
            console.log('Cannot modify free item:', selectedItem.name)
            return
        }
        await orderStore.decrementQuantity()
    } catch (error) {
        handleError('quantity-decrement', error)
    }
}

const handleIncrementQuantity = async () => {
    try {
        const selectedItem = orderStore.selectedItem
        if (selectedItem && selectedItem.isFree) {
            console.log('Cannot modify free item:', selectedItem.name)
            return
        }
        await orderStore.incrementQuantity()
    } catch (error) {
        handleError('quantity-increment', error)
    }
}

const handleItemEditClick = () => {
    try {
        const selectedItem = orderStore.selectedItem
        if (selectedItem && selectedItem.isFree) {
            console.log('Cannot edit free item:', selectedItem.name)
            return
        }
        if (selectedItem && selectedItem.id) {
            modals.toggleEmployeeAssignmentModal({ ...selectedItem })
        } else {
            console.warn('Selected item has no id:', selectedItem)
        }
    } catch (error) {
        handleError('item-edit-open', error)
    }
}

const handleTipsClick = () => {
    try {
        const selectedItem = orderStore.selectedItem
        if (selectedItem && selectedItem.isFree) {
            console.log('Cannot edit free item:', selectedItem.name)
            return
        }
        if (selectedItem && selectedItem.id) {
            modals.toggleTipsModal({ ...selectedItem })
        } else {
            console.warn('Selected item has no id:', selectedItem)
        }
    } catch (error) {
        handleError('tips-open', error)
    }
}


const handleRemoveSelectedItem = async () => {
    try {
        const selectedItem = orderStore.selectedItem
        if (selectedItem && selectedItem.isFree) {
            console.log('Cannot remove free item:', selectedItem.name)
            return
        }
        await orderStore.removeItem()
        if (orderItems.value.length == 0) {
            bookingData.value = null
            router.push({
                path: '/pos',
            });
        }
    } catch (error) {
        handleError('item-remove', error)
    }
}

// Event Handlers - Modal Confirmations
const handleEmployeeConfirm = () => {
    modals.handleEmployeeConfirm((employee) => {
        selectedEmployee.value = employee
        orderStore.selectedEmployee = employee.name
        orderStore.selectedEmployeeId = employee.id
    })
}

const handleCustomerConfirm = async (customerData) => {
    try {
        // Set the selected customer
        selectedCustomer.value = customerData
        orderStore.selectedCustomer = customerData.name || customerData
        orderStore.selectedCustomerId = customerData.id || customerData.id
        
        // Fetch loyalty points for the selected customer
        if (customerData.id && !isWalkInCustomer(customerData)) {
            await fetchCustomerLoyaltyPoints(customerData.id)
        } else {
            resetLoyaltyPoints()
        }
        
        // Close the modal
        modals.handleCustomerConfirm()
    } catch (error) {
        handleError('customer-select', error)
    }
}

const handleCustomerClose = () => {
    modals.handleCustomerClose(forms.resetCustomerForm)
}

const handleEditCustomer = (customer) => {
    modals.handleCustomerClose();
    setTimeout(() => {
        addEditCustomerMode.value = 'edit';
        addEditCustomerData.value = customer;
        showAddEditCustomerModal.value = true;
    }, 0);
}

const handleAddCustomer = () => {
    modals.handleCustomerClose();
    setTimeout(() => {
        addEditCustomerMode.value = 'add';
        addEditCustomerData.value = null;
        showAddEditCustomerModal.value = true;
    }, 0);
}

function handleCustomerModalSaved(customer) {
    showAddEditCustomerModal.value = false
    // If a service is selected, open the employee assignment modal for that service (with price editing)
    const selectedItem = orderStore.selectedItem
    if (selectedItem && selectedItem.type == 'Service') {
        // Pass the selected item to the modal
        modals.toggleEmployeeAssignmentModal({ ...selectedItem })
    }
    // For now, just log
    console.log('Customer saved:', customer)
}

function handleCustomerModalClose() {
    showAddEditCustomerModal.value = false
}

const handleDiscountConfirm = (discountData) => {
    try {
        // The discount is already applied in the DiscountModal, so we just need to confirm and close
        modals.handleDiscountConfirm()
    } catch (error) {
        handleError('discount-apply', error)
    }
}

const handleDiscountClose = () => {
    modals.handleDiscountClose(forms.resetDiscountForm)
}

const handleItemEditConfirm = async () => {
    const result = await forms.handleItemEditSubmit(async (itemData) => {
        orderStore.addToOrder(itemData)
    })

    if (result.success) {
        modals.handleItemEditConfirm()
    } else {
        handleError('item-edit-add', result.error)
    }
}

const handleItemEditClose = () => {
    modals.handleItemEditClose(forms.resetItemEditForm)
}

const handlePaymentConfirm = async (orderData) => {
    globalLoading.value = true
    loadingMessage.value = isInEditMode.value ? 'Updating sale...' : 'Processing payment...'

    try {
        if (isInEditMode.value) {
            // Handle edit mode - the update was already done in PaymentModal
            modals.handlePaymentConfirm()
            resetPaymentState()
            bookingData.value = null
            
            // Reset to Walking-in Customer after successful payment
            await setDefaultCustomer()

            // Fetch order details using $api
            try {
                const response = await $api(`/getOrderDetails/${editingSaleId.value}`, { method: 'GET' })
                lastOrderData.value = response.data
                showBillModal.value = true
                
                // Reset order table after successful payment
                orderStore.clearOrder()

                router.push({
                    path: '/pos',
                });
            } catch (apiError) {
                handleError('fetch-order-details', apiError)
            }
        } else {
            // Handle new sale creation
            if (orderData && orderData.order_id) {
                modals.handlePaymentConfirm()
                resetPaymentState()
                bookingData.value = null
                
                // Reset to Walking-in Customer after successful payment
                await setDefaultCustomer()

                // Fetch order details using $api
                try {
                    const response = await $api(`/getOrderDetails/${orderData.order_id}`, { method: 'GET' })
                    lastOrderData.value = response.data
                    showBillModal.value = true
                    
                    // Reset order table after successful payment
                    orderStore.clearOrder()
                } catch (apiError) {
                    handleError('fetch-order-details', apiError)
                }
            } else {
                handleError('payment-process', 'Order ID not found in payment response')
            }
        }
    } catch (error) {
        handleError('payment-process', error)
    } finally {
        globalLoading.value = false
    }
}

const handlePaymentClose = () => {
    modals.handlePaymentClose(forms.resetPaymentForm)
}

// Date Modal Handlers
const handleDateClick = () => {
    try {
        modals.toggleDateModal()
    } catch (error) {
        handleError('date-open', error)
    }
}

const handleDateConfirm = (dateData) => {
    try {
        // Date is already set in the DateModal, just confirm and close
        modals.handleDateConfirm()
    } catch (error) {
        handleError('date-save', error)
    }
}

const handleDateClose = () => {
    modals.handleDateClose()
}

// Event Handlers - Order Actions
const handleSaveOrder = async () => {
    try {
        globalLoading.value = true
        loadingMessage.value = 'Saving order...'
        await orderStore.saveDraft()
    } catch (error) {
        handleError('order-save', error)
    } finally {
        globalLoading.value = false
    }
}

const handleClearOrder = async () => {
    try {
        orderStore.clearOrder()
        bookingData.value = null

        selectedCustomer.value = null
        selectedEmployee.value = null

        selectedCustomerId.value = null
        selectedEmployeeId.value = null
        orderStore.selectedEmployeeId = null

        orderStore.selectedCustomer = null
        orderStore.selectedEmployee = null

        // Reset loyalty points
        resetLoyaltyPoints()

        // Reset edit mode if in editing
        if (isInEditMode.value) {
            handleCancelEdit()
        } else {
            router.push({
                path: '/pos',
            });
        }
        
        setDefaultCustomer()
    } catch (error) {
        handleError('order-clear', error)
    }
}

const handleChargeModal = () => {
    modals.toggleChargeModal()
}

const handleChargeConfirm = async (chargeData) => {
    try {
        // Charge is already applied in the modal, just confirm
        modals.handleChargeConfirm()
    } catch (error) {
        handleError('charge-apply', error)
    }
}

const handleChargeClose = () => {
    modals.handleChargeClose()
}

const handleClearDiscount = () => {
    orderStore.clearDiscount()
}

const handleEmployeeAssignmentConfirm = (assignmentData) => {
    try {
        // Defensive: ensure itemId and employee are valid
        if (assignmentData) {
            orderStore.assignEmployeeToService(assignmentData.itemId, assignmentData.employee, assignmentData.price)
        } else {
            console.warn('Invalid assignmentData:', assignmentData)
        }
        modals.handleEmployeeAssignmentClose()
    } catch (error) {
        handleError('employee-assignment', error)
    }
}

const handleEmployeeAssignmentClose = () => {
    modals.handleEmployeeAssignmentClose()
}

const handleTipsConfirm = (tipsData) => {
    try {
        // Defensive: ensure itemId and tipsData are valid
        if (tipsData) {
            orderStore.assignTipsToService(tipsData.itemId, tipsData.employeeId, tipsData.employee, tipsData.tipsAmount)
        } else {
            console.warn('Invalid tipsData:', tipsData)
        }
        modals.handleTipsClose()
    } catch (error) {
        handleError('tips-assignment', error)
    }
}

const handleTipsClose = () => {
    modals.handleTipsClose()
}

// Handle cancel edit
const handleCancelEdit = () => {
    try {
        cancelEdit()
        router.push({ name: 'sale' })
    } catch (error) {
        handleError('cancel-edit', error)
    }
}



const loadBookingData = () => {
    if (route.query.booking_data) {
        try {
            bookingData.value = JSON.parse(route.query.booking_data)
            
            // Add booking services to order
            if (bookingData.value.services) {
                bookingData.value.services.forEach(service => {
                    const orderItem = {
                        id: service.id,
                        name: service.name,
                        sale_price: service.price,
                        sale_price: service.price,
                        price: service.price,
                        qty: service.quantity,
                        type: 'Service',
                        booking_id: bookingData.value.id,
                        booking_reference: bookingData.value.reference_no
                    }
                    orderStore.addToOrder(orderItem)
                })
            }
            // Set customer if available
            if (bookingData.value.customer) {
                selectedCustomer.value = bookingData.value.customer
                orderStore.selectedCustomer = bookingData.value.customer.name
                selectedCustomerId.value = bookingData.value.customer.id
            }
        } catch (error) {
            console.error('Error parsing booking data:', error)
        }
    }
}

// Handle payment button click
const handlePaymentClick = async () => {


    // iterate loop if exit type package return false
    for (const item of orderItems.value) {
        if (selectedCustomer.value.name == 'Walk-in Customer' && item.type == 'Package') {
            toast("Package item can't sold to walk-in customer", { type: 'error' })
            return
        }
    }
    if (orderItems.value.length == 0) {
        handleError('payment-error', 'Please add items to cart before proceeding to payment')
        return
    }
    
    // Calculate loyalty points needed if customer is not Walk-in Customer
    if (selectedCustomer.value && !isWalkInCustomer(selectedCustomer.value)) {
        await calculateLoyaltyPointsNeeded(orderTotal.value, selectedCustomer.value.id)
    }
    
    // For both new sales and edit mode, open payment modal
    openPaymentModal()
}

// When opening the payment modal, set customer_id and user_id on the payment form
const openPaymentModal = () => {
    // Enforce customer selection
    if (!selectedCustomer.value || !selectedCustomer.value.id) {
        // Show notification (using error handler)
        handleError('customer-required', 'Please select a customer before proceeding to payment.')
        return;
    }
    
    // Set customer_id from selectedCustomer
    forms.updatePaymentForm('customer_id', selectedCustomer.value.id)
    
    // Set user_id from selectedEmployee ONLY if it is a valid object with id
    if (selectedEmployee.value && typeof selectedEmployee.value == 'object' && selectedEmployee.value.id) {
        forms.updatePaymentForm('user_id', selectedEmployee.value.id)
    } else {
        forms.updatePaymentForm('user_id', null)
    }
    
    // Set order date from order store
    forms.updatePaymentForm('orderDate', orderStore.orderDate)
    
    // Set edit mode flag in payment form if in edit mode
    if (isInEditMode.value) {
        forms.updatePaymentForm('is_edit_mode', true)
        forms.updatePaymentForm('sale_id', editingSaleId.value)
    } else {
        forms.updatePaymentForm('is_edit_mode', false)
        forms.updatePaymentForm('sale_id', null)
    }
    
    modals.togglePaymentModal()
}

const saleEditId = ref(null)

// Load sale data for editing
const loadSaleDataForEdit = async (saleId) => {
    try {
        const saleData = await loadSaleForEdit(saleId)
        if (saleData) {
            // Clear current order
            orderStore.clearOrder()
            
            // Set customer
            if (saleData.customer) {
                selectedCustomer.value = saleData.customer
                orderStore.selectedCustomer = saleData.customer.name
                orderStore.selectedCustomerId = saleData.customer.id
                
                // Fetch loyalty points for the customer
                if (saleData.customer.id && !isWalkInCustomer(saleData.customer)) {
                    await fetchCustomerLoyaltyPoints(saleData.customer.id)
                }
            }
            
            // Set employee
            if (saleData.employee) {
                selectedEmployee.value = saleData.employee
                orderStore.selectedEmployee = saleData.employee.name
                orderStore.selectedEmployeeId = saleData.employee.id
            }
            
            // Add items to order
            if (saleData.items && saleData.items.length > 0) {
                for (const item of saleData.items) {
                    try {
                        await orderStore.addToOrder(item)
                    } catch (itemError) {
                        console.warn(`Failed to add item ${item.name} to order:`, itemError)
                        // Continue with other items
                    }
                }
            }
            
            // Set order date if available
            if (saleData.sale && saleData.sale.order_date) {
                orderStore.setOrderDate(saleData.sale.order_date)
            }
            
            toast('Sale loaded for editing', { type: 'success' })
        } else {
            toast('Failed to load sale data', { type: 'error' })
            // Navigate back to sale list if loading fails
            router.push({ name: 'sale' })
        }
    } catch (error) {
        handleError('load-sale-data', error)
        // Navigate back to sale list if loading fails
        router.push({ name: 'sale' })
    }
}

// Lifecycle
onMounted(async () => {
    if(route.query.id) {
        saleEditId.value = route.query.id
    }
    try {
        // Initialize stores
        const initPromises = [menuStore.initializeMenu()]

        // Initialize order store
        if (typeof orderStore.initializeOrder == 'function') {
            initPromises.push(orderStore.initializeOrder())
        } else {
            // Fallback initialization
            orderStore.loadFromLocalStorage()
            orderStore.clearErrors()
        }

        // Fetch employees
        await fetchEmployee()

        // Fetch promotions
        await fetchPromotions()

        // Fetch company loyalty settings
        await fetchCompanyLoyaltySettings()

        await Promise.all(initPromises)

        if (employees.value.length > 0) {
            selectedEmployee.value = null
            orderStore.selectedEmployee = null
        }

        // Set Walking-in Customer as default
        await setDefaultCustomer()

        // Initialize payment form with order total if method exists
        if (typeof forms.updatePaymentForm == 'function') {
            forms.updatePaymentForm('amount', orderTotal.value)
        }

        // Load booking data if available
        loadBookingData()

        // Load sale data for editing if sale ID is provided
        if (saleEditId.value) {
            await loadSaleDataForEdit(saleEditId.value)
        }

    } catch (error) {
        handleError('pos-init', error)
    }
})

// Function to set Walking-in Customer as default
const setDefaultCustomer = async () => {
    try {
        // Fetch customers to find Walking-in Customer
        const response = await $api('/get-all-customers-pos')
        if (response.success && response.data) {
            const walkInCustomer = response.data.find(customer => 
                customer.name == 'Walk-in Customer' || customer.name == 'Walking-in Customer'
            )
            
            if (walkInCustomer) {
                selectedCustomer.value = walkInCustomer
                orderStore.selectedCustomer = walkInCustomer.name
                orderStore.selectedCustomerId = walkInCustomer.id
            }
        }
    } catch (error) {
        console.warn('Failed to set default customer:', error)
    }
}


// Modile Cart Active Hide
// const activePanel = ref('order')

// function handleCartClick() {
//     activePanel.value = 'order'
// }
// function handleProductGridClick() {
//     activePanel.value = 'menu'
// }



const activePanel = ref('order')   // default section
const isMobile = ref(window.innerWidth < 991) // check initial size

// Handle resize
function handleResize() {
  isMobile.value = window.innerWidth < 991
}

// Attach/detach resize listener
onMounted(() => {
  window.addEventListener('resize', handleResize)
})
onUnmounted(() => {
  window.removeEventListener('resize', handleResize)
})

function handleCartClick() {
  if (isMobile.value) {
    activePanel.value = 'order'
  }
}

function handleProductGridClick() {
  if (isMobile.value) {
    activePanel.value = 'menu'
  }
}


const handleBackButton = async () => {
    if (route.path == "/pos") {
        await router.push("/dashboard");
        window.location.reload();
    }
};

onMounted(() => {
    window.addEventListener("popstate", handleBackButton);
});

onBeforeUnmount(() => {
    window.removeEventListener("popstate", handleBackButton);
});


</script>

<style scoped>
/* Add styles for disabled buttons */
.btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    pointer-events: none;
}

.btn:disabled:hover {
    transform: none;
    box-shadow: none;
}

/* Edit mode indicator styles */
.edit-mode-indicator {
    position: sticky;
    top: 0;
    z-index: 1000;
    margin-bottom: 1rem;
}

.edit-mode-indicator .alert {
    border-radius: 8px;
    border: 2px solid #ffc107;
    background-color: #fff3cd;
    color: #856404;
}

.edit-mode-indicator .btn-outline-warning {
    border-color: #ffc107;
    color: #856404;
}

.edit-mode-indicator .btn-outline-warning:hover {
    background-color: #ffc107;
    color: #000;
}
</style>
