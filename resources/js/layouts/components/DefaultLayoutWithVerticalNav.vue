<script setup>
import navItems from '@/navigation/vertical'
import { themeConfig } from '@themeConfig'
import { useRouter } from 'vue-router'
import { toast } from 'vue3-toastify';
import { useUserData } from '@/composables/useUserData';
import { computed } from 'vue';

// Components
import Footer from '@/layouts/components/Footer.vue'
import NavBarNotifications from '@/layouts/components/NavBarNotifications.vue'
import NavbarThemeSwitcher from '@/layouts/components/NavbarThemeSwitcher.vue'
import NavSearchBar from '@/layouts/components/NavSearchBar.vue'
import UserProfile from '@/layouts/components/UserProfile.vue'
import NavBarI18n from '@core/components/I18n.vue'
// @layouts plugin
import { VerticalNavLayout } from '@layouts'

const { userData } = useUserData()
const userAbilityRules = useCookie("userAbilityRules").value;
const router = useRouter();

// Computed property for POS access
const posAccess = computed(() => {
  if (!userData.value) return false
  
  if (userData.value.id === 1) {
    return true
  } else {
    return userAbilityRules && userAbilityRules.includes('pos')
  }
})

function handlePosClick() {
  const branch_info = useCookie("branch_info").value || 0;

  if(branch_info == 0 || branch_info == null || branch_info == undefined){
    router.push('/branch');
    toast('Please select your branch first', { type: 'error' });
  }else {
    router.push('/pos').then(() => {
      window.location.reload();
    });
  }
}

import { useBranchInfo } from '@/composables/useBranchInfo'
const { branchInfo } = useBranchInfo()


const hasBranchInfo = computed(() => {
  if (!branchInfo.value) return false
  if (typeof branchInfo.value === 'number' && branchInfo.value === 0) return false
  if (typeof branchInfo.value === 'string' && (!branchInfo.value || branchInfo.value === '0')) return false
  if (typeof branchInfo.value === 'object') {
    // Sometimes the plugin may return an empty object or missing keys
    if (Object.keys(branchInfo.value).length === 0) return false
    if ('branch_name' in branchInfo.value && branchInfo.value.branch_name) return true
    // fallback: treat any non-empty object as valid
    return true
  }
  return true
})

</script>

<style scoped>
.pos-icon {
  margin-right: 10px;
}
.pos-icon i {
  margin-right: 5px;
}
.outlet-name {
  display: flex;
  align-items: center;
  margin-right: 50px;
  border-left: 1px solid gray;
  border-right: 1px solid gray;
  padding: 0px 14px;
  cursor: default;
}
.outlet-name i {
  margin-right: 5px;
}
</style>

<template>
  <VerticalNavLayout :nav-items="navItems">
    <!-- 👉 navbar -->
    <template #navbar="{ toggleVerticalOverlayNavActive }">
      <div class="d-flex h-100 align-center">
        
        <IconBtn id="vertical-nav-toggle-btn" class="ms-n3 d-lg-none" @click="toggleVerticalOverlayNavActive(true)">
          <VIcon size="26" icon="tabler-menu-2" />
        </IconBtn>
        <NavSearchBar class="ms-lg-n3" />
        <VSpacer />

        <!-- Fix: Show outlet-name only if branchInfo is valid -->
        <div class="outlet-name" v-if="hasBranchInfo">
          <VIcon icon="tabler-building" />
          {{ branchInfo.branch_name }}
        </div>

        <button class="pos-icon" v-if="posAccess" @click="handlePosClick">
          <VChip label color="primary"
            class="text-capitalize">
            <VIcon icon="tabler-basket-bolt" />
            POS
          </VChip>
        </button>

        <NavBarI18n v-if="themeConfig.app.i18n.enable && themeConfig.app.i18n.langConfig?.length"
          :languages="themeConfig.app.i18n.langConfig" />
        <NavbarThemeSwitcher />

        <NavBarNotifications class="me-1" />
        <UserProfile />
      </div>
    </template>

    <!-- 👉 Pages -->
    <slot />

    <!-- 👉 Footer -->
    <template #footer>
      <Footer />
    </template>

    <!-- 👉 Customizer -->
    <!-- <TheCustomizer /> -->
  </VerticalNavLayout>
</template>
