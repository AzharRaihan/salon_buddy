<script setup>
import { ref, computed, onMounted, watch } from 'vue'

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  },
  selectedBranch: {
    type: Object,
    default: null
  },
  selectedCategory: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['update:isOpen', 'serviceSelected'])

// Local state
const services = ref([])
const loading = ref(false)
const searchTerm = ref('')

// Computed
const filteredServices = computed(() => {
  if (!searchTerm.value) return services.value
  
  return services.value.filter(service => 
    service.name.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
    service.description?.toLowerCase().includes(searchTerm.value.toLowerCase())
  )
})

// Methods
const fetchServices = async () => {
  if (!props.selectedBranch || !props.selectedCategory) return
  
  loading.value = true
  try {
    const response = await $api('/get-services-by-category', {
      params: {
        branch_id: props.selectedBranch.id,
        category_id: props.selectedCategory.id
      }
    })
    services.value = response.data
  } catch (error) {
    console.error('Error fetching services:', error)
  } finally {
    loading.value = false
  }
}

const selectService = (service) => {
  emit('serviceSelected', service)
  emit('update:isOpen', false)
}

const closeModal = () => {
  emit('update:isOpen', false)
}

// Watch for changes
watch(() => [props.selectedBranch, props.selectedCategory], () => {
  if (props.isOpen) {
    fetchServices()
  }
}, { immediate: true })

onMounted(() => {
  if (props.isOpen) {
    fetchServices()
  }
})
</script>

<template>
  <VDialog :model-value="isOpen" @update:model-value="emit('update:isOpen', $event)" max-width="800px" persistent>
    <VCard>
      <VCardTitle class="d-flex justify-space-between align-center">
        <span>Select Service</span>
        <VBtn icon variant="text" @click="closeModal">
          <VIcon>tabler-x</VIcon>
        </VBtn>
      </VCardTitle>

      <VCardText>
        <!-- Search Field -->
        <VTextField
          v-model="searchTerm"
          prepend-inner-icon="tabler-search"
          placeholder="Search services..."
          clearable
          variant="outlined"
          class="mb-4"
        />

        <!-- Services List -->
        <div v-if="loading" class="text-center py-4">
          <VProgressCircular indeterminate color="primary" />
          <p class="mt-2">Loading services...</p>
        </div>

        <div v-else-if="filteredServices.length === 0" class="text-center py-4">
          <VIcon size="64" color="grey-lighten-1">tabler-search-off</VIcon>
          <p class="text-grey mt-2">No services found</p>
        </div>

        <VRow v-else>
          <VCol
            v-for="service in filteredServices"
            :key="service.id"
            cols="12"
            md="6"
          >
            <VCard
              variant="outlined"
              hover
              class="service-card"
              @click="selectService(service)"
            >
              <VCardText>
                <div class="d-flex justify-space-between align-start">
                  <div class="flex-grow-1">
                    <h6 class="text-h6 mb-1">{{ service.name }}</h6>
                    <p class="text-body-2 text-grey mb-2" v-if="service.description">
                      {{ service.description }}
                    </p>
                    <div class="d-flex align-center gap-2">
                      <VChip size="small" color="primary" variant="tonal">
                        {{ service.duration || '1hr' }}
                      </VChip>
                      <VChip size="small" color="success" variant="tonal">
                        ₹{{ service.sale_price || service.price }}
                      </VChip>
                    </div>
                  </div>
                  <VAvatar
                    v-if="service.image"
                    size="60"
                    rounded="lg"
                  >
                    <VImg :src="service.image" />
                  </VAvatar>
                </div>
              </VCardText>
            </VCard>
          </VCol>
        </VRow>
      </VCardText>
    </VCard>
  </VDialog>
</template>

<style scoped>
/* .service-card {
  cursor: pointer;
  transition: all 0.2s ease;
}

.service-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
} */
</style>