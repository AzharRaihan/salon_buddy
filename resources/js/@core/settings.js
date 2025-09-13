import { ref } from "vue";

export const useSettings = () => {
  const settings = ref(null);
  const isLoaded = ref(false);

  const fetchSettings = async () => {
    try {
      const response = await $api("/all-settings");
      settings.value = response.data;
      isLoaded.value = true;
    } catch (error) {
      console.error("Error fetching settings:", error);
      settings.value = {}; // Set empty object as fallback
      isLoaded.value = true;
    }
  };

  // Initialize settings
  fetchSettings();

  return {
    settings,
    isLoaded,
    fetchSettings,
  };
};
