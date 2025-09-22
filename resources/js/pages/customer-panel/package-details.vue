<script setup>
import CustomerProfileHeader from '@/components/frontend/mini-components/CustomerProfileHeader.vue'
import CustomerPanelSidebar from '@/components/frontend/mini-components/CustomerPanelSidebar.vue'
import CustomerPackageDetails from '@/components/frontend/mini-components/CustomerPackageDetails.vue'
import CommonPageBanner from '@/components/frontend/CommonPageBanner.vue'

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
    <CommonPageBanner title="Package Details" breadcrumb="Package Details" />

    <!-- Customer Profile Setting Section -->
    <div class="customer-profile-setting-section">
      <div class="container">
        <!-- Profile Setting Main Area -->
        <div class="customer-panel">
          <CustomerPanelSidebar />
          <div class="customer-panel-body">
            <div class="customer-panel-content">
              <div class="customer-panel-content-header">
                <CustomerProfileHeader />
              </div>
              <CustomerPackageDetails />
            </div>
          </div>
        </div>
        <!-- Profile Setting Main Area -->
      </div>
    </div>
    <!-- Customer Profile Setting Section End -->
  </div>
</template>
