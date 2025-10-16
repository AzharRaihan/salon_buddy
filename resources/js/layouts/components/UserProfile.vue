<script setup>
import { nextTick, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { PerfectScrollbar } from 'vue3-perfect-scrollbar';
import { toast } from 'vue3-toastify';
import { useUserData } from '@/composables/useUserData';

const router = useRouter()
const route = useRoute()

const { userData } = useUserData()

// Debug: Watch for changes
watch(() => userData.value, (newVal, oldVal) => {
  console.log('UserProfile - userData changed!')
  console.log('Old photo_url:', oldVal?.photo_url)
  console.log('New photo_url:', newVal?.photo_url)
}, { deep: true })

const logout = async () => {
  try {
    await $api('/auth/logout', { method: 'GET' })

    // Remove cookies
    useCookie('userAbilityRules').value = null
    useCookie('userData').value = null
    useCookie('accessToken').value = null
    useCookie('branch_info').value = null

    // Redirect to login page
    await nextTick(() => {
      toast("Logout Successfully!", {
        "type": "success",
      });
      router.replace('/admin-login')
    })
  }
  catch (err) {
    console.error(err)
  }
}

const userProfileList = [
  { type: 'divider' },
  {
    type: 'navItem',
    icon: 'tabler-user',
    title: 'Change Profile',
    to: '/profile/change',
  },
  {
    type: 'navItem',
    icon: 'tabler-key',
    title: 'Change Password',
    to: '/profile/change-password',
  },
  {
    type: 'navItem',
    icon: 'tabler-help',
    title: 'Security Question',
    to: '/profile/security-question',
  },
  { type: 'divider' },
]
</script>

<template>
  <VAvatar v-if="userData" size="38" class="cursor-pointer" :color="!userData.photo_url ? 'primary' : undefined"
    :variant="!userData.photo_url ? 'tonal' : undefined">
    <VImg v-if="userData?.photo_url" :key="userData?.photo_url" :src="userData?.photo_url" />
    <VIcon v-else icon="tabler-user" />

    <!-- SECTION Menu -->
    <VMenu activator="parent" width="240" location="bottom end" offset="12px">
      <VList>
        <VListItem>
          <div class="d-flex gap-2 align-center">
            <VListItemAction>
              <VAvatar :color="!userData.photo_url ? 'primary' : undefined"
                :variant="!userData.photo_url ? 'tonal' : undefined">
                <VImg v-if="userData?.photo_url" :key="userData?.photo_url" :src="userData?.photo_url" />
                <VIcon v-else icon="tabler-user" />
              </VAvatar>
            </VListItemAction>

            <div>
              <h6 class="text-h6 font-weight-medium">
                {{ userData?.name }}
              </h6>
              <VListItemSubtitle class="text-capitalize text-disabled">
                {{ userData?.role_name }}
              </VListItemSubtitle>
            </div>
          </div>
        </VListItem>

        <PerfectScrollbar :options="{ wheelPropagation: false }">
          <template v-for="item in userProfileList" :key="item.title">
            <VListItem v-if="item.type === 'navItem'" :to="item.to">
              <template #prepend>
                <VIcon :icon="item.icon" size="22" />
              </template>

              <VListItemTitle>{{ item.title }}</VListItemTitle>
            </VListItem>

            <VDivider v-else class="my-2" />
          </template>

          <div class="px-4 py-2">
            <VBtn block size="small" color="error" append-icon="tabler-logout" @click="logout">
              Logout
            </VBtn>
          </div>
        </PerfectScrollbar>
      </VList>
    </VMenu>
    <!-- !SECTION -->
  </VAvatar>
</template>
