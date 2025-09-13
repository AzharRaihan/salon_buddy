import { $api } from "@/utils/api";
import { computed, ref } from "vue";

export function useEmployees() {
  // Reactive state
  const employees = ref([]);
  const isLoading = ref(false);
  const error = ref(null);
  const searchQuery = ref("");

  // Computed properties
  const filteredEmployees = computed(() => {
    if (!searchQuery.value) return employees.value;
    const query = searchQuery.value.toLowerCase();
    return employees.value.filter(
      (employee) =>
        employee.name.toLowerCase().includes(query) ||
        employee.phone?.toLowerCase().includes(query) ||
        employee.email?.toLowerCase().includes(query) ||
        employee.employee_id?.toLowerCase().includes(query)
    );
  });

  // API methods
  const fetchEmployee = async () => {
    try {
      isLoading.value = true;
      error.value = null;
      // Fetch users with role = 3 (waiters)
      const response = await $api("/get-all-users");

      if (!response.success) {
        throw new Error(`Employee not found`);
      }
      const data = response.data;
      employees.value = data || []; // Handle different response structures
    } catch (err) {
      error.value = err.message;
      console.error("Error fetching employees:", err);
    } finally {
      isLoading.value = false;
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
  const clearEmployees = () => {
    employees.value = [];
    error.value = null;
    searchQuery.value = "";
  };

  return {
    // State
    employees,
    isLoading,
    error,
    searchQuery,

    // Computed
    filteredEmployees,

    // Methods
    fetchEmployee,
    setSearchQuery,
    clearSearch,
    clearEmployees,
  };
}
