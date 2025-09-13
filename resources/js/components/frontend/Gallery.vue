<template>
  <section class="gallery-section default-section-padding-b" >
    <div class="container">
      <div class="row">
        <div class="col-12">
          <div class="section-header text-center">
            <h6 class="heading-mini-title">{{ t('Our Portfolio') }}</h6>
            <h2 class="section-title">{{ t('All The Great Work That We Done') }}</h2>
          </div>
        </div>
      </div>
      <div class="gallery-image">
        <div class="row">
          <div v-for="(item, index) in gallery" :key="item.id" :class="(index === 0 || index == 1) ? 'col-md-6' : 'col-md-4'">
            <div class="gallery-image-wrap">
              <img :src="item.photo_url" alt="">
              <div class="gallery-image-content">
                <h3>{{ item.title }}</h3>
                <p>{{ item.description }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useI18n } from 'vue-i18n';
const { t } = useI18n()
const gallery = ref([])
defineProps({
    classSectionPadding: {
        type: String,
        default: ''
    }
})

const fetchGallery = async () => {
  try {
    const response = await $api('/get-gallery')
    gallery.value = response.data
  } catch (error) {
    gallery.value = []
  }
}
onMounted(async () => {
  await fetchGallery()
})
</script>
<style scoped>
.gallery-section  {
  background: white;
}
.gallery-section .gallery-image-wrap {
  position: relative;
  overflow: hidden;
  border-radius: 12px;
  margin-bottom: 30px;
  transition: all 0.3s ease;
}

.gallery-section .gallery-image-wrap img {
  transition: all 0.3s ease;
}
.gallery-section .gallery-image-wrap:hover img {
  transform: rotate(2deg) scale(1.05);
  transition: all 0.3s ease;
}

.gallery-section .gallery-image-content {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  background: rgba(159, 159, 159, 0.4);
  backdrop-filter: blur(10px);
  padding: 12px 35px;
  color: white;
  opacity: 0;
  transform: translateY(0px);
  transition: opacity 0.4s ease, transform 0.3s ease;
  pointer-events: none;
}
.gallery-section .gallery-image-wrap:hover .gallery-image-content {
  opacity: 1;
  transform: translateY(0);
  pointer-events: auto;
}
.gallery-section .gallery-image-wrap img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.gallery-section .gallery-image-content h3 {
  font-size: 24px;
  font-weight: 600;
  margin-bottom: 8px;
  color: white;
}
.gallery-section .gallery-image-content p {
  font-size: 16px;
  font-weight: 400;
  margin-bottom: 0;
}
</style>
