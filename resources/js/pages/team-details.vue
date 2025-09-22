<script setup>
import { ref } from 'vue'
import CommonPageBanner from '@/components/frontend/CommonPageBanner.vue'
import BookAppointmentBtn from '@/components/frontend/mini-components/BookAppointmentBtn.vue'
import EmployeeRatingForm from '@/components/frontend/EmployeeRatingForm.vue'
import EmployeeReviews from '@/components/frontend/EmployeeReviews.vue'
import { useRoute } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useEmployeeRating } from '@/composables/useEmployeeRating'
import { toast } from 'vue3-toastify'

const { t } = useI18n()
const { checkRatingEligibility } = useEmployeeRating()
const route = useRoute()

const memberId = ref(null)
const memberDetails = ref({})
const socialMedia = ref([])
const showRatingForm = ref(false)
const employeeReviewsRef = ref(null)

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

const handleRatingSubmitted = (ratingData) => {
  toast(t('Rating submitted successfully!'), { type: 'success' })
  showRatingForm.value = false
}

const handleRefreshReviews = () => {
  if (employeeReviewsRef.value) {
    employeeReviewsRef.value.refreshReviews()
  }
}

const handleLoginRequired = (provider) => {
  toast(t('Please login to continue'), { type: 'info' })
}

const toggleRatingForm = () => {
  showRatingForm.value = !showRatingForm.value
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
                <img src="../../js/@frontend/images/team-details/total-service.png" alt="Team Details Card">
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
                <img src="../../js/@frontend/images/team-details/happy-client.png" alt="Team Details Card">
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
                <img src="../../js/@frontend/images/team-details/years.png" alt="Team Details Card">
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
                <img src="../../js/@frontend/images/team-details/ratting.png" alt="Team Details Card">
              </div>
              <div class="team-details-card-content">
                <h3>{{ Number(memberDetails.customer_rattings || 0).toFixed(1) }}</h3>
                <p>{{ t('Customer Ratting') }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Employee Reviews Section -->
        <div class="employee-reviews-section">
          <div class="row">
            <div class="col-lg-8">
              <!-- Reviews Display -->
              <EmployeeReviews 
                ref="employeeReviewsRef"
                v-if="memberId"
                :employee-id="memberId"
                :key="`reviews-${memberId}`"
              />
            </div>
            <div class="col-lg-4">
              <!-- Rating Form -->
              <div class="rating-form-section">
                <div class="rating-form-header">
                  <h4>{{ t('Rate This Employee') }}</h4>
                  <p class="text-muted">{{ t('Share your experience') }}</p>
                </div>
                
                <!-- Toggle Button -->
                <div v-if="!showRatingForm" class="text-center mb-3">
                  <button 
                    class="btn btn-outline-primary"
                    @click="toggleRatingForm"
                  >
                    <VIcon icon="tabler-star" class="me-2" />
                    {{ t('Write a Review') }}
                  </button>
                </div>

                <!-- Rating Form -->
                 
                <EmployeeRatingForm 
                  v-if="showRatingForm && memberId"
                  :employee-id="memberId"
                  :employee-name="memberDetails.name"
                  @rating-submitted="handleRatingSubmitted"
                  @login-required="handleLoginRequired"
                  @refresh-reviews="handleRefreshReviews"
                />

                <!-- Close Button -->
                <div v-if="showRatingForm" class="text-center mt-3">
                  <button 
                    class="btn btn-link text-muted"
                    @click="toggleRatingForm"
                  >
                    {{ t('Cancel') }}
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<style scoped>
.employee-reviews-section {
  margin-top: 40px;
  padding-top: 40px;
  border-top: 1px solid #eee;
}

.rating-form-section {
  background: #f8f9fa;
  border-radius: 12px;
  padding: 24px;
  height: fit-content;
  position: sticky;
  top: 20px;
}

.rating-form-header {
  text-align: center;
  margin-bottom: 20px;
}

.rating-form-header h4 {
  color: #333;
  margin-bottom: 8px;
  font-weight: 600;
}

.btn-outline-primary {
  border-color: var(--primary-bg-color);
  color: var(--primary-bg-color);
  padding: 10px 20px;
  border-radius: 8px;
  font-weight: 500;
  transition: all 0.3s ease;
}

.btn-outline-primary:hover {
  background-color: var(--primary-bg-color);
  color: white;
  transform: translateY(-1px);
}

.btn-link {
  text-decoration: none;
  color: #6c757d;
  font-size: 14px;
  padding: 8px 16px;
  border: none;
  background: none;
  transition: color 0.3s ease;
}

.btn-link:hover {
  color: #333;
  text-decoration: underline;
}

/* Responsive */
@media (max-width: 992px) {
  .rating-form-section {
    position: static;
    margin-top: 30px;
  }
}

@media (max-width: 768px) {
  .employee-reviews-section {
    margin-top: 30px;
    padding-top: 30px;
  }
  
  .rating-form-section {
    padding: 20px;
  }
}
</style>
