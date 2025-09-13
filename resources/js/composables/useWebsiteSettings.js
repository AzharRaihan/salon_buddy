import { watch } from 'vue'
import { useWebsiteSettingsStore } from '@/stores/websiteSetting.js'
import { useSiteSettingsStore } from '@/stores/siteSettings.js'

export function useWebsiteSettings() {
  const websiteStore = useWebsiteSettingsStore()
  const siteSettings = useSiteSettingsStore()

  // Update document title
  const updateDocumentTitle = (pageTitle = '') => {
    const siteTitle = siteSettings.getSiteTitle
    document.title = pageTitle ? `${pageTitle} - ${siteTitle}` : siteTitle
  }

  // Update favicon
  const updateFavicon = () => {
    const faviconUrl = siteSettings.getFavicon
    
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

  // Watch for changes in website settings and update accordingly
  watch(() => websiteStore.settings, (newSettings) => {
    if (newSettings) {
      updateDocumentTitle()
      updateFavicon()
    }
  }, { immediate: true })

  return {
    updateDocumentTitle,
    updateFavicon,
    websiteStore
  }
} 