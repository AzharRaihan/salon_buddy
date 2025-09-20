<template>
    <div v-if="show" class="payment-modal-overlay">
        <div class="payment-modal-content">
            <div class="payment-modal-header">
                <h3>{{ props.formData.is_edit_mode ? t('Update Sale & Payment') : t('Process Payment') }}</h3>
                <button class="close-btn" @click="handleClose">
                    <VIcon icon="tabler-x" />
                </button>
            </div>

            <div class="payment-modal-body">
                <!-- Order Summary -->
                <div class="payment-order-summary">
                    <h4>{{ t('Order Summary') }}</h4>
                    
                    <!-- Order Totals -->
                    <div class="order-totals">
                        <div class="summary-row">
                            <span class="label">{{ t('Subtotal') }}</span>
                            <span class="value">{{ formatNumberInvoice(orderSubtotal) }}</span>
                        </div>
                        <div class="summary-row" v-if="orderDiscount > 0">
                            <span class="label">{{ t('Discount') }}</span>
                            <span class="value">{{ formatNumberInvoice(orderDiscount) }}</span>
                        </div>
                        <div class="summary-row">
                            <span class="label">{{ t('Tax') }}</span>
                            <span class="value">{{ formatNumberInvoice(orderTax) }}</span>
                        </div>
                        <div class="summary-row total">
                            <span class="label">{{ t('Total Payable') }}</span>
                            <span class="value">{{ formatAmount(orderTotal) }}</span>
                        </div>
                    </div>
                </div>

                <div class="payment-amount-wrap">
                    <!-- Paid Amount -->
                    <div class="payment-amount-section">
                        <h4>{{ t('Paid Amount') }}</h4>
                        <div class="amount-input-group">
                            <div class="amount-display">
                                <input v-model="paidAmount" type="number" step="0.01" min="0" :max="totalWithTip" class="amount-input" placeholder="0.00"  @focus="$event.target.select()" />
                            </div>
                        </div>
                    </div>

                    <!-- Due Amount -->
                    <div class="payment-amount-section">
                        <h4>{{ t('Due Amount') }}</h4>
                        <div class="amount-input-group">
                            <div class="amount-display">
                                <input :value="dueAmountDisplay" type="number" class="amount-input" placeholder="0.00" disabled />
                            </div>
                        </div>
                    </div>
                </div>
                

                <!-- Payment Method Selection -->
                <div class="payment-method-section">
                    <h4>{{ t('Payment Method') }}</h4>
                    <div v-if="isLoadingPaymentMethods" class="loading-payment-methods">
                        <div class="spinner-border spinner-border-sm" role="status">
                            <span class="visually-hidden">{{ t('Loading...') }}</span>
                        </div>
                        {{ t('Loading payment methods...') }}
                    </div>
                    <div v-else class="payment-methods">
                        <div v-for="method in availablePaymentMethods" :key="method.id" class="payment-method"
                            :class="{ 'selected': selectedMethodId === method.id }"
                            @click="selectPaymentMethod(method.id)">
                        <!-- <div v-for="method in availablePaymentMethods" :key="method.id" class="payment-method"
                            @click="selectPaymentMethod(method.id)"> -->
                            <div class="method-icon">
                                <img :src="method.payment_method_icon_url" :alt="method.name" class="img-fluid">
                            </div>
                            <span class="method-name">{{ method.name }}</span>
                        </div>
                    </div>
                </div>

                <!-- Loyalty Points Information -->
                <div v-if="selectedPaymentMethod && selectedPaymentMethod.account_type === 'Loyalty Point'" class="loyalty-points-section">
                    <h4>{{ t('Loyalty Points') }}</h4>
                    <div class="loyalty-points-info">
                        <div class="loyalty-points-row">
                            <span class="label">{{ t('Current Points') }}:</span>
                            <span class="value">{{ customerLoyaltyPoints }}</span>
                        </div>
                        <div class="loyalty-points-row">
                            <span class="label">{{ t('Points Needed') }}:</span>
                            <span class="value">{{ loyaltyPointsNeeded }}</span>
                        </div>
                        <div v-if="!hasEnoughLoyaltyPoints" class="loyalty-points-warning">
                            <VIcon icon="tabler-alert-circle" class="warning-icon" />
                            <span>{{ t('Insufficient loyalty points for this purchase') }}</span>
                        </div>
                        <div v-else class="loyalty-points-success">
                            <VIcon icon="tabler-check-circle" class="success-icon" />
                            <span>{{ t('Enough loyalty points available') }}</span>
                        </div>
                    </div>
                </div>
                
                <!-- Card Details (for card payment) -->
                <div v-if="formData.method === 'card'" class="card-details-section">
                    <h4>{{ t('Card Details') }}</h4>
                    <div class="card-form">
                        <div class="form-group">
                            <label>{{ t('Card Number') }}</label>
                            <input v-model="cardDetails.number" type="text" placeholder="1234 5678 9012 3456"
                                maxlength="19" @input="formatCardNumber" class="card-input" />
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>{{ t('Expiry') }}</label>
                                <input v-model="cardDetails.expiry" type="text" placeholder="MM/YY" maxlength="5"
                                    @input="formatExpiry" class="card-input" />
                            </div>
                            <div class="form-group">
                                <label>{{ t('CVV') }}</label>
                                <input v-model="cardDetails.cvv" type="text" placeholder="123" maxlength="4"
                                    class="card-input" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label>{{ t('Cardholder Name') }}</label>
                            <input v-model="cardDetails.name" type="text" placeholder="John Doe" class="card-input" />
                        </div>
                    </div>
                </div>


                <!-- Change Amount (for cash) -->
                <div v-if="formData.method === 'cash' && showChangeAmount" class="change-section">
                    <div class="change-display">
                        <span class="change-label">Change Due:</span>
                        <span class="change-amount">${{ changeAmount.toFixed(2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Modal Actions -->
            <div class="payment-modal-footer">
                <div class="notification d-flex align-items-center gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <VCheckbox v-model="sendSMS" :label="t('SMS')" />
                        <VCheckbox v-model="sendEmail" :label="t('Email')" />
                        <VCheckbox v-model="sendWhatsapp" :label="t('Whatsapp Message')" />
                    </div>
                </div>
                <button class="btn btn-danger" @click="handleClose">
                    <VIcon icon="tabler-x" />
                    {{ t('Cancel') }}
                </button>
                <button class="btn btn-primary" @click="handleConfirm" :disabled="!isFormValid || loading">
                    <VIcon icon="tabler-credit-card-pay" />
                    {{ loading ? t('Processing...') : (props.formData.is_edit_mode ? t('Update Sale') : t('Process Payment')) }}
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, reactive, watch, onMounted } from 'vue'
import { useErrorHandler } from '@/composables/useErrorHandler'
import { usePOSPayment } from '@/composables/pos/usePOSPayment'
import { useTaxCalculation } from '@/composables/useTaxCalculation'
import { useLoyaltyPoints } from '@/composables/pos/useLoyaltyPoints'
import { $api } from '@/utils/api'
import { useRouter } from 'vue-router';
import { useCompanyFormatters } from '@/composables/useCompanyFormatters';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

const { t } = useI18n();

const router = useRouter();
const { formatAmount, formatNumber,  formatNumberInvoice} = useCompanyFormatters()



// Props
const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    formData: {
        type: Object,
        required: true
    },
    orderTotal: {
        type: Number,
        required: true
    },
    orderSubtotal: {
        type: Number,
        required: true
    },
    orderTax: {
        type: Number,
        required: true
    },
    orderDiscount: {
        type: Number,
        default: 0
    },
    promotionDiscount: {
        type: Number,
        default: 0
    },
    orderTaxBreakdown: {
        type: Object,
        default: () => ({})
    },
    orderItems: {
        type: Array,
        default: () => []
    },
    bookingData: {
        type: Object,
        default: null
    },
    selectedCustomer: {
        type: Object,
        default: null
    }
})

