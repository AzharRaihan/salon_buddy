<script setup>
import { ref } from 'vue'
import SectionMoreBtn from '@/components/frontend/mini-components/SectionMoreBtn.vue'
import CommonPageBanner from '@/components/frontend/CommonPageBanner.vue'
import BookAppointmentBtn from '../../components/frontend/mini-components/BookAppointmentBtn.vue'
import { useRoute } from 'vue-router'
import { useI18n } from 'vue-i18n';
const { t } = useI18n()
const route = useRoute()
const memberId = ref(null)
const memberDetails = ref({})
const socialMedia = ref([])

const iconMap = {
  Facebook: "tabler-brand-facebook",
  Instagram: "tabler-brand-instagram",
  YouTube: "tabler-brand-youtube",
  TikTok: "tabler-brand-tiktok",
  Twitter: "tabler-brand-twitter",
}

definePage({
  meta: {
    layout: 'frontend',
    public: true,
  },
})

const fetchMemberDetails = async () => {
  const response = await $api(`/get-member-details/${memberId.value}`)
  if (response.success) {
    memberDetails.value = response.data
    socialMedia.value = JSON.parse(response.data.social_media)
  }
}

onMounted(async () => {
  const member_id = atob(route.query.member_id)
  if (member_id) {
    memberId.value = member_id
  }
  await fetchMemberDetails()
})
</script>

