import { placeOrder as apiPlaceOrder } from "@/utils/pos/api";
import { computed, ref } from "vue";
import { toast } from "vue3-toastify";

export function useOrderManager() {
  // Order state
  const orderItems = ref([]);
  const selectedItemIndex = ref(-1);
  const orderType = ref("Dine In");
  const selectedEmployee = ref("");
  const selectedCustomer = ref("");

  // Computed properties
  const hasSelectedItem = computed(() => selectedItemIndex.value >= 0);
  const selectedItem = computed(() => {
    if (selectedItemIndex.value >= 0) {
      return orderItems.value[selectedItemIndex.value];
    }
    return null;
  });

  // Methods
  const selectItem = (index) => {
    selectedItemIndex.value = index;
  };

  const addToOrder = (item) => {
    const existingItemIndex = orderItems.value.findIndex(
      (i) => i.id === item.id && !i.variations
    );

    if (existingItemIndex !== -1) {
      orderItems.value[existingItemIndex].qty += 1;
    } else {
      orderItems.value.push({
        id: item.id,
        name: item.name,
        selling_price_dine_in: item.selling_price_dine_in || item.price,
        price: item.selling_price_dine_in || item.price,
        qty: 1,
        discount: 0,
      });
    }
  };

  const addVariationToOrder = (item) => {
    const existingItemIndex = orderItems.value.findIndex(
      (i) => i.id === item.id && i.variationKey === item.variationKey
    );

    if (existingItemIndex !== -1) {
      orderItems.value[existingItemIndex].qty += item.qty;
    } else {
      orderItems.value.push(item);
    }
  };

  const incrementQuantity = () => {
    if (selectedItemIndex.value >= 0) {
      orderItems.value[selectedItemIndex.value].qty++;
    }
  };

  const decrementQuantity = () => {
    if (
      selectedItemIndex.value >= 0 &&
      orderItems.value[selectedItemIndex.value].qty > 1
    ) {
      orderItems.value[selectedItemIndex.value].qty--;
    }
  };

  const removeCurrentItem = () => {
    if (selectedItemIndex.value >= 0) {
      orderItems.value.splice(selectedItemIndex.value, 1);
      selectedItemIndex.value = -1;
    }
  };

  const clearOrder = () => {
    orderItems.value = [];
    selectedItemIndex.value = -1;
  };

  const placeOrder = async () => {
    if (orderItems.value.length === 0) {
      toast("No items added to order", { type: "error" });
      return { success: false };
    }

    try {
      const orderData = {
        type: orderType.value,
        items: JSON.parse(JSON.stringify(orderItems.value)),
        employee: selectedEmployee.value,
        customer: selectedCustomer.value,
        date: new Date().toLocaleString(),
      };

      const response = await apiPlaceOrder(orderData, "Running");

      if (response.success) {
        clearOrder();
        toast("Order placed successfully", { type: "success" });
        return { success: true, data: response };
      } else {
        toast(response.message || "Failed to place order", { type: "error" });
        return { success: false, error: response.message };
      }
    } catch (error) {
      console.error("Failed to place order:", error);
      toast("Failed to place order", { type: "error" });
      return { success: false, error: error.message };
    }
  };

  const saveDraft = async () => {
    if (orderItems.value.length === 0) {
      toast("No items added to order", { type: "error" });
      return { success: false };
    }

    try {
      const draftOrderData = {
        type: orderType.value,
        items: JSON.parse(JSON.stringify(orderItems.value)),
        employee: selectedEmployee.value,
        customer: selectedCustomer.value,
        date: new Date().toLocaleString(),
      };

      const response = await apiPlaceOrder(draftOrderData, "Draft");

      if (response.success) {
        clearOrder();
        toast("Order saved as draft", { type: "success" });
        return { success: true, data: response };
      } else {
        toast(response.message || "Failed to save draft", { type: "error" });
        return { success: false, error: response.message };
      }
    } catch (error) {
      console.error("Failed to save draft:", error);
      toast("Failed to save draft", { type: "error" });
      return { success: false, error: error.message };
    }
  };

  return {
    // State
    orderItems,
    selectedItemIndex,
    orderType,
    selectedEmployee,
    selectedCustomer,

    // Computed
    hasSelectedItem,
    selectedItem,

    // Methods
    selectItem,
    addToOrder,
    addVariationToOrder,
    incrementQuantity,
    decrementQuantity,
    removeCurrentItem,
    clearOrder,
    placeOrder,
    saveDraft,
  };
}
