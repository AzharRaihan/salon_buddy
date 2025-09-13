/**
 * POS-related calculation functions
 */

/**
 * Calculate the total for a single item in the order
 * @param {Object} item - Order item with price, qty, and optional variations
 * @returns {number} The total price for the item
 */
export const calculateItemTotal = (item) => {
  if (!item) return 0;

  const basePrice = item.selling_price_dine_in * item.qty;
  const additionalCost = (item.additionalPrice || 0) * item.qty;
  return basePrice + additionalCost - (item.discount || 0);
};

/**
 * Calculate the subtotal for an entire order
 * @param {Array} items - Array of order items
 * @returns {number} The subtotal
 */
export const calculateSubtotal = (items = []) => {
  if (!items.length) return 0;

  return items.reduce((sum, item) => {
    return sum + calculateItemTotal(item);
  }, 0);
};

/**
 * Calculate tax amount based on subtotal and tax rate
 * @param {number} subtotal - Order subtotal
 * @param {number} taxRate - Tax rate as a percentage (e.g., 10 for 10%)
 * @returns {number} The tax amount
 */
export const calculateTax = (subtotal, taxRate = 10) => {
  return subtotal * (taxRate / 100);
};

/**
 * Calculate grand total including tax
 * @param {number} subtotal - Order subtotal
 * @param {number} taxRate - Tax rate as a percentage
 * @returns {number} The grand total
 */
export const calculateGrandTotal = (subtotal, taxRate = 10) => {
  return subtotal + calculateTax(subtotal, taxRate);
};

/**
 * Format a number as currency
 * @param {number} value - The number to format
 * @param {string} currencySymbol - Currency symbol to use
 * @returns {string} Formatted currency string
 */
export const formatCurrency = (value, currencySymbol = "$") => {
  if (isNaN(value)) return `${currencySymbol}0.00`;
  return `${currencySymbol}${parseFloat(value).toFixed(2)}`;
};

/**
 * Process selected variations to generate a list of variations and calculate additional price
 * @param {Object} item - Menu item with variations
 * @param {Object} selectedVariations - The user's selected variations
 * @returns {Object} Object containing the processed variations and additional price
 */
export const processVariations = (item, selectedVariations) => {
  const variations = [];
  let additionalPrice = 0;

  if (!item || !item.variationGroups || !selectedVariations) {
    return { variations, additionalPrice };
  }

  item.variationGroups.forEach((group, groupIndex) => {
    if (group.selectionType === "single" && selectedVariations[groupIndex]) {
      const option = selectedVariations[groupIndex];
      variations.push({
        name: `${group.name}: ${option.name}`,
        priceAdjustment: option.priceAdjustment,
      });
      additionalPrice += option.priceAdjustment;
    } else if (group.selectionType === "multiple") {
      Object.keys(selectedVariations[groupIndex] || {}).forEach((optionId) => {
        if (selectedVariations[groupIndex][optionId]) {
          const option = group.options.find((o) => o.id === optionId);
          if (option) {
            variations.push({
              name: `${group.name}: ${option.name}`,
              priceAdjustment: option.priceAdjustment,
            });
            additionalPrice += option.priceAdjustment;
          }
        }
      });
    }
  });

  return { variations, additionalPrice };
};

/**
 * Generate a unique key for a variation combination
 * @param {Array} variations - Array of variations
 * @returns {string} A unique key for the variation combination
 */
export const generateVariationKey = (variations) => {
  if (!variations || !variations.length) return "";
  return variations.map((v) => `${v.name}:${v.priceAdjustment}`).join("|");
};
