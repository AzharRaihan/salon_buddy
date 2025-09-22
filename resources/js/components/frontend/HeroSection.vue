<template>
  <section class="hero-section" :style="{ backgroundImage: `url(${banners.banner_image_url})` }">
    <div class="container">
      <div class="hero-main">
        <div class="hero-content-wrapper">
          <div class="hero-content">
            <p class="welcome-text"> <span class="welcome-text-badge"></span> {{ banners.banner_tag }}</p>
            <h1 class="hero-title">
              {{ banners.banner_title }}
            </h1>
            <p class="hero-subtitle">
              {{ banners.banner_description }}
            </p>
            <div class="d-flex gap-2">
              <BookingSamllBtn :link="'/appointment-service'" :text="'Book Appointment'" />
              <RouterLink to="/contact-us" class="btn hero-contact-us common-animation-button">
                Contact Us
              </RouterLink>
            </div>
            <div class="customer-review-section" v-if="banners.ratting_count > 0" >
              <div class="customer-review-inner">
                <div v-for="(customer, index) in banners.customer.slice(0, banners.ratting_count)" 
                :key="customer.id">
                  <div class="customer-item" :class="`item-${index + 1}`">
                    <img :src="customer.photo_url" alt="customer-img" >
                  </div>
                </div>
              </div>
              <div class="customer-review-text" :class="banners.ratting_count == 1 ? 'ps-3' : ''">
                <div class="d-flex">
                  <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="currentColor"  class="icon icon-tabler icons-tabler-filled icon-tabler-star"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8.243 7.34l-6.38 .925l-.113 .023a1 1 0 0 0 -.44 1.684l4.622 4.499l-1.09 6.355l-.013 .11a1 1 0 0 0 1.464 .944l5.706 -3l5.693 3l.1 .046a1 1 0 0 0 1.352 -1.1l-1.091 -6.355l4.624 -4.5l.078 -.085a1 1 0 0 0 -.633 -1.62l-6.38 -.926l-2.852 -5.78a1 1 0 0 0 -1.794 0l-2.853 5.78z" /></svg>
                  <span>
                    {{ banners.ratting_count }} +
                  </span>
                </div>
                <p>
                  Customer Reviews
                </p>
              </div>
              
            </div>
          </div>
        </div>
        <div class="hero-image" >
        </div>
      </div>
    </div>
  </section >
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import BookingSamllBtn from './mini-components/BookingSamllBtn.vue'
const banners = ref({})
// Fetch banners
const fetchBanners = async () => {
  try {
    const response = await $api('/get-all-banner')
    banners.value = response.data
  } catch (error) {
    console.error('Error fetching branches:', error)
  }
}
onMounted(async () => {
  await fetchBanners()
})
</script>

<style scoped>
.hero-contact-us {
  padding: 10px 20px;
  background-color: white !important;
  color: var(--primary-bg-color);
  height: 46px;
}
.hero-contact-us::before {
  background-color: var(--primary-bg-color);
}
.hero-contact-us::after {
  border: 1px solid var(--primary-bg-color);
  color: var(--primary-bg-color);
  background-color: white;
}
.hero-contact-us:hover {
  color: var(--color-white);
}
</style>