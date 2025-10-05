<script setup>
import SearchProducts from '@/components/frontend/SearchProducts.vue'
import CommonPageBanner from '@/components/frontend/CommonPageBanner.vue'
import { useI18n } from 'vue-i18n'
import { computed } from 'vue'
import { useRoute } from 'vue-router'

const { t } = useI18n()
const route = useRoute()

// Get search query from route
const searchQuery = computed(() => route.query.q || '')

// Page title based on search query
const pageTitle = computed(() => {
  if (searchQuery.value) {
    // return `${t('Search Results for')} "${searchQuery.value}"`
    return `${t('Search Results')}`
  }
  return t('Search Results')
})

definePage({
  meta: {
    layout: 'frontend',
    public: true,
  },
})
</script>

<template>
  <div>
    <!-- Common Page Banner -->
    <CommonPageBanner :title="pageTitle" :breadcrumb="t('Search Results')" />
    
    <!-- Search Results Component -->
    <SearchProducts 
      mode="page"
      :itemLimit="12"
      sectionClass="search-results default-section-padding"
      :showHeader="true"
      :initialQuery="searchQuery"
    />
  </div>
</template>

<style scoped>
/* Search Results Page Specific Styles */
.search-results {
  background: #f8f9fa;
}

.search-results .container {
  background: white;
  border-radius: 15px;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
  margin-top: -30px;
  position: relative;
  z-index: 2;
  padding: 40px 30px;
}

@media (max-width: 768px) {
  .search-results .container {
    margin-top: -20px;
    padding: 30px 20px;
    border-radius: 10px;
  }
}
</style> 
