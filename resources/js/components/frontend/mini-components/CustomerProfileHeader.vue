<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useCustomerAuth } from '@/composables/useCustomerAuth'

const router = useRouter()
const { 
  customerData
} = useCustomerAuth()
const customerInfo = computed(() => {
  const data = customerData.value
  return {
    name: data?.name || 'Customer',
    email: data?.email || '',
    photo: data?.photo_url || null
  }
})
const goToHome = () => {
  router.push('/')
}

const props = defineProps({
  isDashboard: {
    type: Boolean,
    default: false
  }
})
</script>
<template>
  <div>
    <div class="customer-panel-header customer-panel-header-wrapper">
      <div>
        <template v-if="isDashboard">
          <h2 class="hi-title">
            <span>Hello,</span> {{ customerInfo.name }}
            <img src="../../../../../public/images/dev-image/wave.png" alt="wave" class="wave-img">
          </h2>
          <p class="hi-title-description">Ready to place your next order?</p>
        </template>
      </div>
      <!-- <button class="customer-panel-header-button" @click="goToHome">
        <VIcon icon="tabler-arrow-left" size="20" />
        Home
      </button> -->
    </div>
  </div>
</template>
<style scoped>
.hi-title {
  font-size: 24px;
  font-weight: 400;
  color: var(--title-color);
  margin-bottom: 10px;
}
.hi-title span {
  font-weight: 600;
}
.hi-title-description {
  color: var(--text-color);
  font-size: 16px;
  font-weight: 400;
}
</style>