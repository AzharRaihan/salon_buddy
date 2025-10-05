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
            <div class="gallery-image-wrap" @click="showImage(item)">
              <img :src="item.photo_url" :alt="item.title">
              <div class="light-shadow"></div>
              <div class="view-image">
                <VIcon icon="tabler-eye" />
              </div>
              <div class="gallery-image-content">
                <h3>{{ item.title }}</h3>
                <p>{{ item.description }}</p>
              </div>
            </div>
          </div>
        </div>
        <div class="col-12" v-if="gallery.length == 0">
          <div class="text-center">
            <h5>{{ t('No gallery found') }}</h5>
            <VIcon size="45" icon="tabler-filter-search" />
          </div>
        </div>
      </div>

      <!-- Lightbox -->
      <div v-if="selectedImage" class="lightbox" @click="closeImage">
        <div class="lightbox-content" @click.stop>
          <img :src="selectedImage.photo_url" :alt="selectedImage.title">
          <button class="close-button" @click="closeImage">&times;</button>
          <button class="nav-button prev" @click="showPrev">
            <VIcon icon="tabler-arrow-left" />
          </button>
          <button class="nav-button next" @click="showNext">
            <VIcon icon="tabler-arrow-right" />
          </button>
          <div class="image-counter">{{ currentIndex + 1 }} / {{ gallery.length }}</div>
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

const selectedImage = ref(null)
const currentIndex = ref(0)

const showImage = (image) => {
  selectedImage.value = image
  currentIndex.value = gallery.value.findIndex(img => img.id == image.id)
}

const closeImage = () => {
  selectedImage.value = null
}

const showNext = (e) => {
  e.stopPropagation()
  currentIndex.value = (currentIndex.value + 1) % gallery.value.length
  selectedImage.value = gallery.value[currentIndex.value]
}

const showPrev = (e) => {
  e.stopPropagation()
  currentIndex.value = (currentIndex.value - 1 + gallery.value.length) % gallery.value.length
  selectedImage.value = gallery.value[currentIndex.value]
}

const handleKeydown = (e) => {
  if (!selectedImage.value) return
  
  if (e.key == 'ArrowRight') showNext(e)
  if (e.key == 'ArrowLeft') showPrev(e)
  if (e.key == 'Escape') closeImage()
}

onMounted(() => {
  window.addEventListener('keydown', handleKeydown)
})

definePage({
  meta: {
    layout: 'frontend',
    public: true,
  },
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
.gallery-section .gallery-image-wrap .view-image {
  width: 45px;
  height: 45px;
  border-radius: 50%;
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  background: #098d9c !important;
  color: white !important;
  font-size: 16px;
  padding: 10px !important;
  cursor: pointer !important;
  transition: all 0.3s ease !important;
  display: none;
}
.gallery-section .gallery-image-wrap:hover .view-image {
  display: block;
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

/* Lightbox Styles */
.lightbox {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.9);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.lightbox-content {
  position: relative;
  max-width: 90%;
  max-height: 90vh;
}

.lightbox-content img {
  max-width: 100%;
  max-height: 90vh;
  object-fit: contain;
}

.close-button {
  position: absolute;
  top: -40px;
  right: -40px;
  background: none;
  border: none;
  color: white;
  font-size: 30px;
  cursor: pointer;
}

.nav-button {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  background: rgba(255, 255, 255, 0.1);
  border: none;
  color: white;
  padding: 10px;
  cursor: pointer;
  border-radius: 50%;
}

.nav-button.prev {
  left: -60px;
}

.nav-button.next {
  right: -60px;
}

.image-counter {
  position: absolute;
  bottom: -30px;
  left: 50%;
  transform: translateX(-50%);
  color: white;
}
</style>
