import { useErrorHandler } from "@/composables/useErrorHandler";
import { useMenuStore } from "@/stores/pos/menuStore";
import { useOrderStore } from "@/stores/pos/orderStore";

/**
 * Composable for managing POS event handlers
 * Handles modal confirmations, user interactions, and business logic
 */
export function usePOSEventHandlers() {
  const { handleError } = useErrorHandler();
  const orderStore = useOrderStore();
  const menuStore = useMenuStore();

  // Search & Category Handlers
  const handleCategorySelect = async (category, posState) => {
    try {
      posState.categoryLoading.value = true;
      menuStore.setCategory(category);
      posState.setSelectedCategory(category);
    } catch (error) {
      handleError("category-select", error);
    } finally {
      posState.categoryLoading.value = false;
    }
  };

  const handleSearch = (query, posState) => {
    try {
      // Handle both direct calls and event objects
      const searchValue =
        typeof query === "string" ? query : query?.target?.value || "";
      menuStore.setSearchQuery(searchValue);
      posState.setSearchQuery(searchValue);
    } catch (error) {
      handleError("search-query", error);
    }
  };

  // Modal Confirmation Handlers
  const handleEmployeeConfirm = (modals, posState) => {
    try {
      modals.handleEmployeeConfirm((employee) => {
        posState.setSelectedEmployee(employee);
        orderStore.selectedEmployee = employee.name;
      });
    } catch (error) {
      handleError("employee-confirm", error);
    }
  };

  const handleCustomerConfirm = async (customerData, modals, posState) => {
    try {
      // Set the selected customer
      posState.setSelectedCustomer(customerData);
      orderStore.selectedCustomer = customerData.name || customerData;

      // Close the modal
      modals.handleCustomerConfirm();
    } catch (error) {
      handleError("customer-select", error);
    }
  };

  const handleCustomerClose = (modals, forms) => {
    try {
      modals.handleCustomerClose(forms.resetCustomerForm);
    } catch (error) {
      handleError("customer-close", error);
    }
  };

  const handleEditCustomer = (customer, modals) => {
    try {
      // TODO: Implement edit customer functionality
      console.log("Edit customer:", customer);
      modals.handleCustomerClose();
      // You could implement router navigation here:
      // router.visit(route('customer.edit', customer.id))
    } catch (error) {
      handleError("customer-edit", error);
    }
  };

  const handleAddCustomer = (modals) => {
    try {
      // TODO: Implement add customer functionality
      console.log("Add new customer");
      modals.handleCustomerClose();
      // You could implement router navigation here:
      // router.visit(route('customer.create'))
    } catch (error) {
      handleError("customer-add", error);
    }
  };

  const handleOrderTypeClick = (modals) => {
    try {
      modals.openOrderTypeModal();
    } catch (error) {
      handleError("order-type-open", error);
    }
  };

  const handleOrderTypeConfirm = (orderTypeData, modals, posState) => {
    try {
      // Set the selected order type
      posState.setSelectedOrderType(orderTypeData.type);
      orderStore.selectedOrderType = orderTypeData;

      // Close the modal
      modals.handleOrderTypeConfirm();
    } catch (error) {
      handleError("order-type-select", error);
    }
  };

  const handleOrderTypeClose = (modals) => {
    try {
      modals.handleOrderTypeClose();
    } catch (error) {
      handleError("order-type-close", error);
    }
  };

  const handleDiscountClose = (modals, forms) => {
    try {
      modals.handleDiscountClose(forms.resetDiscountForm);
    } catch (error) {
      handleError("discount-close", error);
    }
  };

  const handleItemEditClose = (modals, forms) => {
    try {
      modals.handleItemEditClose(forms.resetItemEditForm);
    } catch (error) {
      handleError("item-edit-close", error);
    }
  };

  const handlePaymentClose = (modals, forms) => {
    try {
      modals.handlePaymentClose(forms.resetPaymentForm);
    } catch (error) {
      handleError("payment-close", error);
    }
  };

  // Other Handlers
  const handleChargeModal = () => {
    try {
      // TODO: Implement charge modal
      const charge = prompt(
        "Enter service charge:",
        orderStore.serviceCharge || 0
      );
      if (charge !== null && !isNaN(charge)) {
        orderStore.setServiceCharge(parseFloat(charge));
      }
    } catch (error) {
      handleError("charge-modal", error);
    }
  };


  const handleNotificationDismiss = (notificationId, dismissNotification) => {
    try {
      dismissNotification(notificationId);
    } catch (error) {
      handleError("notification-dismiss", error);
    }
  };

  return {
    // Search & Category
    handleCategorySelect,
    handleSearch,

    // Modal Confirmations
    handleEmployeeConfirm,
    handleCustomerConfirm,
    handleCustomerClose,
    handleEditCustomer,
    handleAddCustomer,
    handleOrderTypeClick,
    handleOrderTypeConfirm,
    handleOrderTypeClose,
    handleDiscountClose,
    handleItemEditClose,
    handlePaymentClose,

    // Other Handlers
    handleChargeModal,
    handleNotificationDismiss,
  };
}
