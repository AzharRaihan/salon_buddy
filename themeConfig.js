import { defineThemeConfig } from "@core";
import { Skins } from "@core/enums";
import { breakpointsVuetifyV3 } from "@vueuse/core";
import { VIcon } from "vuetify/components/VIcon";
// ❗ Logo SVG must be imported with ?raw suffix
import logo from "@images/logo.svg?raw";
import {
  AppContentLayoutNav,
  ContentWidth,
  FooterType,
  NavbarType,
} from "@layouts/enums";

export const { themeConfig, layoutConfig } = defineThemeConfig({
  app: {
    title: "vuexy",
    logo: h("div", {
      innerHTML: logo,
      style: "line-height:0; color: rgb(var(--v-global-theme-primary))",
    }),
    contentWidth: ContentWidth.Boxed,
    contentLayoutNav: AppContentLayoutNav.Vertical,
    overlayNavFromBreakpoint: breakpointsVuetifyV3.lg - 1, // 1 for matching with vuetify breakpoint. Docs: https://next.vuetifyjs.com/en/features/display-and-platform/
    i18n: {
      enable: true,
      defaultLocale: "en",
      langConfig: [
        {
          label: "English",
          i18nLang: "en",
          isRTL: false,
        },
        {
          label: "French",
          i18nLang: "fr",
          isRTL: false,
        },
        {
          label: "Arabic",
          i18nLang: "ar",
          isRTL: true,
        },
        {
          label: "Spanish",
          i18nLang: "es",
          isRTL: false,
        },
        {
          label: "Portuguese",
          i18nLang: "pt",
          isRTL: false,
        },
        {
          label: "German",
          i18nLang: "de",
          isRTL: false,
        },
        {
          label: "Bangeli",
          i18nLang: "bn",
          isRTL: false,
        },
        {
          label: "Hindi",
          i18nLang: "hi",
          isRTL: false,
        },
      ],
    },
    theme: "light",
    skin: Skins.Default,
    iconRenderer: VIcon,
  },
  navbar: {
    type: NavbarType.Sticky,
    navbarBlur: true,
  },
  footer: { type: FooterType.Static },
  verticalNav: {
    isVerticalNavCollapsed: false,
    defaultNavItemIconProps: { icon: "tabler-circle" },
    isVerticalNavSemiDark: false,
  },
  horizontalNav: {
    type: "sticky",
    transition: "slide-y-reverse-transition",
    popoverOffset: 6,
  },

  /*
    // ℹ️  In below Icons section, you can specify icon for each component. Also you can use other props of v-icon component like `color` and `size` for each icon.
    // Such as: chevronDown: { icon: 'tabler-chevron-down', color:'primary', size: '24' },
    */
  icons: {
    chevronDown: { icon: "tabler-chevron-down" },
    chevronRight: { icon: "tabler-chevron-right", size: 20 },
    close: { icon: "tabler-x", size: 20 },
    verticalNavPinned: { icon: "tabler-indent-increase", size: 24 },
    verticalNavUnPinned: { icon: "tabler-indent-decrease", size: 24 },
    sectionTitlePlaceholder: { icon: "tabler-minus" },
  },
});
