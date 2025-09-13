<template>
    <div v-if="modelValue" class="bill-modal">
      <div class="bill-content" ref="printSection">
        <div class="text-center mb-2">
          <img :src="`${apiBaseUrl}/public/assets/images/${order.company_logo}`" alt="Company Logo" class="company-logo img-fluid">
          <p class="mb-0">{{ t('Location') }}: {{ order.branch_address }}</p>
        </div>
        <div class="info">
          <div>{{ t('Date') }}: {{ formatDate(order.created_at) }}</div>
          <div>{{ t('Invoice No') }}: {{ order.reference_no }}</div>
          <div v-if="order.customer_name">{{ t('Customer') }}: {{ order.customer_name }}</div>
          <div v-if="order.user_name">{{ t('Cashier') }}: {{ order.user_name }}</div>
        </div>
        <table class="items">
          <thead>
            <tr>
              <th class="text-left">{{ t('Item') }}</th>
              <th class="text-center">{{ t('Qty') }}</th>
              <th class="text-center">{{ t('Price') }}</th>
              <th class="text-right">{{ t('Total') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in order.sale_details" :key="item.id">
              <td class="text-left">{{ item.item_name }}</td>
              <td class="text-center">{{ formatQuantity(item.quantity) }}</td>
              <td class="text-center">{{ formatNumberInvoice(item.unit_price) }}</td>
              <td class="text-right">{{ formatNumberInvoice((item.quantity * item.unit_price) - item.promotion_discount) }}</td>
            </tr>
          </tbody>
        </table>
        <div class="devider"></div>
        <div class="totals">
          <div><span>{{ t('Subtotal') }}:</span> <span>{{ formatNumberInvoice(order.subtotal ?? calcSubtotal(order.sale_details)) }}</span></div>
          <!-- <div v-if="order.total_tax"><span>{{ t('Tax') }}:</span> <span>{{ formatNumberInvoice(order.total_tax) }}</span></div> -->

          <div class="devider" v-if="order.tax_breakdown"></div>
          <div v-if="order.tax_breakdown" class="tax-breakdown" v-for="(amount, taxName) in parseTaxBreakdown(order.tax_breakdown)" :key="taxName">
            <span>{{ taxName }}:</span> <span>{{ formatNumberInvoice(amount) }}</span>
          </div>
          <div class="devider" v-if="order.tax_breakdown"></div>

          <div v-if="order.discount > 0"><span>{{ t('Discount') }}:</span> <span>{{ formatNumberInvoice(order.discount) }}</span></div>
          <div class="grand"><span>{{ t('Total') }}:</span> <span>{{ formatNumberInvoice(order.total_payable) }}</span></div>
          <div v-if="order.total_paid"><span>{{ t('Paid') }}:</span> <span>{{ formatNumberInvoice(order.total_paid) }}</span></div>
          <div v-if="order.total_due > 0"><span>{{ t('Due') }}:</span> <span>{{ formatNumberInvoice(order.total_due) }}</span></div>
          <div v-if="order.payment_method_name"><span>{{ t('Payment') }}:</span> <span>{{ order.payment_method_name }}</span></div>
        </div>
        <div class="devider"></div>
        <div class="center print-btns no-print">
          <button @click="printBill" class="btn btn-primary btn-primary-2 me-2">
            <VIcon icon="tabler-printer"></VIcon>
            {{ t('Print') }}
          </button>
          <button @click="$emit('update:modelValue', false)" class="btn btn-secondary btn-secondary-2">
            <VIcon icon="tabler-x"></VIcon>
            {{ t('Close') }}
          </button>
        </div>
      </div>
    </div>
  </template>
  
<script setup>
import { computed } from 'vue'
import { useCompanyFormatters } from '@/composables/useCompanyFormatters';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const apiBaseUrl = import.meta.env.VITE_APP_URL

const { fetchCompanySettings, formatDate, formatAmount, formatNumber, formatNumberInvoice, getSerialNumber } = useCompanyFormatters()
const formatQuantity = (value) => {
  // Convert to number first
  let num = Number(value)
  // Remove trailing zeros after decimal
  return num % 1 === 0 ? num.toString() : num.toString().replace(/\.?0+$/, "")
}

const parseTaxBreakdown = (taxBreakdown) => {
  if (!taxBreakdown) return {};
  try {
    return typeof taxBreakdown === 'string' ? JSON.parse(taxBreakdown) : taxBreakdown;
  } catch (error) {
    console.error('Error parsing tax breakdown:', error);
    return {};
  }
}
  const props = defineProps({
    modelValue: Boolean,
    order: Object
  })

  
  function printBill() {
    const printContent = document.querySelector('.bill-content').innerHTML;
    const printWindow = window.open('', '', 'width=400,height=600');

    printWindow.document.write(`
      <html>
        <head>
          <title>Print Invoice</title>
          <style>
          @import url('https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap');
            @page {
              size: 56mm auto;
              margin: 0;
            }
            body {
              font-family: "Inter", sans-serif;
              font-size: 11px;
              margin: 0 auto;
              padding: 0;
              width: 56mm;
            }
            .bill-content {
              width: 56mm;
              background: #fff;
              padding: 8px;
              font-size: 11px;
              border-radius: 4px;
              box-shadow: 0 2px 8px #0002;
            }
            .center { 
              text-align: center; 
            }
            .items { 
              width: 100%; 
              border-collapse: collapse;
              margin-top: 10px;
            }
            .items th { 
              border-top: 1px dashed #878787;
              border-bottom: 1px dashed #878787;
            }
            .items th, .items td { 
              padding: 2px 0; 
              font-size: 11px; 
            }
            .items td { 
              border-bottom: 1px solid #eee;
            }
            .items tr:last-child td { 
              border-bottom: none;
            }
            .totals { 
              margin-top: 8px;
              width: 70%;
              margin-left: auto;
            }
            .totals div {
              display: flex;
              justify-content: space-between;
            }
            .grand { 
              font-weight: bold; 
            }
            .print-btns { 
              margin-top: 10px; 
            }
            .devider { 
              border-bottom: 1px dashed #878787;
              margin: 5px 0;
            }
            .btn-primary, .btn-secondary {
              font-size: 12px;
              font-weight: 500;
            }
            .no-print {
              display: none !important;
            }
            .text-center {
              text-align: center;
            }
            .mb-0 {
              margin-bottom: 0;
            }
            .mb-2 {
              margin-bottom: 8px;
            }
            .items tbody td {
              vertical-align: sub !important;
            }
          </style>
        </head>
        <body>
          ${printContent}
        </body>
      </html>
    `);

    printWindow.document.close();
    printWindow.focus();

    setTimeout(() => {
      printWindow.print();
    }, 500);
  }


  function calcSubtotal(details) {
    if (!details) return 0
    return details.reduce((sum, item) => sum + ((item.quantity * item.unit_price) - item.promotion_discount), 0)
  }

  onMounted(() => {
    fetchCompanySettings()
  })
</script>
  
<style scoped>
  .bill-modal {
    position: fixed;
    z-index: 9999;
    left: 0; top: 0; right: 0; bottom: 0;
    background: rgba(0,0,0,0.3);
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .bill-content {
    width: 56mm;
    background: #fff;
    padding: 8px;
    font-size: 11px;
    border-radius: 4px;
    box-shadow: 0 2px 8px #0002;
  }
  .center { 
    text-align: center; 
  }
  .items { 
    width: 100%; 
    border-collapse: collapse;
    margin-top: 10px;
  }
  .items th { 
    border-top: 1px dashed #878787;
    border-bottom: 1px dashed #878787;
  }
  .items th, .items td { 
    padding: 2px 0; 
    font-size: 11px; 
  }
  .items td { 
    border-bottom: 1px solid #eee;
  }
  .items tr:last-child td { 
    border-bottom: none;
  }
  .totals { 
    margin-top: 8px;
    width: 70%;
    margin-left: auto;
  }
  .totals div {
    display: flex;
    justify-content: space-between;
  }
  .grand { 
    font-weight: bold; 
  }
  .print-btns { 
    margin-top: 10px; 
  }
  .devider { 
    border-bottom: 1px dashed #878787;
    margin: 5px 0;
  }
  .btn-primary, .btn-secondary {
    font-size: 12px;
    font-weight: 500;
  }
  .items tbody td {
    vertical-align: sub !important;
  }
</style>
