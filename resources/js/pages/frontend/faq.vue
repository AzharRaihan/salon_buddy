<script setup>
import CommonPageBanner from '@/components/frontend/CommonPageBanner.vue'
import FrequentlyAskQuestion from '@/components/frontend/FrequentlyAskQuestion.vue'

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
    <CommonPageBanner title="FAQ" breadcrumb="FAQ" />

    <!-- FAQ Section -->
    <FrequentlyAskQuestion />
  </div>
</template>

<style scoped>
.frequently-ask-question {
  padding-top: 80px;
  padding-bottom: 80px;
}
</style> 