<script setup>
import EcommerceEarningReports from '@/components/dashboard/EcommerceEarningReports.vue'
import EcommerceExpensesRadialBarCharts from '@/components/dashboard/EcommerceExpensesRadialBarCharts.vue'
import EcommercePopularProducts from '@/components/dashboard/EcommercePopularProducts.vue'
import EcommerceRevenueReport from '@/components/dashboard/EcommerceRevenueReport.vue'
import EcommerceStatistics from '@/components/dashboard/EcommerceStatistics.vue'
import EcommerceTotalProfitLineCharts from '@/components/dashboard/EcommerceTotalProfitLineCharts.vue'
</script>

<template>
    <VRow class="match-height">
        <!-- Ecommerce Transition -->
        <VCol cols="12">
            <EcommerceStatistics class="h-100" />
        </VCol>


        <!-- Total Profit Line -->
        <VCol sm="12" md="6" lg="3">
            <EcommerceTotalProfitLineCharts />
        </VCol>

        <!-- Expenses Radial Bar Charts -->
        <VCol sm="12" md="6" lg="3">
            <EcommerceExpensesRadialBarCharts />
        </VCol>


        <!-- Revenue Report -->
        <VCol sm="12" md="12" lg="6">
            <EcommerceRevenueReport />
        </VCol>

        <!-- Earning Reports -->
        <VCol sm="12" md="6">
            <EcommerceEarningReports />
        </VCol>

        <!-- Popular Products -->
        <VCol sm="12" md="6">
            <EcommercePopularProducts />
        </VCol>

    </VRow>
</template>

<style lang="scss">
@use "@core-scss/template/libs/apex-chart.scss";
</style>
