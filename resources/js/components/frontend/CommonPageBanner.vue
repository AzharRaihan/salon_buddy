<template>
    <div class="common-page-banner" :style="{ backgroundImage: `url(${websiteStore.getCommonBannerImage})` }">
        <div class="banner-overlay"></div>
        <div class="banner-content-section">
            <div class="container banner-container">
                <h1 class="banner-title">{{ title }}</h1>
                <div class="breadcrumb-section">
                    <ul class="breadcrumb-list">
                        <li class="breadcrumb-item">
                            <a href="#" class="breadcrumb-link">{{ t('Home') }}</a>
                        </li>
                        <li class="breadcrumb-separator">
                            <VIcon size="20" icon="tabler-chevron-right" />
                        </li>
                        <li class="breadcrumb-item active">{{ breadcrumb }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>
  
<script setup>
import { onMounted } from 'vue'
import { useWebsiteSettingsStore } from '@/stores/websiteSetting.js'
import { useI18n } from 'vue-i18n';
const { t } = useI18n()
const websiteStore = useWebsiteSettingsStore()
onMounted(async () => {
  await websiteStore.initializeSettings()
})
defineProps({
  title: String,
  breadcrumb: String
})
</script>

<style scoped>
.banner-content-section {
    position: relative;
    /* z-index: 1; */
    overflow: hidden;
}
.banner-content-section::after {
    position: absolute;
    content: '';
    top: -120px;
    right: 166px;
    background-color: #00738099;
    background-size: cover;
    width: 187px;
    height: 160%;
    transform: rotate(222deg);
}
.banner-content-section::before {
    position: absolute;
    content: '';
    top: -236px;
    left: -143px;
    background-color: #00738099;
    background-size: cover;
    width: 187px;
    height: 160%;
    transform: rotate(211deg);
}
</style>