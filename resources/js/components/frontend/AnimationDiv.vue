<template>
  <div class="container py-3">

    <section ref="productSection">
      <transition-group name="fade-up" tag="div" class="grid">
        <div 
          v-for="(product, index) in products" 
          :key="product.id"
          v-show="visible"
          class="card"
          :style="{ transitionDelay: (index * 0.2) + 's' }"
        >
          {{ product.name }}
        </div>
      </transition-group>
    </section>
  </div>
  
  </template>
  
  <script setup>
  import { ref, onMounted } from "vue"
  
  const products = ref([
    { id: 1, name: "Product 1" },
    { id: 2, name: "Product 2" },
    { id: 3, name: "Product 3" },
    { id: 4, name: "Product 4" },
    { id: 5, name: "Product 5" },
    { id: 6, name: "Product 6" },
    { id: 7, name: "Product 7" },
    { id: 8, name: "Product 8" }
  ])
  
  const visible = ref(false)
  const productSection = ref(null)
  
  onMounted(() => {
    const observer = new IntersectionObserver(
      (entries) => {
        if (entries[0].isIntersecting) {
          visible.value = true
          observer.disconnect()
        }
      },
      { threshold: 0.2 }
    )
    observer.observe(productSection.value)
  })
  </script>
  
  <style>
  .grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
  }
  
  .card {
    /* opacity: 0; */
    /* transform: translateY(30px); */
    color: white
  }
  
  .fade-up-enter-active {
    transition: all 0.8s ease;
  }
  
  .fade-up-enter-from {
    opacity: 0;
    transform: translateY(30px);
  }
  
  .fade-up-enter-to {
    opacity: 1;
    transform: translateY(0);
  }
  </style>
  