<script setup>
import { useSiteSettingsStore } from '@/stores/siteSettings';
const siteSettingsStore = useSiteSettingsStore()
</script>

<template>
  <div class="h-100 d-flex align-center justify-md-space-between justify-center">
    <!-- 👉 Footer: left content -->
    <span class="d-flex align-center text-medium-emphasis">
      {{ siteSettingsStore.getFooterText }}
      Developed
      <VIcon icon="tabler-heart-filled" color="error" size="1.25rem" class="mx-1" />
      By <a :href="siteSettingsStore.getCompanyWebsite" target="_blank" rel="noopener noreferrer"
        class="text-primary ms-1">{{ siteSettingsStore.getCompanyName }}</a>
    </span>

  </div>
</template>
