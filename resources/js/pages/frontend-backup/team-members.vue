<script setup>
import { ref } from 'vue'
import SectionMoreBtn from '@/components/frontend/mini-components/SectionMoreBtn.vue'
import CommonPageBanner from '@/components/frontend/CommonPageBanner.vue'
import TeamSection from '@/components/frontend/TeamSection.vue'
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
    <CommonPageBanner :title="t('Team Members')" :breadcrumb="t('Team Members')" />

    <!-- Team Section -->
    <TeamSection :isHome="false" :headerShow="false" :classBind="'page-team-members'" />
  </div>
</template>
