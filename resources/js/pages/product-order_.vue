<script setup>
import CustomerProfileHeader from '@/components/frontend/mini-components/CustomerProfileHeader.vue'
import CustomerPanelSidebar from '@/components/frontend/mini-components/CustomerPanelSidebar.vue'
import CustomerProductOrder from '@/components/frontend/mini-components/CustomerProductOrder.vue'
import CommonPageBanner from '@/components/frontend/CommonPageBanner.vue'
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
    <CommonPageBanner :title="t('Product Order')" :breadcrumb="t('Product Order')" />

    <!-- Customer Profile Setting Section -->
    <div class="customer-profile-setting-section product-order-section">
      <div class="container">
        <!-- Profile Setting Main Area -->
        <div class="customer-panel">
          <CustomerPanelSidebar />
          <div class="customer-panel-body">
            <div class="customer-panel-content">
              <div class="customer-panel-content-header">
                <CustomerProfileHeader />
              </div>
              <CustomerProductOrder />
            </div>
          </div>
        </div>
        <!-- Profile Setting Main Area -->
      </div>
    </div>
    <!-- Customer Profile Setting Section End -->
  </div>
</template>