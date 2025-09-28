<script setup>
import { useSiteSettingsStore } from '@/stores/siteSettings'
import { layoutConfig } from '@layouts'
import {
  VerticalNavGroup,
  VerticalNavLink,
  VerticalNavSectionTitle,
} from '@layouts/components'
import { useLayoutConfigStore } from '@layouts/stores/config'
import { injectionKeyIsVerticalNavHovered } from '@layouts/symbols'
import { useRoute, useRouter } from 'vue-router'
import { PerfectScrollbar } from 'vue3-perfect-scrollbar'
import { toast } from 'vue3-toastify'
import { VNodeRenderer } from './VNodeRenderer'
import {
  removeUserData,
  removeAccessToken
} from '@/utils/storage';
import { useCookie } from '@/@core/composable/useCookie';
import { filterNavItems } from '@/utils/permissions';

const router = useRouter()
const route = useRoute()

const siteSettingsStore = useSiteSettingsStore()

const props = defineProps({
  tag: {
    type: null,
    required: false,
    default: 'aside',
  },
  navItems: {
    type: null,
    required: true,
  },
  isOverlayNavActive: {
    type: Boolean,
    required: true,
  },
  toggleIsOverlayNavActive: {
    type: Function,
    required: true,
  },
})

const refNav = ref()
const isHovered = useElementHover(refNav)

provide(injectionKeyIsVerticalNavHovered, isHovered)

const configStore = useLayoutConfigStore()

const resolveNavItemComponent = item => {
  if ('heading' in item)
    return VerticalNavSectionTitle
  if ('children' in item)
    return VerticalNavGroup

  return VerticalNavLink
}

/*ℹ️ Close overlay side when route is changed
Close overlay vertical nav when link is clicked
*/

watch(() => route.name, () => {
  props.toggleIsOverlayNavActive(false)
})

const isVerticalNavScrolled = ref(false)
const updateIsVerticalNavScrolled = val => isVerticalNavScrolled.value = val

const handleNavScroll = evt => {
  isVerticalNavScrolled.value = evt.target.scrollTop > 0
}

const hideTitleAndIcon = configStore.isVerticalNavMini(isHovered)

// Filter navigation items based on permissions
const filteredNavItems = computed(() => {
  return filterNavItems(props.navItems)
})

