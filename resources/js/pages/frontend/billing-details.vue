<script setup>
import BookAppointmentBtn from '@/components/frontend/mini-components/BookAppointmentBtn.vue'
import CommonPageBanner from '@/components/frontend/CommonPageBanner.vue'

definePage({
  meta: {
    layout: 'frontend',
    public: true,
  },
})
</script>

<template>
  <div>
    <!-- Common Page Banner -->
    <CommonPageBanner title="Billing Details" breadcrumb="Billing Details" />

    <!-- Billing Details Section -->
    <section class="shopping-cart-section default-section-padding-t">
      <div class="container">
        <div class="shopping-cart-table">
          <div class="row justify-content-center">
            <div class="col-lg-8">
              <div class="billing-details-page">
                <div class="contact-infor-wrap">
                  <div class="contact-infor-form">
                    <div class="row">
                      <div class="col-lg-6 mb-3">
                        <div class="form-group">
                          <label for="customer_name">Customer Name <span class="text-danger">*</span></label>
                          <input type="text" class="form-control" id="customer_name" placeholder="Customer name">
                        </div>
                      </div>
                      <div class="col-lg-6 mb-3">
                        <div class="form-group">
                          <label for="email">Email Address <span class="text-danger">*</span></label>
                          <input type="text" class="form-control" id="email" placeholder="Enter your email address">
                        </div>
                      </div>
                      <div class="col-lg-12 mb-3">
                        <div class="form-group">
                          <label for="card_number">Card Number <span class="text-danger">*</span></label>
                          <input type="text" class="form-control" id="card_number" placeholder="Enter your card number">
                        </div>
                      </div>
                      <div class="col-lg-6 mb-3">
                        <div class="form-group">
                          <label for="expiry_date">Expiry Date <span class="text-danger">*</span></label>
                          <input type="text" class="form-control" id="expiry_date" placeholder="Enter your expiry date">
                        </div>
                      </div>
                      <div class="col-lg-6 mb-3">
                        <div class="form-group">
                          <label for="cvv">CVV <span class="text-danger">*</span></label>
                          <input type="text" class="form-control" id="cvv" placeholder="Enter your cvv">
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <BookAppointmentBtn link="#" text="Submit" />
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

