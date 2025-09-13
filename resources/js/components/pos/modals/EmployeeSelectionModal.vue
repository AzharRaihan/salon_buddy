<template>
    <div class="select-modal employee-modal" :class="{ show: show }" v-if="show" @mousedown.self="emit('close')">
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
                    <p class="mt-2 text-muted">{{ t('Loading employees...') }}</p>
                </div>

                <!-- Error State -->
                <div v-else-if="error" class="error-state">
                    <div class="alert alert-danger" role="alert">
                        <i class="fas fa-exclamation-triangle"></i>
                        {{ error }}
                    </div>
                    <button class="btn btn-outline-primary" @click="handleRetry">
                        <i class="fas fa-refresh"></i> {{ t('Retry') }}
                    </button>
                </div>

                <!-- Main Content -->
                <div v-else>
                    <div class="employee-list">
                        <!-- Empty State -->
                        <div v-if="filteredEmployees.length === 0" class="empty-state">
                            <VIcon icon="tabler-user-x" size="48" />
                            <p class="text-muted">
                                {{ searchQuery ? t('No employees found matching your search') : t('No employee available') }}
                            </p>
                        </div>

                        <!-- Employee List -->
                        <ul v-else>
                            <li v-for="(employee, index) in filteredEmployees" :key="employee.id"
                                @click="selectEmployee(employee)">
                                <div class="employee-details" :class="{ 'selected-employee': employee.id === orderStore.selectedEmployeeId }">
                                    <h5>{{ employee.name }} ({{ employee.phone || employee.email || 'N/A' }})</h5>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { watch, onMounted } from 'vue'
import { useEmployees } from '@/composables/useEmployees'
import { useOrderStore } from '@/stores/pos/orderStore'
const orderStore = useOrderStore()
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    employees: {
        type: Array,
        default: () => []
    },
    selected: {
        type: Object,
        default: null
    }
})

const emit = defineEmits(['employee-selected', 'confirm', 'close'])

const {
    employees,
    isLoading,
    error,
    searchQuery,
    filteredEmployees,
    fetchEmployee,
    setSearchQuery,
    clearEmployees
} = useEmployees()

const selectEmployee = (employee) => {
    emit('employee-selected', employee)
    emit('confirm', employee)
}

const handleSearchInput = (event) => {
    const query = event.target.value
    setSearchQuery(query)
}

const handleRetry = async () => {
    await fetchEmployee()
}

watch(() => props.show, (newValue) => {
    if (newValue) {
        if (employees.value.length === 0) {
            fetchEmployee()
        }
    } else {
        setSearchQuery('')
    }
})

onMounted(() => {
    if (props.show && employees.value.length === 0) {
        fetchEmployee()
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
