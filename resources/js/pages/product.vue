<script setup>
import SalonProducts from '@/components/frontend/SalonProducts.vue'
import CommonPageBanner from '@/components/frontend/CommonPageBanner.vue'
import { useI18n } from 'vue-i18n'

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
    <CommonPageBanner :title="t('Product')" :breadcrumb="t('Product')" />
    
    <!-- Salon Products with page mode -->
    <SalonProducts 
      mode="page"
      :itemLimit="8"
      sectionClass="salon-products salon-products-2 default-section-padding"
      :showHeader="false"
    />
  </div>
</template>

<style scoped>
/* This css is only for theis page, don't move in into style.css or anywhere else */
.salon-products .product-header {
  margin-bottom: 30px;
}
.salon-products .product-header .search-inner {
  position: relative;
}
.salon-products .product-header .search-inner input {
  border-radius: 10px;
  border: 1px solid #E0E0E0;
  background: white;
  width: 100%;
  height: 40px;
  font-size: 16px;
  transition: 0.3s;
  transform: translateX(0);
}
.salon-products .product-header .search-inner input::placeholder {
  transition: 0.3s;
  transform: translateX(0);
}
.salon-products .product-header .search-inner input:focus {
  box-shadow: none;
}
.salon-products .product-header .search-inner input:focus::placeholder {
  padding-left: 10px;
  transform: translateX(10px);
  transition: 0.3s;
}
.salon-products .product-header .search-inner i {
  position: absolute;
  right: 10px;
  top: 50%;
  transform: translateY(-50%);
  color: var(--title-color);
}
.salon-products.salon-products-2 {
  background-image: none;
}
/* This css is only for theis page, don't move in into style.css or anywhere else End */

</style> 
