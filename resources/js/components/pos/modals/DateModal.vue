<template>
    <div v-if="show" :class="{ 'show': show }" class="common-modal select-modal show">
        <div class="modal-content">
            <div class="modal-header">
                <h4>{{ t('Select Date') }}</h4>
                <button class="close-modal" @click="closeModal">
                <VIcon icon="tabler-x" />
                </button>
            </div>
            <div class="modal-body">
                <div class="date-picker">
                    <AppDateTimePicker v-model="selectedDate" :placeholder="t('Select date')"
                        :config="{ 
                            dateFormat: 'Y-m-d',
                            enableTime: false,
                            maxDate: new Date()
                        }" />
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-danger" @click="closeModal">
                    <VIcon icon="tabler-x" />
                    {{ t('Cancel') }}
                </button>
                <button class="btn btn-primary" @click="saveDate">
                    <VIcon icon="tabler-check" />
                    {{ t('Save') }}
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useOrderStore } from '@/stores/pos/orderStore';
import { onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    }
});
const emit = defineEmits(['confirm', 'close']);
const orderStore = useOrderStore();
const selectedDate = ref(new Date().toISOString().split('T')[0]);

function closeModal() {
    emit('close');
}

function saveDate() {
    // Format the date properly for the controller (Y-m-d format)
    const formattedDate = selectedDate.value;
    
    // Set the selected date in order store
    orderStore.setOrderDate(formattedDate);

    // Emit the confirm event with the selected date
    emit('confirm', { date: formattedDate });
}

onMounted(() => {
    // If there's an existing date, use it
    if (orderStore.orderDate) {
        selectedDate.value = orderStore.orderDate;
    }
});
</script>

<style scoped>
.common-modal.show {
  z-index: unset;
}
</style>
