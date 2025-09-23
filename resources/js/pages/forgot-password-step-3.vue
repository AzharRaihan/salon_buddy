<script setup>
import LogoPart from '@/components/auth/LogoPart.vue';
import { useForgotPasswordStore } from '@/stores/forgotPassword'
import authV1BottomShape from '@images/svg/auth-v1-bottom-shape.svg?raw'
import authV1TopShape from '@images/svg/auth-v1-top-shape.svg?raw'
import { VNodeRenderer } from '@layouts/components/VNodeRenderer'
import { themeConfig } from '@themeConfig'
import { useRouter } from 'vue-router'
import { toast } from 'vue3-toastify'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const router = useRouter()
const forgotPasswordStore = useForgotPasswordStore()
const loadings = ref(false)

// Check if email exists, if not redirect to step-1
if (!forgotPasswordStore.hasEmail()) {
    toast('Please enter your email first', {
        type: 'error',
    })
    router.push('/step-1')
}

definePage({
    meta: {
        layout: 'blank',
        public: true,
    },
})

const form = ref({
    newPassword: '',
    confirmPassword: '',
})

const isPasswordVisible = ref(false)
const isConfirmPasswordVisible = ref(false)

const passwordError = ref('')
const confirmPasswordError = ref('')

const validatePassword = (password) => {
    if (!password) {
        passwordError.value = t('Password is required')
        return false
    }

    if (password.length < 6) {
        passwordError.value = t('Password must be at least 6 characters long')
        return false
    }

    passwordError.value = ''
    return true
}

const validateConfirmPassword = (confirmPassword) => {
    if (!confirmPassword) {
        confirmPasswordError.value = t('Confirm password is required')
        return false
    } else if (confirmPassword !== form.value.newPassword) {
        confirmPasswordError.value = t('Passwords do not match')
        return false
    }
    confirmPasswordError.value = ''
    return true
}

const forgotPassword = async () => {
    loadings.value = true
    try {
        const res = await $api('/forgot-password/step-3', {
            method: 'POST',
            body: {
                email: forgotPasswordStore.email,
                password: form.value.newPassword,
                password_confirmation: form.value.confirmPassword,
            },
            onResponseError({ response }) {
                toast(response._data.message, {
                    type: 'error',
                })
                loadings.value = false
                return Promise.reject(response._data)
            },
        })

        const { status, message } = res

        if (status == 'error') {
            toast(message, {
                type: 'error',
            })
            loadings.value = false
            return
        }

        toast(message, {
            "type": "success",
        });

        // Store email in Pinia before navigation
        forgotPasswordStore.clearEmail()
        setTimeout(() => {
            router.push('/admin-login')
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

            <!-- 👉 Auth Card -->
            <VCard class="auth-card" max-width="460" :class="$vuetify.display.smAndUp ? 'pa-6' : 'pa-2'">
                <LogoPart />

                <VCardText>
                    <h4 class="text-h4 mb-1">
                        {{ $t('Reset Password') }} 🔒
                    </h4>
                </VCardText>

                <VCardText>
                    <VForm @submit.prevent="forgotPassword">
                        <VRow>
                            <!-- password -->
                            <VCol cols="12">
                                <AppTextField v-model="form.newPassword" autofocus :label="$t('New Password')" :required="true"
                                    placeholder="······" :type="isPasswordVisible ? 'text' : 'password'"
                                    autocomplete="password"
                                    :append-inner-icon="isPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                                    @click:append-inner="isPasswordVisible = !isPasswordVisible"
                                    :error-messages="passwordError" @input="validatePassword($event.target.value)" @blur="validatePassword(form.newPassword)" />
                            </VCol>

                            <!-- Confirm Password -->
                            <VCol cols="12">
                                <AppTextField v-model="form.confirmPassword" :label="$t('Confirm Password')" :required="true"
                                    autocomplete="confirm-password" placeholder="······"
                                    :type="isConfirmPasswordVisible ? 'text' : 'password'"
                                    :append-inner-icon="isConfirmPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                                    @click:append-inner="isConfirmPasswordVisible = !isConfirmPasswordVisible"
                                    :error-messages="confirmPasswordError"
                                    @input="validateConfirmPassword($event.target.value)" @blur="validateConfirmPassword(form.confirmPassword)" />
                            </VCol>

                            <!-- reset password -->
                            <VCol cols="12">
                                <VBtn block type="submit" :loading="loadings" :disabled="loadings">
                                    {{ $t('Set New Password') }}
                                </VBtn>
                            </VCol>

                            <!-- back to login -->
                            <VCol cols="12">
                                <RouterLink class="d-flex align-center justify-center" to="/admin-login">
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
