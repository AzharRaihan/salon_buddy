import { defineStore } from "pinia";

export const useSiteSettingsStore = defineStore("siteSettings", {
  state: () => ({
    settings: null,
    loading: false,
    error: null,
  }),

  getters: {
    getSiteTitle: (state) => state.settings?.site_title || "DoorSoft",
    getCompanyName: (state) => state.settings?.company_name || "",
    getCompanyLogo: (state) => state.settings?.logo_url || "",
    getFavicon: (state) => state.settings?.favicon_url || "",
    getFooterText: (state) => state.settings?.footer || "",
    getCompanyWebsite: (state) => state.settings?.company_website || "",
  },

  actions: {
    async fetchSettings() {
      try {
        this.loading = true;
        const response = await $api("/all-settings");
        this.settings = response.data;
        this.error = null;
      } catch (error) {
        this.error = error.message;
        console.error("Error fetching settings:", error);
      } finally {
        this.loading = false;
      }
    },

    setSettings(settings) {
      this.settings = settings;
    },
  },
});
