<template>
    <div class="customer-panel-sidebar customer-panel-sidebar-wrapper">
        <!-- Logo -->
        <RouterLink class="logo" to="/">
          <img :src="websiteStore.getHeaderLogo" alt="logo" class="logo-img">
        </RouterLink>
        <ul>
            <li>
                <RouterLink to="/frontend/dashboard" :class="{ active: $route.name === 'frontend-dashboard' }">
                    <VIcon icon="tabler-layout-dashboard" size="20" />
                    <span>{{ t('Dashboard') }}</span>
                </RouterLink>
            </li>
            <li>
                <RouterLink to="/frontend/service-order" :class="{ active: $route.name === 'frontend-service-order' }">
                    <VIcon icon="tabler-calendar-week" size="20" />
                    <span>{{ t('Booking History') }}</span>
                </RouterLink>
            </li>
            <li>
                <RouterLink to="/frontend/product-order" :class="{ active: $route.name === 'frontend-product-order' }">
                    <VIcon icon="tabler-garden-cart" size="20" />
                    <span>{{ t('Orders') }}</span>
                </RouterLink>
            </li>
            <li>
                <RouterLink to="/frontend/package-order" :class="{ active: $route.name === 'frontend-package-order' }">
                    <VIcon icon="tabler-package" size="20" />
                    <span>{{ t('Package History') }}</span>
                </RouterLink>
            </li>
            <li>
                <RouterLink to="/frontend/profile-setting" :class="{ active: $route.name === 'frontend-profile-setting' }">
                    <VIcon icon="tabler-user-cog" size="20" />
                    <span>{{ t('Profile Settings') }}</span>
                </RouterLink>
            </li>
            <li>
                <a href="javascript:void(0)" class="logout-trigger" @click="handleLogout">
                    <VIcon icon="tabler-logout" size="20" />
                    <span>{{ t('Logout') }}</span>
                </a>
            </li> 
        </ul>
    </div>
</template>

<script setup>
import { useWebsiteSettingsStore } from '@/stores/websiteSetting.js'
import { useCustomerAuth } from '@/composables/useCustomerAuth'
import { toast } from 'vue3-toastify'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n';
const { t } = useI18n()
const router = useRouter()
const websiteStore = useWebsiteSettingsStore()
const {
  customerLogout,
} = useCustomerAuth()
const handleLogout = async () => {
  try {
    await customerLogout()
    toast('Logged out successfully', {
      type: 'success',
      position: 'top-right',
      autoClose: 2000
    })
    setTimeout(() => {
      router.push('/frontend/login')
    }, 1000)
  } catch (error) {
    toast('Error during logout', {
      type: 'error',
      position: 'top-right',
      autoClose: 3000
    })
  }
}
</script>