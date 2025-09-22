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
      sectionClass="special-offers default-section-padding"
      :showHeader="false"
      :itemLimit="9"
      :buttonText="t('Buy This Package')"
    />
  </div>
</template>