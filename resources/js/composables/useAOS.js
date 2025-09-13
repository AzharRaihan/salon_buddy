import { onMounted, onUnmounted } from 'vue'
import AOS from 'aos'

/**
 * AOS (Animate On Scroll) composable for Vue 3
 * Provides reactive AOS functionality with proper lifecycle management
 */
export function useAOS(options = {}) {
  const defaultOptions = {
    duration: 800,
    easing: 'ease-in-out',
    once: true,
    offset: 100,
    delay: 0,
    ...options
  }

  // Initialize AOS
  const initAOS = () => {
    AOS.init(defaultOptions)
  }

  // Refresh AOS (useful when adding new elements dynamically)
  const refreshAOS = () => {
    AOS.refresh()
  }

  // Refresh AOS on window resize
  const handleResize = () => {
    AOS.refresh()
  }

  // Lifecycle management
  onMounted(() => {
    initAOS()
    window.addEventListener('resize', handleResize)
  })

  onUnmounted(() => {
    window.removeEventListener('resize', handleResize)
    AOS.refresh()
  })

  return {
    initAOS,
    refreshAOS,
    AOS
  }
}

/**
 * Animation presets for common use cases
 */
export const animationPresets = {
  fadeUp: {
    animation: 'fade-up',
    duration: 800,
    delay: 0
  },
  fadeDown: {
    animation: 'fade-down', 
    duration: 800,
    delay: 0
  },
  fadeLeft: {
    animation: 'fade-left',
    duration: 800,
    delay: 0
  },
  fadeRight: {
    animation: 'fade-right',
    duration: 800,
    delay: 0
  },
  zoomIn: {
    animation: 'zoom-in',
    duration: 800,
    delay: 0
  },
  slideUp: {
    animation: 'slide-up',
    duration: 800,
    delay: 0
  },
  slideDown: {
    animation: 'slide-down',
    duration: 800,
    delay: 0
  },
  flipLeft: {
    animation: 'flip-left',
    duration: 800,
    delay: 0
  }
}

/**
 * Generate staggered animation delays for multiple elements
 * @param {number} index - Element index
 * @param {number} staggerDelay - Delay between elements in ms
 * @returns {number} Calculated delay
 */
export function getStaggeredDelay(index, staggerDelay = 200) {
  return index * staggerDelay
}

/**
 * Get animation type based on index for variety
 * @param {number} index - Element index
 * @param {Array} animationTypes - Array of animation types
 * @returns {string} Animation type
 */
export function getAnimationType(index, animationTypes = Object.keys(animationPresets)) {
  return animationTypes[index % animationTypes.length]
}
