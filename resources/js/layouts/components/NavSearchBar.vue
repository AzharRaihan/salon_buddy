<script setup>
import navigation from '@/navigation/vertical'
import { useConfigStore } from '@core/stores/config'
import Shepherd from 'shepherd.js'
import { useI18n } from 'vue-i18n';

const { t } = useI18n({ useScope: 'global' })

defineOptions({
  // 👉 Is App Search Bar Visible
  inheritAttrs: false,
})

const configStore = useConfigStore()
const isAppSearchBarVisible = ref(false)
const isLoading = ref(false)

// 👉 Default suggestions
const suggestionGroups = [
  {
    title: 'Popular Searches',
    content: [
      // HOME & DASHBOARD
      { icon: 'tabler-home', title: 'Home', url: { name: 'home' } },
      { icon: 'tabler-gauge', title: 'Dashboard', url: { name: 'admin-dashboard' } },

      // BRANCH
      { icon: 'tabler-layout-grid', title: 'Add Branch', url: { name: 'branch-create' } },
      { icon: 'tabler-layout-grid', title: 'List Branch', url: { name: 'branch' } },

      // BOOKING
      { icon: 'tabler-calendar-event', title: 'Add Booking', url: { name: 'booking-create' } },
      { icon: 'tabler-calendar-event', title: 'List Booking', url: { name: 'booking' } },
      { icon: 'tabler-calendar-event', title: 'Booking Calendar', url: { name: 'booking-calendar' } },

      // ITEM & STOCK
      { icon: 'tabler-box', title: 'Add Unit', url: { name: 'unit-create' } },
      { icon: 'tabler-box', title: 'List Unit', url: { name: 'unit' } },
      { icon: 'tabler-box', title: 'Add Category', url: { name: 'category-create' } },
      { icon: 'tabler-box', title: 'List Category', url: { name: 'category' } },
      { icon: 'tabler-box', title: 'Add Item', url: { name: 'item-create' } },
      { icon: 'tabler-box', title: 'List Item', url: { name: 'item' } },

      { icon: 'tabler-replace', title: 'Stock', url: { name: 'stock-stock' } },
      { icon: 'tabler-replace', title: 'Alert Stock', url: { name: 'stock-alert-stock' } },
      { icon: 'tabler-replace', title: 'Product Usages', url: { name: 'stock-product-usages' } },

      // SALES & CUSTOMERS
      { icon: 'tabler-shopping-cart-heart', title: 'List Sale', url: { name: 'sale' } },
      { icon: 'tabler-shopping-cart-heart', title: 'Add Promotion', url: { name: 'promotion-create' } },
      { icon: 'tabler-shopping-cart-heart', title: 'List Promotion', url: { name: 'promotion' } },

      { icon: 'tabler-users', title: 'Add Customer', url: { name: 'customer-create' } },
      { icon: 'tabler-users', title: 'List Customer', url: { name: 'customer' } },
      { icon: 'tabler-users', title: 'Add Customer Receive', url: { name: 'customer-receive-create' } },
      { icon: 'tabler-users', title: 'List Customer Receive', url: { name: 'customer-receive' } },

      // PURCHASE & EXPENSE
      { icon: 'tabler-basket-down', title: 'Add Purchase', url: { name: 'purchase-create' } },
      { icon: 'tabler-basket-down', title: 'List Purchase', url: { name: 'purchase' } },

      { icon: 'tabler-user-dollar', title: 'Add Supplier', url: { name: 'supplier-create' } },
      { icon: 'tabler-user-dollar', title: 'List Supplier', url: { name: 'supplier' } },
      { icon: 'tabler-user-dollar', title: 'Add Supplier Payment', url: { name: 'supplier-payment-create' } },
      { icon: 'tabler-user-dollar', title: 'List Supplier Payment', url: { name: 'supplier-payment' } },

      { icon: 'tabler-circle-minus', title: 'Add Expense', url: { name: 'expense-create' } },
      { icon: 'tabler-circle-minus', title: 'List Expense', url: { name: 'expense' } },
      { icon: 'tabler-circle-minus', title: 'Add Expense Category', url: { name: 'expense-category-create' } },
      { icon: 'tabler-circle-minus', title: 'List Expense Category', url: { name: 'expense-category' } },

      { icon: 'tabler-trash', title: 'Add Damage', url: { name: 'damage-create' } },
      { icon: 'tabler-trash', title: 'List Damage', url: { name: 'damage' } },

      // ACCOUNTING
      { icon: 'tabler-receipt-dollar', title: 'Add Payment Method', url: { name: 'payment-method-create' } },
      { icon: 'tabler-receipt-dollar', title: 'List Payment Method', url: { name: 'payment-method' } },

      // EMPLOYEE MANAGEMENT
      { icon: 'tabler-lock', title: 'Add Role', url: { name: 'role-create' } },
      { icon: 'tabler-lock', title: 'List Role', url: { name: 'role' } },
      { icon: 'tabler-user', title: 'Add Employee', url: { name: 'employee-create' } },
      { icon: 'tabler-user', title: 'List Employee', url: { name: 'employee' } },
      { icon: 'tabler-hospital-circle', title: 'Add Attendance', url: { name: 'attendance-create' } },
      { icon: 'tabler-hospital-circle', title: 'List Attendance', url: { name: 'attendance' } },
      { icon: 'tabler-hospital-circle', title: 'Add Salary', url: { name: 'salary-create' } },
      { icon: 'tabler-hospital-circle', title: 'List Salary', url: { name: 'salary' } },
      { icon: 'tabler-user-dollar', title: 'Add Staff Payment', url: { name: 'staff-payment-create' } },
      { icon: 'tabler-user-dollar', title: 'List Staff Payment', url: { name: 'staff-payment' } },

      // SETTINGS & REPORTS
      { icon: 'tabler-book-2', title: 'Daily Summary Report', url: { name: 'report-daily-summary-report' } },
      { icon: 'tabler-book-2', title: 'Profit & Loss Report', url: { name: 'report-profit-loss-report' } },
      { icon: 'tabler-book-2', title: 'Sales Report', url: { name: 'report-sales-report' } },
      { icon: 'tabler-book-2', title: 'Purchase Report', url: { name: 'report-purchase-report' } },
      { icon: 'tabler-book-2', title: 'Stock Report', url: { name: 'report-stock-report' } },
      { icon: 'tabler-book-2', title: 'Employee Commission Report', url: { name: 'report-employee-commission-report' } },
      { icon: 'tabler-book-2', title: 'Staff Earning Report', url: { name: 'report-staff-earning-report' } },
      { icon: 'tabler-book-2', title: 'Staff Payout Report', url: { name: 'report-staff-payout-report' } },
      { icon: 'tabler-book-2', title: 'Staff Evaluation Report', url: { name: 'report-staff-evaluation-report' } },
      { icon: 'tabler-book-2', title: 'Staff Evaluation Details Report', url: { name: 'report-staff-evaluation-details-report' } },
      { icon: 'tabler-book-2', title: 'Expense Report', url: { name: 'report-expense-report' } },
      { icon: 'tabler-book-2', title: 'Damage Report', url: { name: 'report-damage-report' } },
      { icon: 'tabler-book-2', title: 'Salary Report', url: { name: 'report-salary-report' } },
      { icon: 'tabler-book-2', title: 'Attendance Report', url: { name: 'report-attendance-report' } },

      { icon: 'tabler-settings', title: 'Setting', url: { name: 'settings-company' } },
      { icon: 'tabler-settings', title: 'Tax Setting', url: { name: 'settings-tax-setting' } },
      { icon: 'tabler-settings', title: 'White Label', url: { name: 'settings-white-label' } },
      { icon: 'tabler-settings', title: 'Email Settings', url: { name: 'settings-email' } },
      { icon: 'tabler-settings', title: 'SMS Settings', url: { name: 'settings-sms' } },
      { icon: 'tabler-settings', title: 'Whatsapp Settings', url: { name: 'settings-whatsapp' } },
      { icon: 'tabler-settings', title: 'Payment Settings', url: { name: 'settings-payment' } },
      { icon: 'tabler-settings', title: 'Social Auth Settings', url: { name: 'settings-social-auth' } },
      { icon: 'tabler-settings', title: 'Vacation', url: { name: 'vacation' } },
      { icon: 'tabler-settings', title: 'Holiday', url: { name: 'holiday' } },

      { icon: 'tabler-globe', title: 'Website Settings', url: { name: 'website-settings' } },
      { icon: 'tabler-globe', title: 'About Us', url: { name: 'website-about-us' } },
      { icon: 'tabler-globe', title: 'Terms & Conditions', url: { name: 'website-terms-and-conditions' } },
      { icon: 'tabler-globe', title: 'Privacy Policy', url: { name: 'website-privacy-policy' } },
      { icon: 'tabler-globe', title: 'Add Delivery Area', url: { name: 'website-delivery-area-create' } },
      { icon: 'tabler-globe', title: 'List Delivery Area', url: { name: 'website-delivery-area' } },
      { icon: 'tabler-globe', title: 'Add Delivery Partner', url: { name: 'website-delivery-partner-create' } },
      { icon: 'tabler-globe', title: 'List Delivery Partner', url: { name: 'website-delivery-partner' } },
      { icon: 'tabler-globe', title: 'Add Banner', url: { name: 'website-banner-create' } },
      { icon: 'tabler-globe', title: 'List Banner', url: { name: 'website-banner' } },
      { icon: 'tabler-globe', title: 'Add FAQ', url: { name: 'website-faq-create' } },
      { icon: 'tabler-globe', title: 'List FAQ', url: { name: 'website-faq' } },
      { icon: 'tabler-globe', title: 'Add Working Process', url: { name: 'website-workingprocess-create' } },
      { icon: 'tabler-globe', title: 'List Working Process', url: { name: 'website-workingprocess' } },
      { icon: 'tabler-globe', title: 'Add Portfolio', url: { name: 'website-portfolio-create' } },
      { icon: 'tabler-globe', title: 'List Portfolio', url: { name: 'website-portfolio' } },
    ],
  },
]

