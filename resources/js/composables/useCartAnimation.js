import { ref } from 'vue'
import { toast } from 'vue3-toastify'

export function useCartAnimation() {
  const isAnimating = ref(false)
  const animationTarget = ref(null)
  const flyingItem = ref(null)

  // Trigger cart animation with flying effect
  const triggerCartAnimation = (service, buttonElement = null, cartElement = null) => {
    isAnimating.value = true
    animationTarget.value = cartElement

    // Create flying item element
    if (buttonElement && cartElement) {
      createFlyingItem(service, buttonElement, cartElement)
    }

    // Show success toast with enhanced styling
    toast.success(`✅ ${service.name} added to cart!`, {
      position: 'top-right',
      autoClose: 3000,
      hideProgressBar: false,
      closeOnClick: true,
      pauseOnHover: true,
      draggable: true,
      progress: undefined,
      theme: 'colored',
      style: {
        borderRadius: '8px',
        fontSize: '14px',
        fontWeight: '500'
      }
    })

    // Reset animation state after animation completes
    setTimeout(() => {
      isAnimating.value = false
      animationTarget.value = null
      removeFlyingItem()
    }, 1200)
  }

  // Create flying item element
  const createFlyingItem = (service, buttonElement, cartElement) => {
    // Get button and cart positions
    const buttonRect = buttonElement.getBoundingClientRect()
    const cartRect = cartElement.getBoundingClientRect()

    // Create flying item
    const flyingItemEl = document.createElement('div')
    flyingItemEl.className = 'flying-item'
    flyingItemEl.innerHTML = `
      <div class="flying-item-content">
        <span class="flying-item-icon">🛍️</span>
        <span class="flying-item-text">${service.name}</span>
      </div>
    `

    // Set initial position (button position)
    flyingItemEl.style.position = 'fixed'
    flyingItemEl.style.left = `${buttonRect.left + buttonRect.width / 2}px`
    flyingItemEl.style.top = `${buttonRect.top + buttonRect.height / 2}px`
    flyingItemEl.style.zIndex = '9999'
    flyingItemEl.style.pointerEvents = 'none'
    flyingItemEl.style.transform = 'translate(-50%, -50%)'

    // Add to DOM
    document.body.appendChild(flyingItemEl)
    flyingItem.value = flyingItemEl

    // Calculate control points for curved path
    const startX = buttonRect.left + buttonRect.width / 2
    const startY = buttonRect.top + buttonRect.height / 2
    const endX = cartRect.left + cartRect.width / 2
    const endY = cartRect.top + cartRect.height / 2
    
    // Create a curved path with control points
    const controlX = startX + (endX - startX) * 0.5
    const controlY = Math.min(startY, endY) - 50 // Curve upward

    // Trigger animation after a small delay
    setTimeout(() => {
      // Use keyframes for curved path animation
      flyingItemEl.style.animation = 'flyingItemCurved 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards'
      
      // Set custom properties for the animation
      flyingItemEl.style.setProperty('--start-x', `${startX}px`)
      flyingItemEl.style.setProperty('--start-y', `${startY}px`)
      flyingItemEl.style.setProperty('--control-x', `${controlX}px`)
      flyingItemEl.style.setProperty('--control-y', `${controlY}px`)
      flyingItemEl.style.setProperty('--end-x', `${endX}px`)
      flyingItemEl.style.setProperty('--end-y', `${endY}px`)
    }, 50)
  }

  // Remove flying item
  const removeFlyingItem = () => {
    if (flyingItem.value) {
      document.body.removeChild(flyingItem.value)
      flyingItem.value = null
    }
  }

  // Trigger remove animation
  const triggerRemoveAnimation = (service) => {
    // Show removal toast with enhanced styling
    toast.info(`🗑️ ${service.name} removed from cart`, {
      position: 'top-right',
      autoClose: 2000,
      hideProgressBar: false,
      closeOnClick: true,
      pauseOnHover: true,
      draggable: true,
      progress: undefined,
      theme: 'colored',
      style: {
        borderRadius: '8px',
        fontSize: '14px',
        fontWeight: '500'
      }
    })
  }

  // Trigger cart update animation
  const triggerCartUpdate = (message, type = 'success') => {
    const icon = type === 'success' ? '✅' : type === 'error' ? '❌' : type === 'warning' ? '⚠️' : 'ℹ️'
    
    toast[type](`${icon} ${message}`, {
      position: 'top-right',
      autoClose: 3000,
      hideProgressBar: false,
      closeOnClick: true,
      pauseOnHover: true,
      draggable: true,
      progress: undefined,
      theme: 'colored',
      style: {
        borderRadius: '8px',
        fontSize: '14px',
        fontWeight: '500'
      }
    })
  }

  // Trigger validation error
  const triggerValidationError = (field) => {
    const errorMessages = {
      branch: 'Please select a branch first',
      service: 'Please select a service first',
      date: 'Please select a date first',
      time: 'Please select a time first'
    }
    
    toast.error(`❌ ${errorMessages[field] || 'Please fill in all required fields'}`, {
      position: 'top-right',
      autoClose: 3000,
      hideProgressBar: false,
      closeOnClick: true,
      pauseOnHover: true,
      draggable: true,
      progress: undefined,
      theme: 'colored',
      style: {
        borderRadius: '8px',
        fontSize: '14px',
        fontWeight: '500'
      }
    })
  }

  return {
    isAnimating,
    animationTarget,
    triggerCartAnimation,
    triggerRemoveAnimation,
    triggerCartUpdate,
    triggerValidationError
  }
}
