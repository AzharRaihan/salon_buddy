<script setup>
import securityQuestions from '@core/sampleQustions.json';
import { useRouter } from 'vue-router';
import { toast } from 'vue3-toastify';
import { useI18n } from 'vue-i18n';

const { t } = useI18n()

const userData = useCookie("userData").value;
const userAbilityRules = useCookie("userAbilityRules").value;


const form = ref({ question: userData.question, answer: userData.answer })
const router = useRouter()
const questionError = ref('')
const answerError = ref('')
const loadings = ref(false)

const items = Object.values(securityQuestions)

const validateQuestion = (question) => {
  if (!question) {
    questionError.value = t('Question is required')
    return false
  }
  return true
}

const validateAnswer = (answer) => {
  if (!answer) {
    answerError.value = t('Answer is required')
    return false
  }
  return true
}

const resetForm = () => {
  form.value = { question: '', answer: '' }
}

const setSecurityQuestion = async () => {
  loadings.value = true
  if (!validateQuestion(form.value.question) || !validateAnswer(form.value.answer)) {
    loadings.value = false
    return
  }

  try {
    const res = await $api('/set-security-question', {
      method: 'POST',
      body: {
        user_id: userData.id,
        question: form.value.question,
        answer: form.value.answer,
      },
      onResponseError({ response }) {
        if (response._data.error == 'question') {
          questionError.value = response._data.message
          form.value.question = ''
        } else if (response._data.error == 'answer') {
          answerError.value = response._data.message
          form.value.answer = ''
        } else {
          toast(response._data.message, {
            type: 'error',
          })
        }
        form.value.question = ''
        form.value.answer = ''
        loadings.value = false
        return Promise.reject(response._data)
      },
    })

    const { status, message, user } = res

    if (status == 'error') {
      toast(message, {
        type: 'error',
      })
      loadings.value = false
      return
    }
    useCookie('userData').value = user
    if(userData.id == 1){
      toast(message, {
        type: 'success',
      });
      loadings.value = false
      setTimeout(() => {  
        router.push('/dashboard')
      }, 2000);
    } else {

      toast(message, {
        type: 'success',
      });
      loadings.value = false

      setTimeout(() => {
        if (userAbilityRules.includes('dashboard')) {
          router.push('/dashboard')
        }else{
          router.push('/home')
        }
      }, 2000);
    }
  }
  catch (err) {
    if (err.errors) {
      // Show each validation error as a toast
      for (const [field, messages] of Object.entries(err.errors)) {
          messages.forEach(msg => {
              toast(msg, { type: 'error' })
          })
      }
    } else {
        // Show general error if no field-specific errors
        toast(message, {
            type: 'error',
        })
    }
    loadings.value = false
    return
  }
}

</script>

<template>
  <VRow>
    <VCol cols="12">
      <VCard :title="$t('Security Question')">
        <VCardText>
          <!-- 👉 Form -->
          <VForm class="mt-3" @submit.prevent="setSecurityQuestion">
            <VRow>
              <!-- 👉 Country -->
              <VCol cols="12" md="6">
                <AppAutocomplete v-model="form.question" autofocus :label="$t('Security Question')" :required="true" :items="items"
                  :error-messages="questionError" @input="validateQuestion($event.target.value)"
                  :placeholder="$t('Select Security Question')" />
              </VCol>
              <!-- 👉 First Name -->
              <VCol md="6" cols="12">
                <AppTextField v-model="form.answer" :label="$t('Answer')" :required="true" type="text" :placeholder="$t('Enter your answer')"
                  :error-messages="answerError" @input="validateAnswer($event.target.value)" />
              </VCol>
              <!-- 👉 Form Actions -->
              <VCol cols="12" class="d-flex flex-wrap gap-4">
                <VBtn type="submit" :loading="loadings" :disabled="loadings">
                  <VIcon start icon="tabler-checkbox" />
                  {{ $t('Save changes') }}
                </VBtn>
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>