// 👉 No Data suggestion
const noDataSuggestions = [
  {
    title: 'Analytics',
    icon: 'tabler-chart-bar',
    url: { name: 'dashboards-analytics' },
  },
  {
    title: 'CRM',
    icon: 'tabler-chart-donut-3',
    url: { name: 'dashboards-crm' },
  },
  {
    title: 'eCommerce',
    icon: 'tabler-shopping-cart',
    url: { name: 'dashboards-ecommerce' },
  },
]

const searchQuery = ref('')
const router = useRouter()
const searchResult = ref([])

const searchNavigation = (query) => {
  const results = []

  const searchInItems = (items, parentTitle = '') => {
    console.log(items)
    items.forEach(item => {
      // Check if item title matches search query
      if (item.title &&
          query &&
          item.title.toLowerCase().includes(query.toLowerCase())
        ) {
        results.push({
          title: parentTitle || 'Navigation',
          children: [{
            title: item.title,
            icon: item.icon?.icon || 'tabler-circle-dot',
            url: item.to
          }]
        })
      }

      // Recursively search in children if they exist
      if (item.children) {
        searchInItems(item.children, item.title)
      }
    })
  }

  searchInItems(navigation)
  return results
}

const fetchResults = async () => {
  isLoading.value = true

  // Simulate loading for better UX
  setTimeout(() => {
    searchResult.value = searchNavigation(searchQuery.value)
    isLoading.value = false
  }, 300)
}

