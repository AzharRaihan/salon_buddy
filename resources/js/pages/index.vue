<script setup>
import HeroSection from '@/components/frontend/HeroSection.vue'
import FeaturedServices from '@/components/frontend/FeaturedServices.vue'
import SpecialOffersHome from '@/components/frontend/SpecialOffersHome.vue'
import SalonProducts from '@/components/frontend/SalonProducts.vue'
import AboutSection from '@/components/frontend/AboutSection.vue'
import WorkingProcess from '@/components/frontend/WorkingProcess.vue'
import TeamSection from '@/components/frontend/TeamSection.vue'
import TestimonialsSection from '@/components/frontend/TestimonialsSection.vue'
import FrequentlyAskQuestion from '@/components/frontend/FrequentlyAskQuestion.vue' 
import Gallery from '@/components/frontend/Gallery.vue'
import AnimationDiv from '@/components/frontend/AnimationDiv.vue'
import AnimationDiv2 from '@/components/frontend/AnimationDiv2.vue'
import AnimatePackage from '@/components/frontend/AnimatePackage.vue'
import AOSExample from '../pages/AOSExample.vue'
definePage({
  meta: {
    layout: 'frontend',
    public: true,
  },
})
</script>

<template>
  <div class="homepage">
    <!-- Hero Section -->
    <HeroSection />
    
    <!-- Featured Services -->
    <FeaturedServices />
    
    <!-- Special Offers -->
    <SpecialOffersHome 
      mode="homepage" 
      :itemLimit="10" 
      sectionClass="special-offers default-section-padding-b"
      buttonText="Buy This Package"
    />

    <!-- Animation Div -->
    <AnimationDiv />
    <AnimationDiv2 />
    <AnimatePackage />
    <AOSExample />
    
    <!-- Salon Products -->
    <SalonProducts 
      mode="homepage" 
      :itemLimit="8" 
      buttonText="View All Products"
    />

    <!-- Working Process -->
    <WorkingProcess classSectionPadding="default-section-padding-t" />
    
    <!-- About Section -->
    <AboutSection :showAboutUs="true" :showUserCounter="true" :classBind="'default-section-padding-t'" />

    <!-- Gallery -->
    <Gallery />
    
    <!-- Team Section -->
    <TeamSection :isHome="true" :headerShow="true" :classBind="'home-team-section'" />
    

    <!-- Testimonials -->
    <TestimonialsSection :paginationShow="true" classSectionPadding="default-section-padding" />

    <!-- Frequently Ask Question -->
    <FrequentlyAskQuestion />

    <!-- <AnimationDiv /> -->
  </div>



</template>

<style lang="scss">
.homepage {
  margin: 0;
  padding: 0;
  
  section {
    position: relative;
    z-index: 1;
  }
}
</style>
