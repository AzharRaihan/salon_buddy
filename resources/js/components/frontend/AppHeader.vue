<template>
  <header class="header-section">
    <!-- Top Bar -->
    <div class="top-bar">
      <div class="righ-side-overlay"></div>
      <div class="container">
        <div class="row align-items-center">
          <div class="col-8 col-md-6 col-lg-6">
            <div class="contact-info d-flex">
              <span class="phone-number">
                <VIcon icon="tabler-phone" />
                {{ websiteStore.getPhone }}
              </span>
              <span class="email-address">
                <VIcon icon="tabler-mail" />
                {{ websiteStore.getEmail }}
              </span>
            </div>
          </div>
          <div class="col-4 col-md-6 col-lg-6 d-flex align-items-center justify-content-end text-end">
            <div class="topbar-right-sidebar">
                <!-- <NavBarI18n v-if="themeConfig.app.i18n.enable && themeConfig.app.i18n.langConfig?.length"
                :languages="themeConfig.app.i18n.langConfig" /> -->
              <div class="dropdown" v-if="themeConfig.app.i18n.enable && themeConfig.app.i18n.langConfig?.length">
                <template v-for="lang in themeConfig.app.i18n.langConfig" :key="lang.i18nLang">
                <a 
                  v-if="locale === lang.i18nLang"
                  class="text-white me-3 text-decoration-none" 
                  href="#" 
                  role="button" 
                  id="languageDropdown" 
                  data-bs-toggle="dropdown" 
                  aria-expanded="false"
                >
                  <VIcon size="22" icon="tabler-language" />
                  {{ lang.label }}
                </a>
              </template>
                <ul class="dropdown-menu" aria-labelledby="languageDropdown">
                  <li v-for="lang in themeConfig.app.i18n.langConfig" :key="lang.i18nLang">
                    <a class="dropdown-item" href="#" @click="setLanguage(lang.i18nLang)" :class="{ 'active-ln': locale === lang.i18nLang }" >{{ lang.label }}</a>
                  </li>
                </ul>
              </div>
              <div class="social-links">
                <template v-for="social in activeSocialMedia" :key="social.name">
                  <a v-if="social.url" :href="social.url" target="_blank" class="text-white">
                    <VIcon size="22" :icon="getSocialIcon(social.name)" />
                  </a>
                </template>
              </div>
            </div>
            
          </div>
        </div>
      </div>
    </div>

    <!-- Main Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white">
      <div class="container">
        <!-- Logo -->
        <RouterLink class="navbar-brand" to="/">
          <img :src="websiteStore.getHeaderLogo" alt="logo" class="logo-img">
        </RouterLink>

        <!-- Mobile Toggle -->
        <div class="mobile-header-icon-wrapper">
          <div class="mobile-header-icon">
            <a href="javascript:void(0)" class="btn mobile-togler cart-wrapper cart-wrapper-mobile" data-bs-toggle="offcanvas" data-bs-target="#staticBackdrop" aria-controls="staticBackdrop" @click="removePadding(); disableScroll()">
              <span class="cart-badge">{{ cartStore.items.length }}</span>
              <VIcon size="25" icon="tabler-basket-bolt main-icon" />
            </a>
            <button class="btn mobile-togler" type="button" data-bs-toggle="offcanvas" data-bs-target="#headerOffCanvas" aria-controls="headerOffCanvas" @click="removePadding(); disableScroll()">
              <VIcon icon="tabler-menu-deep" />
            </button>
          </div>
        </div>

        <!-- Navigation Menu -->
        <div class="collapse navbar-collapse" id="navbarNav">
          <ul class="navbar-nav mx-auto">
            <li class="nav-item">
              <RouterLink class="nav-link" :class="{ active: $route.name === 'root' }" to="/">{{ t('Home') }}</RouterLink>
            </li>
            <li class="nav-item">
              <RouterLink class="nav-link" :class="{ active: $route.name === 'frontend-aboutus' }" to="/frontend/aboutus">{{ t('About Us') }}</RouterLink>
            </li>
            <li class="nav-item">
              <RouterLink class="nav-link" :class="{ active: $route.name === 'frontend-appointment-service' }" to="/frontend/appointment-service">{{ t('Booking') }}</RouterLink>
            </li>
            <li class="nav-item">
              <RouterLink class="nav-link" :class="{ active: $route.name === 'frontend-service' }" to="/frontend/service">{{ t('Service') }}</RouterLink>
            </li>
            <li class="nav-item">
              <RouterLink class="nav-link" :class="{ active: $route.name === 'frontend-package' }" to="/frontend/package">{{ t('Package') }}</RouterLink>
            </li>
          </ul>

          <!-- CTA Button -->
          <div class="navbar-nav">
            <a href="javascript:void(0)" class="d-flex align-items-center me-4 cart-wrapper shopping_cart_btn" data-bs-toggle="offcanvas" data-bs-target="#staticBackdrop" aria-controls="staticBackdrop" @click="removePadding(); disableScroll()">
              <span class="cart-badge">{{ cartStore.items.length }}</span>
              <VIcon size="25" icon="tabler-shopping-bag main-icon" />
            </a>

            <!-- Customer Authentication -->
            <div v-if="isCustomerAuthenticated" class="customer-auth-dropdown dropdown">
              <a class="dropdown-toggle customer-avatar-btn" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <img 
                  :src="customerInfo.photo || '../../../@frontend/images/user_avater.png'" 
                  class="customer-avatar" 
                  :alt="customerInfo.name"
                  @error="$event.target.src = '../../../@frontend/images/user_avater.png'"
                >
                <span class="customer-name">{{ truncateWords(customerInfo.name, 2) }}</span>
                <VIcon icon="tabler-chevron-down" size="16" />
              </a>
              <ul class="dropdown-menu customer-dropdown-menu">
                <li>
                  <RouterLink to="/frontend/dashboard" class="dropdown-item">
                    <div class="d-flex align-items-center gap-2 inner-item">
                      <VIcon icon="tabler-dashboard" size="18" />
                      <span>{{ t('Dashboard') }}</span>
                    </div>
                  </RouterLink>
                </li>
                <li>
                  <RouterLink to="/frontend/profile-setting" class="dropdown-item">
                    <div class="d-flex align-items-center gap-2 inner-item">
                      <VIcon icon="tabler-user-cog" size="18" />
                      <span>{{ t('Profile Setting') }}</span>
                    </div>
                  </RouterLink>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                  <a href="javascript:void(0)" class="dropdown-item logout-item" @click="handleLogout" :disabled="isLoading">
                    <div class="d-flex align-items-center gap-2 inner-item">
                      <VIcon icon="tabler-logout" size="18" />
                      <span>{{ isLoading ? t('Logging out...') : t('Logout') }}</span>
                    </div>
                  </a>
                </li>
              </ul>
            </div>

            <!-- Guest Authentication -->
            <div v-else class="guest-auth-buttons">
              <RouterLink to="/frontend/login" class="btn btn-primary btn-login me-2">
                {{ t('Login') }}
              </RouterLink>
              <RouterLink to="/frontend/register" class="btn btn-primary btn-outlet">
                {{ t('Register') }}
              </RouterLink>
            </div>
          </div>
        </div>
      </div>
    </nav>
  </header>

  <!-- This is Cart Sidebar Content -->
  <div class="cart-sidebar offcanvas offcanvas-end" tabindex="-1" id="staticBackdrop" data-bs-backdrop="static" aria-labelledby="staticBackdropLabel" @click="enableScroll();">
    <div class="offcanvas-header">
      <h1 class="offcanvas-title">Cart {{ cartStore.items.length }}({{ cartStore.itemCount }})</h1>
      <button class="cart-close" type="button" data-bs-dismiss="offcanvas" aria-label="Close">
        <span>{{ t('Close') }}</span>
        <VIcon icon="tabler-xbox-x" />
      </button>
    </div>
    <div class="offcanvas-body" :class="!cartStore.hasItems ? 'd-flex align-items-center justify-content-center' : ''">
      <!-- Empty Cart State -->
      <div v-if="!cartStore.hasItems" class="empty-cart-message text-center py-4">
        <VIcon size="48" icon="tabler-shopping-cart-x" class="text-muted mb-3" />
        <p class="text-muted">{{ t('Your cart is empty') }}</p>
      </div>
      
      <!-- Cart Items -->
      <ul v-else>

        <li v-for="item in cartStore.items" :key="`${item.id}-${item.type}`">
          <div class="cart-item-wrap">
            <div class="img-wrap">
              <img :src="item.image || '../../@frontend/images/cart_product.png'" :alt="item.name">
            </div>
            <div class="info-wrap">
              <div class="info-wrap-inner">
                <h3>{{ item.name }}</h3>
                <VIcon icon="tabler-trash" @click="cartStore.removeItem(item.id, item.type)" style="cursor: pointer;" />
              </div>
              <div class="price-qty-wrap">
                <div class="price-wrap">
                  <span class="single-price">{{ formatAmount(item.price) }}</span>
                  <div class="button-wrap">
                    <span class="minus" @click="cartStore.decrementQuantity(item.id, item.type)" style="cursor: pointer;">
                      <VIcon icon="tabler-minus" />
                    </span>
                    <span>{{ item.quantity }}</span>
                    <span class="plus" @click="cartStore.incrementQuantity(item.id, item.type)" style="cursor: pointer;">
                      <VIcon icon="tabler-plus" />
                    </span>
                  </div>
                </div>
                <p class="subtotal p-0 m-0">{{ formatAmount(item.price * item.quantity) }}</p>
              </div>
            </div>
          </div>
        </li>
      </ul>
    </div>
    <div class="offcanvas-footer" v-if="cartStore.hasItems">
      <h4>{{ t('Order Summary') }}</h4>
      <ul>
        <li>
          <span>{{ t('Subtotal') }}</span>
          <span>{{ formatAmount(cartStore.subtotal) }}</span>
        </li>
        <li>
          <span>{{ t('Tax') }}</span>
          <span>{{ formatAmount(cartStore.taxAmount) }}</span>
        </li>
        <li class="total-price">
          <span>{{ t('Total') }}</span>
          <span>{{ formatAmount(cartStore.total) }}</span>
        </li>
      </ul>
      <BookPackageBtn link="/frontend/shopping-cart" :text="'Checkout'" data-bs-dismiss="offcanvas" aria-label="Close" @click="enableScroll();" />
    </div>
  </div>

  <!-- Header Offcanvas -->
  <div class="offcanvas offcanvas-start" tabindex="-1" id="headerOffCanvas" aria-labelledby="headerOffCanvasLabel" data-bs-backdrop="static" @click="enableScroll();">
    <div class="offcanvas-header">
      <h5 class="offcanvas-title" id="headerOffCanvasLabel">
        <img :src="websiteStore.getHeaderLogo" :alt="websiteStore.getSiteTitle" class="logo-img">
      </h5>
      <button type="button" data-bs-dismiss="offcanvas" aria-label="Close">
        <VIcon icon="tabler-xbox-x" />
      </button>
    </div>
    <div class="offcanvas-body">
      <ul class="navbar-nav">
        <li class="nav-item">
          <RouterLink class="nav-link" :class="{ active: $route.name === 'root' }" to="/">{{ t('Home') }}</RouterLink>
        </li>
        <li class="nav-item">
          <RouterLink class="nav-link" :class="{ active: $route.name === 'frontend-aboutus' }" to="/frontend/aboutus">{{ t('About Us') }}</RouterLink>
        </li>
        <li class="nav-item">
          <RouterLink class="nav-link" :class="{ active: $route.name === 'frontend-appointment-service' }" to="/frontend/appointment-service">{{ t('Booking') }}</RouterLink>
        </li>
        <li class="nav-item">
          <RouterLink class="nav-link" :class="{ active: $route.name === 'frontend-service' }" to="/frontend/service">{{ t('Service') }}</RouterLink>
        </li>
        <li class="nav-item">
          <RouterLink class="nav-link" :class="{ active: $route.name === 'frontend-package' }" to="/frontend/package">{{ t('Package') }}</RouterLink>
        </li>
        
      </ul>

      <!-- Mobile Authentication -->
      <div class="mobile-auth-buttons mt-4">
        <div v-if="isCustomerAuthenticated" class="customer-mobile-auth">
          <div class="customer-info-mobile mb-3">
            <img 
              :src="customerInfo.photo" 
              class="customer-avatar-mobile" 
              :alt="customerInfo.name"
            >
            <span class="customer-name-mobile">{{ customerInfo.name }}</span>
          </div>
          <RouterLink to="/frontend/dashboard" class="btn btn-outline-primary w-100 mb-2" data-bs-dismiss="offcanvas">
            <VIcon icon="tabler-dashboard" size="18" />
            {{ t('Dashboard') }}
          </RouterLink>
          <RouterLink to="/frontend/profile-setting" class="btn btn-outline-primary w-100 mb-2" data-bs-dismiss="offcanvas">
            <VIcon icon="tabler-user-cog" size="18" />
            {{ t('Profile Setting') }}
          </RouterLink>
          <button class="btn btn-outline-danger w-100" @click="handleLogout" :disabled="isLoading">
            <VIcon icon="tabler-logout" size="18" />
            {{ isLoading ? t('Logging out...') : t('Logout') }}
          </button>
        </div>
        <div v-else class="guest-mobile-auth">
          <RouterLink to="/frontend/login" class="btn btn-primary btn-login w-100 mb-2">
            {{ t('Login') }}
          </RouterLink>
          <RouterLink to="/frontend/register" class="btn btn-primary btn-outlet w-100">
            {{ t('Register') }}
          </RouterLink>
        </div>
      </div>
    </div>
    <div class="offcanvas-footer app-footer-offcanvas">
      <div class="social-links mt-4">
        <h6 class="social-title">{{ t('Follow Us') }}</h6>
        <div class="social-icons">
          <template v-for="social in activeSocialMedia" :key="social.name">
            <a v-if="social.url" :href="social.url" target="_blank" class="social-link">
              <VIcon size="22" :icon="getSocialIcon(social.name)" />
            </a>
          </template>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { themeConfig } from '@themeConfig'

