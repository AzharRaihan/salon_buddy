<template>
  <VCol cols="12">
    <VCardText class="d-flex">
      <!-- 👉 Image Preview -->
      <VAvatar rounded size="100" class="me-6" :image="previewImage" />

      <!-- 👉 Upload Image -->
      <form class="d-flex flex-column justify-center gap-4">
        <div class="d-flex flex-wrap gap-4">
          <VBtn color="primary" size="small" @click="refInputEl?.click()">
            <VIcon icon="tabler-cloud-upload" class="d-sm-none" />
            <span class="d-none d-sm-block">{{ $t('Upload Profile Image') }}</span>
          </VBtn>

          <input 
            ref="refInputEl" 
            type="file" 
            name="photo" 
            accept=".jpeg,.png,.jpg,.webp" 
            hidden 
            @input="handleImageChange"
          >

          <VBtn 
            type="reset" 
            size="small" 
            color="secondary" 
            variant="tonal" 
            @click="handleReset"
          >
            <span class="d-none d-sm-block">{{ $t('Reset') }}</span>
            <VIcon icon="tabler-refresh" class="d-sm-none" />
          </VBtn>
          
          <VBtn 
            v-if="imageSrc" 
            type="reset" 
            size="small" 
            color="secondary" 
            variant="tonal" 
            @click="showCropperModal = true"
          >
            <span class="d-none d-sm-block">{{ $t('Crop Image') }}</span>
            <VIcon icon="tabler-crop" class="d-sm-none" />
          </VBtn>
        </div>

        <p class="text-body-1 mb-0">
          {{ helperText }}
        </p>
        <p class="text-body-1 mb-0">
          {{ recommendedText }}
        </p>
      </form>
    </VCardText>

    <!-- Image Cropper Modal -->
    <VDialog v-model="showCropperModal" persistent max-width="400px">
      <VCard class="modal-card modal-card-sm">
        <VCardTitle>{{ $t('Crop Image') }}</VCardTitle>
        <VCardText>
          <cropper
            class="cropper"
            :src="imageSrc"
            :stencil-props="{
              aspectRatio: 420/390,
            }"
            :min-size="420"
            :max-size="420"
            @change="onCrop"
          />
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn color="primary" variant="tonal" @click="handleCrop">
            <VIcon start icon="tabler-crop" />
            {{ $t('Crop & Save') }}
          </VBtn>
          <VBtn color="error" variant="tonal" @click="cancelCrop">
            <VIcon start icon="tabler-x" />
            {{ $t('Cancel') }}
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </VCol>
</template>

<script setup>
import { Cropper } from 'vue-advanced-cropper'
import 'vue-advanced-cropper/dist/style.css'
import { useUserImageUpload } from '@/composables/useUserImageUpload'

// Props
const props = defineProps({
  modelValue: {
    type: File,
    default: null
  },
  previewImageUrl: {
    type: String,
    default: null
  }
})

// Emits
const emit = defineEmits(['update:modelValue', 'image-changed'])

// Image upload composable
const {
  showCropperModal,
  imageSrc,
  croppedImage,
  cropPreview,
  refInputEl,
  previewImage,
  onCrop,
  getCroppedImage,
  cancelCrop,
  changeImage,
  resetImage,
  setPreviewImage,
  getHelperText,
  getRecommendedText
} = useUserImageUpload()

// Initialize preview image
if (props.previewImageUrl) {
  setPreviewImage(props.previewImageUrl)
}

// Computed
const helperText = computed(() => getHelperText())
const recommendedText = computed(() => getRecommendedText())

// Methods
const handleImageChange = (event) => {
  // Use the composable's changeImage function for validation
  const isValid = changeImage(event, { photo: null })
  
  // Only emit the file if validation passes
  if (isValid !== false) {
    const file = event.target.files[0]
    if (file) {
      emit('update:modelValue', file)
    }
  }
  emit('image-changed', event)
}

const handleCrop = () => {
  const croppedFile = getCroppedImage()
  if (croppedFile) {
    emit('update:modelValue', croppedFile)
    emit('image-changed', { target: { files: [croppedFile] } })
  }
}

const handleReset = () => {
  resetImage({ photo: null })
  emit('update:modelValue', null)
  emit('image-changed', { target: { files: [] } })
}
</script>

<style scoped>
.cropper {
  height: 300px;
  background: #f0f0f0;
}
</style>
