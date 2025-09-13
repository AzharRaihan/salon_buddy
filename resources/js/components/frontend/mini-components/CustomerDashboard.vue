<script setup>
import { onMounted } from 'vue'
import { useCustomerDashboard } from '@/composables/useCustomerDashboard'
import { useAuthState } from '@/composables/useAuthState'
import { useCompanyFormatters } from '@/composables/useCompanyFormatters';
import { useI18n } from 'vue-i18n';
const { t } = useI18n()
const { formatDate, formatAmount, formatNumber } = useCompanyFormatters()

const { 
  dashboardStats,
  orderHistory,
  isLoading,
  error,
  initializeDashboard
} = useCustomerDashboard()

const { customerAuthState } = useAuthState()

// Initialize dashboard data on mount
onMounted(() => {
  if (customerAuthState.value.isAuthenticated) {
    initializeDashboard()
  }
})

// Get status class for styling
const getStatusClass = (status) => {
  switch (status.toLowerCase()) {
    case 'confirmed':
      return 'status-confirmed'
    case 'pending':
      return 'status-pending'
    case 'cancelled':
      return 'status-cancel'
    case 'completed':
      return 'status-completed'
    default:
      return 'status-pending'
  }
}
</script>
<template>
    <div class="dashboard-wrapper">
        <!-- Dashboard Stats -->
        <div class="row">
            <div class="col-12 col-sm-6 col-md-6 col-lg-4 col-xl-4 mb-4">
                <div class="dashboard-card">
                    <div class="dashboard-card-icon">
                       <img src="../../../@frontend/images/dashboard/package.png" alt="Dashboard Card Icon">
                    </div>
                    <h5>{{ t('Total Buy Packages') }}</h5>
                    <h4>{{ formatNumber(dashboardStats.total_buy_package) }}</h4>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-6 col-lg-4 col-xl-4 mb-4">
                <div class="dashboard-card">
                    <div class="dashboard-card-icon">
                       <img src="../../../@frontend/images/dashboard/calendar.png" alt="Dashboard Card Icon">
                    </div>
                    <h5>{{ t('Services Booking') }}</h5>
                    <h4>{{ formatNumber(dashboardStats.service_booking) }}</h4>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-6 col-lg-4 col-xl-4 mb-4">
                <div class="dashboard-card">
                    <div class="dashboard-card-icon">
                       <img src="../../../@frontend/images/dashboard/order.png" alt="Dashboard Card Icon">
                    </div>
                    <h5>{{ t('Complete Order') }}</h5>
                    <h4>{{ formatNumber(dashboardStats.complete_order) }}</h4>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12 col-xl-12 mb-md-3 mb-lg-4">
                <div class="latest-order-history">
                    <h4>{{ t('Latest Order History') }}</h4>
                    <div class="latest-order-history-table">
                        <div v-if="orderHistory.length === 0" class="text-center py-4 text-muted">
                            <VIcon icon="tabler-inbox" size="48" class="mb-2" />
                            <p>{{ t('No order history available') }}</p>
                        </div>
                        
                        <table v-else class="table">
                            <thead>
                                <tr>
                                    <th>{{ t('Order ID') }}</th>
                                    <th class="text-center">{{ t('Date') }}</th>
                                    <th class="text-center">{{ t('Amount') }}</th>
                                    <th class="text-right">{{ t('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="order in orderHistory" :key="order.id">
                                    <td>{{ order.order_id }}</td>
                                    <td class="text-center">{{ formatDate(order.date) }}</td>
                                    <td class="text-center">{{ formatAmount(order.total_payable) }}</td>
                                    <td class="text-right">
                                        <span class="status" :class="getStatusClass(order.status)">{{ order.status }}</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<style scoped>
.status {
    padding: 5px 10px;
    border-radius: 25px;
    font-size: 12px;
    font-weight: 500;
    height: 30px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.status-confirmed {
    background-color: #2196F32a;
    color: #2196F3;
}
.status-pending {
    background-color: #ffc1072a;
    color: #FFC107;
}
.status-cancel {
    background-color: #f443362a;
    color: #F44336;
}
.status-completed {
    background-color: #0dca002a;
    color: #4CAF50;
}
</style>

