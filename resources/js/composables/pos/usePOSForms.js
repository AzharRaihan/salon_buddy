import { createPOSForm } from "@/composables/useFormValidation";
import { computed } from "vue";

export function usePOSForms() {
  // Customer Form
  const customerForm = createPOSForm("customerForm", {
    name: "",
    phone: "",
    email: "",
    tableNumber: "",
    specialRequests: "",
  });

  // Discount Form
  const discountForm = createPOSForm("discountForm", {
    type: "",
    amount: "",
    reason: "",
  });

  // Item Edit Form
  const itemEditForm = createPOSForm("itemEditForm", {
    selectedModifiers: [],
    specialInstructions: "",
  });

  // Payment Form
  const paymentForm = createPOSForm("paymentForm", {
    method: "",
    amount: "",
    tip: "",
    reference: "",
    orderDate: new Date().toISOString().split("T")[0], // Add order date field
  });

  // Form validation helpers
  const validateCustomerForm = () => {
    return customerForm.formData.name && customerForm.formData.phone;
  };

  const validateDiscountForm = () => {
    return discountForm.formData.type && discountForm.formData.amount;
  };

  const validatePaymentForm = () => {
    return paymentForm.formData.method && paymentForm.formData.amount;
  };

  // Form reset helpers
  const resetCustomerForm = () => {
    customerForm.resetForm();
  };

  const resetDiscountForm = () => {
    discountForm.resetForm();
  };

  const resetItemEditForm = () => {
    itemEditForm.resetForm();
  };

  const resetPaymentForm = () => {
    paymentForm.resetForm();
  };

  // Update helpers
  const updateCustomerForm = (data) => {
    customerForm.updateFields(data);
  };

  const updateDiscountForm = (data) => {
    discountForm.updateFields(data);
  };

  const updatePaymentForm = (field, value) => {
    paymentForm.updateField(field, value);
  };

  // Form submission handlers
  const handleCustomerSubmit = async (callback) => {
    return await customerForm.handleSubmit(callback);
  };

  const handleDiscountSubmit = async (callback) => {
    return await discountForm.handleSubmit(callback);
  };

  const handleItemEditSubmit = async (callback) => {
    return await itemEditForm.handleSubmit(callback);
  };

  const handlePaymentSubmit = async (callback) => {
    return await paymentForm.handleSubmit(callback);
  };

  // Computed properties
  const isCustomerFormValid = computed(() => customerForm.isValid);
  const isDiscountFormValid = computed(() => discountForm.isValid);
  const isItemEditFormValid = computed(() => itemEditForm.isValid);
  const isPaymentFormValid = computed(() => paymentForm.isValid);

  return {
    // Forms
    customerForm,
    discountForm,
    itemEditForm,
    paymentForm,

    // Validation
    isCustomerFormValid,
    isDiscountFormValid,
    isItemEditFormValid,
    isPaymentFormValid,

    // Helpers
    resetCustomerForm,
    resetDiscountForm,
    resetItemEditForm,
    resetPaymentForm,
    updateCustomerForm,
    updateDiscountForm,
    updatePaymentForm,

    // Submission handlers
    handleCustomerSubmit,
    handleDiscountSubmit,
    handleItemEditSubmit,
    handlePaymentSubmit,
  };
}
