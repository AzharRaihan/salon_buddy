<script setup>
import { ref, onMounted } from 'vue'
import { $api } from '@/utils/api'
import amazonEchoDot from '@images/eCommerce/amazon-echo-dot.png'
import appleWatch from '@images/eCommerce/apple-watch.png'
import headphone from '@images/eCommerce/headphone.png'
import iphone from '@images/eCommerce/iphone.png'
import nike from '@images/eCommerce/nike.png'
import sonyDualsense from '@images/eCommerce/sony-dualsense.png'

const popularProducts = ref([
  {
    avatarImg: '',
    title: 'No Products',
    subtitle: 'Item: #NONE',
    stats: '$0.00',
  },
])

const fetchPopularProducts = async () => {
  try {
    const response = await $api('/dashboard/popular-products')
    
    if (response && response.products && response.products.length > 0) {
      popularProducts.value = response.products.map((product, index) => {
        const images = [
          iphone,
          nike,
          headphone,
          appleWatch,
          amazonEchoDot,
          sonyDualsense,
        ]
        
        return {
          avatarImg: product.avatarImg || '',
          title: product.title,
          subtitle: product.subtitle,
          stats: product.stats,
        }
      })
    } else {
      // Show default state if no products
      popularProducts.value = [
        {
          avatarImg: iphone,
          title: 'No Products',
          subtitle: 'Item: #NONE',
          stats: '$0.00',
        },
      ]
    }
  } catch (error) {
    console.error('Error fetching popular products:', error)
  }
}

onMounted(() => {
  fetchPopularProducts()
})
</script>

<template>
  <VCard
    title="Popular Products"
  >

    <VCardText>
      <VList class="card-list">
        <VListItem
          v-for="product in popularProducts"
          :key="product.title"
        >
          <template #prepend>
            <VAvatar
              size="46"
              rounded
              class="me-1"
              :image="product.avatarImg"
            />
          </template>

          <VListItemTitle class="font-weight-medium me-4">
            {{ product.title }}
          </VListItemTitle>
          <VListItemSubtitle class="me-4">
            {{ product.subtitle }}
          </VListItemSubtitle>

          <template #append>
            <div class="d-flex align-center">
              <span class="text-body-1">{{ product.stats }}</span>
            </div>
          </template>
        </VListItem>
      </VList>
    </VCardText>
  </VCard>
</template>

<style lang="scss" scoped>
.card-list {
  --v-card-list-gap: 1.25rem;
}
</style>
