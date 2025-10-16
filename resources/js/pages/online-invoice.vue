<script setup>
import { onMounted, ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useShoppingCartStore } from '@/stores/shoppingCart.js'
import CommonPageBanner from '@/components/frontend/CommonPageBanner.vue'
import { useCompanyFormatters } from '@/composables/useCompanyFormatters'
import { toast } from 'vue3-toastify'
import { useI18n } from 'vue-i18n'
const { t } = useI18n()
definePage({
  meta: {
    layout: 'frontend',
    public: true,
  },
})

const route = useRoute()
const router = useRouter()
const cartStore = useShoppingCartStore()
const { formatAmount, formatDate, formatNumberInvoice } = useCompanyFormatters()

// Data
const isLoading = ref(false)
const isSubmitting = ref(false)
const saleData = ref(null)
const ratings = ref({})
const comments = ref({})
const submitSuccess = ref(false)

const apiBaseUrl = import.meta.env.VITE_APP_URL

const referenceNo = computed(() => route.query.reference)
const customerId = computed(() => route.query.customerId)

onMounted(async () => {
  cartStore.clearCart()
  if (referenceNo.value && customerId.value) {
    await fetchSaleDetails()
  } else {
    router.push('/')
  }
})

const fetchSaleDetails = async () => {
  try {
    isLoading.value = true
    const response = await $api('/sale-details-for-rating', {
      params: {
        reference: referenceNo.value,
        customerId: customerId.value
      }
    })
    if (response.success) {
      saleData.value = response.data
      response.data.sale_details.forEach(detail => {
        ratings.value[detail.sale_detail_id] = 0
        comments.value[detail.sale_detail_id] = ''
      })
    } else throw new Error(response.message || 'Failed to fetch sale details')
  } catch {
    router.push('/')
  } finally {
    isLoading.value = false
  }
}

const setRating = (id, value) => (ratings.value[id] = value)
const setComment = (id, value) => (comments.value[id] = value)

const submitRatings = async () => {
  try {
    isSubmitting.value = true
    const ratingsData = []
    Object.keys(ratings.value).forEach(saleDetailId => {
      if (ratings.value[saleDetailId] > 0) {
        const detail = saleData.value.sale_details.find(d => d.sale_detail_id == saleDetailId)
        ratingsData.push({
          sale_detail_id: saleDetailId,
          item_id: detail.item_id,
          employee_id: detail?.employee_id,
          rating: ratings.value[saleDetailId],
          comment: comments.value[saleDetailId] || ''
        })
      }
    })

    if (ratingsData.length === 0) {
      toast('Please select at least one rating before submitting.', { type: 'error' })
      return
    }

    const response = await $api('/submit-service-ratings', {
      method: 'POST',
      body: {
        ratings: ratingsData,
        sale_id: saleData.value.sale.id,
        customer_id: saleData.value.customer.id
      },
    })
    if (response.success) {
      toast('Ratings submitted successfully.', { type: 'success' })
      submitSuccess.value = true
    } else throw new Error(response.message || 'Failed to submit ratings')
  } catch (error) {
    toast(error.message || 'Failed to submit ratings', { type: 'error' })
  } finally {
    isSubmitting.value = false
  }
}

const getStarRating = id => ratings.value[id] || 0

const formatQuantity = value => {
  const num = Number(value)
  return num % 1 === 0 ? num.toString() : num.toString().replace(/\.?0+$/, '')
}



import html2canvas from 'html2canvas'
import jsPDF from 'jspdf'

const downloadInvoice = async () => {
  const invoiceElement = document.getElementById('download-area')

  if (!invoiceElement) {
    toast('Invoice section not found!', { type: 'error' })
    return
  }

  try {
    isSubmitting.value = true
    const canvas = await html2canvas(invoiceElement, {
      scale: 2, // higher resolution
      useCORS: true, // allow images from your domain
    })

    const imgData = canvas.toDataURL('image/png')
    const pdf = new jsPDF('p', 'mm', 'a4')
    const pageWidth = pdf.internal.pageSize.getWidth()
    const pageHeight = pdf.internal.pageSize.getHeight()

    const imgWidth = pageWidth
    const imgHeight = (canvas.height * imgWidth) / canvas.width

    let heightLeft = imgHeight
    let position = 0

    pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight)
    heightLeft -= pageHeight

    while (heightLeft > 0) {
      position = heightLeft - imgHeight
      pdf.addPage()
      pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight)
      heightLeft -= pageHeight
    }

    const fileName = `${saleData.value?.sale?.reference_no || 'invoice'}.pdf`
    pdf.save(fileName)
    toast('Invoice downloaded successfully', { type: 'success' })
  } catch (error) {
    console.error(error)
    toast('Failed to download invoice', { type: 'error' })
  } finally {
    isSubmitting.value = false
  }
}

