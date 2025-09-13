import { useGlobalModal } from "@/composables/useModal";
import { ref } from "vue";

export function usePOSModals() {
  const modal = useGlobalModal();

  // Modal state
  const selectedEmployeeCandidate = ref(null);
  const selectedItem = ref(null);
  const selectedServiceItem = ref(null);

  // Enhanced modal opening with auto-close behavior
  const openModalWithAutoClose = async (modalId, data = null, config = {}) => {
    // Always close all other modals before opening a new one
    await modal.closeAllModals();
    return await modal.openModal(modalId, data, {
      ...config,
      stackable: false,
    });
  };

  // Employee Modal Management
  const openEmployeeModal = async (currentEmployee = null) => {
    selectedEmployeeCandidate.value = currentEmployee;
    return await openModalWithAutoClose("employee-selection");
  };

  const toggleEmployeeModal = async (currentEmployee = null) => {
    if (modal.isModalOpen("employee-selection")) {
      selectedEmployeeCandidate.value = null;
      modal.closeModal("employee-selection");
    } else {
      await openEmployeeModal(currentEmployee);
    }
  };

  const handleEmployeeSelect = (employee) => {
    selectedEmployeeCandidate.value = employee;
  };

  const handleEmployeeConfirm = (callback) => {
    if (selectedEmployeeCandidate.value && callback) {
      callback(selectedEmployeeCandidate.value);
    }
    modal.closeModal("employee-selection");
  };

  const handleEmployeeClose = () => {
    selectedEmployeeCandidate.value = null;
    modal.closeModal("employee-selection");
  };

  // Customer Modal Management
  const openCustomerModal = async () => {
    return await openModalWithAutoClose("customer-selection");
  };

  const toggleCustomerModal = async () => {
    if (modal.isModalOpen("customer-selection")) {
      modal.closeModal("customer-selection");
    } else {
      await openCustomerModal();
    }
  };

  const handleCustomerConfirm = () => {
    modal.closeModal("customer-selection");
  };

  const handleCustomerClose = (resetHandler) => {
    if (resetHandler) resetHandler();
    modal.closeModal("customer-selection");
  };

  // Order Type Modal Management
  const openOrderTypeModal = async () => {
    return await openModalWithAutoClose("order-type-selection");
  };

  const toggleOrderTypeModal = async () => {
    if (modal.isModalOpen("order-type-selection")) {
      modal.closeModal("order-type-selection");
    } else {
      await openOrderTypeModal();
    }
  };

  const handleOrderTypeConfirm = () => {
    modal.closeModal("order-type-selection");
  };

  const handleOrderTypeClose = (resetHandler) => {
    if (resetHandler) resetHandler();
    modal.closeModal("order-type-selection");
  };

  // Discount Modal Management
  const openDiscountModal = async () => {
    return await openModalWithAutoClose("discount-selection");
  };

  const toggleDiscountModal = async () => {
    if (modal.isModalOpen("discount-selection")) {
      modal.closeModal("discount-selection");
    } else {
      await openDiscountModal();
    }
  };

  const handleDiscountConfirm = () => {
    modal.closeModal("discount-selection");
  };

  const handleDiscountClose = (resetHandler) => {
    if (resetHandler) resetHandler();
    modal.closeModal("discount-selection");
  };

  // Item Edit Modal Management
  const openItemEditModal = async (item) => {
    selectedItem.value = item;
    return await openModalWithAutoClose("item-edit-selection");
  };

  const toggleItemEditModal = async (item) => {
    if (modal.isModalOpen("item-edit-selection")) {
      selectedItem.value = null;
      modal.closeModal("item-edit-selection");
    } else {
      await openItemEditModal(item);
    }
  };

  const handleItemEditConfirm = () => {
    modal.closeModal("item-edit-selection");
  };

  const handleItemEditClose = (resetHandler) => {
    if (resetHandler) resetHandler();
    selectedItem.value = null;
    modal.closeModal("item-edit-selection");
  };

  // Payment Modal Management
  const openPaymentModal = async () => {
    return await openModalWithAutoClose("payment-processing");
  };

  const togglePaymentModal = async () => {
    if (modal.isModalOpen("payment-processing")) {
      modal.closeModal("payment-processing");
    } else {
      await openPaymentModal();
    }
  };

  const handlePaymentConfirm = () => {
    modal.closeModal("payment-processing");
  };

  const handlePaymentClose = (resetHandler) => {
    if (resetHandler) resetHandler();
    modal.closeModal("payment-processing");
  };

  // Note Modal Management
  const openNoteModal = async (item) => {
    selectedItem.value = item; // Reuse for note context
    return await openModalWithAutoClose("note-editing");
  };

  const toggleNoteModal = async (item) => {
    if (modal.isModalOpen("note-editing")) {
      selectedItem.value = null;
      modal.closeModal("note-editing");
    } else {
      await openNoteModal(item);
    }
  };

  const handleNoteConfirm = () => {
    modal.closeModal("note-editing");
  };

  const handleNoteClose = (resetHandler) => {
    if (resetHandler) resetHandler();
    selectedItem.value = null;
    modal.closeModal("note-editing");
  };

  // Date Modal Management
  const openDateModal = async () => {
    return await openModalWithAutoClose("date-selection");
  };

  const toggleDateModal = async () => {
    if (modal.isModalOpen("date-selection")) {
      modal.closeModal("date-selection");
    } else {
      await openDateModal();
    }
  };

  const handleDateConfirm = () => {
    modal.closeModal("date-selection");
  };

  const handleDateClose = (resetHandler) => {
    if (resetHandler) resetHandler();
    modal.closeModal("date-selection");
  };

  // Tips Modal Management
  const openTipsModal = async () => {
    return await openModalWithAutoClose("tips-selection");
  };

  const toggleTipsModal = async () => {
    if (modal.isModalOpen("tips-selection")) {
      modal.closeModal("tips-selection");
    } else {
      await openTipsModal();
    }
  };

  const handleTipsConfirm = () => {
    modal.closeModal("tips-selection");
  };

  const handleTipsClose = (resetHandler) => {
    if (resetHandler) resetHandler();
    modal.closeModal("tips-selection");
  };

  // Charge Modal Management
  const openChargeModal = async () => {
    return await openModalWithAutoClose("charge-selection");
  };

  const toggleChargeModal = async () => {
    if (modal.isModalOpen("charge-selection")) {
      modal.closeModal("charge-selection");
    } else {
      await openChargeModal();
    }
  };

  const handleChargeConfirm = () => {
    modal.closeModal("charge-selection");
  };

  const handleChargeClose = (resetHandler) => {
    if (resetHandler) resetHandler();
    modal.closeModal("charge-selection");
  };

  // Employee Assignment Modal Management
  const openEmployeeAssignmentModal = async (item) => {
    selectedServiceItem.value = item;
    return await openModalWithAutoClose("employee-assignment");
  };

  const toggleEmployeeAssignmentModal = async (item) => {
    // Always close first to force prop update
    if (modal.isModalOpen("employee-assignment")) {
      selectedServiceItem.value = null;
      await modal.closeModal("employee-assignment");
      // Wait for next tick to ensure reactivity
      await new Promise(resolve => setTimeout(resolve, 0));
    }
    selectedServiceItem.value = item;
    await openEmployeeAssignmentModal(item);
  };

  const handleEmployeeAssignmentConfirm = () => {
    modal.closeModal("employee-assignment");
  };

  const handleEmployeeAssignmentClose = (resetHandler) => {
    if (resetHandler) resetHandler();
    selectedServiceItem.value = null;
    modal.closeModal("employee-assignment");
  };

  // General Modal Methods
  const closeAllModals = () => {
    modal.closeAllModals();
  };

  const isModalOpen = (modalId) => {
    return modal.isModalOpen(modalId);
  };

  const hasAnyModalOpen = () => {
    return modal.isAnyModalOpen;
  };

  return {
    // State
    selectedEmployeeCandidate,
    selectedItem,
    selectedServiceItem,

    // Employee Modal
    openEmployeeModal,
    toggleEmployeeModal,
    handleEmployeeSelect,
    handleEmployeeConfirm,
    handleEmployeeClose,

    // Customer Modal
    openCustomerModal,
    toggleCustomerModal,
    handleCustomerConfirm,
    handleCustomerClose,

    // Order Type Modal
    openOrderTypeModal,
    toggleOrderTypeModal,
    handleOrderTypeConfirm,
    handleOrderTypeClose,

    // Discount Modal
    openDiscountModal,
    toggleDiscountModal,
    handleDiscountConfirm,
    handleDiscountClose,

    // Item Edit Modal
    openItemEditModal,
    toggleItemEditModal,
    handleItemEditConfirm,
    handleItemEditClose,

    // Payment Modal
    openPaymentModal,
    togglePaymentModal,
    handlePaymentConfirm,
    handlePaymentClose,

    // Note Modal
    openNoteModal,
    toggleNoteModal,
    handleNoteConfirm,
    handleNoteClose,

    // Date Modal
    openDateModal,
    toggleDateModal,
    handleDateConfirm,
    handleDateClose,

    // Tips Modal
    openTipsModal,
    toggleTipsModal,
    handleTipsConfirm,
    handleTipsClose,

    // Charge Modal
    openChargeModal,
    toggleChargeModal,
    handleChargeConfirm,
    handleChargeClose,

    // Employee Assignment Modal
    openEmployeeAssignmentModal,
    toggleEmployeeAssignmentModal,
    handleEmployeeAssignmentConfirm,
    handleEmployeeAssignmentClose,

    // General
    closeAllModals,
    isModalOpen,
    hasAnyModalOpen,
  };
}
