<script setup>
</script>
<template>
    <div class="package-order d-flex justify-content-between align-items-center transaction-history-wrapper">
        <h4 class="package-order-title">Package Order</h4>
        <div class="search">
            <form action="">
                <div class="form-group search-form">
                    <div class="search-icon">
                        <VIcon icon="tabler-search" />
                    </div>
                    <input class="form-control" type="text" id="package-name" name="package-name" placeholder="Search...">
                </div>
            </form>
        </div>
    </div>
    <div class="package-order-list">
        <table class="table">
            <thead>
                <tr class="table-header">
                    <th>SN</th>
                    <th>Transaction ID</th>
                    <th>Payment Method</th>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td>1234567890</td>
                    <td>Paypal</td>
                    <td>2025-01-01</td>
                    <td>$100</td>
                    <td>
                        <span class="status status-active">Status</span>
                    </td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>1234567890</td>
                    <td>Cash</td>
                    <td>2025-01-01</td>
                    <td>$100</td>
                    <td>
                        <span class="status status-active">Status</span>
                    </td>
                </tr>
                <tr>
                    <td>3</td>
                    <td>1234567890</td>
                    <td>Bank</td>
                    <td>2025-01-01</td>
                    <td>$100</td>
                    <td>
                        <span class="status status-pending">Status</span>
                    </td>
                </tr>
                <tr>
                    <td>4</td>
                    <td>1234567890</td>
                    <td>Cheque</td>
                    <td>2025-01-01</td>
                    <td>$100</td>
                    <td>
                        <span class="status status-cancel">Status</span>
                    </td>
                </tr>
                
            </tbody>
        </table>
    </div>
</template>

<style scoped>

</style>
