<template>
    <div class="container-fluid p-0">
        <!-- Top Navigation -->
        <TopNavbar 
            @show-booking-modal="showBookingModal = true" 
            :current-employee="currentEmployee"
            @employee-click="openEmployeeModal" 
            @register-click="handleRegister"
        />
        <!-- Main Content Slot -->
        <RouterView #="{ Component }">
            <Suspense :timeout="0" @fallback="isFallbackStateActive = true" @resolve="isFallbackStateActive = false">
                <Component :is="Component" @show-booking-modal="emit('show-booking-modal')" />
            </Suspense>
        </RouterView>
    </div>
</template>

<script setup>
// Import Bootstrap and Font Awesome
import "@fortawesome/fontawesome-free/css/all.min.css";
import "bootstrap-icons/font/bootstrap-icons.css";
import "bootstrap/dist/css/bootstrap.min.css";

// Initialize Bootstrap JS
import "bootstrap/dist/js/bootstrap.bundle.min.js";

import { useWebsiteSettings } from '@/composables/useWebsiteSettings.js'

const { injectSkinClasses } = useSkins()

// ℹ️ This will inject classes in body tag for accurate styling
injectSkinClasses()

// Initialize website settings for title and favicon
useWebsiteSettings()

// SECTION: Loading Indicator
const isFallbackStateActive = ref(false)
const refLoadingIndicator = ref(null)

watch([
    isFallbackStateActive,
    refLoadingIndicator,
], () => {
    if (isFallbackStateActive.value && refLoadingIndicator.value)
        refLoadingIndicator.value.fallbackHandle()
    if (!isFallbackStateActive.value && refLoadingIndicator.value)
        refLoadingIndicator.value.resolveHandle()
}, { immediate: true })

const emit = defineEmits(['show-booking-modal'])

</script>

<style>
/* Import POS-specific styles */
@import '@styles/pos/css/style.css';
@import '@styles/pos/css/responsive.css';
</style>
