import { placeOrder as apiPlaceOrder } from "@/utils/pos/api";
import { defineStore } from "pinia";
import { computed, ref, watch } from "vue";
import { toast } from "vue3-toastify";
import { useTaxCalculation } from '../../composables/useTaxCalculation';
import { usePromotions } from '../../composables/pos/usePromotions';

const branch_info = useCookie("branch_info").value || 0;

export const useOrderStore = defineStore("pos-order", () => {
  // State
  const orderItems = ref([]);
  const selectedItemIndex = ref(-1);
  const orderType = ref("Dine In");
  const selectedEmployee = ref("");
  const selectedEmployeeId = ref("");
  const selectedCustomer = ref("");
  const selectedCustomerId = ref("");
  const orderDate = ref(new Date().toISOString().split("T")[0]); // Today's date by default

  // Loading states
  const isPlacingOrder = ref(false);
  const isSavingDraft = ref(false);
  const isLoadingOrder = ref(false);

  // Error states
  const orderError = ref(null);
  const draftError = ref(null);

  // Discount and charges
  const discountAmount = ref(0);
  const discountType = ref("percentage"); // 'percentage' or 'fixed'
  const taxRate = ref(0.1); // 10% default tax
  const serviceCharge = ref(0);

  // Order history for undo/redo
  const orderHistory = ref([]);
  const historyIndex = ref(-1);

  // Initialize promotions composable
  const { applyPromotionsToOrder, calculateOrderDiscount, calculateSubtotalExcludingFree, fetchPromotions } = usePromotions();

  // Fetch promotions on store initialization
  fetchPromotions();

  // Computed properties
  const hasSelectedItem = computed(() => selectedItemIndex.value >= 0);

  const selectedItem = computed(() => {
    if (selectedItemIndex.value >= 0) {
      return orderItems.value[selectedItemIndex.value];
    }
    return null;
  });

  const {
    companyInfo,
    fetchCompanyTaxSettings,
    items,
    fetchItemsWithTax,
    customerInfo,
    getCustomerState,
    calculateItemTax,
    fetchCustomerInfo,
    updateCustomerAndRecalculateTax,
  } = useTaxCalculation();

  fetchCompanyTaxSettings();
  fetchItemsWithTax();

  // Watch for customer changes and fetch customer info
  watch(selectedCustomerId, async (newCustomerId) => {
    if (newCustomerId) {
      await updateCustomerAndRecalculateTax(newCustomerId)
    } else {
      await updateCustomerAndRecalculateTax(null)
    }
  })

  // Calculate subtotal excluding free items and including discounts
  const subtotal = computed(() => {
    return calculateSubtotalExcludingFree(orderItems.value);
  });

  // Calculate tax for all items
  const taxAmount = computed(() => {
    let totalTax = 0;
    orderItems.value.forEach(item => {
      if (!item.isFree) {
        const taxResult = calculateItemTax(item.id, item.qty, item.sale_price || item.price, getCustomerState.value);
        totalTax += taxResult.totalTax;
      }
    });
    return totalTax;
  });

  // Get tax breakdown for all items
  const taxBreakdown = computed(() => {
    const breakdown = {};
    orderItems.value.forEach(item => {
      if (!item.isFree) {
        const taxResult = calculateItemTax(item.id, item.qty, item.sale_price || item.price, getCustomerState.value);
        Object.keys(taxResult.taxBreakdown).forEach(taxName => {
          if (!breakdown[taxName]) {
            breakdown[taxName] = 0;
          }
          breakdown[taxName] += taxResult.taxBreakdown[taxName];
        });
      }
    });
    
    // Round all values
    Object.keys(breakdown).forEach(taxName => {
      breakdown[taxName] = parseFloat(breakdown[taxName].toFixed(2));
    });
    
    return breakdown;
  });

  const grandTotal = computed(() => {
    // Don't subtract promotion discount again since it's already applied to individual items
    // But DO subtract manual discount from the final total
    const total = subtotal.value + taxAmount.value + parseFloat(serviceCharge.value) - discountValue.value;
    console.log('Grand Total calculation:', {
      subtotal: subtotal.value,
      tax: taxAmount.value,
      serviceCharge: parseFloat(serviceCharge.value),
      manualDiscount: discountValue.value,
      grandTotal: total
    });
    return total;
  });

  const discountValue = computed(() => {
    // Only include manual discount, not promotion discount
    let manualDiscount = 0;
    
    if (discountType.value === "percentage") {
      manualDiscount = subtotal.value * (parseFloat(discountAmount.value) / 100);
      console.log('Manual percentage discount:', discountAmount.value + '%', 'Amount:', manualDiscount, 'Subtotal:', subtotal.value);
    } else {
      manualDiscount = parseFloat(discountAmount.value) || 0;
      console.log('Manual fixed discount:', manualDiscount);
    }
    
    return manualDiscount;
  });

  // Separate computed property for promotion discount display
  const promotionDiscountValue = computed(() => {
    return calculateOrderDiscount(orderItems.value);
  });

  const orderSummary = computed(() => ({
    subtotal: subtotal.value,
    discount: discountValue.value,
    promotionDiscount: promotionDiscountValue.value,
    tax: taxAmount.value,
    charge: serviceCharge.value,
    grandTotal: grandTotal.value,
    itemCount: orderItems.value.length,
    totalQuantity: orderItems.value.reduce((total, item) => total + item.qty, 0),
    branch_id: branch_info.id,
  }));

  const isOrderEmpty = computed(() => orderItems.value.length === 0);

  const canUndo = computed(() => historyIndex.value > 0);
  const canRedo = computed(
    () => historyIndex.value < orderHistory.value.length - 1
  );

  // Actions
  const saveToHistory = () => {
    const currentState = JSON.stringify({
      orderItems: orderItems.value,
      selectedItemIndex: selectedItemIndex.value,
    });

    // Remove any history after current index
    orderHistory.value = orderHistory.value.slice(0, historyIndex.value + 1);
    orderHistory.value.push(currentState);
    historyIndex.value = orderHistory.value.length - 1;

    // Keep only last 20 states
    if (orderHistory.value.length > 20) {
      orderHistory.value.shift();
      historyIndex.value--;
    }
  };

  const undo = () => {
    if (canUndo.value) {
      historyIndex.value--;
      const state = JSON.parse(orderHistory.value[historyIndex.value]);
      orderItems.value = state.orderItems;
      selectedItemIndex.value = state.selectedItemIndex;
    }
  };

  const redo = () => {
    if (canRedo.value) {
      historyIndex.value++;
      const state = JSON.parse(orderHistory.value[historyIndex.value]);
      orderItems.value = state.orderItems;
      selectedItemIndex.value = state.selectedItemIndex;
    }
  };

  const selectItem = (index) => {
    selectedItemIndex.value = index;
  };

  const addToOrder = async (item) => {
    saveToHistory();
    // Check if item already exists in order (by ID and not free)
    const existingItemIndex = orderItems.value.findIndex(
      (orderItem) => orderItem.id === item.id && !orderItem.isFree
    );

    if (existingItemIndex !== -1) {
      // Increment quantity of existing item
      orderItems.value[existingItemIndex].qty += 1;
      
      // Recalculate promotions for this item
      await recalculateItemPromotions(existingItemIndex);
    } else {
             // Create new item with proper structure
       const newItem = {
         id: item.id,
         name: item.name,
         type: item.type || 'Product',
         sale_price: parseFloat(item.sale_price || item.price || 0),
         price: parseFloat(item.sale_price || item.price || 0),
         qty: 1,
         discount: parseFloat(item.discount || 0),
         promotionDiscount: parseFloat(item.discount || 0), // Store promotion discount for database
         branch_id: branch_info.id,
         assignedEmployee: null,
         promotions: item.promotions || [],
         freeItems: item.freeItems || [],
         hasDiscount: item.hasDiscount || false,
         hasFreeItems: item.hasFreeItems || false,
         isFree: false
       };
      orderItems.value.push(newItem);
      // Apply promotions to the entire order
      await applyPromotionsToOrderItems();
    }
    
    clearErrors();
  };

  // Recalculate promotions for a specific item
  const recalculateItemPromotions = async (itemIndex) => {
    const item = orderItems.value[itemIndex];
    if (!item) return;
    if (item.isFree) {
      return;
    }
    // Recalculate discount for this item
    const { calculateItemDiscount, getFreeItems } = usePromotions();
    const discount = calculateItemDiscount(item, item.qty);
    item.discount = discount;
    item.promotionDiscount = discount; // Update promotion discount for database
    
    // Recalculate free items for this specific item
    const freeItems = await getFreeItems(item, item.qty);
    item.freeItems = freeItems;
    item.hasFreeItems = freeItems.length > 0;
    
    // Apply promotions to entire order to update free items
    await applyPromotionsToOrderItems();
  };

  // Apply promotions to all order items
  const applyPromotionsToOrderItems = async () => {
    const updatedItems = await applyPromotionsToOrder(orderItems.value);
    orderItems.value = updatedItems;
  };

  const addVariationToOrder = (item) => {
    saveToHistory();

    const existingItemIndex = orderItems.value.findIndex(
      (i) => i.id === item.id && i.variationKey === item.variationKey
    );

    if (existingItemIndex !== -1) {
      orderItems.value[existingItemIndex].qty += item.qty;
    } else {
      orderItems.value.push(item);
    }

    clearErrors();
  };

  const updateItemQuantity = async (index, quantity) => {
    if (index >= 0 && index < orderItems.value.length && quantity > 0) {
      const item = orderItems.value[index]
      // Prevent actions on free items
      if (item.isFree) {
        return
      }
      
      saveToHistory();
      orderItems.value[index].qty = quantity;
      
      // Recalculate promotions for this item
      await recalculateItemPromotions(index);
    }
  };

  const incrementQuantity = async (index = selectedItemIndex.value) => {
    if (index >= 0) {
      const item = orderItems.value[index]

      // Prevent actions on free items
      if (item.isFree) {
        return
      }
      
      saveToHistory();
      orderItems.value[index].qty++;
      
      // Recalculate promotions for this item
      await recalculateItemPromotions(index);
    }
  };

  const decrementQuantity = async (index = selectedItemIndex.value) => {
    if (index >= 0 && orderItems.value[index].qty > 1) {
      const item = orderItems.value[index]
      
      // Prevent actions on free items
      if (item.isFree) {
        return
      }
      
      saveToHistory();
      orderItems.value[index].qty--;
      
      // Recalculate promotions for this item
      await recalculateItemPromotions(index);
    }
  };

  // const removeItem = async (index = selectedItemIndex.value) => {
  //   if (index >= 0) {
  //     const item = orderItems.value[index]
      
  //     // Prevent actions on free items
  //     if (item.isFree) {
  //       console.log('Cannot remove free item:', item.name)
  //       return
  //     }
      
  //     saveToHistory();
  //     orderItems.value.splice(index, 1);
  //     if (selectedItemIndex.value >= orderItems.value.length) {
  //       selectedItemIndex.value = orderItems.value.length - 1;
  //     }
      
  //     // Reapply promotions after removing item
  //     await applyPromotionsToOrderItems();
  //   }
  // };

  const removeItem = async (index = selectedItemIndex.value) => {
    if (index >= 0) {
      const item = orderItems.value[index];
      if (item.isFree) {
        return;
      }
      saveToHistory();
      orderItems.value.splice(index, 1);
      selectedItemIndex.value = -1;
      await applyPromotionsToOrderItems();
    }
  };


  const updateItemNote = (index, note) => {
    if (index >= 0 && index < orderItems.value.length) {
      saveToHistory();
      orderItems.value[index].note = note;
    }
  };

  const assignEmployeeToService = (itemId, employee, price) => {
    const itemIndex = orderItems.value.findIndex(item => item.id === itemId);
    if (itemIndex >= 0) {
      saveToHistory();
      // Always assign as an object with at least an id property
      orderItems.value[itemIndex].assignedEmployee = employee && employee.id ? { id: employee.id, name: employee.name } : null;
      // If price is provided, update the price
      if (typeof price === 'number' && !isNaN(price) && price >= 0) {
        orderItems.value[itemIndex].price = price;
        orderItems.value[itemIndex].sale_price = price;
      }
    }
  };

  // Update service price only
  const updateServicePrice = (itemId, price) => {
    const itemIndex = orderItems.value.findIndex(item => item.id === itemId);
    if (itemIndex >= 0 && typeof price === 'number' && !isNaN(price) && price >= 0) {
      saveToHistory();
      orderItems.value[itemIndex].price = price;
      orderItems.value[itemIndex].sale_price = price;
    }
  };

  const setDiscount = (amount, type = "percentage") => {
    console.log('Setting discount:', { amount, type, currentDiscountAmount: discountAmount.value, currentDiscountType: discountType.value });
    discountAmount.value = Math.max(0, amount);
    discountType.value = type;
    console.log('Discount set successfully:', { newAmount: discountAmount.value, newType: discountType.value });
  };

  const clearDiscount = () => {
    console.log('Clearing discount');
    discountAmount.value = 0;
    discountType.value = "percentage";
  };

  const setServiceCharge = (amount) => {
    serviceCharge.value = Math.max(0, amount);
  };

  const setOrderDate = (date) => {
    orderDate.value = date;
  };

  const clearErrors = () => {
    orderError.value = null;
    draftError.value = null;
  };

  const clearOrder = () => {
    saveToHistory();
    orderItems.value = [];
    selectedItemIndex.value = -1;
    discountAmount.value = 0;
    serviceCharge.value = 0;
    clearErrors();
  };


  const placeOrder = async () => {
    if (orderItems.value.length === 0) {
      orderError.value = "No items added to order";
      toast("No items added to order", { type: "error" });
      return { success: false, error: orderError.value };
    }

    isPlacingOrder.value = true;
    orderError.value = null;

    try {
      const orderData = {
        type: orderType.value,
        items: JSON.parse(JSON.stringify(orderItems.value)),
        employee: selectedEmployee.value,
        customer: selectedCustomer.value,
        summary: orderSummary.value,
        date: new Date().toISOString(),
      };

      const response = await apiPlaceOrder(orderData, "Running");

      if (response.success) {
        clearOrder();
        toast("Order placed successfully", { type: "success" });
        return { success: true, data: response };
      } else {
        orderError.value = response.message || "Failed to place order";
        toast(orderError.value, { type: "error" });
        return { success: false, error: orderError.value };
      }
    } catch (error) {
      console.error("Failed to place order:", error);
      orderError.value = error.message || "Failed to place order";
      toast(orderError.value, { type: "error" });
      return { success: false, error: orderError.value };
    } finally {
      isPlacingOrder.value = false;
    }
  };

  const saveDraft = async () => {
    if (orderItems.value.length === 0) {
      draftError.value = "No items added to order";
      toast("No items added to order", { type: "error" });
      return { success: false, error: draftError.value };
    }

    isSavingDraft.value = true;
    draftError.value = null;

    try {
      const draftOrderData = {
        type: orderType.value,
        items: JSON.parse(JSON.stringify(orderItems.value)),
        employee: selectedEmployee.value,
        customer: selectedCustomer.value,
        summary: orderSummary.value,
        date: new Date().toISOString(),
      };

      const response = await apiPlaceOrder(draftOrderData, "Draft");

      if (response.success) {
        clearOrder();
        toast("Order saved as draft", { type: "success" });
        return { success: true, data: response };
      } else {
        draftError.value = response.message || "Failed to save draft";
        toast(draftError.value, { type: "error" });
        return { success: false, error: draftError.value };
      }
    } catch (error) {
      console.error("Failed to save draft:", error);
      draftError.value = error.message || "Failed to save draft";
      toast(draftError.value, { type: "error" });
      return { success: false, error: draftError.value };
    } finally {
      isSavingDraft.value = false;
    }
  };

  // Persistence helpers
  const saveToLocalStorage = () => {
    try {
      const orderState = {
        orderItems: orderItems.value,
        orderType: orderType.value,
        selectedEmployee: selectedEmployee.value,
        selectedCustomer: selectedCustomer.value,
        discountAmount: discountAmount.value,
        discountType: discountType.value,
        serviceCharge: serviceCharge.value,
        timestamp: Date.now(),
      };
      localStorage.setItem("pos-order-state", JSON.stringify(orderState));
    } catch (error) {
      console.warn("Failed to save order to localStorage:", error);
    }
  };

  const loadFromLocalStorage = () => {
    try {
      const savedState = localStorage.getItem("pos-order-state");
      if (savedState) {
        const orderState = JSON.parse(savedState);

        // Only load if saved within last 24 hours
        if (Date.now() - orderState.timestamp < 24 * 60 * 60 * 1000) {
          orderItems.value = orderState.orderItems || [];
          orderType.value = orderState.orderType || "Dine In";
          selectedEmployee.value = orderState.selectedEmployee || "";
          selectedCustomer.value = orderState.selectedCustomer || "";
          discountAmount.value = orderState.discountAmount || 0;
          discountType.value = orderState.discountType || "percentage";
          serviceCharge.value = orderState.serviceCharge || 0;
        }
      }
    } catch (error) {
      console.warn("Failed to load order from localStorage:", error);
    }
  };

  const clearLocalStorage = () => {
    try {
      localStorage.removeItem("pos-order-state");
    } catch (error) {
      console.warn("Failed to clear localStorage:", error);
    }
  };

  const initializeOrder = () => {
    try {
      // Load any saved state from localStorage
      loadFromLocalStorage();

      // Clear any existing errors
      clearErrors();

      // Initialize order history with current state
      const initialState = JSON.stringify({
        orderItems: orderItems.value,
        selectedItemIndex: selectedItemIndex.value,
      });
      orderHistory.value = [initialState];
      historyIndex.value = 0;
    } catch (error) {
      console.warn("Failed to initialize order store:", error);
      // Reset to clean state if initialization fails
      orderItems.value = [];
      selectedItemIndex.value = -1;
      clearErrors();
    }
  };

  // Watch for changes in orderItems
  watch(orderItems, () => {
    if (orderItems.value.length === 0) {
      discountAmount.value = 0;
      discountType.value = "percentage";
      serviceCharge.value = 0;
      clearErrors();
    }
  });

  return {
    // State
    orderItems,
    selectedItemIndex,
    orderType,
    selectedEmployee,
    selectedEmployeeId, 
    selectedCustomer,
    selectedCustomerId,
    discountAmount,
    discountType,
    taxRate,
    serviceCharge,
    orderDate,

    // Loading states
    isPlacingOrder,
    isSavingDraft,
    isLoadingOrder,

    // Error states
    orderError,
    draftError,

    // Computed
    hasSelectedItem,
    selectedItem,
    subtotal,
    discountValue,
    promotionDiscountValue,
    taxAmount,
    taxBreakdown,
    grandTotal,
    orderSummary,
    isOrderEmpty,
    canUndo,
    canRedo,

    // Actions
    selectItem,
    addToOrder,
    addVariationToOrder,
    updateItemQuantity,
    incrementQuantity,
    decrementQuantity,
    removeItem,
    updateItemNote,
    assignEmployeeToService,
    updateServicePrice,
    setDiscount,
    clearDiscount,
    setServiceCharge,
    setOrderDate,
    clearOrder,
    placeOrder,
    saveDraft,
    undo,
    redo,
    clearErrors,

    // Persistence
    saveToLocalStorage,
    loadFromLocalStorage,
    clearLocalStorage,
    initializeOrder,
  };
});