// Emits
const emit = defineEmits(['confirm', 'close'])
const branch_info = useCookie("branch_info").value || 0;

// Composables
const { handleError } = useErrorHandler()
const { isProcessingPayment, paymentError, paymentSuccess, processPOSPayment, resetPaymentState } = usePOSPayment()
const { fetchCustomerInfo, getCustomerState, updateCustomerAndRecalculateTax } = useTaxCalculation()
const { 
    customerLoyaltyPoints, 
    loyaltyPointsNeeded, 
    hasEnoughLoyaltyPoints, 
    calculateLoyaltyPointsNeeded, 
    isWalkInCustomer 
} = useLoyaltyPoints()

// Reactive state
const loading = ref(false)
const selectedTip = ref(0)
const customTipAmount = ref('')
const availablePaymentMethods = ref([])
const isLoadingPaymentMethods = ref(false)
const sendSMS = ref(false)
const sendEmail = ref(false)
const sendWhatsapp = ref(false)
const paidAmount = ref(0)

// Card details for card payments
const cardDetails = reactive({
    number: '',
    expiry: '',
    cvv: '',
    name: ''
})


// Computed properties
const totalWithTip = computed(() => {
    const tipAmount = selectedTip.value === 'custom'
        ? parseFloat(customTipAmount.value) || 0
        : props.orderTotal * selectedTip.value
    return props.orderTotal + tipAmount
})