watch(searchQuery, fetchResults)

const closeSearchBar = () => {
  isAppSearchBarVisible.value = false
  searchQuery.value = ''
}

const redirectToSuggestedPage = selected => {
  router.push(selected.url)
  closeSearchBar()
}

const LazyAppBarSearch = defineAsyncComponent(() => import('@core/components/AppBarSearch.vue'))
</script>

<template>
  <div class="d-flex align-center cursor-pointer" v-bind="$attrs" style="user-select: none;"
    @click="isAppSearchBarVisible = !isAppSearchBarVisible">
    <!-- 👉 Search Trigger button -->
    <!-- close active tour while opening search bar using icon -->
    <IconBtn @click="Shepherd.activeTour?.cancel()">
      <VIcon icon="tabler-search" />
    </IconBtn>

    <span v-if="configStore.appContentLayoutNav === 'vertical'" class="d-none d-md-flex align-center text-disabled ms-2"
      @click="Shepherd.activeTour?.cancel()">
      <span class="me-2">{{ t('Search Menu') }}</span>
      <span class="meta-key">&#8984;K</span>
    </span>
  </div>

  <!-- 👉 App Bar Search -->
  <LazyAppBarSearch v-model:is-dialog-visible="isAppSearchBarVisible" :search-results="searchResult"
    :is-loading="isLoading" @search="searchQuery = $event">
    <!-- suggestion -->
    <template #suggestions>
      <VCardText class="app-bar-search-suggestions pa-12">
        <VRow v-if="suggestionGroups">
          <VCol v-for="suggestion in suggestionGroups" :key="suggestion.title" cols="12" sm="6">
            <p class="custom-letter-spacing text-disabled text-uppercase py-2 px-4 mb-0"
              style="font-size: 0.75rem; line-height: 0.875rem;">
              {{ suggestion.title }}
            </p>
            <VList class="card-list">
              <VListItem v-for="item in suggestion.content" :key="item.title"
                class="app-bar-search-suggestion mx-4 mt-2" @click="redirectToSuggestedPage(item)">
                <VListItemTitle>{{ item.title }}</VListItemTitle>
                <template #prepend>
                  <VIcon :icon="item.icon" size="20" class="me-n1" />
                </template>
              </VListItem>
            </VList>
          </VCol>
        </VRow>
      </VCardText>
    </template>

    <!-- no data suggestion -->
    <template #noDataSuggestion>
      <div class="mt-9">
        <span class="d-flex justify-center text-disabled mb-2">Try searching for</span>
        <h6 v-for="suggestion in noDataSuggestions" :key="suggestion.title"
          class="app-bar-search-suggestion text-h6 font-weight-regular cursor-pointer py-2 px-4"
          @click="redirectToSuggestedPage(suggestion)">
          <VIcon size="20" :icon="suggestion.icon" class="me-2" />
          <span>{{ suggestion.title }}</span>
        </h6>
      </div>
    </template>

    <!-- search result -->
    <template #searchResult="{ item }">
      <VListSubheader class="text-disabled custom-letter-spacing font-weight-regular ps-4">
        {{ item.title }}
      </VListSubheader>
      <VListItem v-for="list in item.children" :key="list.title" :to="list.url" @click="closeSearchBar">
        <template #prepend>
          <VIcon size="20" :icon="list.icon" class="me-n1" />
        </template>
        <template #append>
          <VIcon size="20" icon="tabler-corner-down-left" class="enter-icon flip-in-rtl" />
        </template>
        <VListItemTitle>
          {{ list.title }}
        </VListItemTitle>
      </VListItem>
    </template>
  </LazyAppBarSearch>
</template>

<style lang="scss">
@use "@styles/variables/vuetify.scss";

.meta-key {
  border: thin solid rgba(var(--v-border-color), var(--v-border-opacity));
  border-radius: 6px;
  block-size: 1.5625rem;
  font-size: 0.8125rem;
  line-height: 1.3125rem;
  padding-block: 0.125rem;
  padding-inline: 0.25rem;
}

.app-bar-search-dialog {
  .custom-letter-spacing {
    letter-spacing: 0.8px;
  }

  .card-list {
    --v-card-list-gap: 8px;
  }
}
</style>
