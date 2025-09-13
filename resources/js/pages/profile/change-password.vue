<script setup>
import { toast } from 'vue3-toastify';
import { useI18n } from 'vue-i18n';

const { t } = useI18n()
const userData = useCookie("userData").value;
const form = ref({
  old_password: '',
  new_password: '',
  confirm_password: ''
})

const oldPasswordError = ref('')
const newPasswordError = ref('')
const confirmPasswordError = ref('')
const loadings = ref(false)

const validateOldPassword = (password) => {
  if (!password) {
    oldPasswordError.value = t('Current password is required')
    return false
  }
  oldPasswordError.value = ''
  return true
}

const validateNewPassword = (password) => {
  if (!password) {
    newPasswordError.value = t('New password is required')
    return false
  }
  if (password.length < 6) {
    newPasswordError.value = t('Password must be at least 6 characters')
    return false
  }
  newPasswordError.value = ''
  return true
}

const validateConfirmPassword = (password) => {
  if (!password) {
    confirmPasswordError.value = t('Confirm password is required')
    return false
  }
  if (password !== form.value.new_password) {
    confirmPasswordError.value = t('Passwords do not match')
    return false
  }
  confirmPasswordError.value = ''
  return true
}

const resetForm = () => {
  form.value = {
    old_password: '',
    new_password: '',
    confirm_password: ''
  }
}

const updatePassword = async () => {
  loadings.value = true
  if (!validateOldPassword(form.value.old_password) ||
    !validateNewPassword(form.value.new_password) ||
    !validateConfirmPassword(form.value.confirm_password)) {
    loadings.value = false
    return
  }

  try {
    const res = await $api('/change-password', {
      method: 'POST',
      body: {
        user_id: userData.id,
        old_password: form.value.old_password,
        new_password: form.value.new_password,
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
      type: "success",
    })
    loadings.value = false
    resetForm()
  }
  catch (err) {
    console.error(err)
    loadings.value = false
  }
}
</script>

<template>
  <VRow>
    <VCol cols="12">
      <VCard :title="$t('Change Password')">
        <VCardText>
          <VForm class="mt-3" @submit.prevent="updatePassword">
            <VRow>
              <!-- Old Password -->
              <VCol cols="12" md="4">
                <AppTextField v-model="form.old_password" :label="$t('Current Password')" :required="true" type="password"
                  :placeholder="$t('Enter your current password')" :error-messages="oldPasswordError"
                  @input="validateOldPassword($event.target.value)" />
              </VCol>

              <!-- New Password -->
              <VCol cols="12" md="4">
                <AppTextField v-model="form.new_password" :label="$t('New Password')" :required="true" type="password"
                  :placeholder="$t('Enter new password')" :error-messages="newPasswordError"
                  @input="validateNewPassword($event.target.value)" />
              </VCol>

              <!-- Confirm Password -->
              <VCol cols="12" md="4">
                <AppTextField v-model="form.confirm_password" :label="$t('Confirm Password')" :required="true" type="password"
                  :placeholder="$t('Confirm new password')" :error-messages="confirmPasswordError"
                  @input="validateConfirmPassword($event.target.value)" />
              </VCol>

              <!-- Form Actions -->
              <VCol cols="12" class="d-flex flex-wrap gap-4">
                <VBtn type="submit" :loading="loadings" :disabled="loadings">
                  <VIcon start icon="tabler-checkbox" />
                  {{ $t('Change Password') }}
                </VBtn>
                <VBtn color="error" variant="tonal" type="reset" @click.prevent="resetForm">
                  <VIcon start icon="tabler-refresh" />
                  {{ $t('Reset') }}
                </VBtn>
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>
