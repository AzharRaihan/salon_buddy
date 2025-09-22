<template>
  <section :class="`team-section default-section-padding ${classBind}`">
    <div class="container">
      <div class="team-section-swiper">
        <div class="row">
          <div class="col-lg-12 member-title" v-if="headerShow">
            <div class="section-header">
              <h6 class="heading-mini-title">{{ t('Team Member') }}</h6>
              <h2 class="section-title">{{ t('Meet Our Expert Team') }}</h2>
            </div>
          </div>
        </div>

        <div class="member-slider">
          <div class="team-slider-container">
            <div :class="`${isHome ? 'swiper team-swiper' : ''}`">
              <div :class="`${!isHome ? 'row' : 'swiper-wrapper'}`">
                <div 
                  v-for="member in teamMembers" 
                  :key="member.id"
                  :class="`${!isHome ? 'col-xl-3 col-lg-4 col-md-6 col-sm-6 team-card-item' : 'swiper-slide'}`"
                >
                  <div class="team-card">
                    <div class="team-content">
                      <h5 class="team-name">{{ member.name }}</h5>
                      <p v-if="member.designation" class="team-position">{{ member.designation }}</p>
                      <div v-if="member.parsedSocialMedia.length > 0" class="social-links">
                        <a 
                          v-for="social in member.parsedSocialMedia" 
                          :key="social.name"
                          :href="social.url"
                          v-show="social.is_active" 
                          class="social-link"
                          target="_blank"
                          rel="noopener noreferrer"
                        >
                          <VIcon :icon="getSocialIcon(social.name)" />
                        </a>
                      </div>
                    </div>
                    <div class="team-image-wrapper">
                      <div class="team-image">
                        <img :src="member.photo_url" class="img-fluid" :alt="member.name">
                        <div class="light-shadow"></div>
                        <div class="view-more">
                          <BlurTypeBtn :link="`/team-details?member_id=${encryptId(member.id)}`" :text="t('Read More')" />
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Navigation Arrows -->
            <div class="team-nav-prev common-swiper-button common-animation-button" v-if="headerShow && teamMembers.length > 0">
              <VIcon size="24" icon="tabler-arrow-narrow-left" />
            </div>
            <div class="team-nav-next common-swiper-button common-animation-button" v-if="headerShow && teamMembers.length > 0">
              <VIcon size="24" icon="tabler-arrow-narrow-right" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Swiper } from 'swiper'
import { Navigation, Autoplay } from 'swiper/modules'
import 'swiper/css'
import 'swiper/css/navigation'
import BlurTypeBtn from './mini-components/BlurTypeBtn.vue'
import { useI18n } from 'vue-i18n';
const { t } = useI18n()

const teamMembers = ref([])
const props = defineProps({
  headerShow: {
    type: Boolean,
    default: true
  },
  classBind: {
    type: String,
    default: ''
  },
  isHome: {
    type: Boolean,
    default: false
  }
})

const getSocialIcon = (platform) => {
  const icons = {
    'Facebook': 'tabler-brand-facebook',
    'Twitter': 'tabler-brand-twitter', 
    'Instagram': 'tabler-brand-instagram',
    'YouTube': 'tabler-brand-youtube',
    'TikTok': 'tabler-brand-tiktok'
  }
  return icons[platform] || 'tabler-brand-social'
}

const fetchTeamMembers = async () => {
  try {
    const response = await $api('/get-all-employees-frontend')
    teamMembers.value = response.data.map(member => ({
      ...member,
      parsedSocialMedia: JSON.parse(member.social_media || '[]')
    }))
  } catch (error) {
    console.error('Error fetching team members:', error)
    teamMembers.value = [] // Set empty array on error
  }
}

// IRUL ID Encryption
function encryptId(id) {
  return btoa(id) // Base64 encode
}

onMounted(async () => {
  await fetchTeamMembers()
  if (props.isHome) {
    const swiper = new Swiper('.team-swiper', {
      modules: [Navigation, Autoplay],
      slidesPerView: 1,
      spaceBetween: 30,
      loop: true,
      autoplay: {
        delay: 4000,
        disableOnInteraction: false,
      },
      navigation: {
        nextEl: '.team-nav-next',
        prevEl: '.team-nav-prev',
      },
      breakpoints: {
        450: {
          slidesPerView: 1.5,
          spaceBetween: 15,
        },
        576: {
          slidesPerView: 1.5,
          spaceBetween: 15,
        },
        768: {
          slidesPerView: 2,
          spaceBetween: 30,
        },
        992: {
          slidesPerView: 2.5,
          spaceBetween: 30,
        }, 
        1200: {
          slidesPerView: 3,
          spaceBetween: 30,
        },
        1366: {
          slidesPerView: 3.5,
          spaceBetween: 30,
        },
        1920: {
          slidesPerView: 4,
          spaceBetween: 30,
        }
      },
      speed: 800,
    })
  }
})
</script>

<style scoped>
.team-section {
  background-image: url('../../../../public/assets/images/default-images/Product.png');
  background-repeat: no-repeat;
}
.team-section.page-team-members {
  background-image: none;
}
.team-card-item {
  margin-bottom: 30px;
}
</style>