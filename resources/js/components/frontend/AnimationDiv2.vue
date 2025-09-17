<template>
    <section ref="productSection2" class="product-grid">
      <div 
      v-for="(product, index) in products2" 
      :key="product.id"
      class="card"
      :ref="el => cards2[index] = el"
    >
      {{ product.name }}
    </div>
    </section>
  </template>
  
  <script setup>
import { ref, onMounted } from "vue"
import gsap from "gsap"
import { ScrollTrigger } from "gsap/ScrollTrigger"

gsap.registerPlugin(ScrollTrigger)

const products2 = ref([
  { id: 1, name: "Product 1" },
  { id: 2, name: "Product 2" },
  { id: 3, name: "Product 3" },
  { id: 4, name: "Product 4" },
  { id: 5, name: "Product 5" },
  { id: 6, name: "Product 6" },
  { id: 7, name: "Product 7" },
  { id: 8, name: "Product 8" }
])

// must be a reactive array for refs
const cards2 = ref([])
const productSection2 = ref(null)

onMounted(() => {
  gsap.from(cards2.value, {
    opacity: 0,
    y: 50,
    stagger: 0.2,
    duration: 1,
    ease: "power2.out",
    scrollTrigger: {
      trigger: productSection2.value,
      start: "top 80%",
    }
  })
})
</script>
  
  <style>
  /* .product-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin: 100px 0;
  } */
  
  /* .card {
    background: #740000 !important;
    padding: 20px !important;
    border-radius: 10px !important;
    text-align: center !important;
  } */
  </style>