<script setup>
const props = defineProps({
  currentStep: {
    type: Number,
    required: true
  },
  maxSteps: {
    type: Number,
    default: 3
  }
})

const steps = [
  { number: 1, label: 'Service' },
  { number: 2, label: 'Details' },
  { number: 3, label: 'Done' }
]

const getStepClass = (stepNumber) => {
  if (stepNumber <= props.currentStep) {
    return 'completed'
  } else if (stepNumber === props.currentStep) {
    return 'active'
  }
  return ''
}

const getCircleClass = (stepNumber) => {
  if (stepNumber <= props.currentStep) {
    return 'filled'
  } else if (stepNumber === props.currentStep) {
    return 'active'
  }
  return ''
}
</script>

<template>
  <div class="step-container">
    <template v-for="(step, index) in steps" :key="step.number">
      <div class="step" :class="getStepClass(step.number)">
        <div class="circle" :class="getCircleClass(step.number)">
          <VIcon v-if="(step.number < currentStep) || (step.number === currentStep && props.currentStep === 3)" size="16">tabler-check</VIcon>
          <span v-else>{{ step.number }}</span>
        </div>
        <span class="step-label">{{ step.label }}</span>
      </div>
      
      <!-- Line between steps -->
      <div 
        v-if="index < steps.length - 1" 
        class="line"
        :class="{ 'completed': step.number < currentStep }"
      ></div>
    </template>
  </div>
</template>