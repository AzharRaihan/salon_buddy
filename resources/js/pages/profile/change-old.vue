<script setup>
import { useRouter } from 'vue-router';
import { toast } from 'vue3-toastify';
import { useI18n } from 'vue-i18n';
import { useUserData } from '@/composables/useUserData';

const { t } = useI18n()
const { userData: userDataRef, updateUserData } = useUserData()
const userData = userDataRef.value || {}

const form = ref({
  name: userData?.name || '',
  email: userData?.email || '',
  phone: userData?.phone || '',
  photo: userData?.photo_url || ''
})
const router = useRouter()
const refInputEl = ref()
const loadings = ref(false)

const changeAvatar = file => {
  const fileReader = new FileReader()
  const { files } = file.target
  if (files && files.length) {
    fileReader.readAsDataURL(files[0])
    fileReader.onload = () => {
      if (typeof fileReader.result == 'string')
        form.value.photo = fileReader.result
    }
  }
}

// reset avatar image
const resetAvatar = () => {
  form.value.photo = userData?.photo_url || ''
}

const nameError = ref('')
const emailError = ref('')
const phoneError = ref('')

const validateName = (name) => {
  if (!name) {
    nameError.value = t('Name is required')
    return false
  }
  nameError.value = ''
  return true
}

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

const validatePhone = (phone) => {
  if (!phone) {
    phoneError.value = t('Phone is required')
    return false
  }
  phoneError.value = ''
  return true
}

const resetForm = () => {
  form.value = {
    name: userData?.name || '',
    email: userData?.email || '',
    phone: userData?.phone || ''
  }
}

const updateProfile = async () => {
  loadings.value = true
  if (!validateName(form.value.name) ||
    !validateEmail(form.value.email) ||
    !validatePhone(form.value.phone)) {
    loadings.value = false
    return
  }

  try {
    const formData = new FormData()
    formData.append('user_id', userData?.id || '')
    formData.append('name', form.value.name)
    formData.append('email', form.value.email)
    formData.append('phone', form.value.phone)

    // If photo is a base64 string, convert it to a file
    if (form.value.photo && form.value.photo.startsWith('data:image')) {
      const response = await fetch(form.value.photo)
      const blob = await response.blob()
      formData.append('photo', blob, 'profile.jpg')
    }

    const res = await $api('/update-profile', {
      method: 'POST',
      body: formData,
      onResponseError({ response }) {
        toast(response._data.message, {
          type: 'error',
        })
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
    
    console.log('Profile updated successfully. New user data:', user)
    updateUserData(user)
    console.log('updateUserData called')
    
    toast(message, {
      type: 'success',
    });
    loadings.value = false
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
      <VCard :title="$t('Change Profile')">
        <VCardText class="d-flex">
          <!-- 👉 Avatar -->
          <VAvatar rounded size="100" class="me-6" :image="form.photo" />

          <!-- 👉 Upload Photo -->
          <form class="d-flex flex-column justify-center gap-4">
            <div class="d-flex flex-wrap gap-4">
              <VBtn color="primary" size="small" @click="refInputEl?.click()">
                <VIcon icon="tabler-cloud-upload" class="d-sm-none" />
                <span class="d-none d-sm-block">{{ $t('Upload new photo') }}</span>
              </VBtn>

              <input ref="refInputEl" type="file" name="file" accept=".jpeg,.png,.jpg,GIF" hidden @input="changeAvatar">

              <VBtn type="reset" size="small" color="secondary" variant="tonal" @click="resetAvatar">
                <span class="d-none d-sm-block">{{ $t('Reset') }}</span>
                <VIcon icon="tabler-refresh" class="d-sm-none" />
              </VBtn>
            </div>

            <p class="text-body-1 mb-0">
              {{ $t('Allowed JPG, GIF or PNG. Max size of 2 MB') }}
            </p>
          </form>
        </VCardText>
        <VCardText class="pt-2">
          <VForm class="mt-3" @submit.prevent="updateProfile">
            <VRow>
              <!-- Name -->
              <VCol cols="12" md="4">
                <AppTextField v-model="form.name" :label="$t('Name')" :required="true" type="text" :placeholder="$t('Enter your name')"
                  :error-messages="nameError" @input="validateName($event.target.value)" />
              </VCol>

              <!-- Email -->
              <VCol cols="12" md="4">
                <AppTextField v-model="form.email" :label="$t('Email')" :required="true" type="email" :placeholder="$t('Enter your email')"
                  :error-messages="emailError" @input="validateEmail($event.target.value)" />
              </VCol>

              <!-- Phone -->
              <VCol cols="12" md="4">
                <AppTextField v-model="form.phone" :label="$t('Phone')" :required="true" type="text" :placeholder="$t('Enter your phone number')"
                  :error-messages="phoneError" @input="validatePhone($event.target.value)" />
              </VCol>

              <!-- Form Actions -->
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
