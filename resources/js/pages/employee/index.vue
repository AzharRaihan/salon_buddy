<script setup>
import { computed, ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import { toast } from 'vue3-toastify';
import { useI18n } from 'vue-i18n';
import { useCompanyFormatters } from '@/composables/useCompanyFormatters';
const userCookieData = useCookie("userData").value;
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
const { t } = useI18n();
const router = useRouter()
const searchQuery = ref('')

// Data table options
const itemsPerPage = ref(10)
const page = ref(1)
const sortBy = ref()
const orderBy = ref()
const isConfirmDialogOpen = ref(false)
const selectedUserId = ref(null)
const userData = ref(null)
const authUser = ref({ id: userCookieData?.id }) // Replace with real auth user data

// Company formatters
const { fetchCompanySettings, formatDate, formatAmount, getSerialNumber } = useCompanyFormatters()

// Computed properties for i18n translations
const SN = computed(() => t('SN'))
const Name = computed(() => t('Name'))
const Email = computed(() => t('Email'))
const Phone = computed(() => t('Phone Number'))
const Role = computed(() => t('Role'))
const Status = computed(() => t('Status'))
const Action = computed(() => t('Action'))

// Data table Headers
const headers = [
    {
        title: SN,
        key: 'serial_number',
        sortable: false,
    },
    {
        title: Name,
        key: 'name',
        sortable: true,
    },
    {
        title: Email,
        key: 'email',
        sortable: true,
    },
    {
        title: Phone,
        key: 'phone',
        sortable: true,
    },
    {
        title: Role,
        key: 'role_name',
        sortable: true,
    },
    {
        title: Status,
        key: 'status_name',
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

const fetchUsers = async () => {
    const response = await $api('/users', {
        method: 'GET',
        query: {
            q: searchQuery.value,
            itemsPerPage: itemsPerPage.value,
            page: page.value,
            sortBy: sortBy.value,
            orderBy: orderBy.value,
        },
    })
    userData.value = response.data
}

// Initial fetch
await fetchUsers()

const users = computed(() => {
    const data = userData.value?.users || []
    return data.map((user, index) => ({
        ...user,
        serial_number: getSerialNumber(index, totalUsers.value, page.value, itemsPerPage.value),
    }))
})
const totalUsers = computed(() => userData.value?.total || 0)

const openConfirmDialog = (userId) => {
    isConfirmDialogOpen.value = true
    selectedUserId.value = userId
}

const handleDelete = async (confirmed) => {
    if (!confirmed) return;

    try {
        await $api(`/users/${selectedUserId.value}`, {
            method: 'DELETE',
        })
        selectedUserId.value = null
        isConfirmDialogOpen.value = false
        await fetchUsers()
        toast('User deleted successfully', {
            "type": "success",
        });
    } catch (error) {
        isConfirmDialogOpen.value = false
        selectedUserId.value = null
        console.error('Error deleting user:', error)
        toast('Failed to delete user', {
            "type": "error",
        });
    }
}

// Watch for changes in search query
watch(searchQuery, () => {
    page.value = 1 // Reset to first page when searching
    fetchUsers()
})

// Watch for changes in pagination
watch([page, itemsPerPage], () => {
    fetchUsers()
})

onMounted(async () => {
    await fetchCompanySettings()
})
</script>

<template>
    <div>
        <VCard :title="t('List Employee')">
            <VCardText>
                <div class="d-flex justify-space-between flex-wrap gap-y-4">
                    <AppTextField v-model="searchQuery" style="max-inline-size: 280px; min-inline-size: 280px;"
                        :placeholder="t('Search Employee')" />
                    <div class="d-flex flex-row gap-4 align-center flex-wrap">
                        <VBtn prepend-icon="tabler-plus" :to="{ name: 'employee-create' }">
                            {{ t('Add Employee') }}
                        </VBtn>
                        <AppSelect v-model="itemsPerPage" :items="[5, 10, 20, 50, 100]" />

                        <ExportTable 
                            :data="users" 
                            :headers="exportHeaders" 
                            filename="user-report"
                            :title="$t('List Employee')"
                        />
                    </div>
                </div>
            </VCardText>

            <VDivider />
            <VDataTableServer v-model:items-per-page="itemsPerPage" v-model:page="page" :items="users" item-value="id"
                :headers="headers" :items-length="totalUsers" class="text-no-wrap" @update:options="updateOptions">
                
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
                        {{ t('No users found') }}
                    </div>
                </template>

                <!-- Role name -->
                <template #item.role_name="{ item }">
                    {{ item.role.name }}
                </template>

                <!-- Status badge -->
                <template #item.status_name="{ item }">
                    <VChip
                        :color="item.status_name == 'Active' ? 'success' : 'error'"
                        size="small"
                    >
                        {{ item.status_name }}
                    </VChip>
                </template>

                <!-- <template #item.action="{ item }">
                    <div class="d-flex justify-center gap-1">
                        <VBtn icon variant="text" color="default" size="small"
                            :to="{ name: 'user-edit', query: { id: item.id } }">
                            <VIcon size="22" icon="tabler-edit" />
                        </VBtn>

                        <VBtn icon variant="text" color="default" size="small" @click="openConfirmDialog(item.id)">
                            <VIcon size="22" icon="tabler-trash" />
                        </VBtn>
                    </div>
                </template> -->


                <template #item.action="{ item }">
                    <div class="d-flex justify-center gap-1">
                        <!-- Edit button: show only if role.id == 1 and user.id == 1 -->
                        <VBtn
                            v-if="item.id == 1 && authUser?.id == 1"
                            icon
                            variant="text"
                            color="info"
                            size="small"
                            @click="$router.push({ name: 'employee-edit', query: { id: item.id } })"
                            >
                            <VIcon size="22" icon="tabler-edit" />
                        </VBtn>
                        <VBtn
                            v-if="item.id != 1"
                            icon
                            variant="text"
                            color="info"
                            size="small"
                            @click="$router.push({ name: 'employee-edit', query: { id: item.id } })"
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
            :confirmation-question="t('Are you sure you want to delete this user?')" :confirm-title="t('Deleted!')"
            :confirm-msg="t('User has been deleted successfully.')" :cancel-title="t('Cancelled')"
            :cancel-msg="t('User Deletion Cancelled!')" @confirm="handleDelete" />
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