const changeAmount = computed(() => {
    const paid = parseFloat(props.formData.amount) || 0
    return Math.max(0, paid - totalWithTip.value)
})

const showChangeAmount = computed(() => {
    return props.formData.method === 'cash' &&
        parseFloat(props.formData.amount) > totalWithTip.value
})

const isFormValid = computed(() => {
    
    // if (!props.formData.method) return false
    // if (!props.formData.amount || parseFloat(props.formData.amount) <= 0) return false

    if ((props.formData.amount || parseFloat(props.formData.amount) > 0) && !selectedMethodId.value) {
        return false
    } 

    // Check loyalty points validation
    const selectedMethod = availablePaymentMethods.value.find(method => method.id == props.formData.method)
    if (selectedMethod && selectedMethod.account_type === 'Loyalty Point') {
        const customerId = props.formData.customer_id || (props.selectedCustomer && props.selectedCustomer.id)
        if (!customerId || isWalkInCustomer(props.selectedCustomer)) {
            return false
        }
        if (!hasEnoughLoyaltyPoints.value) {
            return false
        }
    }

    if (props.formData.method === 'card') {
        return cardDetails.number && cardDetails.expiry && cardDetails.cvv && cardDetails.name
    }

    return true
})

// Add computed for dueAmountDisplay
const dueAmountDisplay = computed(() => {
    const paid = parseFloat(props.formData.amount) || 0;
    const due = totalWithTip.value - paid;
    return due > 0 ? due.toFixed(2) : '0.00';
});

// Add computed for selectedPaymentMethod
const selectedPaymentMethod = computed(() => {
    return availablePaymentMethods.value.find(method => method.id == props.formData.method)
});

// Keep paidAmount and formData.amount in sync
watch(paidAmount, (newVal) => {
  props.formData.amount = parseFloat(newVal) || 0
})

watch(() => props.formData.amount, (newVal) => {
  paidAmount.value = parseFloat(newVal) || 0
})

watch(() => props.show, async (newValue) => {
  if (newValue) {
    // When modal opens, set Paid = Total, Due = 0
    props.formData.amount = totalWithTip.value.toFixed(2)
    paidAmount.value = totalWithTip.value.toFixed(2)
    fetchPaymentMethods()
    
    // Fetch customer information if customer is selected
    if (props.selectedCustomer && props.selectedCustomer.id) {
      await updateCustomerAndRecalculateTax(props.selectedCustomer.id)
    } else if (props.formData.customer_id) {
      await updateCustomerAndRecalculateTax(props.formData.customer_id)
    } else {
      console.log('No customer selected, tax will default to Same state')
    }
  }
})

// Watch for changes in selectedCustomer prop
watch(() => props.selectedCustomer, async (newCustomer) => {
  if (newCustomer && newCustomer.id) {
    await updateCustomerAndRecalculateTax(newCustomer.id)
  }
}, { deep: true })

