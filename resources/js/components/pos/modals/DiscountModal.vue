<template>
  <div v-if="show" :class="{ 'show': show }" class="common-modal select-modal show discount-modal">
    <div class="modal-content">
      <div class="modal-header">
        <h4>{{ t('Discount') }}</h4>
        <button class="close-modal" @click="closeModal">
          <VIcon icon="tabler-x" />
        </button>
      </div>
      <div class="modal-body">
        <!-- Discount Type Toggle -->
        <div class="discount-type-toggle mb-3">
          <label class="form-label">{{ t('Discount Type') }}</label>
          <div class="btn-group w-100" role="group">
            <input type="radio" class="btn-check" id="discount-fixed" v-model="discountType" value="fixed" autocomplete="off">
            <label class="btn btn-outline-primary" for="discount-fixed">
              <VIcon icon="tabler-lock-dollar" />
              {{ t('Fixed Amount') }}
            </label>
            
            <input type="radio" class="btn-check" id="discount-percentage" v-model="discountType" value="percentage" autocomplete="off">
            <label class="btn btn-outline-primary" for="discount-percentage">
              <VIcon icon="tabler-rosette-discount" />
              {{ t('Percentage') }}
            </label>
          </div>
        </div>

        <!-- Value Label and Input -->
        <div class="discount-value mb-3">
          <label class="form-label">
            {{ discountType === 'percentage' ? 'Percentage (%)' : 'Amount ($)' }}
          </label>
          <input type="number" class="form-control"
            :placeholder="discountType === 'percentage' ? 'Enter percentage (e.g., 10)' : 'Enter discount amount'"
            v-model="discountValue" step="0.01" min="0" :max="discountType === 'percentage' ? 100 : null" />
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-danger" @click="closeModal">
            <VIcon icon="tabler-x" />
            {{ t('Cancel') }}
        </button>
        <button class="btn btn-primary" @click="applyDiscount">
            <VIcon icon="tabler-discount" />
            {{ t('Apply Discount') }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useOrderStore } from '@/stores/pos/orderStore';
import { toast } from 'vue3-toastify';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

// Props
const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  orderSubtotal: {
    type: Number,
    default: 0
  }
});

const emit = defineEmits(['confirm', 'close']);
const orderStore = useOrderStore();
const discountType = ref('fixed');
const discountValue = ref('');

// Computed properties
const calculatedDiscountAmount = computed(() => {
  const amount = parseFloat(discountValue.value);
  if (isNaN(amount)) return 0;
  if (discountType.value === 'percentage') {
    return (props.orderSubtotal * amount) / 100;
  }
  return amount;
});

const isValidAmount = computed(() => {
  const amount = parseFloat(discountValue.value);
  if (isNaN(amount) || amount < 0) return false;
  if (discountType.value === 'percentage') {
    return amount >= 0 && amount <= 100;
  }
  return amount >= 0;
});

// Methods
function closeModal() {
  discountValue.value = '';
  discountType.value = 'fixed';
  emit('close');
}

function applyDiscount() {
  // if discount value is '' then close modal
  if (discountValue.value === '') {
    closeModal();
    return;
  }
  // Check if cart is empty
  if (!orderStore.orderItems || orderStore.orderItems.length === 0) {
    toast.error('Please add at least one product to cart before applying discount');
    return;
  }
  if (!isValidAmount.value) {
    toast.error('Please enter a valid discount value');
    return;
  }
  let value = parseFloat(discountValue.value);
  let type = discountType.value;
  
  // If percentage is entered directly in the input (e.g. "10%")
  if (discountValue.value.toString().includes('%')) {
    value = parseFloat(discountValue.value.toString().replace('%', ''));
    type = 'percentage';
  }
  
  const finalAmount = calculatedDiscountAmount.value;

  try {
    // Use the correct orderStore method
    orderStore.setDiscount(value, type);
    toast.success(`Discount of ${finalAmount.toFixed(2)} applied successfully`);
    emit('confirm', {
      amount: finalAmount,
      type: type,
      originalValue: value
    });
    closeModal();
  } catch (error) {
    console.error('Error applying discount:', error);
    toast.error('Failed to apply discount');
  }
}
watch(() => props.show, (newValue) => {
  if (newValue) {
    if (orderStore.discountAmount > 0) {
      discountValue.value = orderStore.discountAmount.toString();
      discountType.value = orderStore.discountType;
    } else {
      discountValue.value = '';
      discountType.value = 'fixed'; // Default to fixed amount
    }
  }
});
</script>
