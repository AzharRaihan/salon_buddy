<template>
    <div v-if="show" class="common-modal select-modal show">
        <div class="modal-content">
            <div class="modal-header">
                <h4>{{ t('Tips') }}</h4>
                <button class="close-modal" @click="handleClose">
                    <VIcon icon="tabler-x" />
                </button>
            </div>
            <div class="modal-body tips-modal-body">
                <h4>{{ getItemName(props.item) }}</h4>
                <div class="mt-1 text-muted mb-3">
                    <p>{{ t('Current Price') }}: {{ formatPrice(getItemPrice(props.item)) }}</p>
                </div>
                
                <!-- Tips Section -->
                <div class="tips-section" v-if="getItemType(props.item) == 'Service'">
                    <!-- Employee Selection -->
                    <div class="employee-selection mb-3" v-if="props.employees && props.employees.length > 0">

                        <!-- I've getting  in option object object now fix the error -->
                        <label class="form-label">{{ t('Select Employee') }} <span class="required-star-2">*</span></label>
                        <AppAutocomplete
                            :model-value="selectedEmployeeId"
                            @update:model-value="(value) => selectedEmployeeId = value"
                            :items="props.employees"
                            :item-title="item => `${item.name}  ${ item.phone ? `(${item.phone})` : ''}`"
                            item-value="id"
                            :placeholder="t('Select Employee')"
                            :error-messages="employeeIdError"
                            @change="onEmployeeChange"
                        />
                        <!-- <label class="form-label">{{ t('Select Employee') }}</label>
                        <select 
                            class="form-control" 
                            v-model="selectedEmployeeId"
                            @change="onEmployeeChange"
                        >
                            <option value="">{{ t('Choose an employee...') }}</option>
                            <option 
                                v-for="employee in props.employees" 
                                :key="employee.id"
                                :value="employee.id"
                            >
                                {{ employee.name }}
                            </option>
                        </select> -->
                    </div>

                    <!-- Tips Amount Input -->
                    <div class="tips-amount mb-3">
                        <label class="form-label">{{ t('Tips Amount') }} <span class="required-star-2">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">{{ currencySymbol }}</span>
                            <input
                                type="number"
                                class="form-control"
                                v-model.number="tipsAmount"
                                min="0"
                                step="0.01"
                                :placeholder="t('Enter tips amount')"
                            />
                        </div>
                        <small class="form-text text-muted">
                            {{ t('This tip will be added to the selected employee') }}
                        </small>
                    </div>

                    <!-- Quick Tips Buttons -->
                    <div class="quick-tips mb-3">
                        <label class="form-label">{{ t('Quick Tips') }}</label>
                        <div class="quick-tips-buttons">
                            <button 
                                v-for="percentage in quickTipsPercentages" 
                                :key="percentage"
                                class="btn btn-outline-primary btn-sm me-2 mb-2"
                                @click="setQuickTip(percentage)"
                            >
                                {{ percentage }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Non-Service Item Message -->
                <div v-else class="alert alert-warning">
                    <VIcon icon="tabler-info-circle" class="me-2" />
                    {{ t('Tips can only be added to service type items') }}
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-danger" @click="handleClose">
                    <VIcon icon="tabler-x" />
                    {{ t('Cancel') }}
                </button>
                <button 
                    class="btn btn-primary" 
                    @click="handleConfirm"
                    :disabled="!canConfirm"
                >
                    <VIcon icon="tabler-coin" />
                    {{ t('Add Tips') }}
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useErrorHandler } from '@/composables/useErrorHandler'
import { unref } from 'vue'
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

// Props
const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    item: {
        type: Object,
        default: null
    },
    employees: {
        type: Array,
        default: () => []
    }
})

const emit = defineEmits(['confirm', 'close'])
const { handleError } = useErrorHandler()

