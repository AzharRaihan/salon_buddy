<script setup>
import LogoPart from '@/components/auth/LogoPart.vue';
import { useForgotPasswordStore } from '@/stores/forgotPassword'
import securityQuestions from '@core/sampleQustions.json'
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

const form = ref({ question: null, answer: '' })

const questionError = ref('')
const answerError = ref('')

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

const forgotPassword = async () => {
    loadings.value = true
    if (!validateQuestion(form.value.question) || !validateAnswer(form.value.answer)) {
        loadings.value = false
        return
    }

    try {
        const res = await $api('/forgot-password-step-2', {
            method: 'POST',
            body: {
                email: forgotPasswordStore.email,
                question: form.value.question,
                answer: form.value.answer,
            },
            onResponseError({ response }) {
                loadings.value = false
                return Promise.reject(response._data)
            },
        })

        const { status, error, message } = res

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

        setTimeout(() => {
            router.push('/forgot-password-step-3')
        }, 1000)
        
    }
    catch (err) {
        if (err.error == 'question') {
            questionError.value = err.message
            if (form.value.answer == '') {
                answerError.value = t('Answer is required')
            } else {
                answerError.value = ''
            }
        } else if (err.error == 'answer') {
            answerError.value = err.message
            questionError.value = ''
        } else {
            toast(err.message, {
                type: 'error',
            })
        }
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
                        {{ $t('Verify Security') }}
                    </h4>
                    <p class="mb-0">
                        {{ $t('Select the security question and enter the answer.') }}
                    </p>
                </VCardText>

                <VCardText>
                    <VForm @submit.prevent="forgotPassword">
                        <VRow>
                            <!-- answer -->
                            <VCol cols="12">
                                <AppAutocomplete v-model="form.question" autofocus :label="$t('Security Question')" :required="true"
                                    :items="items" :error-messages="questionError"
                                    @input="validateQuestion($event.target.value)"
                                    @blur="validateQuestion(form.question)"
                                    :placeholder="$t('Select Security Question')" 
                                    clearable
                                    />
                            </VCol>

                            <VCol cols="12">
                                <AppTextField v-model="form.answer" :label="$t('Answer')" :required="true" type="text"
                                    :placeholder="$t('Enter your answer')" :error-messages="answerError"
                                    @input="validateAnswer($event.target.value)" @blur="validateAnswer(form.answer)" />
                            </VCol>

                            <!-- reset password -->
                            <VCol cols="12">
                                <VBtn block type="submit" :loading="loadings" :disabled="loadings">
                                    {{ $t('Go to next step') }}
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
