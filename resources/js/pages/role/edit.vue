<script setup>
import { useRoute, useRouter } from 'vue-router';
import { toast } from 'vue3-toastify';
import { useI18n } from 'vue-i18n';

const { t } = useI18n()
const route = useRoute();
const router = useRouter();
const loadings = ref(false)

const form = ref({
    name: '',
    description: '',
    permissions: []
})

const roleNameError = ref('')

const validateRoleName = (name) => {
    if (!name) {
        roleNameError.value = t('Role name is required')
        return false
    }
    roleNameError.value = ''
    return true
}

const permissions = ref({})
const permissionsList = ref([])

// Fetch role data and permissions when component mounts
onMounted(async () => {
    try {
        // Fetch role data
        const roleResponse = await $api(`/roles/${route.query.id}`)
        const roleData = roleResponse.data
        form.value.name = roleData.name
        form.value.description = roleData.description
        form.value.permissions = roleData.permissions.map(p => p.id)

        // Fetch permissions
        const permissionsResponse = await $api('/permissions')
        permissions.value = permissionsResponse.data

        // Transform permissions into the format needed for the form
        permissionsList.value = Object.entries(permissions.value).map(([groupName, perms]) => ({
            name: groupName.split('-').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' '),
            permissions: perms,
            isGroupSelected: perms.every(p => form.value.permissions.includes(p.id))
        }))

        // Set initial state of select all checkbox based on if all permissions are selected
        const totalPerms = Object.values(permissions.value).flat().length
        isSelectAll.value = form.value.permissions.length == totalPerms
    } catch (error) {
        console.error('Error fetching data:', error)
        toast(t('Failed to load role data'), {
            type: 'error'
        })
    }
})

const formatPermissionName = (permissionName) => {
    return permissionName.split('-').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
}

const isSelectAll = ref(false)
const rolePermissions = ref(null)

const checkedCount = computed(() => {
    return form.value.permissions.length
})

const totalPermissionsCount = computed(() => {
    let total = 0
    permissionsList.value.forEach(group => {
        total += group.permissions.length
    })
    return total
})

const isIndeterminate = computed(() => {
    const total = totalPermissionsCount.value
    return checkedCount.value > 0 && checkedCount.value < total
})

const toggleGroupPermissions = (group) => {
    const groupPermissionIds = group.permissions.map(p => p.id)
    
    if (group.isGroupSelected) {
        // Add all permissions from this group
        form.value.permissions = [...new Set([...form.value.permissions, ...groupPermissionIds])]
    } else {
        // Remove all permissions from this group
        form.value.permissions = form.value.permissions.filter(id => !groupPermissionIds.includes(id))
    }
}

// Watch individual permissions to update group selection state
watch(() => form.value.permissions, (newPermissions) => {
    permissionsList.value.forEach(group => {
        const groupPermissionIds = group.permissions.map(p => p.id)
        const groupPermissionsSelected = groupPermissionIds.every(id => newPermissions.includes(id))
        group.isGroupSelected = groupPermissionsSelected
    })

    // Update select all checkbox
    const total = totalPermissionsCount.value
    if (newPermissions.length == total) {
        isSelectAll.value = true
    } else if (newPermissions.length == 0) {
        isSelectAll.value = false
    }
}, { deep: true })

watch(isSelectAll, val => {
    if (val) {
        // Select all permissions
        const allPermissions = []
        permissionsList.value.forEach(group => {
            group.permissions.forEach(permission => {
                allPermissions.push(permission.id)
            })
            group.isGroupSelected = true
        })
        form.value.permissions = allPermissions
    } else {
        // Deselect all permissions
        form.value.permissions = []
        permissionsList.value.forEach(group => {
            group.isGroupSelected = false
        })
    }
})

const updateRole = async () => {
    loadings.value = true
    if (!validateRoleName(form.value.name)) {
        loadings.value = false
        return
    }

    try {
        const res = await $api(`/roles/${route.query.id}`, {
            method: 'PUT',
            body: {
                name: form.value.name,
                description: form.value.description,
                permissions: form.value.permissions,
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
            toast(err.message, {
                type: 'error',
            })
            loadings.value = false
            return
        }

        toast(message, {
            type: "success",
        })
        loadings.value = false
        router.push({ name: 'role' })
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
            <VCard :title="t('Edit Role')">
                <VCardText>
                    <VForm class="mt-3" @submit.prevent="updateRole">
                        <VRow>
                            <!-- Role Name -->
                            <VCol cols="12" md="4">
                                <AppTextField v-model="form.name" :label="t('Role Name')" :required="true" type="text"
                                    :placeholder="t('Enter role name')" :error-messages="roleNameError"
                                    @input="validateRoleName($event.target.value)" />
                            </VCol>
                            <VCol cols="12" md="8">
                                <AppTextField v-model="form.description" :label="t('Description')" type="text"
                                    :placeholder="t('Enter Description')" />
                            </VCol>
                            <VCol cols="12">
                                <div class="d-flex align-center justify-space-between mb-4">
                                    <h5 class="text-h5">{{ t('Role Permissions') }}</h5>
                                    <VCheckbox v-model="isSelectAll" v-model:indeterminate="isIndeterminate" :label="t('Select All')" />
                                </div>

                                <VRow>
                                    <VCol v-for="group in permissionsList" :key="group.name" cols="12" md="4">
                                        <VCard>
                                            <VCardTitle class="text-h6 pa-4 d-flex justify-space-between align-center">
                                                {{ group.name }}
                                                <VCheckbox 
                                                    v-model="group.isGroupSelected"
                                                    @change="toggleGroupPermissions(group)"
                                                    density="compact"
                                                    hide-details
                                                />
                                            </VCardTitle>
                                            <VDivider />
                                            <VCardText>
                                                <VCheckbox v-for="permission in group.permissions"
                                                    :key="permission.id"
                                                    v-model="form.permissions"
                                                    :value="permission.id"
                                                    :label="formatPermissionName(permission.name)"
                                                    density="compact"
                                                    class="mb-1"
                                                />
                                            </VCardText>
                                        </VCard>
                                    </VCol>
                                </VRow>
                            </VCol>
                            <!-- Form Actions -->
                            <VCol cols="12" class="d-flex flex-wrap gap-4">
                                <VBtn type="submit" :loading="loadings" :disabled="loadings">
                                    <VIcon start icon="tabler-checkbox" />
                                    {{ t('Update') }}
                                </VBtn>
                                <VBtn color="primary" variant="tonal" type="reset" @click.prevent="router.push({ name: 'role' })">
                                    <VIcon start icon="tabler-arrow-back" />
                                    {{ t('Back') }}
                                </VBtn>
                            </VCol>
                        </VRow>
                    </VForm>
                </VCardText>
            </VCard>
        </VCol>
    </VRow>
</template>

<style lang="scss">
.v-card-text .v-checkbox:last-child {
    margin-bottom: 0;
}
</style>