<template>
  <div>
    <!-- Common Page Banner -->
    <CommonPageBanner :title="t('Team Details')" :breadcrumb="t('Team Details')" />

    <section class="team-details-wrapper">
      <div class="container">
        <div class="row">
          <div class="col-md-5 col-lg-6">
            <div class="team-details-image">
              <img :src="memberDetails.photo_url" alt="Team Details Image">
            </div>
          </div>
          <div class="col-md-7 col-lg-6 team-details-content-wrapper">
            <div class="team-details-content">
              <h2 class="team-details-title">{{ memberDetails.name }}</h2>
              <p>{{ memberDetails.designation }}</p>
            </div>
            <div class="team-details-info">
              <h3>{{ t('About Info') }}</h3>
              <p v-if="memberDetails.description">{{ memberDetails.description }}</p>
            </div>
            <ul>
              <li v-if="memberDetails.designation">
                <p class="title">{{ t('Designation') }}:</p>
                <p class="value">{{ memberDetails.designation }}</p>
              </li>
              <li v-if="memberDetails.email">
                <p class="title">{{ t('Email') }}:</p>
                <p class="value">{{ memberDetails.email }}</p>
              </li>
              <li v-if="memberDetails.age">
                <p class="title">{{ t('Age') }}:</p>
                <p class="value">{{ memberDetails.age }}</p>
              </li>
              <li v-if="memberDetails.qualification">
                <p class="title">{{ t('Qualification') }}:</p>
                <p class="value">{{ memberDetails.qualification }}</p>
              </li>
              <li v-if="memberDetails.gender">
                <p class="title">{{ t('Gender') }}:</p>
                <p class="value">{{ memberDetails.gender }}</p>
              </li>
              <li v-if="memberDetails.experience">
                <p class="title">{{ t('Experience') }}:</p>
                <p class="value">{{ memberDetails.experience }}</p>
              </li>
            </ul>

            <h5 class="team-details-portfolio-title">{{ t('Portfolio') }}:</h5>
            <div class="team-details-social">
              <div class="social-icon-wrap" v-for="(item, index) in socialMedia" :key="index">
                <a 
                  :href="item.url"
                  class="social-icon" 
                  target="_blank"
                  v-if="item.is_active"
                >
                  <VIcon size="22" :icon="iconMap[item.name]" />
                </a>
              </div>
            </div>
          </div>
        </div>
        <div class="row team-details-card-wrapper">
          <div class="col-lg-3 col-md-6 col-sm-6 col-12 mb-3">
            <div class="team-details-card">
              <div class="image-circle">
                <img src="../../@frontend/images/team-details/total-service.png" alt="Team Details Card">
              </div>
              <div class="team-details-card-content">
                <h3>{{ memberDetails.service_done || 0 }}</h3>
                <p>{{ t('Total Service') }}</p>
              </div>
            </div>
          </div>
          <div class="col-lg-3 col-md-6 col-sm-6 col-12 mb-3">
            <div class="team-details-card">
              <div class="image-circle">
                <img src="../../@frontend/images/team-details/happy-client.png" alt="Team Details Card">
              </div>
              <div class="team-details-card-content">
                <h3>{{ memberDetails.happy_customers || 0 }} +</h3>
                <p>{{ t('Happy Client') }}</p>
              </div>
            </div>
          </div>
          
          <div class="col-lg-3 col-md-6 col-sm-6 col-12 mb-3">
            <div class="team-details-card">
              <div class="image-circle">
                <img src="../../@frontend/images/team-details/years.png" alt="Team Details Card">
              </div>
              <div class="team-details-card-content">
                <h3>{{ memberDetails.experience || 0 }}</h3>
                <p>{{ t('Experience') }}</p>
              </div>
            </div>
          </div>
          
          
          <div class="col-lg-3 col-md-6 col-sm-6 col-12 mb-3">
            <div class="team-details-card">
              <div class="image-circle">
                <img src="../../@frontend/images/team-details//ratting.png" alt="Team Details Card">
              </div>
              <div class="team-details-card-content">
                <h3>{{ Number(memberDetails.customer_rattings || 0).toFixed(1) }}</h3>
                <p>{{ t('Customer Ratting') }}</p>
              </div>
            </div>
          </div>
        </div>

        <div class="customer-comment-wrapper">
          <h3 class="customer-comment-title">{{ t('Customer Review') }}</h3>
          <div class="row">
            <div class="col-sm-12 col-md-10 col-lg-6">
              <div class="customer-comment-box">
                <div class="customer-comment-box-header">
                  <div>
                    <div class="customer-comment-box-header-left-image">
                      <img src="../../@frontend/images/rectangle-avater.png" alt="Customer Comment Image">
                    </div>

                  </div>
                  <div class="customer-comment-info">
                    <div class="customer-name d-flex align-items-center">
                      <h3 class="m-0 pe-2">Samantha W</h3>
                      <p class="m-0">2 days ago</p>
                    </div>
                    <div class="customer-ratting">
                      <VIcon icon="tabler-star" />
                      <VIcon icon="tabler-star" />
                      <VIcon icon="tabler-star" />
                      <VIcon icon="tabler-star" />
                      <VIcon icon="tabler-star" />
                    </div>
                    <div class="customer-comment">
                      <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Quisquam, quos. Lorem ipsum dolor sit amet consectetur adipisicing elit. Quisquam, quos. Lorem ipsum dolor sit amet consectetur adipisicing elit. Quisquam, quos.</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="review-box-wrapper">
          <div class="row">
            <div class="col-md-9 col-lg-7">
              <div class="review-box">
                <div class="review-box-header">
                  <h3>{{ t('Review') }}</h3>
                  <VIcon icon="tabler-star" />
                  <VIcon icon="tabler-star" />
                  <VIcon icon="tabler-star" />
                  <VIcon icon="tabler-star" />
                  <VIcon icon="tabler-star" />
                </div>
                <div class="review-box-content">
                  <div class="row">
                    <div class="col-md-6 mb-3">
                      <div class="form-group">
                        <label for="review-name">{{ t('Name') }}</label>
                        <input type="text" class="form-control" id="review-name" placeholder="Enter your name">
                      </div>
                    </div>
                    <div class="col-md-6 mb-3">
                      <div class="form-group">
                        <label for="review-email">{{ t('Email Address') }}</label>
                        <input type="text" class="form-control" id="review-email" placeholder="Enter your email address">
                      </div>
                    </div>
                    <div class="col-md-12 mb-3">
                      <div class="form-group">
                        <label for="review-message">{{ t('Message') }}</label>
                        <textarea class="form-control" id="review-message" placeholder="Enter your message" rows="7"></textarea>
                      </div>
                    </div>
                  </div>
                  <BookAppointmentBtn :text="t('Submit Review')" link="#" />
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>
