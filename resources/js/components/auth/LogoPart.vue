<script setup>
import { useSiteSettingsStore } from '@/stores/siteSettings';

const siteSettingsStore = useSiteSettingsStore()
</script>

<template>
    <VCardItem class="justify-center">
        <VCardTitle>
            <RouterLink to="/">
                <div class="app-logo">
                    <img v-if="siteSettingsStore.getCompanyLogo" :src="siteSettingsStore.getCompanyLogo" alt="Company Logo"
                        class="company-logo" />
                    <h1 v-else class="app-logo-title">
                        {{ siteSettingsStore.getCompanyName || 'Salon Buddy' }}
                    </h1>
                </div>
            </RouterLink>
        </VCardTitle>
    </VCardItem>
</template>
<style scoped>
.app-logo img {
    max-width: 200px;
}
</style>
