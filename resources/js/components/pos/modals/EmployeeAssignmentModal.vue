<template>
    <div v-if="show" class="common-modal select-modal show">
        <div class="modal-content">
            <div class="modal-header">
                <h4>{{ t('Edit') }}</h4>
                <button class="close-modal" @click="handleClose">
                    <VIcon icon="tabler-x" />
                </button>
            </div>
            <div class="modal-body employee-assignment-modal-body">
                <h4>{{ getItemName(props.item) }}</h4>
                <div class="mt-1 text-muted mb-1">
                    <p>{{ t('Current Price') }}: {{ formatPrice(getItemPrice(props.item)) }}</p>
                </div>
                <div class="service-info">
                    <div class="price-edit">
                        <label for="service-price">{{ t('Update Price') }}</label>
                        <input
                            id="service-price"
                            type="number"
                            class="form-control"
                            v-model.number="servicePrice"
                            min="0"
                            step="0.01"
                            :placeholder="formatPrice(getItemPrice(props.item))"
                        />
                    </div>
                </div>
                <!-- Employee List -->
                <div class="employee-list-section" v-if="getItemType(props.item) == 'Service'">
                    <h4>{{ t('Select Employee') }}</h4>
                    <div class="search-container">
                        <input 
                            type="text" 
                            class="form-control" 
                            :placeholder="t('Search employees...')" 
                            v-model="searchQuery"
                        />
                        <VIcon icon="tabler-search" class="search-icon" />
                    </div>
                    
                    <div class="employee-list">
                        <div 
                            v-for="employee in filteredEmployees" 
                            :key="employee.id"
                            class="employee-item"
                            :class="{ 'selected': selectedEmployeeId == employee.id }"
                            @click="selectEmployee(employee)"
                        >
                            <div class="employee-avatar">
                                <VIcon icon="tabler-user" />
                            </div>
                            <div class="employee-info">
                                <div class="employee-name">{{ employee.name }}</div>
                                <div class="employee-details">
                                    <span v-if="employee.phone">{{ employee.phone }}</span>
                                    <span v-if="employee.email">{{ employee.email }}</span>
                                </div>
                            </div>
                            <div class="employee-status" v-if="getEmployeeId(props.item) == employee.id">
                                <VIcon icon="tabler-check" class="text-success"  />
                            </div>
                        </div>
                    </div>

                    <!-- No employees found -->
                    <div v-if="filteredEmployees.length === 0" class="no-employees">
                        <VIcon icon="tabler-users" class="text-muted" />
                        <p>{{ t('No employees found') }}</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-danger" @click="handleClose">
                    <VIcon icon="tabler-x" />
                    {{ t('Cancel') }}
                </button>
                <button class="btn btn-primary" @click="handleConfirm">
                    <VIcon icon="tabler-edit" />
                    {{ t('Update') }}
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, nextTick } from 'vue'
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
    itemId: {
        type: [String, Number],
        default: null
    },
    employees: {
        type: Array,
        default: () => []
    }
})

const emit = defineEmits(['confirm', 'close'])
const { handleError } = useErrorHandler()
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
    if (!itemValue) return 0
    return (
        itemValue.name || ''
    )
}
const getItemType = (item) => {
    const itemValue = unref(item);
    if (!itemValue) return 0
    return (
        itemValue.type || ''
    )
}
const getEmployeeId = (item) => {
    const itemValue = unref(item);
    if (!itemValue) return 0
    return (
        itemValue.assignedEmployee?.id || ''
    )
}

// Reactive state
const loading = ref(false)
const searchQuery = ref('')
const selectedEmployee = ref(null)
// Service price state
const servicePrice = ref(getItemPrice(props.item))
const serviceName = ref(getItemName(props.item))
const serviceType = ref(getItemType(props.item))


// Computed properties
const filteredEmployees = computed(() => {
    if (!searchQuery.value) return props.employees
    
    const query = searchQuery.value.toLowerCase()
    return props.employees.filter(employee => 
        employee.name?.toLowerCase().includes(query) ||
        employee.phone?.toLowerCase().includes(query) ||
        employee.email?.toLowerCase().includes(query)
    )
})


// Methods
const formatPrice = (price) => {
    return parseFloat(price || 0).toFixed(2)
}

const selectedEmployeeId = ref(null)

const selectEmployee = (employee) => {
    selectedEmployee.value = employee

    selectedEmployeeId.value = employee.id

}

const handleConfirm = async () => {
    // if (!selectedEmployee.value) return
    try {
        loading.value = true
        const itemValue = unref(props.item)
        emit('confirm', {
            itemId: itemValue?.id,
            employee: selectedEmployee.value || 1,
            price: servicePrice.value
        })
    } catch (error) {
        handleError('employee-assignment', error)
    } finally {
        loading.value = false
    }
}

const handleClose = () => {
    // Reset form
    searchQuery.value = ''
    selectedEmployee.value = null
    selectedEmployeeId.value = getEmployeeId(props.item)
    emit('close')
}

// Watch for modal visibility
watch(() => props.show, (newValue) => {
    if (newValue) {
        searchQuery.value = ''
        selectedEmployee.value = null
        servicePrice.value = getItemPrice(props.item)
        serviceName.value = getItemName(props.item)
    }
})

watch(() => props.item, (newItem) => {
    servicePrice.value = getItemPrice(newItem)
    serviceName.value = getItemName(newItem)
})
</script>