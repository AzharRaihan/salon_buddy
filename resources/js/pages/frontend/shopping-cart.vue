<script setup>
import { onMounted } from 'vue'
import { useShoppingCartStore } from '@/stores/shoppingCart.js'
import BookingSamllBtn from '@/components/frontend/mini-components/BookingSamllBtn.vue' 
import BookingSamllBtn2 from '@/components/frontend/mini-components/BookingSamllBtn2.vue'
import CommonPageBanner from '@/components/frontend/CommonPageBanner.vue'
import AppAutocomplete from '../../@core/components/app-form-elements/AppAutocomplete.vue'

import { useI18n } from 'vue-i18n';
const { t } = useI18n()
definePage({
  meta: {
    layout: 'frontend',
    public: true,
  },
})

const cartStore = useShoppingCartStore()

onMounted(async () => {
  cartStore.loadFromStorage()
  await cartStore.initializeTaxSettings()
})
</script>

<template>
  <div>
    <!-- Common Page Banner -->
    <CommonPageBanner title="Shopping Cart" breadcrumb="Shopping Cart" />

    <!-- Shopping Cart Section -->
    <section class="shopping-cart-section default-section-padding-t">
      <div class="container">
        <div class="shopping-cart-table">
          <div class="row">
            <div class="col-lg-8">
              <div class="shopping-cart-details">
                <div class="shopping-cart-header">
                  <ul>
                    <li>SN</li>
                    <li>Product</li>
                    <li>Price</li>
                    <li>Quantity</li>
                    <li>Sub Total</li>
                  </ul>
                </div>
                <div class="shopping-cart-body">

                  <!-- Empty Cart State -->
                  <div v-if="!cartStore.hasItems" class="empty-cart-message text-center py-4">
                    <VIcon size="48" icon="tabler-shopping-cart-x" class="text-muted mb-3" />
                    <p class="text-muted">Your cart is empty</p>
                    <RouterLink to="/frontend/product" class="btn btn-primary">Continue Shopping</RouterLink>
                  </div>

                  
                  <!-- Cart Items -->
                  <ul v-else>
                    <li v-for="(item, index) in cartStore.items" :key="`${item.id}-${item.type}`">
                      <p>{{ index + 1 }}</p>
                      <div class="shopping-cart-item-name">
                        <img :src="item.image || '../../@frontend/images/cart_product.png'" :alt="item.name" width="50" height="50" class="me-2">
                        <p>{{ item.name }}</p>
                      </div>
                      <p>${{ item.price.toFixed(2) }}</p>
                      <div class="cart-inc-dec-group">
                        <button class="btn btn-default btn-rounded" @click="cartStore.decrementQuantity(item.id, item.type)">
                          <VIcon icon="tabler-minus" size="16" />
                        </button>
                        <p>{{ item.quantity }}</p>
                        <button class="btn btn-default btn-rounded" @click="cartStore.incrementQuantity(item.id, item.type)">
                          <VIcon icon="tabler-plus" size="16" />
                        </button>
                      </div>
                      <div class="cart-sub-total">
                        <p>${{ (item.price * item.quantity).toFixed(2) }}</p>
                        <button class="btn btn-default btn-rounded" @click="cartStore.removeItem(item.id, item.type)">
                          <VIcon icon="tabler-trash" size="16" />
                        </button>
                      </div>
                    </li>
                  </ul>
                </div>
                <div class="shopping-cart-footer" v-if="cartStore.hasItems">
                  <div class="d-flex justify-content-between">
                    <RouterLink :to="`/frontend/product`" class="btn btn-default btn-rounded">Return To Shop</RouterLink>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-lg-4">
              <div class="order-summary-section" v-if="cartStore.hasItems">
                <h3>Order Summary</h3>
                <div class="order-summary-table">
                  <ul>
                    <li>
                      <span>Subtotal</span>
                      <span class="text-end">{{ cartStore.subtotal.toFixed(2) }}</span>
                    </li>
                    <li>
                      <span>Tax</span>
                      <span class="text-end">{{ cartStore.taxAmount.toFixed(2) }}</span>
                    </li>
                    <!-- Show Delivery Area  list -->
                    <li>
                      <div class="flex-grow-1">
                        <label>Delivery Area</label>
                        <AppAutocomplete 
                          class="delivery-area-selection" 
                          :items="cartStore.deliveryAreas"
                          :placeholder="t('Select a delivery area')"
                          item-title="name"
                          item-value="id"
                          v-model="cartStore.selectedDeliveryAreaId"
                          @update:model-value="cartStore.setDeliveryArea"
                        />
                      </div>
                    </li>
                    <li>
                      <span>Delivery Charge</span>
                      <span class="text-end">
                        {{ cartStore.deliveryCharge.toFixed(2) }} 
                        <template v-if="cartStore.selectedDeliveryArea?.name">({{ cartStore.selectedDeliveryArea?.name }})</template>
                      </span>
                    </li>
                    <li class="total">
                      <span>Total</span>
                      <span class="text-end">{{ cartStore.total.toFixed(2) }}</span>
                    </li>
                  </ul>
                  <div class="d-flex justify-content-center order-summary-button-group" v-if="cartStore.selectedDeliveryAreaId">
                    <BookingSamllBtn :link="'/frontend/checkout'" :text="'Proceed to checkout'" />
                  </div>
                  <div class="d-flex justify-content-center order-summary-button-group" v-else>
                    <BookingSamllBtn2 :disabled="true" :text="'Proceed to checkout'" />
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>
