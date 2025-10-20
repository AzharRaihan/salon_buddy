<template>
    <div v-if="show" class="common-modal select-modal show">
        <div class="modal-content">
            <div class="modal-header">
                <h4>{{ t('Distribute Tips') }}</h4>
                <button class="close-modal" @click="handleClose">
                    <VIcon icon="tabler-x" />
                </button>
            </div>
            <div class="modal-body tips-distribution-modal-body">
                <div class="tips-input-section mb-4">
                    <label class="form-label">{{ t('Total Tips Amount') }} <span class="required-star-2">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">{{ currencySymbol }}</span>
                        <input
                            type="number"
                            class="form-control"
                            v-model.number="totalTipsAmount"
                            min="0"
                            step="0.01"
                            :placeholder="t('Enter total tips amount')"
                            @input="calculateDistribution"
                            @focus="$event.target.select()"
                        />
                    </div>
                    <small v-if="tipsError" class="text-danger">{{ tipsError }}</small>
                </div>

                <!-- Employee Distribution Display -->
                <div v-if="assignedEmployees.length > 0 && totalTipsAmount > 0" class="distribution-section">
                    <h5 class="mb-3">{{ t('Tips Distribution') }}</h5>
                    <div class="alert alert-info mb-3">
                        <VIcon icon="tabler-info-circle" class="me-2" />
                        {{ t('Tips will be distributed equally among') }} {{ assignedEmployees.length }} {{ t('employee(s)') }}
                    </div>
                    
                    <div class="employee-tips-list">
                        <div 
                            v-for="employee in assignedEmployees" 
                            :key="employee.id"
                            class="employee-tips-item"
                        >
                            <div class="employee-info">
                                <div class="employee-avatar">
                                    <VIcon icon="tabler-user" />
                                </div>
                                <div class="employee-details">
                                    <div class="employee-name">{{ employee.name }}</div>
                                    <div class="employee-meta text-muted">
                                        <span v-if="employee.phone">{{ employee.phone }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="tips-amount">
                                <span class="amount-value">{{ currencySymbol }}{{ employee.tipsAmount }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Distribution Status -->
                    <div v-if="!isDistributionValid" class="alert alert-danger mt-3">
                        <VIcon icon="tabler-alert-triangle" class="me-2" />
                        {{ t('Tips cannot be distributed equally. Please adjust the amount.') }}
                        <br>
                        <small>{{ t('Total') }}: {{ currencySymbol }}{{ totalTipsAmount }} | {{ t('Distributed') }}: {{ currencySymbol }}{{ totalDistributed }}</small>
                    </div>
                    <div v-else class="alert alert-success mt-3">
                        <VIcon icon="tabler-check" class="me-2" />
                        {{ t('Tips distribution is valid') }}
                    </div>
                </div>

                <!-- No Employees Assigned -->
                <div v-else-if="assignedEmployees.length === 0" class="alert alert-warning">
                    <VIcon icon="tabler-alert-circle" class="me-2" />
                    {{ t('No employees are assigned to services in this order. Tips will be added without distribution.') }}
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
                    <VIcon icon="tabler-check" />
                    {{ t('Add Tips') }}
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

// Props
const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    items: {
        type: Array,
        default: () => []
    },
    currentTips: {
        type: Number,
        default: 0
    }
})

const emit = defineEmits(['confirm', 'close'])

// Reactive state
const totalTipsAmount = ref(0)
const tipsError = ref('')
const currencySymbol = ref('$')

// Computed properties
const assignedEmployees = computed(() => {
    const employeeMap = new Map()
    
    props.items.forEach(item => {
        // Only process Service type items
        if (item.type === 'Service') {
            let employeeId = null
            let employeeName = ''
            let employeePhone = ''

            // Check for employee assignment from tips
            if (item.tips && typeof item.tips === 'object' && item.tips.employeeId) {
                employeeId = item.tips.employeeId
                employeeName = item.tips.employee?.name || `Employee ${employeeId}`
                employeePhone = item.tips.employee?.phone || ''
            }
            // Check for direct employee_id assignment
            else if (item.employee_id) {
                employeeId = item.employee_id
                employeeName = item.employee_name || item.assignedEmployee?.name || `Employee ${employeeId}`
                employeePhone = item.assignedEmployee?.phone || ''
            }
            // Check for assignedEmployee object
            else if (item.assignedEmployee && item.assignedEmployee.id) {
                employeeId = item.assignedEmployee.id
                employeeName = item.assignedEmployee.name || `Employee ${employeeId}`
                employeePhone = item.assignedEmployee.phone || ''
            }

            if (employeeId) {
                if (!employeeMap.has(employeeId)) {
                    employeeMap.set(employeeId, {
                        id: employeeId,
                        name: employeeName,
                        phone: employeePhone,
                        tipsAmount: 0
                    })
                }
            }
        }
    })
    
    return Array.from(employeeMap.values())
})

