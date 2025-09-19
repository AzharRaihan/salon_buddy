<template>
  <section class="frequently-ask-question">
    <div class="container">
      <div class="row">
        <div class="col-md-5 col-lg-4">
          <div class="section-header">
            <h3 class="section-title">{{ t('Frequently Asked Question') }}</h3>
          </div>
          <div class="image-dotted">
            <img src="../../@pos/assets/images/homepage/question.png" alt="Team Member" class="img-fluid">
          </div>
        </div>
        <div class="col-md-7 col-lg-8">
          <div class="faq-container">
            <!-- Loading state -->
            <div v-if="loading" class="d-flex justify-content-center align-items-center" style="min-height: 200px;">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">{{ t('Loading...') }}</span>
              </div>
            </div>
            
            <!-- FAQ items -->
            <div v-else-if="faqs.length > 0" class="faq-item" v-for="(faq, index) in faqs" :key="index">
              <div class="faq-question" @click="toggleFaq(index)" :class="{ 'active': activeFaq === index }">
                <h4>{{ faq.title }}</h4>
                <span class="icon">
                  <i class="fas" :class="activeFaq === index ? 'fa-minus' : 'fa-plus'"></i>
                </span>
              </div>
              <div class="faq-answer" :class="{ 'show': activeFaq === index }">
                <p>{{ faq.description }}</p>
              </div>
            </div>
            
            <!-- No data state -->
            <div v-else class="text-center" style="min-height: 200px; display: flex; align-items: center; justify-content: center;">
              <div>
                <i class="fas fa-question-circle fa-3x text-muted mb-3"></i>
                <p class="text-muted">{{ t('No FAQs available at the moment.') }}</p>
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
const activeFaq = ref(null)
const faqs = ref([])
const loading = ref(true)
const error = ref(null)

const toggleFaq = (index) => {
  activeFaq.value = activeFaq.value === index ? null : index
}

// const fetchFaqs = async () => {
//   try {
//     loading.value = true
//     const response = await $api('/get-all-faq', {
//       method: 'GET'
//     })
    
//     if (response.data && Array.isArray(response.data)) {
//       faqs.value = response.data
//     } else {
//       console.error('Invalid FAQ data format:', response)
//       faqs.value = []
//     }
//   } catch (err) {
//     console.error('Error fetching FAQs:', err)
//     error.value = 'Failed to load FAQs'
//     faqs.value = []
//   } finally {
//     loading.value = false
//   }
// }

const fetchFaqs = async () => {
  try {
    loading.value = true
    const response = await $api('/get-all-faq', { method: 'GET' })
    
    if (response.data && Array.isArray(response.data)) {
      faqs.value = response.data
      // expand index 1 if it exists
      if (faqs.value.length > 1) {
        activeFaq.value = 0
      }
    } else {
      console.error('Invalid FAQ data format:', response)
      faqs.value = []
    }
  } catch (err) {
    console.error('Error fetching FAQs:', err)
    error.value = 'Failed to load FAQs'
    faqs.value = []
  } finally {
    loading.value = false
  }
}



onMounted(() => {
  fetchFaqs()
})
</script>

<style scoped>
.spinner-border {
  width: 3rem;
  height: 3rem;
}
</style>