import { computed, onMounted, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useWebsiteSettingsStore } from '@/stores/websiteSetting.js'
import { useShoppingCartStore } from '@/stores/shoppingCart.js'
import { useCustomerAuth } from '@/composables/useCustomerAuth'
import { useAuthState } from '@/composables/useAuthState'
import { toast } from 'vue3-toastify'
import BookPackageBtn from './mini-components/BookPackageBtn.vue'
import 'bootstrap/dist/js/bootstrap.bundle.min.js'
import { useCompanyFormatters } from '@/composables/useCompanyFormatters';
import { useI18n } from 'vue-i18n';

const { locale, t } = useI18n({ useScope: 'global' })

const websiteStore = useWebsiteSettingsStore()
const cartStore = useShoppingCartStore()
const router = useRouter()
const { formatAmount } = useCompanyFormatters()
// Customer Authentication
const {  
  customerLogout, 
  isLoading 
} = useCustomerAuth()
const { customerAuthState, updateCustomerAuthState } = useAuthState()
const isCustomerAuthenticated = computed(() => customerAuthState.value.isAuthenticated)
const customerInfo = computed(() => {
  const data = customerAuthState.value.customerData
  return {
    name: data?.name || 'Customer',
    email: data?.email || '',
    photo: data?.photo_url || null
  }
})
const handleLogout = async () => {
  try {
    await customerLogout()
    updateCustomerAuthState()
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

function setLanguage(lang) {
  locale.value = lang
  localStorage.setItem('selectedLanguage', lang) // save in localStorage
}

// on mount, restore language
onMounted(() => {
  const savedLang = localStorage.getItem('selectedLanguage')
  if (savedLang) {
    locale.value = savedLang
  }
})

// Active social media links
const activeSocialMedia = computed(() => {
  return websiteStore.getSocialMedia.filter(social => social.is_active && social.url)
})

// Social media icon mapping
const getSocialIcon = (socialName) => {
  const iconMap = {
    'Facebook': 'tabler-brand-facebook',
    'Twitter': 'tabler-brand-twitter', 
    'Instagram': 'tabler-brand-instagram',
    'YouTube': 'tabler-brand-youtube',
    'TikTok': 'tabler-brand-tiktok',
    'LinkedIn': 'tabler-brand-linkedin'
  }
  return iconMap[socialName] || 'tabler-link'
}

const removePadding = () => {
  document.body.style.paddingRight = '0';
}
const restorePadding = () => {
  document.body.style.paddingRight = '';
}
function disableScroll() {
  document.body.classList.add('body-no-scroll');
}
function enableScroll() {
  document.body.classList.remove('body-no-scroll');
}
function truncateWords(text, count) {
  if (!text) return ''
  const words = text.trim().split(/\s+/) // split by spaces
  return words.length > count
    ? words.slice(0, count).join(' ') + '..'
    : text
}
// Initialize website settings and cart on component mount
onMounted(async () => {
  await websiteStore.initializeSettings()
  cartStore.loadFromStorage()
})
</script>
<style scoped>
/* Customer Authentication Dropdown */
.customer-auth-dropdown .customer-avatar-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 12px;
  border-radius: 8px;
  text-decoration: none;
  color: inherit;
  transition: all 0.3s ease;
  border: 1px solid transparent;
}

.customer-auth-dropdown .customer-avatar-btn:hover {
  background-color: rgba(0, 0, 0, 0.05);
  border-color: rgba(0, 0, 0, 0.1);
  transform: translateY(-1px);
}

.customer-avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid #ffffff;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.customer-name {
  font-weight: 500;
  font-size: 14px;
}

