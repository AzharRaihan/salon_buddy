<script setup>



// import AboutSection from '@/'
// import TeamSection from '@/components/frontend/TeamSection.vue'
// import TestimonialsSection from '@/components/frontend/TestimonialsSection.vue'
// import FrequentlyAskQuestion from '@/components/frontend/FrequentlyAskQuestion.vue' 

definePage({
  meta: {
    layout: 'frontend',
    public: true,
  },
})
</script>

<template>
  <div class="homepage">
    <h1>About Us</h1>
    <!-- About Section -->
    <!-- <AboutSection /> -->
    
    <!-- Team Section -->
    <!-- <TeamSection /> -->
    
    <!-- Testimonials -->
    <!-- <TestimonialsSection /> -->
  </div>
</template>

<style lang="scss">
.homepage {
  // Remove any default margins/padding
  margin: 0;
  padding: 0;
  
  // Ensure sections flow properly
  section {
    position: relative;
    z-index: 1;
  }
}
</style>
