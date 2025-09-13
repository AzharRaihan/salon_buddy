<script setup>
import { useConfigStore } from '@core/stores/config'
import { switchToVerticalNavOnLtOverlayNavBreakpoint } from '@layouts/utils'
const DefaultLayoutWithVerticalNav = defineAsyncComponent(() => import('./components/DefaultLayoutWithVerticalNav.vue'))
const configStore = useConfigStore()
switchToVerticalNavOnLtOverlayNavBreakpoint()
const { layoutAttrs, injectSkinClasses } = useSkins()
injectSkinClasses()

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
// !SECTION
</script>

<template>
  <Component v-bind="layoutAttrs" :is="DefaultLayoutWithVerticalNav">
    <AppLoadingIndicator ref="refLoadingIndicator" />
    <RouterView v-slot="{ Component }">
      <Suspense :timeout="0" @fallback="isFallbackStateActive = true" @resolve="isFallbackStateActive = false">
        <Component :is="Component" />
      </Suspense>
    </RouterView>
  </Component>
</template>

<style lang="scss">
// As we are using `layouts` plugin we need its styles to be imported
@use "@layouts/styles/default-layout";
@import '@styles/main-template/css/style.css';
</style>