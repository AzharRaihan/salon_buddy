<script setup>

import { computed, ref, watch, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { toast } from 'vue3-toastify';
import { useI18n } from 'vue-i18n';
import { useCompanyFormatters } from '@/composables/useCompanyFormatters';
import ExportTable from '@/components/ExportTable.vue';
// Computed headers for export (converts computed refs to strings)
const exportHeaders = computed(() => {
    return headers.map(header => ({
        ...header,
        title: typeof header.title == 'object' && header.title.value !== undefined 
            ? header.title.value 
            : header.title
    }))
})
const userData = useCookie("userData").value;
const { t } = useI18n()
const router = useRouter()
const searchQuery = ref('')


// Data table options
const itemsPerPage = ref(10)
const page = ref(1)
const sortBy = ref()
const orderBy = ref()
const isConfirmDialogOpen = ref(false)
const selectedRoleId = ref(null)
const roleData = ref(null)
const authUser = ref({ id: userData?.id }) // Replace with real auth user data
// Company formatters
const { fetchCompanySettings, formatDate, formatAmount, getSerialNumber } = useCompanyFormatters()

// Computed properties for i18n translations
const SN = computed(() => t('SN'))
const RoleName = computed(() => t('Role Name'))
const Description = computed(() => t('Description'))
const Action = computed(() => t('Action'))

// Data table Headers
const headers = [
    {
        title: SN,
        key: 'serial_number',
        sortable: false,
    },
    {
        title: RoleName,
        key: 'name',
        sortable: true,
    },
    {
        title: Description,
        key: 'description',
        sortable: true,
    },
    {
        title: Action,
        key: 'action',
        sortable: false,
        align: 'center',
    },
]

const updateOptions = options => {
    sortBy.value = options.sortBy[0]?.key
    orderBy.value = options.sortBy[0]?.order
}

const fetchRoles = async () => {
    const response = await $api('/roles', {
        method: 'GET',
        query: {
            q: searchQuery.value,
            itemsPerPage: itemsPerPage.value,
            page: page.value,
            sortBy: sortBy.value,
            orderBy: orderBy.value,
        },
    })
    roleData.value = response.data
}

// Initial fetch
await fetchRoles()
const roles = computed(() => {
    const roles = roleData.value?.roles || [];
    return roles.map((role, index) => ({
        ...role,
        serial_number: getSerialNumber(index, totalRoles.value, page.value, itemsPerPage.value),
    }));
})
const totalRoles = computed(() => roleData.value?.total || 0)

const openConfirmDialog = (roleId) => {
    isConfirmDialogOpen.value = true
    selectedRoleId.value = roleId
}

const handleDelete = async (confirmed) => {
    if (!confirmed) return;

    try {
        await $api(`/roles/${selectedRoleId.value}`, {
            method: 'DELETE',
        })
        selectedRoleId.value = null
        isConfirmDialogOpen.value = false
        await fetchRoles()
        toast(t('Role deleted successfully'), {
            "type": "success",
        });
    } catch (error) {
        isConfirmDialogOpen.value = false
        selectedRoleId.value = null
        console.error('Error deleting role:', error)
        toast(t('Failed to delete role'), {
            "type": "error",
        });
    }
}

// Watch for changes in search query
watch(searchQuery, () => {
    page.value = 1 // Reset to first page when searching
    fetchRoles()
})

// Watch for changes in pagination
watch([page, itemsPerPage], () => {
    fetchRoles()
})

onMounted(async () => {
    await fetchCompanySettings()
})
</script>

<template>
    <div>
        <VCard :title="t('List Role')">
            <VCardText>
                <div class="d-flex justify-space-between flex-wrap gap-y-4">
                    <AppTextField v-model="searchQuery" style="max-inline-size: 280px; min-inline-size: 280px;"
                        :placeholder="t('Search Role')" />
                    <div class="d-flex flex-row gap-4 align-center flex-wrap">
                        <VBtn prepend-icon="tabler-plus" :to="{ name: 'role-create' }">
                            {{ t('Add Role') }}
                        </VBtn>
                        <AppSelect v-model="itemsPerPage" :items="[5, 10, 20, 50, 100]" />

                        <ExportTable 
                            :data="roles" 
                            :headers="exportHeaders" 
                            filename="role-report"
                            :title="$t('List Role')"
                        />
                    </div>
                </div>
            </VCardText>

            <VDivider />
            <VDataTableServer v-model:items-per-page="itemsPerPage" v-model:page="page" :items="roles" item-value="id"
                :headers="headers" :items-length="totalRoles" class="text-no-wrap" @update:options="updateOptions">

                <!-- Serial Number -->
                <template #item.serial_number="{ item }">
                    {{ item.serial_number }}
                </template>

                <!-- Loading state -->
                <template #loading>
                    <VSkeletonLoader type="table-row" :rows="itemsPerPage" />
                </template>

                <!-- No data state -->
                <template #no-data>
                    <div class="d-flex align-center justify-center pa-4">
                        <VIcon icon="tabler-alert-circle" class="me-2" />
                        {{ t('No roles found') }}
                    </div>
                </template>

                <template #item.action="{ item }">
                    <div class="d-flex justify-center gap-1">
                        <!-- Edit button: show only if role.id == 1 and user.id == 1 -->
                        <VBtn
                            v-if="item.id == 1 && authUser?.id == 1"
                            icon
                            variant="text"
                            color="info"
                            size="small"
                            @click="$router.push({ name: 'role-edit', query: { id: item.id } })"
                            >
                            <VIcon size="22" icon="tabler-edit" />
                        </VBtn>
                        <VBtn
                            v-if="item.id != 1"
                            icon
                            variant="text"
                            color="info"
                            size="small"
                            @click="$router.push({ name: 'role-edit', query: { id: item.id } })"
                            >
                            <VIcon size="22" icon="tabler-edit" />
                        </VBtn>

                        <!-- Delete button: only show if role.id != 1 -->
                        <VBtn
                        v-if="item.id != 1"
                        icon
                        variant="text"
                        color="error"
                        size="small"
                        @click="openConfirmDialog(item.id)"
                        >
                        <VIcon size="22" icon="tabler-trash" />
                        </VBtn>
                    </div>
                </template>

            </VDataTableServer>
        </VCard>

        <ConfirmDialog v-model:is-dialog-visible="isConfirmDialogOpen"
            :confirmation-question="t('Are you sure you want to delete this role?')" :confirm-title="t('Deleted!')"
            :confirm-msg="t('Role has been deleted successfully.')" :cancel-title="t('Cancelled')"
            :cancel-msg="t('Role Deletion Cancelled!')" @confirm="handleDelete" />
    </div>
</template>

<style lang="scss" scoped>
.text-link {
    color: rgb(var(--v-theme-primary));
    text-decoration: none;

    &:hover {
        color: rgba(var(--v-theme-primary), 0.8);
    }
}
</style>
