import { watch, computed } from 'vue'
import { useRoute } from 'vue-router'
import { useWebsiteSettingsStore } from '@/stores/websiteSetting.js'
import { useSiteSettingsStore } from '@/stores/siteSettings.js'

export function useWebsiteSettings() {
  const route = useRoute()
  const websiteStore = useWebsiteSettingsStore()
  const siteSettings = useSiteSettingsStore()

  // Computed property for current layout
  const currentLayout = computed(() => route.meta?.layout || 'default')

  // Determine which settings to use based on current layout
  const getCurrentLayoutSettings = () => {
    // Frontend layout uses website_settings, others use settings
    if (currentLayout.value === 'frontend') {
      return {
        title: websiteStore.getWebsiteTitle,
        favicon: websiteStore.getFavicon
      }
    } else {
      // POS and default layouts use site settings
      return {
        title: siteSettings.getSiteTitle,
        favicon: siteSettings.getFavicon
      }
    }
  }

  // Update document title
  const updateDocumentTitle = (pageTitle = '') => {
    const settings = getCurrentLayoutSettings()
    if (!settings.title) return // Don't update if settings not loaded yet
    
    const newTitle = pageTitle ? `${pageTitle} - ${settings.title}` : settings.title
    if (document.title !== newTitle) {
      document.title = newTitle
    }
  }

  // Update favicon
  const updateFavicon = () => {
    const settings = getCurrentLayoutSettings()
    const faviconUrl = settings.favicon
    
    if (!faviconUrl) return // Don't update if settings not loaded yet
    
    // Remove existing favicon
    const existingFavicon = document.querySelector('link[rel="icon"]')
    if (existingFavicon) {
      existingFavicon.remove()
    }

    // Add new favicon
    const link = document.createElement('link')
    link.rel = 'icon'
    link.type = 'image/x-icon'
    link.href = faviconUrl
    document.head.appendChild(link)
  }

  // Watch for route/layout changes to update title and favicon
  watch(currentLayout, () => {
    updateDocumentTitle()
    updateFavicon()
  }, { immediate: true })

  // Watch for changes in website settings (for frontend layout)
  watch(() => websiteStore.settings, (newSettings) => {
    if (newSettings && currentLayout.value === 'frontend') {
      updateDocumentTitle()
      updateFavicon()
    }
  }, { deep: true })

  // Watch for changes in site settings (for pos and default layouts)
  watch(() => siteSettings.settings, (newSettings) => {
    if (newSettings && (currentLayout.value === 'pos' || currentLayout.value === 'default')) {
      updateDocumentTitle()
      updateFavicon()
    }
  }, { deep: true })

  return {
    updateDocumentTitle,
    updateFavicon,
    websiteStore,
    currentLayout
  }
} 