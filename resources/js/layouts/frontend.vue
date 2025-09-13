<script setup>
import AppHeader from '@/components/frontend/AppHeader.vue'
import AppFooter from '@/components/frontend/AppFooter.vue'
import "bootstrap/dist/js/bootstrap.bundle.min.js";
</script>
<template>
  <div class="frontend-layout">
    <!-- Header -->
    <AppHeader />
    <!-- Main Content -->
    <main class="main-content">
      <RouterView #="{Component}">
        <Suspense :timeout="0">
          <Component :is="Component" />
        </Suspense>
      </RouterView>
    </main>
    <!-- Footer -->
    <AppFooter />
  </div>
</template>
<style>
@import "bootstrap/dist/css/bootstrap.min.css";
@import "../../frontend/assets/css/style.css";
@import "../../frontend/assets/css/responsive.css";
</style>
