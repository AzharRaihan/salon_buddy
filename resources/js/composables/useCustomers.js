import { computed, ref } from "vue";
import { $api } from "@/utils/api";
import { toast } from 'vue3-toastify';

export function useCustomers() {
  // Reactive state
  const customers = ref([]);
  const isLoading = ref(false);
  const error = ref(null);
  const searchQuery = ref("");

  // Computed properties
  const filteredCustomers = computed(() => {
    if (!searchQuery.value) return customers.value;
    const query = searchQuery.value.toLowerCase();
    return customers.value.filter(
      (customer) =>
        customer.name.toLowerCase().includes(query) ||
        customer.phone?.toLowerCase().includes(query) ||
        customer.email?.toLowerCase().includes(query)
    );
  });

  // API methods
  const fetchCustomers = async () => {
    try {
      isLoading.value = true;
      error.value = null;

      const response = await $api("/get-all-customers-pos");

      if (!response.success) {
        throw new Error(`Customer not found`);
      }
      customers.value = response.data;
    } catch (err) {
      error.value = err.message;
      console.error("Error fetching customers:", err);
    } finally {
      isLoading.value = false;
    }
  };

  const getCustomer = async (customerId) => {
    try {
      isLoading.value = true;
      error.value = null;
      const response = await $api(`/customers/${customerId}`, {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
        },
      });
      if (!response.success) throw new Error('Customer not found');
      return response.data;
    } catch (err) {
      error.value = err.message;
      return null;
    } finally {
      isLoading.value = false;
    }
  };


  const createCustomer = async (customerData) => {
    try {
      // isLoading.value = true;
      error.value = null;
      const res = await $api('/customers', {
          method: 'POST',
          body: JSON.stringify(customerData),
          headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
          },
          onResponseError({ response }) {
              toast(response._data.message, {
                  type: 'error',
              })
              // isLoading.value = false
              return Promise.reject(response._data)
          },
      })

      const { status, message } = res

      if (status === 'error') {
          toast(message, {
              type: 'error',
          })
          // isLoading.value = false
          return
      }

      const newCustomer = res.data;
      // Add to the local list
      customers.value.unshift(newCustomer);
      toast('Customer created successfully', { type: 'success' })
      return { success: true, data: newCustomer };
    } catch (err) {
      if (err.errors) {
        // Show each validation error as a toast
        for (const [field, messages] of Object.entries(err.errors)) {
          messages.forEach(msg => {
            toast(msg, { type: 'error' })
          })
        }
      } else {
        // Show general error if no field-specific errors
        toast(message, {
          type: 'error',
        })
      }
      return { success: false, error: err.message };
    }
  }

  // const updateCustomer = async (customerId, customerData) => {
  //   try {
  //     isLoading.value = true;
  //     error.value = null;

  //     const response = await $api("/customers", {
  //       method: "POST",
  //       body: JSON.stringify(customerData),
  //       headers: {
  //         'Accept': 'application/json',
  //         'Content-Type': 'application/json',
  //       },
  //     });

  //     if (!response.success) {
  //       throw new Error(`Customer not updated`);
  //     }

  //     const updatedCustomer = response.data;

  //     // Update in local list
  //     const index = customers.value.findIndex((c) => c.id === customerId);
  //     if (index !== -1) {
  //       customers.value[index] = updatedCustomer;
  //     }

  //     return { success: true, data: updatedCustomer };
  //   } catch (err) {
  //     error.value = err.message;
  //     console.error("Error updating customer:", err);
  //     return { success: false, error: err.message };
  //   } finally {
  //     isLoading.value = false;
  //   }
  // };

  const updateCustomer = async (customerId, customerData) => {
    try {
      error.value = null;
      const response = await $api(`/customers/${customerId}`, {
        method: "PUT",
        body: JSON.stringify(customerData),
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
        onResponseError({ response }) {
          toast(response._data.message, {
              type: 'error',
          })
          return Promise.reject(response._data)
        },
      });
      const { status, message } = response
      if (status === 'error') {
        toast(message, {
          type: 'error',
        })
        return
      }
      const updatedCustomer = response.data;
      // Update in local list
      const index = customers.value.findIndex((c) => c.id === customerId);
      if (index !== -1) customers.value[index] = updatedCustomer;
      toast('Customer updated successfully', { type: 'success' })
      return { success: true, data: updatedCustomer };
    } catch (err) {
      if (err.errors) {
        // Show each validation error as a toast
        for (const [field, messages] of Object.entries(err.errors)) {
          messages.forEach(msg => {
            toast(msg, { type: 'error' })
          })
        }
      } else {
        // Show general error if no field-specific errors
        toast(message, {
          type: 'error',
        })
      }
      return { success: false, error: err.message };
    }
  };

  // Search functionality
  const setSearchQuery = (query) => {
    searchQuery.value = query;
  };

  const clearSearch = () => {
    searchQuery.value = "";
  };

  // Clear all data
  const clearCustomers = () => {
    customers.value = [];
    error.value = null;
    searchQuery.value = "";
  };

  return {
    // State
    customers,
    isLoading,
    error,
    searchQuery,

    // Computed
    filteredCustomers,

    // Methods
    fetchCustomers,
    getCustomer,
    createCustomer,
    updateCustomer,
    setSearchQuery,
    clearSearch,
    clearCustomers,
  };
}
