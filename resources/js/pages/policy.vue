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
  <PolicyDisplay policy-type="privacy_policy" />
</template> 