</script>

<template>
  <div>
    <CommonPageBanner title="Rate Our Services" breadcrumb="Service Rating" />

    <section class="rating-section default-section-padding">
      <div class="container">
        <!-- ✅ Online Invoice + Rating Form -->
        <div v-if="saleData" class="row justify-content-center" id="download-area">
          <div class="col-lg-10">

            <!-- 🧾 Online Invoice Section -->
            <div class="invoice-card mb-5">
              <div class="bill-content p-3 rounded shadow-sm bg-white">
                <div class="text-center mb-3">
                  <img :src="`${apiBaseUrl}/public/assets/images/${saleData.company.logo}`" alt="Company Logo" class="company-logo mb-2" style="max-height:60px">
                  <p class="mb-0">{{ t('Location') }}: {{ saleData.branch.address }}</p>
                </div>

                <div class="info mb-3">
                  <div>{{ t('Date') }}: {{ formatDate(saleData.sale.order_date) }}</div>
                  <div>{{ t('Invoice No') }}: {{ saleData.sale.reference_no }}</div>
                  <div>{{ t('Customer') }}: {{ saleData.customer.name }}</div>
                </div>

                <table class="items w-100">
                  <thead>
                    <tr>
                      <th class="text-start">{{ t('Item') }}</th>
                      <th class="text-center">{{ t('Qty') }}</th>
                      <th class="text-center">{{ t('Price') }}</th>
                      <th class="text-end">{{ t('Total') }}</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="item in saleData.sale_details" :key="item.sale_detail_id">
                      <td>{{ item.item_name }}</td>
                      <td class="text-center">{{ formatQuantity(item.quantity) }}</td>
                      <td class="text-center">{{ formatNumberInvoice(item.unit_price) }}</td>
                      <td class="text-end">{{ formatNumberInvoice((item.quantity * item.unit_price) - item.promotion_discount) }}</td>
                    </tr>
                  </tbody>
                </table>

                <div class="divider-2"></div>

                <div class="totals ms-auto w-75">
                  <div><span>{{ t('Subtotal') }}:</span> <span>{{ formatNumberInvoice(saleData.sale.subtotal) }}</span></div>
                  <div v-if="saleData.sale.discount > 0"><span>{{ t('Discount') }}:</span> <span>{{ formatNumberInvoice(saleData.sale.discount) }}</span></div>
                  <div v-if="saleData.sale.total_tips > 0"><span>{{ t('Tips') }}:</span> <span>{{ formatNumberInvoice(saleData.sale.total_tips) }}</span></div>
                  <div class="grand"><span>{{ t('Total') }}:</span> <span>{{ formatNumberInvoice(saleData.sale.total_payable) }}</span></div>
                  <div><span>{{ t('Paid') }}:</span> <span>{{ formatNumberInvoice(saleData.sale.total_paid) }}</span></div>
                  <div v-if="saleData.sale.total_due > 0"><span>{{ t('Due') }}:</span> <span>{{ formatNumberInvoice(saleData.sale.total_due) }}</span></div>
                  <div v-if="saleData.sale.payment_method_name"><span>{{ t('Payment') }}:</span> <span>{{ saleData.sale.payment_method_name }}</span></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div v-else class="text-center py-5">
          <VIcon size="120" icon="tabler-alert-circle" class="text-danger mb-3" />
          <h2>Sale Not Found</h2>
          <RouterLink to="/" class="btn btn-primary mt-3">
            <VIcon icon="tabler-arrow-narrow-left" class="me-2" /> Back to Home
          </RouterLink>
        </div>

        <div class="text-center">
          <VBtn @click="downloadInvoice">Download Invoice</VBtn>
        </div>

      </div>
    </section>
  </div>
</template>

<style scoped>
.invoice-card .bill-content {
  width: 300px;
  max-width: 100%;
  background: #fff;
  padding: 8px;
  font-size: 11px;
  border-radius: 6px;
  box-shadow: 0 2px 8px #0002;
  margin: 0 auto;
}

.items th, .items td {
  padding: 2px 0;
  font-size: 11px;
  /* border-bottom: 1px solid #eee; */
}
.items th {
  border-top: 1px dashed #878787;
  border-bottom: 1px dashed #878787;
}
.divider-2 {
  border-bottom: 1px dashed #878787;
  margin: 5px 0;
}
.totals div {
  display: flex;
  justify-content: space-between;
}
.totals .grand {
  font-weight: bold;
}

/* Rating styles */
/* Buttons */
.btn-booking {
  background-color: var(--primary-bg-color);
  color: #fff;
  border: none;
  padding: 10px 20px;
  border-radius: 8px;
}
.btn-booking:hover {
  background-color: var(--primary-bg-hover-color);
}
</style>