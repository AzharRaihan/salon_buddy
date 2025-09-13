<script setup>
import { ref, computed, onMounted } from 'vue'
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
    <CommonPageBanner title="Verify Email" breadcrumb="Verify Email" />

    <!-- Forgot Password Section -->
    <section class="login-wrapper default-section-padding-t verify-email-wrapper">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-12 col-sm-10 col-md-8 col-lg-6">
            <div class="login-form forgot-password-wrapper verify-email-wrapper">
              <div class="message-header-icon">
                <VIcon icon="tabler-message" />
              </div>
              <p class="message-header-text">Signup successfully, Please check your email inbox/spam to verify your email and activate your account</p>
              <div class="d-flex justify-content-center verify-email-button-wrapper">
                <button class="btn btn-varify me-2 trasparent-btn">
                  <VIcon icon="tabler-arrow-narrow-left" size="18" class="me-2" />
                  Back to Login
                </button>
                <button class="btn btn-varify ms-2 color-btn">
                  Resend Email
                  <VIcon icon="tabler-mail-opened" size="18" class="ms-2" />
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

