<script setup>
import { onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { useWebsitePolicyForm } from '@/composables/useWebsitePolicyForm'
import PolicyEditor from '@/components/PolicyEditor.vue'

const { t } = useI18n()

// Use the policy form composable
const {
  loading,
  editorContent,
  form,
  errors,
  isValid,
  fetchPolicy,
  savePolicy,
  resetForm,
  handleEditorInput
} = useWebsitePolicyForm('terms_and_conditions')


// Fetch data on component mount
onMounted(() => {
  fetchPolicy()
})

// Handle form submission
const handleSubmit = async () => {
  await savePolicy()
}

// Handle form reset
const handleReset = () => {
  resetForm()
}
</script>

<template>
  <VRow>
    <VCol cols="12">
      <PolicyEditor
        :title="t('Terms & Conditions')"
        :field-label="t('Terms & Conditions Content')"
        field-name="terms_and_conditions"
        v-model:editorContent="editorContent"
        :loading="loading"
        :errors="errors"
        :is-valid="isValid"
        editor-height="400"
        @submit="handleSubmit"
        @reset="handleReset"
        @input="handleEditorInput"
      />
    </VCol>
  </VRow>
</template>