// Watch for changes in formData.customer_id
watch(() => props.formData.customer_id, async (newCustomerId) => {
  if (newCustomerId) {
    await updateCustomerAndRecalculateTax(newCustomerId)
  }
})

const fetchPaymentMethods = async () => {
    try {
        isLoadingPaymentMethods.value = true
        const response = await $api('/get-all-payment-methods-pos', {
            method: 'GET'
        })
        
        if (response.success) {
            availablePaymentMethods.value = response.data || []
        }
    } catch (error) {
        console.error('Failed to fetch payment methods:', error)
    } finally {
        isLoadingPaymentMethods.value = false
    }
}

const selectedMethodId = ref(null);

const selectPaymentMethod = async (methodId) => {
    props.formData.method = methodId

    selectedMethodId.value = methodId;
    
    // Check if this is a loyalty point payment method
    const selectedMethod = availablePaymentMethods.value.find(method => method.id == methodId)
    if (selectedMethod && selectedMethod.account_type === 'Loyalty Point') {
        // Validate loyalty points for this customer
        const customerId = props.formData.customer_id || (props.selectedCustomer && props.selectedCustomer.id)
        if (customerId && !isWalkInCustomer(props.selectedCustomer)) {
            await calculateLoyaltyPointsNeeded(totalWithTip.value, customerId)
            
            if (!hasEnoughLoyaltyPoints.value) {
                handleError('loyalty-points', 'Insufficient loyalty points for this purchase')
                return
            }
        } else {
            handleError('loyalty-points', 'Loyalty points cannot be used for Walk-in Customers')
            return
        }
    }
    
    // if (!props.formData.amount) {
    //     props.formData.amount = totalWithTip.value.toFixed(2)
    // }
}

const formatCardNumber = (event) => {
    let value = event.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '')
    let matches = value.match(/\d{4,16}/g)
    let match = matches && matches[0] || ''
    let parts = []

    for (let i = 0, len = match.length; i < len; i += 4) {
        parts.push(match.substring(i, i + 4))
    }

    if (parts.length) {
        cardDetails.number = parts.join(' ')
    } else {
        cardDetails.number = value
    }
}

const formatExpiry = (event) => {
    let value = event.target.value.replace(/\D/g, '')
    if (value.length >= 2) {
        value = value.substring(0, 2) + '/' + value.substring(2, 4)
    }
    cardDetails.expiry = value
}

