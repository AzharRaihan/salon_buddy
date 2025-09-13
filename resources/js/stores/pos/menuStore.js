import { getItemMenus } from "@/utils/pos/api";
import { defineStore } from "pinia";
import { computed, ref, watch } from "vue";
import { toast } from "vue3-toastify";

export const useMenuStore = defineStore("pos-menu", () => {
  // State
  const menuItems = ref([]);
  const categories = ref([]);
  const selectedCategory = ref("");
  const searchQuery = ref("");

  // Loading states
  const isLoadingMenu = ref(false);
  const isRefreshing = ref(false);

  // Error states
  const menuError = ref(null);

  // Cache management
  const lastFetchTime = ref(0);
  // const cacheExpiry = 5 * 60 * 1000; // 5 minutes
  const cacheExpiry = 10 * 1000; // 1 minute

  // Category styling options
  // const categoryClassNames = [
  //   "btn-teal w-100 mb-2",
  //   "btn-orange w-100 mb-2",
  //   "btn-yellow text-white w-100 mb-2",
  //   "btn-blue w-100 mb-2",
  //   "btn-purple w-100 mb-2",
  // ];

  // Helper function to get random className
  // const getRandomClassName = () => {
  //   return categoryClassNames[
  //     Math.floor(Math.random() * categoryClassNames.length)
  //   ];
  // };

  // Computed properties
  const filteredMenuItems = computed(() => {
    let items = menuItems.value;

    // Apply category filter
    if (selectedCategory.value && selectedCategory.value !== "All Category") {
      // Regular category filtering - check both possible data structures
      items = items.filter(
        (item) =>
          item.category?.name === selectedCategory.value ||
          item.item_menu_category?.name === selectedCategory.value
      );
    }

    // Apply search filter
    if (searchQuery.value.trim()) {
      const query = searchQuery.value.toLowerCase().trim();
      items = items.filter(
        (item) =>
          item.name.toLowerCase().includes(query) ||
          item.description?.toLowerCase().includes(query) ||
          item.category?.name?.toLowerCase().includes(query) ||
          item.item_menu_category?.name?.toLowerCase().includes(query) ||
          item.code?.toLowerCase().includes(query)
      );
    }

    return items;
  });

  const availableCategories = computed(() => {
    const categoryMap = new Map();

    categoryMap.set("All Category", {
      id: 0,
      name: "All Category",
      className: "btn-primary w-100 mb-2",
      count: menuItems.value.length,
    });

    // Add dynamic categories from menu items
    let nextId = 1;
    menuItems.value.forEach((item) => {
      // Check both possible data structures for category
      const categoryName = item.category?.name || item.item_menu_category?.name;

      if (categoryName) {
        if (!categoryMap.has(categoryName)) {
          categoryMap.set(categoryName, {
            id: nextId++,
            name: categoryName,
            className: 'btn-primary w-100 mb-2',
            count: 0,
          });
        }
        // Increment count for this category
        const category = categoryMap.get(categoryName);
        category.count++;
      }
    });

    return Array.from(categoryMap.values());
  });

  const menuStats = computed(() => ({
    totalItems: menuItems.value.length,
    filteredItems: filteredMenuItems.value.length,
    categories: availableCategories.value.length - 1, // Exclude 'All'
    vegetarianItems: menuItems.value.filter(
      (item) =>
        item.is_vegetarian ||
        item.is_veg_item === 1 ||
        item.is_veg_item === true
    ).length,
    comboItems: menuItems.value.filter(
      (item) => item.is_combo || item.type === "combo"
    ).length,
  }));

  const needsRefresh = computed(() => {
    return Date.now() - lastFetchTime.value > cacheExpiry;
  });

  // Actions
  const clearError = () => {
    menuError.value = null;
  };

  const setCategory = (categoryName) => {
    selectedCategory.value = categoryName;
    clearError();
  };

  const setSearchQuery = (query) => {
    searchQuery.value = query;
  };

  const clearAllFilters = () => {
    selectedCategory.value = "All Category";
    searchQuery.value = "";
  };

  const fetchMenuItems = async (force = false) => {
    // Check if we need to fetch (force or cache expired)
    if (!force && !needsRefresh.value && menuItems.value.length > 0) {
      return { success: true, fromCache: true };
    }

    isLoadingMenu.value = true;
    menuError.value = null;

    try {
      const response = await getItemMenus();

      if (response.success) {
        menuItems.value = response.data || [];
        lastFetchTime.value = Date.now();

        // Set default category if none selected
        if (!selectedCategory.value) {
          selectedCategory.value = "All Category";
        }

        saveToCache();
        return { success: true, data: response.data };
      } else {
        menuError.value = response.message || "Failed to fetch menu items";
        toast(menuError.value, { type: "error" });
        return { success: false, error: menuError.value };
      }
    } catch (error) {
      console.error("Failed to fetch menu items:", error);
      menuError.value = error.message || "Failed to fetch menu items";
      toast(menuError.value, { type: "error" });
      return { success: false, error: menuError.value };
    } finally {
      isLoadingMenu.value = false;
    }
  };

  const refreshMenu = async () => {
    isRefreshing.value = true;
    try {
      const result = await fetchMenuItems(true);
      if (result.success) {
        toast("Menu refreshed successfully", { type: "success" });
      }
      return result;
    } finally {
      isRefreshing.value = false;
    }
  };

  const getItemById = (id) => {
    return menuItems.value.find((item) => item.id === id) || null;
  };

  const getItemsByCategory = (categoryName) => {
    if (
      !categoryName ||
      categoryName === "All Category" ||
      categoryName === "All"
    ) {
      return menuItems.value;
    }

    // Regular category filtering
    return menuItems.value.filter(
      (item) =>
        item.category?.name === categoryName ||
        item.item_menu_category?.name === categoryName
    );
  };

  const searchItems = (query) => {
    if (!query || !query.trim()) {
      return menuItems.value;
    }

    const searchTerm = query.toLowerCase().trim();
    return menuItems.value.filter(
      (item) =>
        item.name.toLowerCase().includes(searchTerm) ||
        item.description?.toLowerCase().includes(searchTerm) ||
        item.category?.name?.toLowerCase().includes(searchTerm) ||
        item.item_menu_category?.name?.toLowerCase().includes(searchTerm) ||
        item.code?.toLowerCase().includes(searchTerm)
    );
  };

  // Cache management
  const saveToCache = () => {
    try {
      const cacheData = {
        menuItems: menuItems.value,
        categories: categories.value,
        timestamp: lastFetchTime.value,
      };
      localStorage.setItem("pos-menu-cache", JSON.stringify(cacheData));
    } catch (error) {
      console.warn("Failed to save menu to cache:", error);
    }
  };

  const loadFromCache = () => {
    try {
      const cachedData = localStorage.getItem("pos-menu-cache");
      if (cachedData) {
        const {
          menuItems: cachedItems,
          categories: cachedCategories,
          timestamp,
        } = JSON.parse(cachedData);

        // Only use cache if it's not expired
        if (Date.now() - timestamp < cacheExpiry) {
          menuItems.value = cachedItems || [];
          categories.value = cachedCategories || [];
          lastFetchTime.value = timestamp;
          return true;
        }
      }
    } catch (error) {
      console.warn("Failed to load menu from cache:", error);
    }
    return false;
  };

  const clearCache = () => {
    try {
      localStorage.removeItem("pos-menu-cache");
      lastFetchTime.value = 0;
    } catch (error) {
      console.warn("Failed to clear cache:", error);
    }
  };

  // Initialize menu data
  const initializeMenu = async () => {
    // Try to load from cache first
    const cacheLoaded = loadFromCache();

    if (!cacheLoaded || needsRefresh.value) {
      await fetchMenuItems(true);
    }

    // Set default category if not set
    if (!selectedCategory.value) {
      selectedCategory.value = "All Category";
    }
  };

  // Auto-save search queries for analytics (debounced)
  let searchDebounceTimer = null;
  watch(searchQuery, (newQuery) => {
    if (searchDebounceTimer) {
      clearTimeout(searchDebounceTimer);
    }

    searchDebounceTimer = setTimeout(() => {
      // Could send analytics data here
    }, 1000);
  });

  return {
    // State
    menuItems,
    categories,
    selectedCategory,
    searchQuery,

    // Loading states
    isLoadingMenu,
    isRefreshing,

    // Error states
    menuError,

    // Computed
    filteredMenuItems,
    availableCategories,
    menuStats,
    needsRefresh,

    // Actions
    clearError,
    setCategory,
    setSearchQuery,
    clearAllFilters,
    fetchMenuItems,
    refreshMenu,
    getItemById,
    getItemsByCategory,
    searchItems,
    initializeMenu,

    // Cache management
    saveToCache,
    loadFromCache,
    clearCache,
  };
});
