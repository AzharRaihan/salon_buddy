import { $api } from "../api";
/**
 * POS-related API functions
 */

/**
 * Fetch item menu categories
 * @returns {Promise} API response with categories
 */
export const fetchItemMenuCategories = async () => {
  try {
    const response = await $api("/get-category-list", {
      method: "GET",
    });
    return response.data || [];
  } catch (error) {
    console.error("Failed to fetch item menu categories:", error);
    return [];
  }
};

/**
 * Fetch item menu items
 * @param {Object} params - Query parameters
 * @returns {Promise} API response with item menu items
 */
export const fetchItemMenuItems = async (params = {}) => {
  try {
    const response = await $api("/get-all-type-item-list", {
      method: "GET",
      params,
    });
    return {
      items: response.data || [],
      total: response.data?.length || 0,
    };
  } catch (error) {
    console.error("Failed to fetch item menu items:", error);
    return { items: [], total: 0 };
  }
};

/**
 * Get item menus (wrapper for fetchItemMenuItems)
 * @param {Object} params - Query parameters
 * @returns {Promise} API response in expected format for menuStore
 */
export const getItemMenus = async (params = {}) => {
  try {
    const result = await fetchItemMenuItems(params);
    return {
      success: true,
      data: result.items,
      total: result.total,
    };
  } catch (error) {
    console.error("Failed to get item menus:", error);
    return {
      success: false,
      data: [],
      message: error.message || "Failed to fetch item menu items",
    };
  }
};

/**
 * Fetch running orders
 * @returns {Promise} API response with running orders
 */
export const fetchRunningOrders = async () => {
  try {
    const response = await $api("/pos/running-orders", {
      method: "GET",
    });
    return response.data?.orders || [];
  } catch (error) {
    console.error("Failed to fetch running orders:", error);
    return [];
  }
};

/**
 * Fetch customers for POS
 * @returns {Promise} API response with customers
 */
export const fetchCustomers = async () => {
  try {
    const response = await $api("/customer", {
      method: "GET",
    });
    if (response.success) {
      return response.data?.customers || [];
    }
    return [];
  } catch (error) {
    console.error("Failed to fetch customers:", error);
    return [];
  }
};

/**
 * Save a new customer
 * @param {Object} customerData - Customer data
 * @returns {Promise} API response
 */
export const saveCustomer = async (customerData) => {
  try {
    const response = await $api("/customer", {
      method: "POST",
      body: customerData,
    });
    return response;
  } catch (error) {
    console.error("Failed to save customer:", error);
    throw error;
  }
};

/**
 * Place an order
 * @param {Object} orderData - Order data
 * @param {String} status - Order status (Draft or Running)
 * @returns {Promise} API response
 */
export const placeOrder = async (orderData, status = "Running") => {
  try {
    // Ensure we have the status in the order data
    const orderPayload = {
      ...orderData,
      status: status,
    };

    const response = await $api("/pos/place-order", {
      method: "POST",
      body: orderPayload,
    });
    return response;
  } catch (error) {
    console.error("Failed to place order:", error);
    throw error;
  }
};
