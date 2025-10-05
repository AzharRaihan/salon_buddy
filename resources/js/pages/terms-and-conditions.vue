<script setup>
import PolicyDisplay from '@/components/PolicyDisplay.vue'

definePage({
  meta: {
    layout: 'frontend',
    public: true,
  },
})
</script>

<template>
  <PolicyDisplay policy-type="terms_and_conditions" />
</template>