// Helper functions
const getItemPrice = (item) => {
    const itemValue = unref(item);
    if (!itemValue) return 0
    return (
        itemValue.sale_price ||
        itemValue.price ||
        0
    )
}

const getItemName = (item) => {
    const itemValue = unref(item);
    if (!itemValue) return ''
    return (
        itemValue.name || ''
    )
}

const getItemType = (item) => {
    const itemValue = unref(item);
    if (!itemValue) return ''
    return (
        itemValue.type || ''
    )
}

// Reactive state
const loading = ref(false)
const selectedEmployeeId = ref(null)
const selectedEmployee = ref(null)
const tipsAmount = ref(0)
const currencySymbol = ref('$') // You can make this dynamic based on company settings

// Quick tips percentages
const quickTipsPercentages = [5, 10, 20, 30, 50, 100]

// Computed properties
const canConfirm = computed(() => {
    return selectedEmployeeId.value && tipsAmount.value > 0 && getItemType(props.item) === 'Service'
})

// Methods
const formatPrice = (price) => {
    return parseFloat(price || 0).toFixed(2)
}

const onEmployeeChange = () => {
    const employee = props.employees.find(emp => emp.id == selectedEmployeeId.value)
    selectedEmployee.value = employee || null
}

const setQuickTip = (percentage) => {
    tipsAmount.value = (percentage).toFixed(2)
}

const handleConfirm = async () => {
    if (!canConfirm.value) return
    
    try {
        loading.value = true
        const itemValue = unref(props.item)
        emit('confirm', {
            itemId: itemValue?.id,
            employeeId: selectedEmployeeId.value,
            employee: selectedEmployee.value,
            tipsAmount: tipsAmount.value
        })
    } catch (error) {
        handleError('tips-assignment', error)
    } finally {
        loading.value = false
    }
}

const handleClose = () => {
    // Reset form
    selectedEmployeeId.value = null
    selectedEmployee.value = null
    tipsAmount.value = 0
    emit('close')
}

// Watch for modal visibility
watch(() => props.show, (newValue) => {
    if (newValue) {
        // Check if item already has tips assigned
        const itemValue = unref(props.item)
        if (itemValue && itemValue.tips) {
            // Pre-populate with existing tip data
            selectedEmployeeId.value = itemValue.tips.employeeId
            selectedEmployee.value = itemValue.tips.employee
            tipsAmount.value = parseFloat(itemValue.tips.amount) || 0
        } else {
            // Reset form when modal opens for new tips
            selectedEmployeeId.value = null
            selectedEmployee.value = null
            tipsAmount.value = 0
        }
    }
})

// Watch for item changes
watch(() => props.item, (newItem) => {
    if (newItem) {
        // Check if new item already has tips assigned
        const itemValue = unref(newItem)
        if (itemValue && itemValue.tips) {
            // Pre-populate with existing tip data
            selectedEmployeeId.value = itemValue.tips.employeeId
            selectedEmployee.value = itemValue.tips.employee
            tipsAmount.value = parseFloat(itemValue.tips.amount) || 0
        } else {
            // Reset form when item changes
            selectedEmployeeId.value = null
            selectedEmployee.value = null
            tipsAmount.value = 0
        }
    }
})
</script>

<style scoped>
.tips-modal-body {
    max-height: 70vh;
    overflow-y: auto;
}

.tips-section h5 {
    color: #007bff;
    margin-bottom: 1rem;
}

.quick-tips-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.quick-tips-buttons .btn {
    min-width: 60px;
}

.tips-summary .alert {
    border-left: 4px solid #007bff;
}

.form-label {
    font-size: 20px;
    font-weight: 600;
    margin-bottom: 8px;
}

.input-group-text {
    background-color: #f8f9fa;
    border-color: #ced4da;
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

@media (max-width: 768px) {
    .quick-tips-buttons {
        justify-content: center;
    }
}
.required-star-2 {
    font-weight: 600;
    font-size: 18px;
    color: red;
}
</style>
