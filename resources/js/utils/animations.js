/**
 * Simple Animation Utilities for Vue 3
 * Replaces @animxyz/vue with native Vue transitions and CSS animations
 */

// Animation presets
export const animationPresets = {
  // Fade animations
  fadeIn: {
    enterActiveClass: 'animate-fade-in',
    enterFromClass: 'opacity-0',
    enterToClass: 'opacity-100'
  },
  
  fadeInUp: {
    enterActiveClass: 'animate-fade-in-up',
    enterFromClass: 'opacity-0 transform translate-y-8',
    enterToClass: 'opacity-100 transform translate-y-0'
  },
  
  fadeInDown: {
    enterActiveClass: 'animate-fade-in-down',
    enterFromClass: 'opacity-0 transform -translate-y-8',
    enterToClass: 'opacity-100 transform translate-y-0'
  },
  
  fadeInLeft: {
    enterActiveClass: 'animate-fade-in-left',
    enterFromClass: 'opacity-0 transform -translate-x-8',
    enterToClass: 'opacity-100 transform translate-x-0'
  },
  
  fadeInRight: {
    enterActiveClass: 'animate-fade-in-right',
    enterFromClass: 'opacity-0 transform translate-x-8',
    enterToClass: 'opacity-100 transform translate-x-0'
  },
  
  // Scale animations
  scaleIn: {
    enterActiveClass: 'animate-scale-in',
    enterFromClass: 'opacity-0 transform scale-95',
    enterToClass: 'opacity-100 transform scale-100'
  },
  
  scaleInUp: {
    enterActiveClass: 'animate-scale-in-up',
    enterFromClass: 'opacity-0 transform scale-95 translate-y-8',
    enterToClass: 'opacity-100 transform scale-100 translate-y-0'
  },
  
  // Slide animations
  slideInUp: {
    enterActiveClass: 'animate-slide-in-up',
    enterFromClass: 'transform translate-y-full',
    enterToClass: 'transform translate-y-0'
  },
  
  slideInDown: {
    enterActiveClass: 'animate-slide-in-down',
    enterFromClass: 'transform -translate-y-full',
    enterToClass: 'transform translate-y-0'
  },
  
  slideInLeft: {
    enterActiveClass: 'animate-slide-in-left',
    enterFromClass: 'transform -translate-x-full',
    enterToClass: 'transform translate-x-0'
  },
  
  slideInRight: {
    enterActiveClass: 'animate-slide-in-right',
    enterFromClass: 'transform translate-x-full',
    enterToClass: 'transform translate-x-0'
  }
}

// Transition durations
export const durations = {
  fast: 300,
  normal: 600,
  slow: 1000
}

// Easing functions
export const easings = {
  linear: 'linear',
  ease: 'ease',
  easeIn: 'ease-in',
  easeOut: 'ease-out',
  easeInOut: 'ease-in-out'
}

// Create custom transition props
export function createTransition(type, options = {}) {
  const preset = animationPresets[type] || animationPresets.fadeIn
  const duration = options.duration || durations.normal
  const easing = options.easing || easings.easeOut
  const delay = options.delay || 0
  
  return {
    ...preset,
    enterActiveClass: `${preset.enterActiveClass} duration-${duration} ${easing}`,
    enterDelay: delay,
    appear: options.appear || false,
    mode: options.mode || 'out-in'
  }
}

// Stagger animation for lists
export function createStaggerAnimation(baseType, staggerDelay = 100, options = {}) {
  return {
    type: baseType,
    staggerDelay,
    ...options
  }
}

// Hover effects
export const hoverEffects = {
  lift: 'hover-lift',
  scale: 'hover-scale',
  rotate: 'hover-rotate',
  glow: 'hover-glow'
}

// Utility functions
export function addHoverEffect(element, effect) {
  if (!element) return
  
  element.classList.add(hoverEffects[effect] || hoverEffects.lift)
}

export function removeHoverEffect(element, effect) {
  if (!element) return
  
  element.classList.remove(hoverEffects[effect] || hoverEffects.lift)
}

// Click effects
export function addClickEffect(element, effect = 'scale') {
  if (!element) return
  
  switch (effect) {
    case 'scale':
      element.style.transform = 'scale(0.95)'
      element.style.transition = 'transform 0.1s ease-out'
      setTimeout(() => {
        element.style.transform = 'scale(1)'
        element.style.transition = 'transform 0.2s ease-out'
      }, 100)
      break
      
    case 'bounce':
      element.style.animation = 'bounce 0.6s ease-out'
      setTimeout(() => {
        element.style.animation = ''
      }, 600)
      break
      
    case 'shake':
      element.style.animation = 'shake 0.5s ease-in-out'
      setTimeout(() => {
        element.style.animation = ''
      }, 500)
      break
  }
}

// Scroll-triggered animations
export function createScrollAnimation(element, animationClass, threshold = 0.1) {
  if (!element) return
  
  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add(animationClass)
          observer.unobserve(entry.target)
        }
      })
    },
    { threshold }
  )
  
  observer.observe(element)
  return observer
}

// Parallax effect
export function createParallaxEffect(element, speed = 0.5) {
  if (!element) return
  
  const handleScroll = () => {
    const scrolled = window.pageYOffset
    const rate = scrolled * speed
    element.style.transform = `translateY(${rate}px)`
  }
  
  window.addEventListener('scroll', handleScroll)
  return () => window.removeEventListener('scroll', handleScroll)
}

// Export default for easy importing
export default {
  presets: animationPresets,
  durations,
  easings,
  createTransition,
  createStaggerAnimation,
  hoverEffects,
  addHoverEffect,
  removeHoverEffect,
  addClickEffect,
  createScrollAnimation,
  createParallaxEffect
}
