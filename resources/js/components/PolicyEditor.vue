<template>
  <VCard class="mb-4">
    <VCardText>
      <VForm class="mt-3" @submit.prevent="handleSubmit">
        <VCardTitle class="text-h3 d-flex align-center mb-5 ps-0">
          {{ title }}
        </VCardTitle>
        
        <VRow>
          <VCol cols="12">
            <VLabel class="text-body-1 mb-2">
              {{ fieldLabel }}
              <span class="text-error">*</span>
            </VLabel>
            <div :title="fieldLabel">
              <DemoEditorCustomEditor 
                :content="localEditorContent"
                :height="editorHeight" 
                v-model="localEditorContent" 
                @input="handleEditorInput"
                :disabled="loading"
              />
            </div>
            <VAlert
              v-if="errors[fieldName]"
              type="error"
              variant="tonal"
              class="mt-2"
            >
              {{ errors[fieldName] }}
            </VAlert>
          </VCol>

          <!-- Form Actions -->
          <VCol cols="12" class="d-flex flex-wrap gap-4">
            <VBtn 
              type="submit" 
              color="primary" 
              :loading="loading" 
              :disabled="loading || !isValid"
            >
              <VIcon start icon="tabler-checkbox" />
              {{ t('Save Changes') }}
            </VBtn>
            <VBtn 
              color="error" 
              variant="tonal" 
              @click="handleReset"
              :disabled="loading"
            >
              <VIcon start icon="tabler-circle-minus" />
              {{ t('Reset') }}
            </VBtn>
          </VCol>
        </VRow>
      </VForm>
    </VCardText>
  </VCard>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import DemoEditorCustomEditor from '@core/components/editor/DemoEditorCustomEditor.vue'

// Props
const props = defineProps({
  title: {
    type: String,
    required: true
  },
  fieldLabel: {
    type: String,
    required: true
  },
  fieldName: {
    type: String,
    required: true
  },
  editorContent: {
    type: String,
    default: ''
  },
  loading: {
    type: Boolean,
    default: false
  },
  errors: {
    type: Object,
    default: () => ({})
  },
  isValid: {
    type: Boolean,
    default: false
  },
  editorHeight: {
    type: String,
    default: '300'
  }
})

// Emits
const emit = defineEmits([
  'update:editorContent',
  'submit',
  'reset',
  'input'
])

// Composables
const { t } = useI18n()

// Local reactive editor content
const localEditorContent = ref(props.editorContent)

// Watch for prop changes and update local content
watch(() => props.editorContent, (newValue) => {
  localEditorContent.value = newValue
}, { immediate: true })

// Methods
const handleEditorInput = (content) => {

  // Extract the actual HTML content if it's an event object
  const htmlContent = content.target?.innerHTML || content
  
  localEditorContent.value = htmlContent
  emit('update:editorContent', htmlContent)
  emit('input', htmlContent)
}

const handleSubmit = () => {
  emit('submit')
}

const handleReset = () => {
  emit('reset')
}
</script>

<style scoped>
.v-label {
  font-weight: 500;
}
</style>
