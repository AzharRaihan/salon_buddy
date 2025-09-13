import { computed, ref, watch } from "vue";

export function useOrderCalculations(orderItems) {
  // Calculation state
  const subtotal = ref(0);
  const discount = ref(0);
  const totalDiscount = ref(0);
  const tax = ref(0);
  const charge = ref(0);
  const tips = ref(0);

  // Tax rate (5% example)
  const taxRate = ref(0.05);

  // Computed grand total
  const grandTotal = computed(() => {
    return (
      subtotal.value -
      discount.value -
      totalDiscount.value +
      tax.value +
      charge.value +
      tips.value
    );
  });

  // Methods
  const calculateItemTotal = (item) => {
    const itemTotal =
      (item.selling_price_dine_in || item.price || 0) * item.qty;
    const variationsTotal = item.variations
      ? item.variations.reduce(
          (sum, variation) => sum + (variation.price || 0),
          0
        ) * item.qty
      : 0;
    return itemTotal + variationsTotal;
  };

  const calculateTotals = () => {
    // Calculate subtotal
    subtotal.value = orderItems.value.reduce((total, item) => {
      return total + calculateItemTotal(item);
    }, 0);

    // Calculate tax
    tax.value = subtotal.value * taxRate.value;
  };

  const applyDiscount = (discountValue, discountType = "amount") => {
    if (discountType === "percentage") {
      discount.value = (subtotal.value * discountValue) / 100;
    } else {
      discount.value = discountValue;
    }
  };

  const applyTips = (tipsValue, tipsType = "amount") => {
    if (tipsType === "percentage") {
      tips.value = (subtotal.value * tipsValue) / 100;
    } else {
      tips.value = tipsValue;
    }
  };

  const applyCharge = (chargeValue, chargeType = "amount") => {
    if (chargeType === "percentage") {
      charge.value = (subtotal.value * chargeValue) / 100;
    } else {
      charge.value = chargeValue;
    }
  };

  const clearDiscounts = () => {
    discount.value = 0;
    totalDiscount.value = 0;
  };

  const clearCharges = () => {
    charge.value = 0;
    tips.value = 0;
  };

  const resetCalculations = () => {
    subtotal.value = 0;
    discount.value = 0;
    totalDiscount.value = 0;
    tax.value = 0;
    charge.value = 0;
    tips.value = 0;
  };

  // Watch orderItems for changes
  watch(
    () => orderItems.value,
    () => {
      calculateTotals();
    },
    { deep: true, immediate: true }
  );

  return {
    // State
    subtotal,
    discount,
    totalDiscount,
    tax,
    charge,
    tips,
    taxRate,

    // Computed
    grandTotal,

    // Methods
    calculateItemTotal,
    calculateTotals,
    applyDiscount,
    applyTips,
    applyCharge,
    clearDiscounts,
    clearCharges,
    resetCalculations,
  };
}
