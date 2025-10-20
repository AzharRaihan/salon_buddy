<script setup>
import { computed, ref, watch, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { toast } from 'vue3-toastify';
import defaultBranchIcon from '@images/system-config/branch_icon.png';
import { useI18n } from 'vue-i18n';
import { useAuthState } from '@/composables/useAuthState'

const { userAuthState } = useAuthState()

const { t } = useI18n()

const router = useRouter()
const searchQuery = ref('')
const userData = useCookie("userData").value;
const userAbilityRules = useCookie("userAbilityRules").value;
import { useBranchInfo } from '@/composables/useBranchInfo'
const { branchInfo } = useBranchInfo()

watch(searchQuery, async (newValue) => {
  if (newValue) {
    try {
        const response = await $api(`/set-branch-data/${newValue}`, {
            method: 'GET'
        })
        if (response.data) {
            // useCookie('branch_info').value = response.data

            branchInfo.value = response.data
        
            if(userData.id == 1){
                router.push('/dashboard')
                toast(t('Branch selected successfully'), {
                    type: 'success',
                    position: 'top-right', 
                    autoClose: 3000
                })
            } else {
                if (userAbilityRules.includes('dashboard')) {
                    router.push('/dashboard')
                    toast(t('Branch selected successfully'), {
                        type: 'success',
                        position: 'top-right', 
                        autoClose: 3000
                    })
                }else{
                    router.push('/home')
                }

            }
        }
    } catch (err) {
      console.error('Error setting branch data:', err)
      toast(t('Failed to set branch data'), {
        type: 'error',
        position: 'top-right',
        autoClose: 3000
      })
    }
  }
})


// Data table options
const itemsPerPage = ref(10)
const page = ref(1)
const sortBy = ref()
const orderBy = ref()
const isConfirmDialogOpen = ref(false)
const selectedBranchId = ref(null)
const branchData = ref(null)
const loading = ref(false)
const error = ref(null)

const fetchBranchs = async () => {
    loading.value = true
    error.value = null
    try {
        const response = await $api('/branches', {
            method: 'GET',
            query: {
                q: searchQuery.value || undefined, // Only include if has value
                itemsPerPage: itemsPerPage.value,
                page: page.value,
                sortBy: sortBy.value,
                orderBy: orderBy.value,
            }
        })
        branchData.value = response.data
    } catch (err) {
        error.value = 'Failed to load branches. Please try again.'
        toast('Failed to load branches', {
            type: 'error'
        })
    } finally {
        loading.value = false
    }
}
// Initial fetch with error handling
try {
    await fetchBranchs()
} catch (err) {
    console.error('Initial fetch failed:', err)
}

const branchs = computed(() => branchData.value?.branches || [])
const totalBranchs = computed(() => branchData.value?.total || 0)

const openConfirmDialog = (branchId) => {
    isConfirmDialogOpen.value = true
    selectedBranchId.value = branchId
}

const handleDelete = async (confirmed) => {
    if (!confirmed) return;

    try {
        await $api(`/branches/${selectedBranchId.value}`, {
            method: 'DELETE',
        })
        selectedBranchId.value = null
        isConfirmDialogOpen.value = false
        await fetchBranchs()
        toast(t('Branch deleted successfully'), {
            "type": "success",
        });
    } catch (error) {
        isConfirmDialogOpen.value = false
        selectedBranchId.value = null
        console.error('Error deleting branch:', error)
        toast(t('Failed to delete branch'), {
            "type": "error",
        });
    }
}

// Watch for changes in search query
watch(searchQuery, () => {
    page.value = 1 // Reset to first page when searching
    fetchBranchs() 
})

// Watch for changes in pagination
watch([page, itemsPerPage], () => {
    fetchBranchs()
})
</script>

<template>
    <div>
        <div v-if="error" class="text-center my-4">
            <p class="text-error">{{ error }}</p>
            <VBtn color="primary" @click="fetchBranchs">
                Retry
            </VBtn>
        </div>

        <VProgressCircular
            v-if="loading"
            indeterminate
            class="ma-4"
        />

        <VRow>
            <VCol cols="12" md="6" lg="4" v-for="branch in branchs" :key="branch.id">
                <VCard>
                    <VCardText class="branch-card">
                        <img :src="defaultBranchIcon" alt="branch-icon">
                        <h3 v-if="branch.branch_name" class="mt-2 text-truncate">{{ branch.branch_name }}</h3>
                        <h5 v-if="branch.branch_code">Branch Code: {{ branch.branch_code }}</h5>
                        <VDivider class="my-3"/>
                        <p v-if="branch.address" class="d-flex align-items-center">
                            <VIcon icon="tabler-map-pin" class="me-2"/>
                            {{ $t('Address') }}: {{ branch.address }}
                        </p>
                        <p v-if="branch.phone" class="d-flex align-items-center">
                            <VIcon icon="tabler-phone" class="me-2"/>
                            {{ $t('Phone') }}: {{ branch.phone }}
                        </p>
                        <p class="d-flex align-items-center mb-0">
                            <VIcon icon="tabler-mail" class="me-2"/>
                            {{ $t('Email') }}: {{ branch.email ? branch.email : 'N/A' }}
                        </p>
                    </VCardText>

                    <VCardText class="pt-0">
                        <VRow>
                            <VCol cols="6">
                                <VBtn block color="primary" @click="$router.push({ name: 'branch-edit', query: { id: branch.id } })">
                                    <VIcon icon="tabler-edit" class="me-2"/>
                                    {{ $t('Edit') }}
                                </VBtn>
                            </VCol>
                            <VCol cols="6">
                                <VBtn block color="error" @click="openConfirmDialog(branch.id)">
                                    <VIcon icon="tabler-trash" class="me-2"/>
                                    {{ $t('Delete') }}
                                </VBtn>
                            </VCol>
                        </VRow>
                        <VBtn block color="primary" class="mt-4" @click="searchQuery = branch.id">
                            <VIcon icon="tabler-corner-down-right-double" class="me-2"/>
                            {{ $t('Enter') }}
                        </VBtn>
                    </VCardText>
                </VCard>
            </VCol>
        </VRow>

        <ConfirmDialog v-model:is-dialog-visible="isConfirmDialogOpen"
            :confirmation-question="$t('Are you sure you want to delete this branch?')" 
            :confirm-title="$t('Deleted!')"
            :confirm-msg="$t('Branch has been deleted successfully.')" 
            :cancel-title="$t('Cancelled')"
            :cancel-msg="$t('Branch Deletion Cancelled!')" 
            @confirm="handleDelete"/>
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
.branch-card h3 {
    font-size: 18px;
    font-weight: 500;
}
.branch-card h5 {
    font-size: 16px;
    font-weight: 400;
    margin-top: 10px;
}
</style>