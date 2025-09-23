<template>
    <div class="customer-panel-sidebar customer-panel-sidebar-wrapper">
        <!-- Logo -->
        <RouterLink class="logo" to="/">
          <img :src="websiteStore.getHeaderLogo" alt="logo" class="logo-img">
        </RouterLink>
        <ul>
            <li>
                <RouterLink to="/dashboard_" :class="{ active: $route.name === 'dashboard_' }">
                    <VIcon icon="tabler-layout-dashboard" size="20" />
                    <span>{{ t('Dashboard') }}</span>
                </RouterLink>
            </li>
            <li>
                <RouterLink to="/service-order_" :class="{ active: $route.name === 'service-order_' }">
                    <VIcon icon="tabler-calendar-week" size="20" />
                    <span>{{ t('Booking History') }}</span>
                </RouterLink>
            </li>
            <li>
                <RouterLink to="/product-order_" :class="{ active: $route.name === 'product-order_' }">
                    <VIcon icon="tabler-garden-cart" size="20" />
                    <span>{{ t('Orders') }}</span>
                </RouterLink>
            </li>
            <li>
                <RouterLink to="/package-order_" :class="{ active: $route.name === 'package-order_' }">
                    <VIcon icon="tabler-package" size="20" />
                    <span>{{ t('Package History') }}</span>
                </RouterLink>
            </li>
            <li>
                <RouterLink to="/profile-setting_" :class="{ active: $route.name === 'profile-setting_' }">
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
      router.push('/login_')
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

<style scoped>

</style>