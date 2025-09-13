<script setup>
import { onMounted, onUnmounted } from 'vue'
import { useRoute } from 'vue-router'
import { usePackageOrders } from '@/composables/usePackageOrders'

const route = useRoute()

const {
  packageDetails,
  isLoadingDetails,
  detailsError,
  hasDetailsError,
  hasPackageDetails,
  fetchPackageDetails,
  clearPackageDetails,
  getStatusClass
} = usePackageOrders()

// Get package ID from route params
const packageId = route.params.id

// Initialize package details on mount
onMounted(async () => {
  if (packageId) {
    await fetchPackageDetails(packageId)
  }
})

// Clear details when component unmounts
onUnmounted(() => {
  clearPackageDetails()
})
</script>
<template>
    <!-- Loading State -->
    <div v-if="isLoadingDetails" class="text-center py-4">
        <div class="spinner-border" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2">Loading package details...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="hasDetailsError" class="alert alert-danger" role="alert">
        <VIcon icon="tabler-alert-circle" size="20" />
        {{ detailsError }}
    </div>

    <!-- Package Details -->
    <div v-else-if="hasPackageDetails" class="package-details package-details-wrapper">
        <h4 class="package-details-title">Package Order Details</h4>
        
        <!-- Package Header -->
        <div class="package-header">
            <ul>
                <li>
                    <span class="title">Order ID:</span>
                    <span class="value">{{ packageDetails.order_id }}</span>
                </li>
                <li>
                    <span class="title">Package Name:</span>
                    <span class="value">{{ packageDetails.package_name }}</span>
                </li>
                <li>
                    <span class="title">Package Price:</span>
                    <span class="value">${{ packageDetails.package_price.toFixed(2) }}</span>
                </li>
                <li>
                    <span class="title">Duration:</span>
                    <span class="value">{{ packageDetails.duration }}</span>
                </li>
                <li>
                    <span class="title">Branch:</span>
                    <span class="value">{{ packageDetails.branch }}</span>
                </li>
                <li>
                    <span class="title">Start Date:</span>
                    <span class="value">{{ packageDetails.start_date }}</span>
                </li>
                <li>
                    <span class="title">End Date:</span>
                    <span class="value">{{ packageDetails.end_date }}</span>
                </li>
                <li>
                    <span class="title">Status:</span>
                    <span class="value">
                        <span class="status" :class="getStatusClass(packageDetails.status)">
                            {{ packageDetails.status }}
                        </span>
                    </span>
                </li>
            </ul>
        </div>

        <!-- Package Items -->
        <div class="package-details-items">
            <ul>
                <li class="package-details-heading">
                    <span class="title">Service Name</span>
                    <span class="value">Total Quantity</span>
                    <span class="value">Used</span>
                    <span class="value">Remaining</span>
                </li>
                
                <li 
                    v-for="(item, index) in packageDetails.items" 
                    :key="item.id"
                    class="package-details-item"
                    :class="{ 'last-item': index === packageDetails.items.length - 1 }"
                >
                    <span class="title">{{ item.name }}</span>
                    <span class="value">{{ item.quantity }}</span>
                    <span class="value">{{ item.used }}</span>
                    <span class="value">{{ item.remaining }}</span>
                </li>
            </ul>
        </div>
    </div>

    <!-- No Details State -->
    <div v-else class="text-center py-5">
        <VIcon icon="tabler-package-off" size="64" class="text-muted mb-3" />
        <h5 class="text-muted">Package details not found</h5>
        <p class="text-muted">Unable to load package details. Please try again later.</p>
    </div>
</template>

<style scoped>

</style>
