<script setup>
import LogoPart from '@/components/auth/LogoPart.vue';
import authV1BottomShape from '@images/svg/auth-v1-bottom-shape.svg?raw';
import authV1TopShape from '@images/svg/auth-v1-top-shape.svg?raw';
import { VNodeRenderer } from '@layouts/components/VNodeRenderer';
import { nextTick, ref } from 'vue';
import { toast } from 'vue3-toastify';
import { useI18n } from 'vue-i18n';
const { t } = useI18n()
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
  name: '',
  email: '',
  password: '',
  confirmPassword: '',
  privacyPolicies: false,
})

const isPasswordVisible = ref(false)
const isConfirmPasswordVisible = ref(false)

const passwordMatch = computed(() => {
  if (!form.value.confirmPassword) return true
  return form.value.password == form.value.confirmPassword
})

const isValidEmail = computed(() => {
  if (!form.value.email) return true
  const emailPattern = /^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}$/i
  return emailPattern.test(form.value.email)
})

const register = async () => {
  loadings.value = true
  if (!isValidEmail) {
    loadings.value = false
    return
  }

  try {
    const res = await $api('/auth/register', {
      method: 'POST',
      body: {
        name: form.value.name,
        email: form.value.email,
        password: form.value.password,
        confirmPassword: form.value.confirmPassword,
        privacyPolicies: form.value.privacyPolicies,
      },
      onResponseError({ response }) {
        toast(response._data.message, {
          "type": "error",
        });
        form.value.password = ''
        form.value.confirmPassword = ''
        loadings.value = false
        return Promise.reject(response._data)
      },
    })

    const { accessToken, userData, userAbilityRules } = res

    useCookie('userAbilityRules').value = userAbilityRules
    // useAbility().update(userAbilityRules);

    useCookie('userData').value = userData
    useCookie('accessToken').value = accessToken

    await nextTick(() => {
      toast("Register Success. Redirecting...", {
        "type": "success",
      });
      router.replace(route.query.to ? String(route.query.to) : '/')
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

      <!-- 👉 Auth card -->
      <VCard class="auth-card" max-width="460" :class="$vuetify.display.smAndUp ? 'pa-6' : 'pa-0'">
        <LogoPart />

        <VCardText>
          <h4 class="text-h4 mb-1">
            {{ t('Adventure starts here') }} 🚀
          </h4>
          <p class="mb-0">
            {{ t('Explore our site and start your journey') }}
          </p>
        </VCardText>

        <VCardText>
          <VForm @submit.prevent="register">
            <VRow>
              <!-- Username -->
              <VCol cols="12">
                <AppTextField v-model="form.name" autofocus :label="t('Full Name')" :placeholder="t('John Doe')" />
              </VCol>
              <!-- email -->
              <VCol cols="12">
                <AppTextField v-model="form.email" :label="t('Email')" type="email" :placeholder="t('Enter your email')"
                  :error-messages="!isValidEmail ? t('Please enter a valid email address') : ''" />
              </VCol>

              <!-- password -->
              <VCol cols="12">
                <AppTextField v-model="form.password" :label="t('Password')" :placeholder="t('Enter your password')"
                  :type="isPasswordVisible ? 'text' : 'password'" autocomplete="new-password"
                  :append-inner-icon="isPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                  @click:append-inner="isPasswordVisible = !isPasswordVisible" />

                <!-- Confirm Password -->
                <AppTextField v-model="form.confirmPassword" :label="t('Confirm Password')" :placeholder="t('Enter your confirm password')"
                  :type="isConfirmPasswordVisible ? 'text' : 'password'" autocomplete="new-password"
                  :append-inner-icon="isConfirmPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                  @click:append-inner="isConfirmPasswordVisible = !isConfirmPasswordVisible" class="mt-3"
                  :error-messages="!passwordMatch ? t('Passwords do not match') : ''" />

                <div class="d-flex align-center my-6">
                  <VCheckbox id="privacy-policy" v-model="form.privacyPolicies" inline />
                  <VLabel for="privacy-policy" style="opacity: 1;">
                    <span class="me-1 text-high-emphasis">{{ t('I agree to') }}</span>
                    <a href="javascript:void(0)" class="text-primary">{{ t('privacy policy & terms') }}</a>
                  </VLabel>
                </div>

                <VBtn block type="submit" :loading="loadings" :disabled="loadings">
                  {{ t('Sign up') }}
                </VBtn>
              </VCol>

              <!-- login instead -->
              <VCol cols="12" class="text-center text-base">
                <span>{{ t('Already have an account?') }}</span>
                <RouterLink class="text-primary ms-1" to="/login">
                  {{ t('Sign in instead') }}
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
