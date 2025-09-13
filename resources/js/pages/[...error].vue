<script setup>
import { useGenerateImageVariant } from '@core/composable/useGenerateImageVariant'
import misc404 from '@images/pages/404.png'
import miscMaskDark from '@images/pages/misc-mask-dark.png'
import miscMaskLight from '@images/pages/misc-mask-light.png'
import error404 from '../../../public/images/dev-image/404.png'
import BackButton from '../components/frontend/mini-components/BackButton.vue'

const authThemeMask = useGenerateImageVariant(miscMaskLight, miscMaskDark)

// definePage({
//   alias: '/pages/misc/not-found/:error(.*)',
//   meta: {
//     layout: 'blank',
//     public: true,
//   },
// })
definePage({
  meta: {
    layout: 'frontend',
    public: true,
  },
})
</script>

<template>
  <div class="misc-wrapper default-section-padding">
    

    <!-- 👉 Image -->
    <div class="misc-avatar w-100 text-center">
      <VImg
        :src="error404"
        alt="error 404"
        :max-height="$vuetify.display.smAndDown ? 350 : 500"
        class="mx-auto"
      />
      <h2 class="lost-heading">Look Like You’re Lost</h2>
      <p class="lost-description">Sorry, we couldn't find the page you where looking for. <br> We 
        suggest that you return to homepage.</p>

      <BackButton :link="'/'" :text="'Back to Home'" />
    </div>
  </div>
</template>

<style lang="scss">
@use "@core-scss/template/pages/misc.scss";
</style>

<style scoped>
.lost-heading {
  font-size: 38px;
  font-weight: 600;
  color: #2D2C2B;
  text-align: center;
  margin-top: 40px;
}
.lost-description {
  font-size: 18px;
  color: #727272;
  text-align: center;
  margin-top: 20px;
  font-weight: 400;
}
</style>