.customer-dropdown-menu {
  border: none;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
  border-radius: 12px;
  padding: 8px 0;
  min-width: 200px;
  margin-top: 8px;
  animation: dropdownFadeIn 0.3s ease;
}

@keyframes dropdownFadeIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.customer-dropdown-menu .dropdown-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 20px;
  font-size: 14px;
  font-weight: 500;
  transition: all 0.2s ease;
  border-radius: 8px;
  margin: 2px 8px;
  width: unset !important;

}
.customer-dropdown-menu .dropdown-item .inner-item {
  transition: all 0.2s ease;
  transform: translateX(0px);
}

.customer-dropdown-menu .dropdown-item:hover .inner-item {
  transform: translateX(4px);
}
.customer-dropdown-menu .dropdown-item:hover {
  background-color: #f8f9fa;
}

.customer-dropdown-menu .logout-item:hover {
  background-color: #fff5f5;
  color: #dc3545;
}

.customer-dropdown-menu .dropdown-divider {
  margin: 8px 16px;
  border-color: #e9ecef;
}

/* Mobile Customer Auth */
.customer-info-mobile {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px;
  background-color: #f8f9fa;
  border-radius: 8px;
}

.customer-avatar-mobile {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid #ffffff;
}

.customer-name-mobile {
  font-weight: 600;
  font-size: 16px;
}

.customer-mobile-auth .btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  transition: all 0.2s ease;
}

.customer-mobile-auth .btn:hover {
  transform: translateY(-1px);
}
</style>
