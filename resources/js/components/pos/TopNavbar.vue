<template>
    <div class="nav-top-bar">
        <div class="container d-flex justify-content-between align-items-center">
            <div class="left-actions">
                <!-- Options Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        {{ t('Options') }}
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="optionsDropdown">
                        <li v-for="(item, index) in menuItems" :key="index">
                            <a class="dropdown-item" :href="item.action" @click.prevent="item.action">
                                <VIcon :icon="item.icon" />
                                {{ item.text }}
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="dropdown mobile-booking-package-dropdown">
                    <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        {{ t('Menu') }}
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="optionsDropdown">
                        <li>
                            <a class="dropdown-item" @click.prevent="router.push('/pos/booking-list')">
                                {{ t('Bookings') }}
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" @click.prevent="router.push('/pos/package-list')">
                                {{ t('Packages') }}
                            </a>
                        </li>
                    </ul>
                </div>
                <button v-for="(button, index) in actionButtons" :key="index" class="btn book-package" :class="button.class"
                    @click="button.action">
                    <VIcon :icon="button.icon" />
                    {{ button.text }}
                </button>
                <div class="dropdown" v-if="themeConfig.app.i18n.enable && themeConfig.app.i18n.langConfig?.length">
                    <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        <VIcon icon="tabler-language" />
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="optionsDropdown">
                        <li v-for="lang in themeConfig.app.i18n.langConfig" :key="lang.i18nLang">
                            <!-- <a class="dropdown-item" @click="locale = lang.i18nLang">
                                {{ lang.label }}
                            </a> -->
                            <a class="dropdown-item" :class="{ 'active-ln': locale == lang.i18nLang }" @click="setLanguage(lang.i18nLang)">
                                {{ lang.label }}
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="right-actions">
                <button v-for="(icon, index) in rightIcons" :key="index"
                    class="btn btn-primary rounded-circle position-relative" @click="icon.action">
                    <VIcon :icon="icon.class" />
                    <span v-if="icon.badge"
                        class="position-absolute top-85 start-95 translate-middle badge rounded-pill bg-danger">
                        {{ icon.badge }}
                    </span>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { themeConfig } from '@themeConfig'
import { useI18n } from 'vue-i18n';
import { ref, onMounted, computed, nextTick } from 'vue';
import { useRouter } from 'vue-router';
import { useNotificationStore } from '@/stores/pos/notification';
import { storeToRefs } from 'pinia';
import { toast } from 'vue3-toastify';
    
const notificationStore = useNotificationStore();
const { count } = storeToRefs(notificationStore);
const { locale, t } = useI18n({ useScope: 'global' })
onMounted(() => notificationStore.fetchCount());

const listPackageLang = computed(() => {
    return t('Packages')
})
const listBookingLang = computed(() => {
    return t('Bookings')
})
const posLang = computed(() => {
    return t('POS')
})
const dashboardLang = computed(() => {
    return t('Dashboard')
})
const logoutLang = computed(() => {
    return t('Logout')
})
const saleListLang = computed(() => {
    return t('List Sale')
})

// Define emits
defineEmits([
    'navigate',
    'show-register',
    'show-notifications',
    'toggle-fullscreen',
])
const router = useRouter();
const menuItems = ref([
    { text: dashboardLang, icon: 'tabler-dashboard', action: () => router.push('/dashboard').then(() => {
          window.location.reload();
      }) 
    },
    { text: saleListLang, icon: 'tabler-list-check', action: () => router.push('/pos/sale-list') },
    { text: logoutLang, icon: 'tabler-logout', action: () => logout() }
]);
const actionButtons = ref([
    {
        text: listBookingLang,
        icon: 'tabler-bookmarks',
        class: 'btn-primary',
        action: () => router.push('/pos/booking-list')
    },
    {
        text: listPackageLang,
        icon: 'tabler-components',
        class: 'btn-primary',
        action: () => router.push('/pos/package-list')
    },
]);

// Make rightIcons reactive using computed
const rightIcons = computed(() => [
    {
        class: 'tabler-bell',
        badge: count.value > 99 ? '99+' : count.value,
        action: () => router.push('/pos/notifications')
    },
    {
        class: 'tabler-arrows-maximize',
        action: () => {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen();
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                }
            }
        }
    }
]);

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

const logout = async () => {
  try {
    await $api('/auth/logout', { method: 'GET' })

    // Remove cookies
    useCookie('userAbilityRules').value = null
    useCookie('userData').value = null
    useCookie('accessToken').value = null
    useCookie('branch_info').value = null
    useCookie('company_settings').value = null

    // Redirect to login page
    await nextTick(() => {
      toast("Logout Successfully!", {
        "type": "success",
      });
      router.replace('/admin-login')
    })
  }
  catch (err) {
    console.error(err)
  }
}
</script>