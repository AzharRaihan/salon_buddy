import { useErrorHandler } from "@/composables/useErrorHandler";
import { ref } from "vue";

/**
 * Composable for managing POS state
 * Handles employee, customer, order type selection and other global POS state
 */
export function usePOSState() {
  const { handleError } = useErrorHandler();

  // Core POS State
  const selectedEmployee = ref(null);
  const selectedOrderType = ref("dine-in");
  const selectedCustomer = ref(null);
  const selectedCategory = ref(null);

  // Loading States
  const categoryLoading = ref(false);
  const globalLoading = ref(false);
  const loadingMessage = ref("");

  // Search State
  const searchQuery = ref("");

  // Loading State Management
  const setGlobalLoading = (loading, message = "") => {
    globalLoading.value = loading;
    loadingMessage.value = message;
  };

  // State Setters with Error Handling
  const setSelectedEmployee = (employee) => {
    try {
      selectedEmployee.value = employee;
    } catch (error) {
      handleError("employee-select", error);
    }
  };

  const setSelectedCustomer = (customer) => {
    try {
      selectedCustomer.value = customer;
    } catch (error) {
      handleError("customer-select", error);
    }
  };

  const setSelectedOrderType = (orderType) => {
    try {
      selectedOrderType.value = orderType;
    } catch (error) {
      handleError("order-type-select", error);
    }
  };

  const setSelectedCategory = (category) => {
    try {
      selectedCategory.value = category;
    } catch (error) {
      handleError("category-select", error);
    }
  };

  const setSearchQuery = (query) => {
    try {
      searchQuery.value = query;
    } catch (error) {
      handleError("search-query-set", error);
    }
  };

  // Auto-initialization
  const initializePOSState = () => {
    try {
      // Auto-select first employee if available
      if (employees.value.length > 0) {
        selectedEmployee.value = employees.value[0];
      }

      // Set default order type
      selectedOrderType.value = "dine-in";
    } catch (error) {
      handleError("pos-state-init", error);
    }
  };

  return {
    // Core State
    selectedEmployee,
    selectedOrderType,
    selectedCustomer,
    selectedCategory,

    // Loading States
    categoryLoading,
    globalLoading,
    loadingMessage,

    // Search State
    searchQuery,

    // Methods
    setGlobalLoading,
    setSelectedEmployee,
    setSelectedCustomer,
    setSelectedOrderType,
    setSelectedCategory,
    setSearchQuery,
    initializePOSState,
  };
}
