<template>
  <div>
    <!-- Common Page Banner -->
    <CommonPageBanner 
      :title="pageTitle" 
      :breadcrumb="breadcrumbText" 
    />

    <!-- Policy Content Section -->
    <section class="default-section-padding">
      <div class="container">
        <div class="row">
          <div class="col-lg-12">
            <!-- Loading State -->
            <div v-if="loading" class="text-center py-5">
              <VProgressCircular indeterminate color="primary" />
              <p class="mt-3">{{ t('Loading policy content...') }}</p>
            </div>

            <!-- Error State -->
            <div v-else-if="error" class="alert alert-danger">
              <h4>{{ t('Error') }}</h4>
              <p>{{ error }}</p>
              <VBtn @click="fetchPolicyData" color="primary" variant="outlined">
                {{ t('Retry') }}
              </VBtn>
            </div>

            <!-- Content Display -->
            <div v-else class="policy-content">
              <div 
                class="policy-text" 
                v-html="policyContent"
              ></div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { useI18n } from 'vue-i18n'
import CommonPageBanner from '@/components/frontend/CommonPageBanner.vue'

// Props
const props = defineProps({
  policyType: {
    type: String,
    default: 'terms_and_conditions',
    validator: (value) => ['terms_and_conditions', 'privacy_policy'].includes(value)
  }
})

// Composables
const { t } = useI18n()

// Use the frontend policy composable
const {
  loading,
  policyData,
  error,
  fetchPolicyData,
  getPolicyContent,
  getPageTitle,
  getBreadcrumbText
} = useFrontendPolicy(props.policyType)

// Computed properties
const pageTitle = computed(() => getPageTitle())
const breadcrumbText = computed(() => getBreadcrumbText())
const policyContent = computed(() => getPolicyContent())

// Page meta configuration
definePage({
  meta: {
    layout: 'frontend',
    public: true,
  },
})
</script>