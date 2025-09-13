<template>
  <div class="container py-3">
    <section>
      <div class="grid">
        <div 
          v-for="(product, index) in products" 
          :key="product.id"
          class="card"
          :data-aos="getAnimationTypeForIndex(index)"
          :data-aos-delay="getDelay(index)"
          :data-aos-duration="props.duration"
        >
          <div class="card-content">
            <h3>{{ product.name }}</h3>
            <p>{{ product.description }}</p>
            <div class="product-price">${{ product.price }}</div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref } from "vue"
import { useAOS, getStaggeredDelay, getAnimationType, animationPresets } from "@/composables/useAOS.js"

// Props for customization
const props = defineProps({
  animationType: {
    type: String,
    default: 'fade-up'
  },
  staggerDelay: {
    type: Number,
    default: 200
  },
  duration: {
    type: Number,
    default: 800
  },
  products: {
    type: Array,
    default: () => []
  }
})

// Use AOS composable
const { refreshAOS } = useAOS({
  duration: props.duration,
  offset: 100,
  once: true
})

// Sample product data (used if no products prop provided)
const defaultProducts = ref([
  { 
    id: 1, 
    name: "Premium Hair Care", 
    description: "Professional hair treatment for all hair types",
    price: 89.99
  },
  { 
    id: 2, 
    name: "Facial Treatment", 
    description: "Deep cleansing and rejuvenating facial",
    price: 129.99
  },
  { 
    id: 3, 
    name: "Manicure & Pedicure", 
    description: "Complete nail care and polish service",
    price: 75.99
  },
  { 
    id: 4, 
    name: "Massage Therapy", 
    description: "Relaxing full body massage session",
    price: 149.99
  },
  { 
    id: 5, 
    name: "Makeup Artistry", 
    description: "Professional makeup application",
    price: 95.99
  },
  { 
    id: 6, 
    name: "Eyebrow Shaping", 
    description: "Precise eyebrow design and styling",
    price: 45.99
  },
  { 
    id: 7, 
    name: "Hair Coloring", 
    description: "Expert hair coloring and highlights",
    price: 199.99
  },
  { 
    id: 8, 
    name: "Spa Package", 
    description: "Complete spa day experience",
    price: 299.99
  }
])

// Use provided products or default products
const products = props.products.length > 0 ? props.products : defaultProducts.value

// Animation types array for variety
const animationTypes = Object.keys(animationPresets)

// Get animation type based on index
const getAnimationTypeForIndex = (index) => {
  return getAnimationType(index, animationTypes)
}

// Get staggered delay for element
const getDelay = (index) => {
  return getStaggeredDelay(index, props.staggerDelay)
}
</script>

<style scoped>
.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem 1rem;
}

.grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 2rem;
  margin-top: 2rem;
}

.card {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 15px;
  padding: 2rem;
  color: white;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  position: relative;
  overflow: hidden;
}

.card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%);
  pointer-events: none;
}

.card:hover {
  transform: translateY(-5px);
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
}

.card-content {
  position: relative;
  z-index: 1;
}

.card h3 {
  font-size: 1.5rem;
  font-weight: 600;
  margin-bottom: 0.5rem;
  color: #ffffff;
}

.card p {
  font-size: 0.95rem;
  opacity: 0.9;
  margin-bottom: 1rem;
  line-height: 1.5;
}

.product-price {
  font-size: 1.25rem;
  font-weight: 700;
  color: #ffd700;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

/* Responsive design */
@media (max-width: 768px) {
  .grid {
    grid-template-columns: 1fr;
    gap: 1.5rem;
  }
  
  .card {
    padding: 1.5rem;
  }
  
  .card h3 {
    font-size: 1.25rem;
  }
}

/* Custom AOS animations */
[data-aos="slide-up"] {
  transform: translateY(50px);
  opacity: 0;
}

[data-aos="slide-up"].aos-animate {
  transform: translateY(0);
  opacity: 1;
}

[data-aos="slide-down"] {
  transform: translateY(-50px);
  opacity: 0;
}

[data-aos="slide-down"].aos-animate {
  transform: translateY(0);
  opacity: 1;
}

[data-aos="flip-left"] {
  transform: perspective(1000px) rotateY(-90deg);
  opacity: 0;
}

[data-aos="flip-left"].aos-animate {
  transform: perspective(1000px) rotateY(0deg);
  opacity: 1;
}
</style>
