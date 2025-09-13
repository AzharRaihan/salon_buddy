<script setup>
import { useI18n } from 'vue-i18n';

const { t } = useI18n()

const props = defineProps({
    userData: {
        type: Object,
        required: true,
    },
})

const resolveUserRoleVariant = role => {
    if (role === 'subscriber')
        return {
            color: 'warning',
            icon: 'tabler-user',
        }
    if (role === 'author')
        return {
            color: 'success',
            icon: 'tabler-circle-check',
        }
    if (role === 'maintainer')
        return {
            color: 'primary',
            icon: 'tabler-chart-pie-2',
        }
    if (role === 'editor')
        return {
            color: 'info',
            icon: 'tabler-pencil',
        }
    if (role === 'admin')
        return {
            color: 'secondary',
            icon: 'tabler-server-2',
        }

    return {
        color: 'primary',
        icon: 'tabler-user',
    }
}
</script>

<template>
    <VRow>
        <!-- SECTION User Details -->
        <VCol cols="12">
            <VCard v-if="props.userData">
                <VCardText class="text-center pt-12">
                    <!-- 👉 Avatar -->
                    <VAvatar rounded :size="100" :color="!props.userData.photo_url ? 'primary' : undefined"
                        :variant="!props.userData.photo_url ? 'tonal' : undefined">
                        <VImg v-if="props.userData.photo_url" :src="props.userData.photo_url" />
                        <span v-else class="text-5xl font-weight-medium">
                            {{ avatarText(props.userData.name) }}
                        </span>
                    </VAvatar>

                    <!-- 👉 User fullName -->
                    <h5 class="text-h5 mt-4">
                        {{ props.userData.name }}
                    </h5>
                    <h6 class="text-h6 mt-2">
                        {{ props.userData.email }}
                    </h6>

                    <!-- 👉 Role chip -->
                    <VChip label :color="resolveUserRoleVariant(props.userData.role).color" size="small"
                        class="text-capitalize mt-4">
                        {{ props.userData.role_name }}
                    </VChip>
                </VCardText>

                <VCardText>

                    <!-- 👉 Details -->
                    <h5 class="text-h5">
                        {{ t('Profile Details') }}
                    </h5>

                    <VDivider class="my-4" />

                    <!-- 👉 User Details list -->
                    <VList class="card-list mt-2">
                        <VListItem>
                            <VListItemTitle>
                                <h6 class="text-h6">
                                    {{ t('Name') }}:
                                    <div class="d-inline-block text-body-1">
                                        {{ props.userData.name }}
                                    </div>
                                </h6>
                            </VListItemTitle>
                        </VListItem>

                        <VListItem>
                            <VListItemTitle>
                                <span class="text-h6">
                                    {{ t('Email') }}:
                                </span>
                                <span class="text-body-1">
                                    {{ props.userData.email }}
                                </span>
                            </VListItemTitle>
                        </VListItem>
                        <VListItem>
                            <VListItemTitle>
                                <span class="text-h6">
                                    {{ t('Phone') }}:
                                </span>
                                <span class="text-body-1">
                                    {{ props.userData.phone }}
                                </span>
                            </VListItemTitle>
                        </VListItem>

                        <VListItem>
                            <VListItemTitle>
                                <h6 class="text-h6">
                                    {{ t('Status') }}:
                                    <div class="d-inline-block text-body-1 text-capitalize">
                                        {{ props.userData.status }}
                                    </div>
                                </h6>
                            </VListItemTitle>
                        </VListItem>

                        <VListItem>
                            <VListItemTitle>
                                <h6 class="text-h6">
                                    {{ t('Role') }}:
                                    <div class="d-inline-block text-capitalize text-body-1">
                                        {{ props.userData.role_name }}
                                    </div>
                                </h6>
                            </VListItemTitle>
                        </VListItem>
                    </VList>
                </VCardText>

                <!-- 👉 Edit and Suspend button -->
                <VCardText class="d-flex justify-center gap-x-4">
                    <VBtn variant="elevated" :to="`/profile/change`">
                        <VIcon icon="tabler-edit" />
                        {{ t('Edit') }}
                    </VBtn>

                    <VBtn variant="tonal" color="error" :to="`/profile/change-password`">
                        <VIcon icon="tabler-lock" />
                        {{ t('Change Password') }}
                    </VBtn>
                </VCardText>
            </VCard>
        </VCol>
        <!-- !SECTION -->
    </VRow>
</template>

<style lang="scss" scoped>
.card-list {
    --v-card-list-gap: 0.5rem;
}

.text-capitalize {
    text-transform: capitalize !important;
}
</style>
