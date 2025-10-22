<script setup>
import LogoPart from '@/components/auth/LogoPart.vue';
import { useSiteSettingsStore } from '@/stores/siteSettings';
import authV1BottomShape from '@images/svg/auth-v1-bottom-shape.svg?raw';
import authV1TopShape from '@images/svg/auth-v1-top-shape.svg?raw';
import { VNodeRenderer } from '@layouts/components/VNodeRenderer';
import { nextTick, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { toast } from 'vue3-toastify';
import { useI18n } from 'vue-i18n';

const { t } = useI18n()
const siteSettingsStore = useSiteSettingsStore()
const router = useRouter()
const route = useRoute()
const loadings = ref(false)


definePage({
  meta: {
    layout: 'blank',
    public: true,
  },
})

const form = ref({
  email: '',
  password: '',
  remember: false,
})

onMounted(() => {
  if (!sessionStorage.getItem('pageReloaded')) {
    sessionStorage.setItem('pageReloaded', 'true')
    window.location.reload()
  } else {
    sessionStorage.removeItem('pageReloaded') // reset for next visit
  }
})

const isPasswordVisible = ref(false)
const emailError = ref('')
const passwordError = ref('')

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

const validatePassword = (password) => {
  if (!password) {
    passwordError.value = t('Password is required')
    return false
  }
  passwordError.value = ''
  return true
}

const login = async () => {
  loadings.value = true
  if (!validateEmail(form.value.email)) {
    loadings.value = false
    return
  }
  if (!validatePassword(form.value.password)) {
    loadings.value = false
    return
  }

  try {
    const res = await $api('/auth/login', {
      method: 'POST',
      body: {
        email: form.value.email,
        password: form.value.password,
        remember: form.value.remember,
      },
      onResponseError({ response }) {
        toast(response._data.message, {
          "type": "error",
        });
        form.value.password = ''
        loadings.value = false
        return Promise.reject(response._data)
      },
    })

    const { accessToken, token_type, userData, userAbilityRules, company_settings } = res

    useCookie('userAbilityRules').value = userAbilityRules
    useCookie('userData').value = userData
    useCookie('accessToken').value = accessToken
    useCookie('company_settings').value = company_settings

    await nextTick(() => {
      toast("Login Success. Redirecting...", {
        "type": "success",
      });
      setTimeout(() => {
        // If first_login is 0, redirect to security question page 
        if(userData.is_first_login == 0){
          router.replace('/profile/security-question')
        } else {
          if(userData.id == 1){
            router.replace(route.query.to ? String(route.query.to) : '/dashboard')
          } else {
            router.replace(route.query.to ? String(route.query.to) : '/home')
          }
        }
      }, 1000);
    })
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

      <!-- 👉 Auth Card -->
      <VCard class="auth-card" width="460" :class="$vuetify.display.smAndUp ? 'pa-6' : 'pa-0'">
        <LogoPart />
        <VCardText>
          <VForm @submit.prevent="login">
            <VRow>
              <!-- email -->
              <VCol cols="12">
                <AppTextField v-model="form.email" autofocus :label="$t('Email')" :required="true" type="email" placeholder="johndoe@email.com"
                  :error-messages="emailError" @input="validateEmail($event.target.value)" />
              </VCol>

              <!-- password -->
              <VCol cols="12">
                <AppTextField v-model="form.password" :label="$t('Password')" :required="true" placeholder="········"
                  :type="isPasswordVisible ? 'text' : 'password'" autocomplete="password"
                  :append-inner-icon="isPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                  :error-messages="passwordError"
                  @input="validatePassword($event.target.value)"
                  @click:append-inner="isPasswordVisible = !isPasswordVisible" />

                <!-- remember me checkbox -->
                <div class="text-end my-6">
                  <RouterLink class="text-primary" to="/forgot-password-step-1">
                    {{ $t('Forgot Password?') }}
                  </RouterLink>
                </div>
                <!-- login button -->
                <VBtn block type="submit" :loading="loadings" :disabled="loadings">
                  {{ $t('Login') }}
                </VBtn>
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
