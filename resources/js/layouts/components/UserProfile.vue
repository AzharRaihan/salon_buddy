<script setup>
import { nextTick } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { PerfectScrollbar } from 'vue3-perfect-scrollbar';
import { toast } from 'vue3-toastify';

const router = useRouter()
const route = useRoute()

const userData = useCookie('userData')

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
      router.replace('/login')
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
  <VBadge v-if="userData" dot bordered location="bottom right" offset-x="1" offset-y="2" color="success">
    <VAvatar size="38" class="cursor-pointer" :color="!userData.photo_url ? 'primary' : undefined"
      :variant="!userData.photo_url ? 'tonal' : undefined">
      <VImg v-if="userData?.photo_url" :src="userData?.photo_url" />
      <VIcon v-else icon="tabler-user" />

      <!-- SECTION Menu -->
      <VMenu activator="parent" width="240" location="bottom end" offset="12px">
        <VList>
          <VListItem>
            <div class="d-flex gap-2 align-center">
              <VListItemAction>
                <VBadge dot location="bottom right" offset-x="3" offset-y="3" color="success" bordered>
                  <VAvatar :color="!userData.photo_url ? 'primary' : undefined"
                    :variant="!userData.photo_url ? 'tonal' : undefined">
                    <VImg v-if="userData?.photo_url" :src="userData?.photo_url" />
                    <VIcon v-else icon="tabler-user" />
                  </VAvatar>
                </VBadge>
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
  </VBadge>
</template>
