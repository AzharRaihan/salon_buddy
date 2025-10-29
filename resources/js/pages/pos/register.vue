<template>
    <div class="container register-view">
        <!-- Export Options -->
        <div class="export-options">
            <button class="btn print-btn">
                <i class="fas fa-print"></i> Print
            </button>
            <button class="btn excel-btn">
                <i class="fas fa-file-excel"></i> Excel
            </button>
            <button class="btn csv-btn">
                <i class="fas fa-file-csv"></i> CSV
            </button>
            <button class="btn pdf-btn">
                <i class="fas fa-file-pdf"></i> PDF
            </button>
        </div>

        <!-- Register Info -->
        <div class="register-info">
            <div class="info-row">
                <div class="info-label">Counter Name</div>
                <div class="info-value">{{ registerStore.counterName }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Time Range</div>
                <div class="info-value">{{ registerStore.getFormattedTimeRange }}</div>
            </div>
        </div>

        <!-- Register Table -->
        <div class="register-table">
            <table class="table">
                <thead>
                    <tr>
                        <th>SN</th>
                        <th>Payment Account</th>
                        <th>Transaction</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(method, index) in registerStore.paymentMethods" :key="index">
                        <td>{{ index + 1 }}</td>
                        <td>{{ method.name }}</td>
                        <td>
                            <div v-for="(transaction, tIndex) in method.transactions" :key="tIndex"
                                class="transaction-row">
                                <div>{{ transaction.name }}</div>
                            </div>
                        </td>
                        <td>
                            <div v-for="(transaction, tIndex) in method.transactions" :key="tIndex" class="amount-row">
                                <div>{{ transaction.amount.toFixed(2) }}</div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <button class="btn btn-primary close-register-btn" @click="closeRegister">
                <i class="fas fa-power-off"></i> Close Register
            </button>
            <button class="btn btn-danger close-btn" @click="close">
                <i class="fas fa-times"></i> Close
            </button>
        </div>
    </div>
</template>

<script setup>
import { useRouter } from 'vue-router';
import { useRegisterStore } from '@/stores/pos/registerStore';

// Page configuration
definePage({
    meta: {
        layout: 'pos',
        public: true,
    },
})

const router = useRouter();
const registerStore = useRegisterStore();

// Methods
function closeRegister() {
    registerStore.closeRegister();
}

function close() {
    router.push('/pos');
}
</script>

<style scoped>
.register-view {
    padding: 20px;
    background-color: #f5f5f5;
    min-height: calc(100vh - 74px);
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.export-options {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.export-options .btn {
    padding: 8px 15px;
    background-color: white;
    color: #333;
    border: 1px solid #ddd;
    display: flex;
    align-items: center;
    gap: 5px;
}

.print-btn i {
    color: #0d6efd;
}

.excel-btn i {
    color: #198754;
}

.csv-btn i {
    color: #0dcaf0;
}

.pdf-btn i {
    color: #dc3545;
}

.register-info {
    background-color: white;
    border-radius: 5px;
    padding: 15px;
}

.info-row {
    display: flex;
    margin-bottom: 10px;
}

.info-label {
    width: 200px;
    font-weight: bold;
    color: #333;
    font-size: 18px;
}

.info-value {
    color: #0d6efd;
    font-size: 18px;
}

.register-table {
    background-color: white;
    border-radius: 5px;
    overflow: hidden;
}

.table {
    width: 100%;
    border-collapse: collapse;
}

.table th {
    background-color: #f2f2f2;
    padding: 12px 15px;
    text-align: left;
    font-weight: bold;
}

.table td {
    padding: 10px 15px;
    border-bottom: 1px solid #eee;
    vertical-align: top;
}

.transaction-row,
.amount-row {
    padding: 5px 0;
}

.action-buttons {
    display: flex;
    justify-content: space-between;
}

.close-register-btn,
.close-btn {
    padding: 10px 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 16px;
}
</style>
