import { defineStore } from "pinia";

export const useWebsiteSettingsStore = defineStore("websiteSettings", {
  state: () => ({
    settings: null,
    loading: false,
    error: null,
  }),

  getters: {
    getWebsiteTitle: (state) => state.settings?.website_title || "Salon Buddy",
    getFavicon: (state) => state.settings?.favicon_url || '',
    getEmail: (state) => state.settings?.email || "info@salonbuddy.com",
    getPhone: (state) => state.settings?.phone || "+1 (555) 123-4567",
    getAddress: (state) => state.settings?.address || "123 Beauty Street, Style City, SC 12345",
    getHeaderLogo: (state) => state.settings?.header_logo_url ? `${state.settings.header_logo_url}` : '',
    getCommonBannerImage: (state) => state.settings?.common_banner_image_url ? `${state.settings.common_banner_image_url}` : '',
    getLoginImage: (state) => state.settings?.login_image_url ? `${state.settings.login_image_url}` : '',
    getFooterLogo: (state) => state.settings?.footer_logo_url ? `${state.settings.footer_logo_url}` : '',
    getFooterCopyright: (state) => state.settings?.footer_copyright || `© ${new Date().getFullYear()} Salon Buddy. All rights reserved.`,
    getFooterDescription: (state) => state.settings?.footer_mini_description || "Your trusted partner for professional hair care services.",
    getGoogleMapUrl: (state) => state.settings?.google_map_url || "",
    getCurrency: (state) => state.settings?.company?.currency || "$",
    getTaxIsGst: (state) => state.settings?.company?.tax_is_gst || "No",
    getPrintFormate: (state) => state.settings?.company?.print_formate || "56mm",
    getOverSale: (state) => state.settings?.company?.over_sale || "No",
    getUseWebsite: (state) => state.settings?.company?.use_website,
    getSocialMedia: (state) => {
      if (!state.settings?.social_media) return [];
      try {
        return typeof state.settings.social_media === 'string' 
          ? JSON.parse(state.settings.social_media) 
          : state.settings.social_media;
      } catch (e) {
        console.error('Error parsing social media:', e);
        return [];
      }
    },
    getLanguages: (state) => {
      if (!state.settings?.languages) return ['en'];
      try {
        return typeof state.settings.languages === 'string' 
          ? JSON.parse(state.settings.languages) 
          : state.settings.languages;
      } catch (e) {
        console.error('Error parsing languages:', e);
        return ['en'];
      }
    },
    getBusinessHours: (state) => ({
      openDayStart: state.settings?.open_day_start || 'Monday',
      openDayEnd: state.settings?.open_day_end || 'Friday',
      openTimeStart: state.settings?.open_day_start_time || '09:00',
      openTimeEnd: state.settings?.open_day_end_time || '18:00',
    }),
    getTestimonialData: (state) => ({
      title: state.settings?.testimonial_title || 'Special Offer',
      heading: state.settings?.testimonial_heading || 'Have it Cut Today by Our Professional hair Style, and Get The Discount !',
      image: state.settings?.testimonial_image_url ? `${state.settings.testimonial_image_url}` : '',
    }),
  },

  actions: {
    async fetchSettings() {
      try {
        this.loading = true;
        const response = await $api("/website-settings-frontend");
        this.settings = response.data;
        this.error = null;
      } catch (error) {
        this.error = error.message;
        console.error("Error fetching website settings:", error);
      } finally {
        this.loading = false;
      }
    },

    setSettings(settings) {
      this.settings = settings;
    },

    // Reset settings by forcing a fresh fetch
    async resetSettings() {
      this.settings = null;
      await this.fetchSettings();
    },

    // Initialize settings on app start
    async initializeSettings() {
      if (!this.settings) {
        await this.fetchSettings();
      }
    },
  },
});