const handleConfirm = async () => {
    if (!isFormValid.value) return

    if (isWalkInCustomer(props.selectedCustomer) && dueAmountDisplay.value > 0) {
        toast('Due sale not allow for Walk-in customer', { type: 'error' })
        return false
    }

    // Additional loyalty points validation
    const selectedMethod = availablePaymentMethods.value.find(method => method.id == props.formData.method)
    if (selectedMethod && selectedMethod.account_type === 'Loyalty Point') {
        const customerId = props.formData.customer_id || (props.selectedCustomer && props.selectedCustomer.id)
        if (!customerId || isWalkInCustomer(props.selectedCustomer)) {
            handleError('loyalty-points', 'Loyalty points cannot be used for Walk-in Customers')
            return
        }
        
        await calculateLoyaltyPointsNeeded(totalWithTip.value, customerId)
        if (!hasEnoughLoyaltyPoints.value) {
            handleError('loyalty-points', 'Insufficient loyalty points for this purchase')
            return
        }
    }


    try {
        loading.value = true

        // Prepare order data for saving to sales table
        const orderData = {
            items: props.orderItems.map(item => ({
                id: item.id,
                qty: item.qty,
                price: item.price,
                employee_id: item.assignedEmployee ? item.assignedEmployee.id : null,
                is_free: item.isFree ? 'Yes' : 'No',
                promotion_id: item.promotionId || null,
                promotion_discount: item.discount || 0
            })),
            send_sms: sendSMS.value,
            send_email: sendEmail.value,
            send_whatsapp: sendWhatsapp.value,
            subtotal: props.orderSubtotal,
            tax: props.orderTax,
            tax_breakdown: props.orderTaxBreakdown || {},
            discount: props.orderDiscount,
            promotionDiscount: props.promotionDiscount || 0,
            total: totalWithTip.value,
            payment_method_id: selectedMethodId.value,
            branch_id: branch_info.id,
            payment_amount: parseFloat(props.formData.amount),
            due_amount: parseFloat(dueAmountDisplay.value),
            customer_id: props.formData.customer_id || (props.selectedCustomer && props.selectedCustomer.id) || null,
            user_id: props.formData.user_id || null,
            order_date: props.formData.orderDate || new Date().toISOString().split('T')[0] // Add order date
        }
        
        // Attach booking_id/reference if present
        if (props.formData.booking_id) {
            orderData.booking_id = props.formData.booking_id;
        } else if (props.bookingData && props.bookingData.id) {
            orderData.booking_id = props.bookingData.id;
        }

                // Check if we're in edit mode
        if (props.formData.is_edit_mode && props.formData.sale_id) {
            // For edit mode, update existing sale
            orderData.sale_id = props.formData.sale_id
            
            // Update existing sale (payment processing is handled by the backend)
            const response = await $api(`/update-order/${props.formData.sale_id}`, {
                method: 'PUT',
                body: JSON.stringify(orderData)
            })

            if (response.success) {
                emit('confirm', response.data)
                selectedMethodId.value = null;
                sendSMS.value = false
                sendEmail.value = false
                sendWhatsapp.value = false
            } else {
                throw new Error(response.message || 'Failed to update sale')
            }
        } else {
            // For new sales, create new sale
            // Process payment first
            let paymentResult = {};
            if(selectedMethodId.value) {
                paymentResult = await processPOSPayment(
                    props.formData.method, 
                        totalWithTip.value, 
                        orderData
                    )
            } else {
                paymentResult = {
                    success: true,
                };
            }

            if (paymentResult.success) {

                // Add transaction_id to orderData if available
                if (paymentResult.transaction_id) {
                    orderData.transaction_id = paymentResult.transaction_id
                }

                // Save order to sales and sale_details tables
                const response = await $api('/save-order', {
                    method: 'POST',
                    body: orderData
                })

                if (response.success) {
                    emit('confirm', response.data)
                    router.push({
                        path: '/pos'
                    });
                    selectedMethodId.value = null;
                    sendSMS.value = false
                    sendEmail.value = false
                    sendWhatsapp.value = false
                } else {
                    throw new Error(response.message || 'Failed to save order')
                }
            } else {
                throw new Error(paymentResult.message || 'Payment processing failed')
            }
        }
    } catch (error) {
        handleError('process-payment', error)
    } finally {
        loading.value = false
    }
}

const handleClose = () => {
    // Reset form
    selectedTip.value = 0
    customTipAmount.value = ''
    Object.keys(cardDetails).forEach(key => {
        cardDetails[key] = ''
    })
    resetPaymentState()

    emit('close')
}

// Watch for changes in tip amount
watch([selectedTip, customTipAmount], () => {
    if (selectedTip.value !== 'custom') {
        customTipAmount.value = ''
    }
})



// Lifecycle
onMounted(async () => {
    fetchPaymentMethods()
})
</script>

<style scoped>
.loyalty-points-section {
    margin-top: 1rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}
.loyalty-points-info {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}
.loyalty-points-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.25rem 0;
}
.loyalty-points-row .label {
    font-weight: 500;
    color: #6c757d;
}
.loyalty-points-row .value {
    font-weight: 600;
    color: #495057;
}
.loyalty-points-warning {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem;
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    border-radius: 4px;
    color: rgba(133, 101, 4, 0.255);
    margin-top: 0.5rem;
}
.loyalty-points-success {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem;
    background: #d1edff;
    border: 1px solid #bee5eb;
    border-radius: 4px;
    color: #27ae5f3c;
    margin-top: 0.5rem;
}
.warning-icon {
    color: #f39c12;
}
.success-icon {
    color: #27ae60;
}
</style>