const logout = async () => {
  try {
    await $api('/auth/logout', { method: 'GET' })

    // Remove cookies
    removeUserData()
    removeAccessToken()

    useCookie('userAbilityRules').value = null
    useCookie('userData').value = null
    useCookie('accessToken').value = null

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

<template>
  <Component :is="props.tag" ref="refNav" data-allow-mismatch class="layout-vertical-nav" :class="[
    {
      'overlay-nav': configStore.isLessThanOverlayNavBreakpoint,
      'hovered': isHovered,
      'visible': isOverlayNavActive,
      'scrolled': isVerticalNavScrolled,
    },
  ]">
    <!-- 👉 Header -->
    <div class="nav-header">
      <slot name="nav-header">
        <RouterLink to="/admin-dashboard" class="app-logo app-title-wrapper w-100">
          <img v-show="hideTitleAndIcon" :src="siteSettingsStore.getFavicon" alt="Company Logo"
            class="company-logo" />
          

          <Transition name="vertical-nav-app-title">
            <img v-show="!hideTitleAndIcon" :src="siteSettingsStore.getCompanyLogo" alt="Company Logo"
            class="company-logo" />
          </Transition>
        </RouterLink>
        <!-- 👉 Vertical nav actions -->
        <!-- Show toggle collapsible in >md and close button in <md -->
        <div class="header-action">
          <Component :is="layoutConfig.app.iconRenderer || 'div'" v-show="configStore.isVerticalNavCollapsed"
            class="d-none nav-unpin" :class="configStore.isVerticalNavCollapsed && 'd-lg-block'"
            v-bind="layoutConfig.icons.verticalNavUnPinned"
            @click="configStore.isVerticalNavCollapsed = !configStore.isVerticalNavCollapsed" />
          <Component :is="layoutConfig.app.iconRenderer || 'div'" v-show="!configStore.isVerticalNavCollapsed"
            class="d-none nav-pin" :class="!configStore.isVerticalNavCollapsed && 'd-lg-block'"
            v-bind="layoutConfig.icons.verticalNavPinned"
            @click="configStore.isVerticalNavCollapsed = !configStore.isVerticalNavCollapsed" />
          <Component :is="layoutConfig.app.iconRenderer || 'div'" class="d-lg-none" v-bind="layoutConfig.icons.close"
            @click="toggleIsOverlayNavActive(false)" />
        </div>
      </slot>
    </div>
    <slot name="before-nav-items">
      <div class="vertical-nav-items-shadow" />
    </slot>
    <slot name="nav-items" :update-is-vertical-nav-scrolled="updateIsVerticalNavScrolled">
      <PerfectScrollbar :key="configStore.isAppRTL" tag="ul" class="nav-items" :options="{ wheelPropagation: false }"
        @ps-scroll-y="handleNavScroll">
        <Component :is="resolveNavItemComponent(item)" v-for="(item, index) in filteredNavItems" :key="index" :item="item" />
      </PerfectScrollbar>
    </slot>
    <slot name="after-nav-items" />
    <div class="vertical-nav-footer">
      <div class="vertical-nav-footer-item">
        <VBtn block color="error" append-icon="tabler-logout" @click="logout" style="border-radius: 0;" v-show="!configStore.isVerticalNavCollapsed">
          Logout
        </VBtn>
        <VBtn block color="error" append-icon="tabler-logout" @click="logout" style="border-radius: 0;" v-show="configStore.isVerticalNavCollapsed" />
      </div>
    </div>
  </Component>
</template>

<style lang="scss" scoped>
.app-logo {
  display: flex;
  align-items: center;
  column-gap: 0.75rem;

  .company-logo {
    max-height: 40px;
    width: auto;
  }

  .app-logo-title {
    font-size: 1.375rem;
    font-weight: 700;
    letter-spacing: 0.25px;
    line-height: 1.5rem;
    text-transform: capitalize;
  }
}
</style>

<style lang="scss">
@use "@configured-variables" as variables;
@use "@layouts/styles/mixins";

// 👉 Vertical Nav
.layout-vertical-nav {
  position: fixed;
  z-index: variables.$layout-vertical-nav-z-index;
  display: flex;
  flex-direction: column;
  block-size: 100%;
  inline-size: variables.$layout-vertical-nav-width;
  inset-block-start: 0;
  inset-inline-start: 0;
  transition: inline-size 0.25s ease-in-out, box-shadow 0.25s ease-in-out;
  will-change: transform, inline-size;

  .nav-header {
    display: flex;
    align-items: center;

    .header-action {
      cursor: pointer;

      @at-root {
        #{variables.$selector-vertical-nav-mini} .nav-header .header-action {

          &.nav-pin,
          &.nav-unpin {
            display: none !important;
          }
        }
      }
    }
  }

  .app-title-wrapper {
    margin-inline-end: auto;
  }

  .nav-items {
    block-size: 100%;

    // ℹ️ We no loner needs this overflow styles as perfect scrollbar applies it
    // overflow-x: hidden;

    // // ℹ️ We used `overflow-y` instead of `overflow` to mitigate overflow x. Revert back if any issue found.
    // overflow-y: auto;
  }

  .nav-item-title {
    overflow: hidden;
    margin-inline-end: auto;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  // 👉 Collapsed
  .layout-vertical-nav-collapsed & {
    &:not(.hovered) {
      inline-size: variables.$layout-vertical-nav-collapsed-width;
    }
  }
}

// Small screen vertical nav transition
@media (max-width: 1279px) {
  .layout-vertical-nav {
    &:not(.visible) {
      transform: translateX(-#{variables.$layout-vertical-nav-width});

      @include mixins.rtl {
        transform: translateX(variables.$layout-vertical-nav-width);
      }
    }

    transition: transform 0.25s ease-in-out;
  }
}
</style>
