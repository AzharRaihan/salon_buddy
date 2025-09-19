<script setup>
import LogoPart from '@/components/auth/LogoPart.vue';
import { useForgotPasswordStore } from '@/stores/forgotPassword';
import authV1BottomShape from '@images/svg/auth-v1-bottom-shape.svg?raw';
import authV1TopShape from '@images/svg/auth-v1-top-shape.svg?raw';
import { VNodeRenderer } from '@layouts/components/VNodeRenderer';
import { ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { toast } from 'vue3-toastify';
import { useI18n } from 'vue-i18n';

const { t } = useI18n()

const router = useRouter()
const route = useRoute()
const forgotPasswordStore = useForgotPasswordStore()
const loadings = ref(false)

definePage({
  meta: {
    layout: 'blank',
    public: true,
  },
})

const form = ref({ 
  email: '' 
})
const emailError = ref('')

const validateEmail = (email) => {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  if (!email) {
    emailError.value = t('Email is required')
    return false
  }
  if (!emailRegex.test(email)) {
    emailError.value = t('Please enter a valid email address')
    return false
  }
  emailError.value = ''
  return true
}

const forgotPassword = async () => {
  loadings.value = true
  if (!validateEmail(form.value.email)) {
    loadings.value = false
    return
  }

  try {
    const res = await $api('/forgot-password/step-1', {
      method: 'POST',
      body: {
        email: form.value.email,
      },
      onResponseError({ response }) {
        emailError.value = response._data.message
        loadings.value = false
        return Promise.reject(response._data)
      },
    })

    const { status, message } = res

    if (status == 'error') {
      emailError.value = message
      form.value.email = ''
      loadings.value = false
      return
    }

    toast(message, {
      "type": "success",
    });

    // Store email in Pinia before navigation
    forgotPasswordStore.setEmail(form.value.email)
    setTimeout(() => {
      router.push('/forgot-password/step-2')
    }, 1000)
  }
  catch (err) {
    console.error(err)
    loadings.value = false
  }
}
</script>

<template>
  <div class="auth-wrapper d-flex align-center justify-center pa-4">
    <div class="position-relative my-sm-16">
      <!-- 👉 Top shape -->
      <VNodeRenderer :nodes="h('div', { innerHTML: authV1TopShape })"
        class="text-primary auth-v1-top-shape d-none d-sm-block" />

      <!-- 👉 Bottom shape -->
      <VNodeRenderer :nodes="h('div', { innerHTML: authV1BottomShape })"
        class="text-primary auth-v1-bottom-shape d-none d-sm-block" />

      <!-- 👉 Auth card -->
      <VCard class="auth-card" max-width="460" :class="$vuetify.display.smAndUp ? 'pa-6' : 'pa-0'">
        <LogoPart />

        <VCardText>
          <h4 class="text-h4 mb-1">
            {{ $t('Forgot Password?') }} 🔒
          </h4>
          <p class="mb-0">
            {{ $t('Enter your email and go to next step.') }}
          </p>
        </VCardText>

        <VCardText>
          <VForm @submit.prevent="forgotPassword">
            <VRow>
              <!-- email -->
              <VCol cols="12">
                <AppTextField v-model="form.email" autofocus :label="$t('Email')" :required="true" type="email" placeholder="johndoe@email.com"
                  :error-messages="emailError" @input="validateEmail($event.target.value)" @blur="validateEmail(form.email)" />
              </VCol>

              <!-- reset password -->
              <VCol cols="12">
                <VBtn block type="submit" :loading="loadings" :disabled="loadings">
                  {{ $t('Go to next step') }}
                </VBtn>
              </VCol>

              <!-- back to login -->
              <VCol cols="12">
                <RouterLink class="d-flex align-center justify-center" to="/login">
                  <VIcon icon="tabler-chevron-left" size="20" class="me-1 flip-in-rtl" />
                  <span>{{ $t('Back to login') }}</span>
                </RouterLink>
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </div>
  </div>
</template>

<style lang="scss">
@use "@core-scss/template/pages/page-auth";
</style>
