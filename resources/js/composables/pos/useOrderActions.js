import { useErrorHandler } from "@/composables/useErrorHandler";
import { useOrderStore } from "@/stores/pos/orderStore";

/**
 * Composable for managing order actions in POS
 * Handles item selection, quantity changes, and order operations
 */
export function useOrderActions() {
  const orderStore = useOrderStore();
  const { handleError } = useErrorHandler();

  // Item Selection & Addition
  const handleItemSelect = async (item, modals) => {
    try {
      orderStore.addToOrder(item);
    } catch (error) {
      handleError("item-select", error);
    }
  };

  // Order Item Management
  const handleOrderItemSelect = (index) => {
    try {
      orderStore.selectItem(index);
    } catch (error) {
      handleError("order-item-select", error);
    }
  };

  const handleItemRemove = async (itemId) => {
    try {
      const itemIndex = orderStore.orderItems.findIndex(
        (item) => item.id === itemId
      );
      if (itemIndex >= 0) {
        orderStore.removeItem(itemIndex);
      }
    } catch (error) {
      handleError("item-remove", error);
    }
  };

  const handleQuantityChange = async (itemId, quantity) => {
    try {
      const itemIndex = orderStore.orderItems.findIndex(
        (item) => item.id === itemId
      );
      if (itemIndex >= 0) {
        orderStore.updateItemQuantity(itemIndex, quantity);
      }
    } catch (error) {
      handleError("quantity-change", error);
    }
  };

  // Quantity Controls
  const handleDecrementQuantity = () => {
    try {
      orderStore.decrementQuantity();
    } catch (error) {
      handleError("quantity-decrement", error);
    }
  };

  const handleIncrementQuantity = () => {
    try {
      orderStore.incrementQuantity();
    } catch (error) {
      handleError("quantity-increment", error);
    }
  };

  // Item Actions
  const handleItemEditClick = (modals) => {
    try {
      const selectedItem = orderStore.selectedItem;
      if (selectedItem) {
        // Defensive: always pass a copy with id
        if (selectedItem.id) {
          modals.toggleEmployeeAssignmentModal({ ...selectedItem });
        } else {
          console.warn("Selected item has no id:", selectedItem);
        }
      }
    } catch (error) {
      handleError("item-edit-open", error);
    }
  };

  const handleNoteClick = () => {
    try {
      const selectedItem = orderStore.selectedItem;
      if (selectedItem) {
        // For now, use a simple prompt - can be replaced with proper modal later
        const currentNote = selectedItem.note || "";
        const newNote = prompt(
          `Add note for ${selectedItem.name}:`,
          currentNote
        );

        if (newNote !== null) {
          // User didn't cancel
          orderStore.updateItemNote(orderStore.selectedItemIndex, newNote);
        }
      }
    } catch (error) {
      handleError("note-open", error);
    }
  };

  const handleRemoveSelectedItem = () => {
    try {
      orderStore.removeItem();
    } catch (error) {
      handleError("item-remove", error);
    }
  };

  // Order Management
  const handleSaveOrder = async (setLoading) => {
    try {
      setLoading(true, "Saving order...");
      await orderStore.saveDraft();
    } catch (error) {
      handleError("order-save", error);
    } finally {
      setLoading(false);
    }
  };

  const handleClearOrder = async () => {
    try {
      orderStore.clearOrder();
    } catch (error) {
      handleError("order-clear", error);
    }
  };

  // Modal Handlers for Order Actions
  const handleDiscountConfirm = async (forms, modals) => {
    const result = await forms.handleDiscountSubmit(async (discountData) => {
      orderStore.setDiscount(
        discountData.amount,
        discountData.type || "percentage"
      );
    });

    if (result.success) {
      modals.handleDiscountConfirm();
    } else {
      handleError("discount-apply", result.error);
    }
  };

  const handleItemEditConfirm = async (forms, modals) => {
    const result = await forms.handleItemEditSubmit(async (itemData) => {
      orderStore.addToOrder(itemData);
    });

    if (result.success) {
      modals.handleItemEditConfirm();
    } else {
      handleError("item-edit-add", result.error);
    }
  };

  const handlePaymentConfirm = async (forms, modals, setLoading) => {
    setLoading(true, "Processing payment...");

    const result = await forms.handlePaymentSubmit(async (paymentData) => {
      await orderStore.placeOrder();
    });

    if (result.success) {
      modals.handlePaymentConfirm();
    } else {
      handleError("payment-process", result.error);
    }

    setLoading(false);
  };

  return {
    // Item Selection & Addition
    handleItemSelect,

    // Order Item Management
    handleOrderItemSelect,
    handleItemRemove,
    handleQuantityChange,

    // Quantity Controls
    handleDecrementQuantity,
    handleIncrementQuantity,

    // Item Actions
    handleItemEditClick,
    handleNoteClick,
    handleRemoveSelectedItem,

    // Order Management
    handleSaveOrder,
    handleClearOrder,

    // Modal Handlers
    handleDiscountConfirm,
    handleItemEditConfirm,
    handlePaymentConfirm,
  };
}
