<template>
    <section class="working-process-section" :class="classSectionPadding">
      <div ref="workingProcessSection">
        <div class="container">
          <div class="row">
            <div class="col-12 text-center">
              <div class="section-header text-center">
                <h6 class="heading-mini-title">{{ t('Our Workflow') }}</h6>
                <h2 class="section-title">{{ t('Working Process') }}</h2>
              </div>
            </div>
          </div>

          <transition-group name="fade-up" tag="div" class="row" >
            <div class="col-md-4" v-for="(process, index) in workingProcess" :key="process.id" v-show="visible" :style="{ transitionDelay: (index * 0.4) + 's' }">
                <div class="working-process-item">
                    <div class="working-process-item-icon">
                        <div class="inner-icon">
                            <img :src="process.photo_url" alt="service">
                        </div>
                    </div>
                    <div class="working-process-item-content">
                        <h3 class="working-process-item-title">{{ process.title }}</h3>
                        <p class="working-process-item-description">{{ process.description }}</p>
                    </div>
                </div>
            </div>
          </transition-group>
        </div>
      </div>
    </section>
</template>
  

<script setup>
import { ref, onMounted } from 'vue'
import { useI18n } from 'vue-i18n';
const { t } = useI18n()

// Animation
const visible = ref(false)
const workingProcessSection = ref(null)
onMounted(() => {
    const observer = new IntersectionObserver(
      (entries) => {
        if (entries[0].isIntersecting) {
          visible.value = true
          observer.disconnect()
        }
      },
      { threshold: 0.2 }
    )
    observer.observe(workingProcessSection.value)
  })
// Animation End


const workingProcess = ref([])
defineProps({
    classSectionPadding: {
        type: String,
        default: ''
    }
})

const fetchTeamMembers = async () => {
  try {
    const response = await $api('/get-working-process')
    workingProcess.value = response.data
  } catch (error) {
    workingProcess.value = []
  }
}
onMounted(async () => {
  await fetchTeamMembers()
})
</script>
