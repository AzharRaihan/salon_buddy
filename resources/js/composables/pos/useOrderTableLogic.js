import { computed } from "vue";

/**
 * Order Table Logic Composable
 *
 * Handles all business logic for the OrderTable component including:
 * - Item calculations and formatting
 * - Variation handling
 * - Price calculations
 * - Data validation
 *
 * @param {Object} props - Component props
 * @returns {Object} - Composable methods and computed properties
 */
export function useOrderTableLogic(props) {
  // Computed properties
  const hasItems = computed(() => {
    return Array.isArray(props.items) && props.items.length > 0;
  });

  const itemsCount = computed(() => {
    return props.items?.length || 0;
  });

  // Helper functions for item calculations
  const hasVariations = (item) => {
    if (!item) return false;
    return (
      item.variations &&
      Array.isArray(item.variations) &&
      item.variations.length > 0
    );
  };

  const getItemPrice = (item) => {
    if (!item) return 0;

    // Priority order for price fields (common in Laravel apps)
    return (
      item.selling_price_dine_in ||
      item.selling_price ||
      item.price ||
      item.unit_price ||
      0
    );
  };

  const formatPrice = (price) => {
    const numPrice = parseFloat(price) || 0;
    return numPrice.toFixed(2);
  };

  const formatCurrency = (price, currency = "$") => {
    return `${currency}${formatPrice(price)}`;
  };

  const calculateItemTotal = (item) => {
    if (!item) return 0;

    const itemPrice = getItemPrice(item);
    const quantity = parseInt(item.qty) || 0;
    const itemTotal = itemPrice * quantity;

    // Calculate variations total if they exist
    const variationsTotal = hasVariations(item)
      ? item.variations.reduce((sum, variation) => {
          const variationPrice = parseFloat(variation.price) || 0;
          const variationQty = parseInt(variation.qty) || 1;
          return sum + variationPrice * variationQty;
        }, 0) * quantity
      : 0;

    return itemTotal + variationsTotal;
  };

  const calculateVariationTotal = (item) => {
    if (!hasVariations(item)) return 0;

    return item.variations.reduce((sum, variation) => {
      const variationPrice = parseFloat(variation.price) || 0;
      const variationQty = parseInt(variation.qty) || 1;
      const itemQty = parseInt(item.qty) || 0;
      return sum + variationPrice * variationQty * itemQty;
    }, 0);
  };

  const getItemSubtotal = (item) => {
    if (!item) return 0;

    const itemPrice = getItemPrice(item);
    const quantity = parseInt(item.qty) || 0;
    return itemPrice * quantity;
  };

  // Item validation helpers
  const isValidItem = (item) => {
    return (
      item &&
      typeof item === "object" &&
      item.hasOwnProperty("id") &&
      item.hasOwnProperty("name")
    );
  };

  const isValidQuantity = (qty) => {
    const quantity = parseInt(qty);
    return quantity > 0 && quantity <= 999; // reasonable limit
  };

  const isValidPrice = (price) => {
    const numPrice = parseFloat(price);
    return numPrice >= 0 && numPrice <= 9999999; // reasonable limit
  };

  // Formatting helpers
  const formatItemName = (item) => {
    if (!item || !item.name) return "Unknown Item";
    return item.name.trim();
  };

  const formatItemNote = (note) => {
    if (!note || typeof note !== "string") return "";
    return note.trim().substring(0, 100); // Limit note length
  };

  const getItemDisplayData = (item) => {
    if (!isValidItem(item)) return null;

    return {
      id: item.id,
      name: formatItemName(item),
      price: getItemPrice(item),
      quantity: parseInt(item.qty) || 0,
      subtotal: getItemSubtotal(item),
      variationsTotal: calculateVariationTotal(item),
      total: calculateItemTotal(item),
      note: formatItemNote(item.note),
      hasVariations: hasVariations(item),
      variations: item.variations || [],
    };
  };

  // Table summary calculations
  const calculateTableSummary = () => {
    if (!hasItems.value) {
      return {
        totalItems: 0,
        totalQuantity: 0,
        subtotal: 0,
        variationsTotal: 0,
        grandTotal: 0,
      };
    }

    const summary = props.items.reduce(
      (acc, item) => {
        if (isValidItem(item)) {
          acc.totalItems += 1;
          acc.totalQuantity += parseInt(item.qty) || 0;
          acc.subtotal += getItemSubtotal(item);
          acc.variationsTotal += calculateVariationTotal(item);
          acc.grandTotal += calculateItemTotal(item);
        }
        return acc;
      },
      {
        totalItems: 0,
        totalQuantity: 0,
        subtotal: 0,
        variationsTotal: 0,
        grandTotal: 0,
      }
    );

    return summary;
  };

  const tableSummary = computed(() => calculateTableSummary());

  // Search and filter helpers
  const filterItemsBySearch = (searchQuery) => {
    if (!searchQuery || !hasItems.value) return props.items;

    const query = searchQuery.toLowerCase().trim();
    return props.items.filter((item) => {
      if (!isValidItem(item)) return false;

      const itemName = item.name?.toLowerCase() || "";
      const itemNote = item.note?.toLowerCase() || "";

      // Search in variations too
      const variationMatch = hasVariations(item)
        ? item.variations.some((variation) =>
            variation.name?.toLowerCase().includes(query)
          )
        : false;

      return (
        itemName.includes(query) || itemNote.includes(query) || variationMatch
      );
    });
  };

  // Error handling
  const validateItemData = (item) => {
    const errors = [];

    if (!isValidItem(item)) {
      errors.push("Invalid item structure");
    }

    if (!isValidQuantity(item.qty)) {
      errors.push("Invalid quantity");
    }

    if (!isValidPrice(getItemPrice(item))) {
      errors.push("Invalid price");
    }

    if (hasVariations(item)) {
      item.variations.forEach((variation, index) => {
        if (!isValidPrice(variation.price)) {
          errors.push(`Invalid variation price at index ${index}`);
        }
      });
    }

    return {
      isValid: errors.length === 0,
      errors,
    };
  };

  // Debug helpers
  const getDebugInfo = () => {
    return {
      itemsCount: itemsCount.value,
      hasItems: hasItems.value,
      tableSummary: tableSummary.value,
      items: props.items?.map((item) => getItemDisplayData(item)),
    };
  };

  return {
    // Computed properties
    hasItems,
    itemsCount,
    tableSummary,

    // Core calculation functions
    hasVariations,
    getItemPrice,
    formatPrice,
    formatCurrency,
    calculateItemTotal,
    calculateVariationTotal,
    getItemSubtotal,

    // Validation functions
    isValidItem,
    isValidQuantity,
    isValidPrice,
    validateItemData,

    // Formatting functions
    formatItemName,
    formatItemNote,
    getItemDisplayData,

    // Utility functions
    filterItemsBySearch,
    calculateTableSummary,

    // Debug functions
    getDebugInfo,
  };
}
