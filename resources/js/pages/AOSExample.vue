<template>
  <div class="aos-example-page">


    <!-- Products Section -->
    <section class="products-section">
      <div class="container">
        <h2 class="section-title" data-aos="fade-up">Our Services</h2>
        <p class="section-description" data-aos="fade-up" data-aos-delay="200">
          Discover our premium salon services with smooth scroll animations
        </p>
        
        <!-- AnimationAOS Component -->
        <AnimationAOS 
          :stagger-delay="300"
          :duration="1000"
        />
      </div>
    </section>

    <!-- Features Section -->
    <section class="features-section">
      <div class="container">
        <h2 class="section-title" data-aos="fade-up">Why Choose Us?</h2>
        
        <div class="features-grid">
          <div 
            v-for="(feature, index) in features" 
            :key="feature.id"
            class="feature-card"
            :data-aos="feature.animation"
            :data-aos-delay="index * 200"
          >
            <div class="feature-icon">
              <i :class="feature.icon"></i>
            </div>
            <h3>{{ feature.title }}</h3>
            <p>{{ feature.description }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials-section">
      <div class="container">
        <h2 class="section-title" data-aos="fade-up">What Our Clients Say</h2>
        
        <div class="testimonials-grid">
          <div 
            v-for="(testimonial, index) in testimonials" 
            :key="testimonial.id"
            class="testimonial-card"
            :data-aos="testimonial.animation"
            :data-aos-delay="index * 300"
          >
            <div class="testimonial-content">
              <p>"{{ testimonial.quote }}"</p>
              <div class="testimonial-author">
                <strong>{{ testimonial.author }}</strong>
                <span>{{ testimonial.role }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import AnimationAOS from '@/components/frontend/AnimationAOS.vue'
import { useAOS } from '@/composables/useAOS.js'

// Initialize AOS
useAOS({
  duration: 800,
  offset: 100,
  once: true
})

// Features data
const features = ref([
  {
    id: 1,
    title: "Professional Staff",
    description: "Our certified professionals provide the best service",
    icon: "fas fa-user-tie",
    animation: "fade-right"
  },
  {
    id: 2,
    title: "Premium Products",
    description: "We use only the highest quality products",
    icon: "fas fa-gem",
    animation: "fade-up"
  },
  {
    id: 3,
    title: "Modern Equipment",
    description: "State-of-the-art equipment for best results",
    icon: "fas fa-tools",
    animation: "fade-left"
  },
  {
    id: 4,
    title: "Relaxing Environment",
    description: "Comfortable and peaceful atmosphere",
    icon: "fas fa-spa",
    animation: "zoom-in"
  }
])

// Testimonials data
const testimonials = ref([
  {
    id: 1,
    quote: "Amazing service! The staff is professional and the results are outstanding.",
    author: "Sarah Johnson",
    role: "Regular Client",
    animation: "slide-up"
  },
  {
    id: 2,
    quote: "Best salon experience I've ever had. Highly recommended!",
    author: "Michael Chen",
    role: "New Client",
    animation: "slide-up"
  },
  {
    id: 3,
    quote: "The attention to detail and customer care is exceptional.",
    author: "Emily Davis",
    role: "VIP Client",
    animation: "slide-up"
  }
])
</script>

<style scoped>
.aos-example-page {
  min-height: 100vh;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1rem;
}

/* Hero Section */
.hero-section {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 8rem 0;
  text-align: center;
}

.hero-title {
  font-size: 3.5rem;
  font-weight: 700;
  margin-bottom: 1rem;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

.hero-subtitle {
  font-size: 1.25rem;
  opacity: 0.9;
  max-width: 600px;
  margin: 0 auto;
}

/* Products Section */
.products-section {
  padding: 6rem 0;
  background: #f8f9fa;
}

.section-title {
  font-size: 2.5rem;
  font-weight: 600;
  text-align: center;
  margin-bottom: 1rem;
  color: #333;
}

.section-description {
  font-size: 1.1rem;
  text-align: center;
  color: #666;
  margin-bottom: 3rem;
  max-width: 600px;
  margin-left: auto;
  margin-right: auto;
}

/* Features Section */
.features-section {
  padding: 6rem 0;
  background: white;
}

.features-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 2rem;
  margin-top: 3rem;
}

.feature-card {
  text-align: center;
  padding: 2rem;
  border-radius: 15px;
  background: #f8f9fa;
  transition: transform 0.3s ease;
}

.feature-card:hover {
  transform: translateY(-5px);
}

.feature-icon {
  font-size: 3rem;
  color: #667eea;
  margin-bottom: 1rem;
}

.feature-card h3 {
  font-size: 1.5rem;
  font-weight: 600;
  margin-bottom: 1rem;
  color: #333;
}

.feature-card p {
  color: #666;
  line-height: 1.6;
}

/* Testimonials Section */
.testimonials-section {
  padding: 6rem 0;
  background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
  color: white;
}

.testimonials-section .section-title {
  color: white;
}

.testimonials-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 2rem;
  margin-top: 3rem;
}

.testimonial-card {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  border-radius: 15px;
  padding: 2rem;
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.testimonial-content p {
  font-size: 1.1rem;
  line-height: 1.6;
  margin-bottom: 1.5rem;
  font-style: italic;
}

.testimonial-author strong {
  display: block;
  font-size: 1.1rem;
  margin-bottom: 0.25rem;
}

.testimonial-author span {
  opacity: 0.8;
  font-size: 0.9rem;
}

/* Responsive Design */
@media (max-width: 768px) {
  .hero-title {
    font-size: 2.5rem;
  }
  
  .hero-subtitle {
    font-size: 1rem;
  }
  
  .section-title {
    font-size: 2rem;
  }
  
  .features-grid,
  .testimonials-grid {
    grid-template-columns: 1fr;
  }
}
</style>
