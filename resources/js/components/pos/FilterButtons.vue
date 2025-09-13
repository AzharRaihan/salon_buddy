<template>
    <div class="filter-options">
        <button class="btn btn-light" id="employee-btn" @click="$emit('show-employee-modal')">
            <img :src="EmployeeIcon" alt="Employee" />
            {{ employee ? employee.name : t('Employee') }}
        </button>
        <button class="btn btn-light" id="customer-btn" @click="$emit('show-customer-modal')">
            <img :src="CustomerIcon" alt="Customer" />
            {{ customer ? customer.name : t('Customer') }}
        </button>
    </div>
</template>

<script setup>
import CustomerIcon from '@images/pos/images/icon/customer.png';
import EmployeeIcon from '@images/pos/images/icon/waiter.png';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

// Define props
defineProps({
    employee: {
        type: Object,
        default: null
    },
    customer: {
        type: Object,
        default: null
    }
})

defineEmits([
    'show-employee-modal',
    'show-customer-modal'
])
</script>