const totalDistributed = computed(() => {
    return assignedEmployees.value.reduce((sum, emp) => sum + parseFloat(emp.tipsAmount || 0), 0).toFixed(2)
})

const isDistributionValid = computed(() => {
    if (assignedEmployees.value.length === 0) return true // No distribution needed
    if (totalTipsAmount.value <= 0) return true // No tips to distribute
    
    return parseFloat(totalDistributed.value) === parseFloat(totalTipsAmount.value.toFixed(2))
})

const canConfirm = computed(() => {
    if (!totalTipsAmount.value || totalTipsAmount.value <= 0) {
        return false
    }
    
    // If there are assigned employees, distribution must be valid
    if (assignedEmployees.value.length > 0 && !isDistributionValid.value) {
        return false
    }
    
    return true
})

// Methods
const calculateDistribution = () => {
    tipsError.value = ''
    
    if (assignedEmployees.value.length === 0) return
    
    if (totalTipsAmount.value > 0) {
        const perEmployee = (totalTipsAmount.value / assignedEmployees.value.length).toFixed(2)
        
        assignedEmployees.value.forEach(employee => {
            employee.tipsAmount = perEmployee
        })
        
        // Adjust for rounding errors
        const distributed = parseFloat(totalDistributed.value)
        const target = parseFloat(totalTipsAmount.value.toFixed(2))
        
        if (Math.abs(distributed - target) > 0.01) {
            tipsError.value = t('Amount cannot be distributed equally')
        }
    } else {
        assignedEmployees.value.forEach(employee => {
            employee.tipsAmount = 0
        })
    }
}

const handleConfirm = () => {
    if (!canConfirm.value) {
        if (!totalTipsAmount.value || totalTipsAmount.value <= 0) {
            tipsError.value = t('Please enter a valid tips amount')
        } else if (!isDistributionValid.value) {
            tipsError.value = t('Tips distribution is not valid')
        }
        return
    }
    
    emit('confirm', {
        totalTips: parseFloat(totalTipsAmount.value),
        distribution: assignedEmployees.value.map(emp => ({
            employeeId: emp.id,
            employeeName: emp.name,
            tipsAmount: parseFloat(emp.tipsAmount)
        }))
    })
    
    handleClose()
}

const handleClose = () => {
    totalTipsAmount.value = 0
    tipsError.value = ''
    assignedEmployees.value.forEach(employee => {
        employee.tipsAmount = 0
    })
    emit('close')
}

// Watch for modal visibility
watch(() => props.show, (newValue) => {
    if (newValue) {
        totalTipsAmount.value = props.currentTips || 0
        tipsError.value = ''
        calculateDistribution()
    }
})

// Watch for items changes
watch(() => props.items, () => {
    if (props.show) {
        calculateDistribution()
    }
}, { deep: true })
</script>

<style scoped>
.tips-distribution-modal-body {
    max-height: 70vh;
    overflow-y: auto;
}

.form-label {
    font-size: 20px;
    font-weight: 600;
    margin-bottom: 8px;
    display: block;
}

.required-star-2 {
    font-weight: 600;
    font-size: 18px;
    color: red;
}

.input-group-text {
    background-color: #f8f9fa;
    border-color: #ced4da;
    font-weight: 600;
}

.text-danger {
    color: #dc3545;
    font-size: 14px;
    margin-top: 4px;
    display: block;
}

.distribution-section h5 {
    font-size: 18px;
    font-weight: 600;
    color: #333;
}

.employee-tips-list {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 1rem;
}

.employee-tips-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px;
    background: white;
    border-radius: 6px;
    margin-bottom: 8px;
    border: 1px solid #e0e0e0;
}

.employee-tips-item:last-child {
    margin-bottom: 0;
}

.employee-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.employee-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #e3f2fd;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #1976d2;
}

.employee-details {
    display: flex;
    flex-direction: column;
}

.employee-name {
    font-weight: 600;
    font-size: 16px;
    color: #333;
}

.employee-meta {
    font-size: 14px;
    color: #666;
}

.tips-amount {
    display: flex;
    align-items: center;
}

.amount-value {
    font-size: 18px;
    font-weight: 700;
    color: #28a745;
}

.alert {
    padding: 12px 16px;
    border-radius: 6px;
    display: flex;
    align-items: center;
}

.alert-info {
    background-color: #e3f2fd;
    border: 1px solid #90caf9;
    color: #1565c0;
}

.alert-success {
    background-color: #e8f5e9;
    border: 1px solid #a5d6a7;
    color: #2e7d32;
}

.alert-warning {
    background-color: #fff3e0;
    border: 1px solid #ffcc80;
    color: #e65100;
}

.alert-danger {
    background-color: #ffebee;
    border: 1px solid #ef9a9a;
    color: #c62828;
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

@media (max-width: 768px) {
    .employee-tips-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }
    
    .tips-amount {
        width: 100%;
        justify-content: space-between;
    }
}
</style>

