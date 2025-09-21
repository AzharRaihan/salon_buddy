<script setup>
import SpecialOffers from '@/components/frontend/SpecialOffers.vue'
import { useI18n } from 'vue-i18n';
const { t } = useI18n()
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
    <CommonPageBanner :title="t('Package')" :breadcrumb="t('Package')" />
    
    <!-- Special Offers with Pagination -->
    <SpecialOffers 
      mode="page" 
      sectionClass="special-offers package-single-page-section"
      :showHeader="false"
      :buttonText="t('Buy This Package')"
    />
  </div>
</template>